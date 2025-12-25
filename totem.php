<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MES Totem</title>

    <link rel="stylesheet" href="totem.css"/>
</head>

<body>

<div id="header">
    <div id="header-left">
        <button>Login</button>

        <div id="operators">
            <div class="operator-slot">Op 1</div>
            <div class="operator-slot">Op 2</div>
            <div class="operator-slot">Op 3</div>
            <div class="operator-slot">Op 4</div>
            <div class="operator-slot">Op 5</div>
            <div class="operator-slot">Op 6</div>
        </div>

        <button class="secondary">Logout</button>
    </div>

    <div id="logo">
        <img src="PATH_TO_LOGO.png" alt="MES Logo">
    </div>
</div>

<!-- ===== MAIN BODY ===== -->
<div id="main">

    <!-- === PRODUCTION AREA === -->
    <div id="production-area">

        <div class="panel">
            <h3>Production Status</h3>
            <p>State: <strong>Working / Setup / No Order</strong></p>
            <p>Produced: <strong>0 / 0</strong></p>

            <div class="progress-container">
                <div class="progress-bar"></div>
            </div>
        </div>

        <div class="panel">
            <h3>Performance</h3>
            <p>Stroke Rate: <strong>-- / min</strong></p>
            <p>Expected Prod/hour: <strong>--</strong></p>
            <p>Actual Prod/hour: <strong>--</strong></p>
            <p>Operators: <strong>Expected -- / Actual --</strong></p>
        </div>

        <div class="panel">
            <h3>Production Order</h3>
            <p>Order No: --</p>
            <p>Part Number: --</p>
            <p>Description: --</p>
            <p>Packaging: Gitterbox</p>
            <p>Qty / Packaging: 1000</p>

            <button>Stop Production</button>
            <button class="secondary">Suspend</button>
            <button class="secondary">Adjustments</button>
        </div>

    </div>

    <!-- === MACHINE STATUS === -->
    <div id="machine-panel">
        <div class="panel">
            <h3>Machine</h3>
            <p>Status: <span class="status-working">WORKING</span></p>
            <p>Clock: <strong>--:--:--</strong></p>
            <button class="secondary">Stop Reason</button>
            <button class="secondary">Warn</button>
        </div>
    </div>

</div>

<!-- ===== FOOTER ===== -->
<div id="footer">

    <div id="qc" class="footer-section">
        <h3>Quality Control</h3>
        <p>Bad Parts (Rebuturi)</p>
        <button>Add Defect</button>
    </div>

    <div id="raw-material" class="footer-section">
        <h3>Raw Material</h3>
        <button>Scan</button>
        <button>Print Remaining Label</button>
        <button class="secondary">History</button>
    </div>

    <div id="labels" class="footer-section">
        <h3>Container Labels</h3>
        <button>Print at Current Qty</button>
        <button class="secondary">Quick End & Print</button>
        <button class="secondary">History</button>
    </div>

</div>

</body>
</html>
