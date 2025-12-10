<?php
require_once 'includes/UserManager.php';
require_once 'includes/Database.php';
require_once 'includes/IsAdmin.php';
require_once 'includes/Config.php';
require_once 'includes/PlanningDefaultManager.php';

$isAdmin = isAdmin();
$readOnly = !$isAdmin;

$userManager = new UserManager($pdo);

function calculateOreZi($row)
{
    $totalSeconds = 0;
    for ($shift = 1; $shift <= 3; $shift++) {
        if ($row["shift{$shift}_enabled"]) {
            $start = new DateTime($row["shift{$shift}_start"] ?: '00:00:00');
            $end = new DateTime($row["shift{$shift}_end"] ?: '00:00:00');
            $breakStart = new DateTime($row["shift{$shift}_break_start"] ?: '00:00:00');
            $breakEnd = new DateTime($row["shift{$shift}_break_end"] ?: '00:00:00');

            $shiftSeconds = ($end->getTimestamp() - $start->getTimestamp()) - ($breakEnd->getTimestamp() - $breakStart->getTimestamp());
            $totalSeconds += $shiftSeconds > 0 ? $shiftSeconds : 0;
        }
    }
    return number_format($totalSeconds / 3600, 2);
}

$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d');
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$machineId = isset($_GET['machine_id']) ? $_GET['machine_id'] : '';

$dates = [];
$currentDate = new DateTime($startDate);
$end = new DateTime($endDate);
$end->modify('+1 day');
while ($currentDate < $end) {
    $dates[] = $currentDate->format('Y-m-d');
    $currentDate->modify('+1 day');
}

$defaultMgr = new PlanningDefaultManager($pdo);
$defaultMgr->ensureDefaults($dates, $machineId);

$machineFilter = $machineId ? "machine_code LIKE :machine_id" : "1=1";
$stmt = $pdo->prepare("SELECT DISTINCT machine_code, machine_name FROM machine_planning WHERE $machineFilter ORDER BY machine_code");
if ($machineId)
    $stmt->bindValue(':machine_id', "%$machineId%");
$stmt->execute();
$machines = $stmt->fetchAll(PDO::FETCH_ASSOC);

$rows = [];
foreach ($machines as $machine) {
    for ($d = 0; $d < count($dates); $d++) {
        $date = $dates[$d];
        $stmt = $pdo->prepare("SELECT * FROM machine_planning WHERE machine_code = :code AND plan_date = :date");
        $stmt->execute(['code' => $machine['machine_code'], 'date' => $date]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            $row = [
                'id' => null,
                'machine_code' => $machine['machine_code'],
                'machine_name' => $machine['machine_name'],
                'plan_date' => $date,
                'shift1_enabled' => 0,
                'shift1_start' => null,
                'shift1_break_start' => null,
                'shift1_break_end' => null,
                'shift1_end' => null,
                'shift2_enabled' => 0,
                'shift2_start' => null,
                'shift2_break_start' => null,
                'shift2_break_end' => null,
                'shift2_end' => null,
                'shift3_enabled' => 0,
                'shift3_start' => null,
                'shift3_break_start' => null,
                'shift3_break_end' => null,
                'shift3_end' => null,
            ];
        }
        $rows[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MES Backoffice - Planning</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="<?= $siteBaseUrl ?>styles/backoffice.css" rel="stylesheet" />
</head>

<body>
    <?php include 'includes/Sidebar.php'; ?>

    <div class="content">
        <h1>Planning</h1>

        <form method="GET" class="mb-4">
            <div class="row g-3">
                <div class="col-auto">
                    <label for="start_date" class="form-label">Start Date:</label>
                    <input type="date" id="start_date" name="start_date"
                        value="<?php echo htmlspecialchars($startDate); ?>" class="form-control">
                </div>
                <div class="col-auto">
                    <label for="end_date" class="form-label">End Date:</label>
                    <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($endDate); ?>"
                        class="form-control">
                </div>
                <div class="col-auto">
                    <label for="machine_id" class="form-label">Machine ID:</label>
                    <input type="text" id="machine_id" name="machine_id"
                        value="<?php echo htmlspecialchars($machineId); ?>" class="form-control">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </div>
        </form>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Edit</th>
                    <th>Machine</th>
                    <th>Date | Ore Zi</th>
                    <th>Shift 1 Start</th>
                    <th>Break Start</th>
                    <th>Break End</th>
                    <th>Shift End</th>
                    <th>Enabled</th>
                    <th>Shift 2 Start</th>
                    <th>Break Start</th>
                    <th>Break End</th>
                    <th>Shift End</th>
                    <th>Enabled</th>
                    <th>Shift 3 Start</th>
                    <th>Break Start</th>
                    <th>Break End</th>
                    <th>Shift End</th>
                    <th>Enabled</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td>
                            <?php if ($row['id']): ?>
                                <a href="edit.php?id=<?php echo htmlspecialchars($row['id']); ?>"
                                    class="btn btn-sm btn-warning <?php echo $readOnly ? 'disabled-link' : ''; ?>" <?php echo $readOnly ? 'disabled' : ''; ?>>Edit</a>
                            <?php else: ?>
                                <a href="edit.php?machine_code=<?php echo htmlspecialchars($row['machine_code']); ?>&date=<?php echo htmlspecialchars($row['plan_date']); ?>"
                                    class="btn btn-sm btn-primary <?php echo $readOnly ? 'disabled-link' : ''; ?>" <?php echo $readOnly ? 'disabled' : ''; ?>>Create</a>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['machine_code'] . ' - ' . $row['machine_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['plan_date'] . ' | ' . calculateOreZi($row)); ?></td>
                        <td><?php echo htmlspecialchars($row['shift1_start'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['shift1_break_start'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['shift1_break_end'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['shift1_end'] ?? ''); ?></td>
                        <td><?php echo $row['shift1_enabled'] ? 'Yes' : 'No'; ?></td>
                        <td><?php echo htmlspecialchars($row['shift2_start'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['shift2_break_start'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['shift2_break_end'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['shift2_end'] ?? ''); ?></td>
                        <td><?php echo $row['shift2_enabled'] ? 'Yes' : 'No'; ?></td>
                        <td><?php echo htmlspecialchars($row['shift3_start'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['shift3_break_start'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['shift3_break_end'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['shift3_end'] ?? ''); ?></td>
                        <td><?php echo $row['shift3_enabled'] ? 'Yes' : 'No'; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>

</html>