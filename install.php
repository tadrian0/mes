<?php
if (file_exists('includes/Database.php')) {
    // If installed, redirect to index
    header('Location: index.php');
    exit;
}

$message = '';
$status = ''; // 'success' or 'error'

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = $_POST['host'] ?? 'localhost';
    $dbname = $_POST['dbname'] ?? 'mes';
    $user = $_POST['user'] ?? 'root';
    $password = $_POST['password'] ?? '';
    $siteUrl = $_POST['site_url'] ?? 'http://localhost/mes/';

    try {
        // Validate dbname to prevent SQL injection and ensure valid name
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $dbname)) {
            throw new Exception("Database name must contain only alphanumeric characters and underscores.");
        }

        // 1. Connect to MySQL Server (no DB selected yet)
        $pdo = new PDO("mysql:host=$host", $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        // 2. Create Database
        // We validated $dbname so it is safe to use in query
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$dbname`");

        // 3. Create Tables
        $queries = [
            "CREATE TABLE IF NOT EXISTS user (
                OperatorID INT AUTO_INCREMENT PRIMARY KEY,
                OperatorUsername VARCHAR(255) NOT NULL UNIQUE,
                OperatorPassword VARCHAR(255) NOT NULL,
                OperatorRoles VARCHAR(255) NOT NULL
            )",
            "CREATE TABLE IF NOT EXISTS api_keys (
                KeyID INT AUTO_INCREMENT PRIMARY KEY,
                KeyString VARCHAR(64) NOT NULL UNIQUE,
                Name VARCHAR(255) NOT NULL,
                UserID INT NOT NULL,
                Permissions TEXT,
                ScopePlants TEXT,
                LastUsedAt DATETIME,
                IsActive BOOLEAN DEFAULT 1,
                CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (UserID) REFERENCES user(OperatorID) ON DELETE CASCADE
            )",
            "CREATE TABLE IF NOT EXISTS api_audit_log (
                LogID INT AUTO_INCREMENT PRIMARY KEY,
                KeyID INT,
                UserID INT,
                Action VARCHAR(50),
                Endpoint VARCHAR(255),
                IPAddress VARCHAR(45),
                Details TEXT,
                Timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS country (
                CountryID INT AUTO_INCREMENT PRIMARY KEY,
                Name VARCHAR(255) NOT NULL,
                ISOCode VARCHAR(10) NOT NULL
            )",
            "CREATE TABLE IF NOT EXISTS city (
                CityID INT AUTO_INCREMENT PRIMARY KEY,
                Name VARCHAR(255) NOT NULL,
                CountryID INT NOT NULL,
                PostalCode VARCHAR(20),
                FOREIGN KEY (CountryID) REFERENCES country(CountryID) ON DELETE CASCADE
            )",
            "CREATE TABLE IF NOT EXISTS plant (
                PlantID INT AUTO_INCREMENT PRIMARY KEY,
                Name VARCHAR(255) NOT NULL,
                Description TEXT,
                CityID INT,
                Address TEXT,
                ContactEmail VARCHAR(255),
                ContactPhone VARCHAR(50),
                ManagerName VARCHAR(255),
                Status VARCHAR(50) DEFAULT 'Active',
                FOREIGN KEY (CityID) REFERENCES city(CityID) ON DELETE SET NULL
            )",
            "CREATE TABLE IF NOT EXISTS section (
                SectionID INT AUTO_INCREMENT PRIMARY KEY,
                Name VARCHAR(255) NOT NULL,
                PlantID INT NOT NULL,
                Description TEXT,
                FloorAreaSqM FLOAT,
                MaxCapacity INT,
                FOREIGN KEY (PlantID) REFERENCES plant(PlantID) ON DELETE CASCADE
            )",
            "CREATE TABLE IF NOT EXISTS machine (
                MachineID INT AUTO_INCREMENT PRIMARY KEY,
                Name VARCHAR(255) NOT NULL,
                Status VARCHAR(50) DEFAULT 'Active',
                Capacity FLOAT DEFAULT 0,
                LastMaintenanceDate DATE,
                Location VARCHAR(255),
                Model VARCHAR(255),
                PlantID INT,
                SectionID INT,
                FOREIGN KEY (PlantID) REFERENCES plant(PlantID) ON DELETE SET NULL,
                FOREIGN KEY (SectionID) REFERENCES section(SectionID) ON DELETE SET NULL
            )",
            "CREATE TABLE IF NOT EXISTS article (
                ArticleID INT AUTO_INCREMENT PRIMARY KEY,
                Name VARCHAR(255) NOT NULL,
                Description TEXT,
                ImagePath VARCHAR(255),
                QualityControl VARCHAR(50) DEFAULT 'Pending',
                CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
                UpdatedAt DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS production_recipes (
                RecipeID INT AUTO_INCREMENT PRIMARY KEY,
                ArticleID INT NOT NULL,
                MachineID INT NOT NULL,
                Version VARCHAR(50) NOT NULL,
                EstimatedTime FLOAT NOT NULL,
                OperationDescription TEXT,
                IsActive TINYINT(1) DEFAULT 1,
                Notes TEXT,
                FOREIGN KEY (ArticleID) REFERENCES article(ArticleID) ON DELETE CASCADE,
                FOREIGN KEY (MachineID) REFERENCES machine(MachineID) ON DELETE CASCADE
            )",
             "CREATE TABLE IF NOT EXISTS recipe_inputs (
                InputID INT AUTO_INCREMENT PRIMARY KEY,
                RecipeID INT NOT NULL,
                ArticleID INT NOT NULL,
                Quantity FLOAT NOT NULL,
                Unit VARCHAR(50) DEFAULT 'unit',
                InputType VARCHAR(50) DEFAULT 'part',
                CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
                UpdatedAt DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (RecipeID) REFERENCES production_recipes(RecipeID) ON DELETE CASCADE,
                FOREIGN KEY (ArticleID) REFERENCES article(ArticleID) ON DELETE CASCADE
            )",
            "CREATE TABLE IF NOT EXISTS recipe_outputs (
                OutputID INT AUTO_INCREMENT PRIMARY KEY,
                RecipeID INT NOT NULL,
                ArticleID INT NOT NULL,
                Quantity FLOAT NOT NULL,
                Unit VARCHAR(50) DEFAULT 'unit',
                IsPrimary TINYINT(1) DEFAULT 1,
                CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
                UpdatedAt DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (RecipeID) REFERENCES production_recipes(RecipeID) ON DELETE CASCADE,
                FOREIGN KEY (ArticleID) REFERENCES article(ArticleID) ON DELETE CASCADE
            )",
            "CREATE TABLE IF NOT EXISTS production_order (
                OrderID INT AUTO_INCREMENT PRIMARY KEY,
                ArticleID INT NOT NULL,
                RecipeID INT,
                TargetQuantity FLOAT NOT NULL,
                PlannedStartDate DATETIME,
                PlannedEndDate DATETIME,
                Status VARCHAR(50) DEFAULT 'Planned',
                IsDeleted TINYINT(1) DEFAULT 0,
                CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
                DeletedAt DATETIME,
                DeletedBy INT,
                ActualStartDate DATETIME,
                FOREIGN KEY (ArticleID) REFERENCES article(ArticleID) ON DELETE CASCADE,
                FOREIGN KEY (RecipeID) REFERENCES production_recipes(RecipeID) ON DELETE SET NULL
            )",
             "CREATE TABLE IF NOT EXISTS reject_category (
                CategoryID INT AUTO_INCREMENT PRIMARY KEY,
                CategoryName VARCHAR(255) NOT NULL,
                PlantID INT,
                SectionID INT,
                FOREIGN KEY (PlantID) REFERENCES plant(PlantID) ON DELETE CASCADE,
                FOREIGN KEY (SectionID) REFERENCES section(SectionID) ON DELETE CASCADE
            )",
            "CREATE TABLE IF NOT EXISTS reject_reason (
                ReasonID INT AUTO_INCREMENT PRIMARY KEY,
                ReasonName VARCHAR(255) NOT NULL,
                CategoryID INT NOT NULL,
                PlantID INT,
                SectionID INT,
                FOREIGN KEY (CategoryID) REFERENCES reject_category(CategoryID) ON DELETE CASCADE,
                FOREIGN KEY (PlantID) REFERENCES plant(PlantID) ON DELETE CASCADE,
                FOREIGN KEY (SectionID) REFERENCES section(SectionID) ON DELETE CASCADE
            )"
        ];

        foreach ($queries as $sql) {
            $pdo->exec($sql);
        }

        // 4. Seed Data
        // Users
        $users = [
            ['admin', 'admin123', 'admin'],
            ['operator1', 'op123', 'operator'],
            ['operator2', 'op123', 'operator'],
            ['operator3', 'op123', 'operator'],
            ['operator4', 'op123', 'operator'],
        ];
        $stmt = $pdo->prepare("INSERT INTO user (OperatorUsername, OperatorPassword, OperatorRoles) VALUES (?, ?, ?)");
        foreach ($users as $u) {
            // Check if user exists
            $check = $pdo->prepare("SELECT COUNT(*) FROM user WHERE OperatorUsername = ?");
            $check->execute([$u[0]]);
            if ($check->fetchColumn() == 0) {
                 $stmt->execute($u);
            }
        }

        // Helper for random data
        function getRandom($pdo, $table, $col) {
            $stmt = $pdo->query("SELECT $col FROM $table ORDER BY RAND() LIMIT 1");
            return $stmt->fetchColumn();
        }

        // Countries
        $stmt = $pdo->prepare("INSERT INTO country (Name, ISOCode) VALUES (?, ?)");
        for ($i = 1; $i <= 50; $i++) {
            $stmt->execute(["Country $i", "C$i"]);
        }

        // Cities
        $stmt = $pdo->prepare("INSERT INTO city (Name, CountryID, PostalCode) VALUES (?, ?, ?)");
        for ($i = 1; $i <= 50; $i++) {
            $cid = getRandom($pdo, 'country', 'CountryID');
            $stmt->execute(["City $i", $cid, "100$i"]);
        }

        // Plants
        $stmt = $pdo->prepare("INSERT INTO plant (Name, Description, CityID, Address, ContactEmail, ContactPhone, ManagerName, Status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        for ($i = 1; $i <= 50; $i++) {
            $cid = getRandom($pdo, 'city', 'CityID');
            $stmt->execute(["Plant $i", "Description for Plant $i", $cid, "Address $i", "plant$i@example.com", "555-010$i", "Manager $i", "Active"]);
        }

        // Sections
        $stmt = $pdo->prepare("INSERT INTO section (Name, PlantID, Description, FloorAreaSqM, MaxCapacity) VALUES (?, ?, ?, ?, ?)");
        for ($i = 1; $i <= 50; $i++) {
            $pid = getRandom($pdo, 'plant', 'PlantID');
            $stmt->execute(["Section $i", $pid, "Section Desc $i", rand(100, 1000), rand(10, 100)]);
        }

        // Machines
        $stmt = $pdo->prepare("INSERT INTO machine (Name, Status, Capacity, LastMaintenanceDate, Location, Model, PlantID, SectionID) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        for ($i = 1; $i <= 50; $i++) {
            $pid = getRandom($pdo, 'plant', 'PlantID');
            // get a section from this plant
            $sid = $pdo->query("SELECT SectionID FROM section WHERE PlantID = $pid ORDER BY RAND() LIMIT 1")->fetchColumn();
            if (!$sid) $sid = null;

            $stmt->execute(["Machine $i", 'Active', rand(100, 500), date('Y-m-d'), "Loc $i", "Model $i", $pid, $sid]);
        }

        // Articles (Parts) - 4 forms
        $forms = ['Raw Steel Coil', 'Stamped Part', 'Washed Part', 'Packaged Part'];
        $stmt = $pdo->prepare("INSERT INTO article (Name, Description, QualityControl) VALUES (?, ?, ?)");
        for ($i = 1; $i <= 50; $i++) {
            $form = $forms[$i % 4];
            $stmt->execute(["$form $i", "Description for $form $i", "Pending"]);
        }

        // Recipes
        $stmt = $pdo->prepare("INSERT INTO production_recipes (ArticleID, MachineID, Version, EstimatedTime, OperationDescription, IsActive, Notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
        for ($i = 1; $i <= 50; $i++) {
             $aid = getRandom($pdo, 'article', 'ArticleID');
             $mid = getRandom($pdo, 'machine', 'MachineID');
             $stmt->execute([$aid, $mid, "v1.$i", rand(10, 300), "Operation $i", 1, "Notes $i"]);
        }

        // Production Orders
        $stmt = $pdo->prepare("INSERT INTO production_order (ArticleID, RecipeID, TargetQuantity, PlannedStartDate, PlannedEndDate, Status) VALUES (?, ?, ?, ?, ?, ?)");
        for ($i = 1; $i <= 50; $i++) {
            $rid = getRandom($pdo, 'production_recipes', 'RecipeID');
            // Get article from recipe
            $r = $pdo->query("SELECT ArticleID, EstimatedTime FROM production_recipes WHERE RecipeID = $rid")->fetch(PDO::FETCH_ASSOC);
            $aid = $r['ArticleID'];
            $qty = rand(100, 1000);
            $duration = $r['EstimatedTime'] * $qty;

            $start = date('Y-m-d H:i:s', strtotime("+$i days"));
            $end = date('Y-m-d H:i:s', strtotime($start) + $duration);

            $stmt->execute([$aid, $rid, $qty, $start, $end, 'Planned']);
        }

        // Reject Categories
        $stmt = $pdo->prepare("INSERT INTO reject_category (CategoryName, PlantID, SectionID) VALUES (?, ?, ?)");
        for ($i = 1; $i <= 50; $i++) {
             $pid = getRandom($pdo, 'plant', 'PlantID');
             $stmt->execute(["Reject Cat $i", $pid, null]);
        }

         // Reject Reasons
        $stmt = $pdo->prepare("INSERT INTO reject_reason (ReasonName, CategoryID, PlantID) VALUES (?, ?, ?)");
        for ($i = 1; $i <= 50; $i++) {
             $cid = getRandom($pdo, 'reject_category', 'CategoryID');
             // get plant from cat
             $cat = $pdo->query("SELECT PlantID FROM reject_category WHERE CategoryID = $cid")->fetch(PDO::FETCH_ASSOC);
             $pid = $cat['PlantID'];

             $stmt->execute(["Reason $i", $cid, $pid]);
        }

        // 5. Create Config.php
        if (!is_dir('includes')) {
            mkdir('includes', 0755, true);
        }

        $configContent = "<?php\n";
        $configContent .= "\$siteBaseUrl = " . var_export($siteUrl, true) . ";\n";
        $configContent .= "define('INCLUDE_PATH', __DIR__ . '/includes/');\n";
        $configContent .= "?>";
        file_put_contents('includes/Config.php', $configContent);

        // 6. Create Database.php
        $dbContent = "<?php\n";
        $dbContent .= "\$host = " . var_export($host, true) . ";\n";
        $dbContent .= "\$dbname = " . var_export($dbname, true) . ";\n";
        $dbContent .= "\$username = " . var_export($user, true) . ";\n";
        $dbContent .= "\$password = " . var_export($password, true) . ";\n\n";
        $dbContent .= "try {\n";
        $dbContent .= "    \$pdo = new PDO(\"mysql:host=\$host;dbname=\$dbname\", \$username, \$password, [\n";
        $dbContent .= "        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION\n";
        $dbContent .= "    ]);\n";
        $dbContent .= "} catch (PDOException \$e) {\n";
        $dbContent .= "    die('Connection failed: ' . \$e->getMessage());\n";
        $dbContent .= "}\n";
        $dbContent .= "?>";
        file_put_contents('includes/Database.php', $dbContent);

        $message = "Installation successful! Redirecting...";
        $status = 'success';

        header("refresh:3;url=index.php");

    } catch (Exception $e) {
        $message = "Installation failed: " . $e->getMessage();
        $status = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MES Installation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">MES System Installation</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-<?= $status === 'success' ? 'success' : 'danger' ?>">
                                <?= htmlspecialchars($message) ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($status !== 'success'): ?>
                        <p>Welcome! Please provide the database connection details to install the system.</p>
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">Database Host</label>
                                <input type="text" name="host" class="form-control" value="localhost" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Database Name</label>
                                <input type="text" name="dbname" class="form-control" value="mes" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Database User</label>
                                <input type="text" name="user" class="form-control" value="root" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Database Password</label>
                                <input type="password" name="password" class="form-control" value="mes">
                            </div>
                             <div class="mb-3">
                                <label class="form-label">Site URL</label>
                                <input type="text" name="site_url" class="form-control" value="http://<?= $_SERVER['HTTP_HOST'] ?>/mes/" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Install & Seed Database</button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>