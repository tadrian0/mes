<?php
/**
 * PlanningDefaultManager.php
 * -------------------------------------------------
 * Inserts default shift rows for every machine when a date range
 * contains days that have no planning rows at all.
 *
 * Usage (in planning.php):
 *      require_once INCLUDE_PATH . 'PlanningDefaultManager.php';
 *      $defaultMgr = new PlanningDefaultManager($pdo);
 *      $defaultMgr->ensureDefaults($dates, $machineId);
 */

class PlanningDefaultManager
{
    private PDO $pdo;
    private string $planningTable = 'machine_planning';
    private string $machineTable = 'machine';

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Main public method – call it **once** after you have built $dates
     * and (optionally) filtered $machineId.
     *
     * @param array  $dates      ['2025-11-10','2025-11-11',…]
     * @param string $machineId  '' = all machines, otherwise a LIKE pattern
     */
    public function ensureDefaults(array $dates, string $machineId = ''): void
    {
        if (empty($dates)) {
            return;
        }

        $machines = $this->getMachines($machineId);
        if (empty($machines)) {
            return;
        }

        $missing = $this->findMissingCombinations($machines, $dates);
        if (empty($missing)) {
            return;
        }

        $this->insertDefaults($missing);
    }

    private function getMachines(string $filter): array
    {
        $sql = "SELECT MachineID, Name AS machine_name, CONCAT('M', LPAD(MachineID, 3, '0')) AS machine_code
                FROM {$this->machineTable}";
        if ($filter !== '') {
            $sql .= " WHERE CONCAT('M', LPAD(MachineID, 3, '0')) LIKE ?";
        }
        $sql .= " ORDER BY MachineID";

        $stmt = $this->pdo->prepare($sql);
        if ($filter !== '') {
            $stmt->execute(["%$filter%"]);
        } else {
            $stmt->execute();
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function findMissingCombinations(array $machines, array $dates): array
    {
        $missing = [];

        $placeholders = str_repeat('?,', count($dates) - 1) . '?';
        $in = "('" . implode("','", array_fill(0, count($dates), '')) . "')"; 

        $sql = "SELECT machine_code, plan_date
                FROM {$this->planningTable}
                WHERE machine_code IN (SELECT CONCAT('M', LPAD(MachineID, 3, '0')) FROM {$this->machineTable})
                    AND plan_date IN ($placeholders)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($dates);
        $existing = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); 

        foreach ($machines as $m) {
            $code = $m['machine_code'];
            foreach ($dates as $d) {
                if (!isset($existing[$code . $d])) { 
                    $missing[] = ['machine' => $m, 'date' => $d];
                }
            }
        }
        return $missing;
    }

    private function insertDefaults(array $missing): void
    {
        $insertSql = "
            INSERT INTO {$this->planningTable} (
                machine_code, machine_name, plan_date,
                shift1_enabled, shift1_start, shift1_break_start, shift1_break_end, shift1_end,
                shift2_enabled, shift2_start, shift2_break_start, shift2_break_end, shift2_end,
                shift3_enabled, shift3_start, shift3_break_start, shift3_break_end, shift3_end
            ) VALUES (
                ?, ?, ?, 
                ?, ?, ?, ?, ?, 
                ?, ?, ?, ?, ?, 
                ?, ?, ?, ?, ?
            )";

        $stmt = $this->pdo->prepare($insertSql);

        foreach ($missing as $item) {
            $machine = $item['machine'];
            $date = $item['date'];

            $dow = (int) (new DateTime($date))->format('N');
            $isWeekday = $dow <= 5;

            $shifts = [
                1 => ['start' => '06:00', 'end' => '14:00', 'bS' => '10:00', 'bE' => '10:15', 'enabled' => $isWeekday ? 1 : 0],
                2 => ['start' => '14:00', 'end' => '22:00', 'bS' => '18:00', 'bE' => '18:15', 'enabled' => $isWeekday ? 1 : 0],
                3 => ['start' => null, 'end' => null, 'bS' => null, 'bE' => null, 'enabled' => 0],
            ];

            $params = [
                $machine['machine_code'],
                $machine['machine_name'],
                $date,
            ];

            foreach ($shifts as $s) {
                $params[] = $s['enabled'];
                $params[] = $s['start'];
                $params[] = $s['bS'];
                $params[] = $s['bE'];
                $params[] = $s['end'];
            }

            try {
                $stmt->execute($params);
            } catch (PDOException $e) {
                error_log("PlanningDefaultManager insert error (code {$machine['machine_code']} / $date): " . $e->getMessage());
            }
        }
    }
}