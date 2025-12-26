# Experimental MES (Manufacturing Execution System)

![Status](https://img.shields.io/badge/Status-Work_In_Progress-orange)
![License](https://img.shields.io/badge/License-MIT-blue)
![PHP](https://img.shields.io/badge/PHP-8.2-777BB4)
![MySQL](https://img.shields.io/badge/Database-MariaDB%2FMySQL-003545)

**Warning: This project is currently in an experimental Alpha stage.** It is intended for educational purposes and proof-of-concept testing for industrial manufacturing environments. It is **not** yet ready for production deployment.

## 📖 Overview

This is an open-source Manufacturing Execution System (MES) designed to bridge the gap between the planning system (ERP) and the shop floor (SCADA/PLC/Operators). It focuses on tracking the transformation of raw materials to finished goods, managing machine downtime, quality control, and operator logs in real-time.

The architecture allows for multi-plant management, detailed traceability, and hierarchical factory definition (Country -> City -> Plant -> Section -> Machine).

## 🚀 Key Features (Implemented)

* **Factory Topology:** Hierarchical management of Countries, Cities, Plants, and Sections.
* **Machine Registry:** Detailed asset tracking with status monitoring and capacity definitions.
* **Production Tracking:**
    * Operator Login/Logout logs (Time tracking).
    * Production Order Runs (Start/Stop events with automatic duration calculation).
    * Batch Generation & Labeling (Finished Goods vs. WIP).
* **Downtime Management:** Tracking machine stops (categorized by Mechanical, Electrical, Process, etc.) with reason codes.
* **Quality Control:** Reject management with specific reasons scoped to Plants or Sections.
* **Traceability:** Raw material consumption logging against specific Production Orders.
* **Security:**
    * Role-based Admin login.
    * API Key Management system with auditing (Creation, Usage, Revocation).

## 🗺️ Roadmap & To-Do List

We are actively working on the following modules to bring the system to maturity:

- [x] **Multi-plant Management** (Basic structure implemented, refinement needed)
- [ ] **Plant Filtering** (Global context switching for users)
- [ ] **Shift Planning** (Calendar view and shift assignment)
- [ ] **Permission System** (Granular RBAC beyond simple Admin/Operator)
- [ ] **Qualification Matrix** (Tracking operator certifications)
- [ ] **Operator Skill Management** (Linking skills to machine operation rights)
- [ ] **Inventory System** (SAP-like logic for stock movements)
- [ ] **Totem View** (Simplified Touch-UI for shop floor operators)
- [ ] **Data Analysis & Exports** (Excel, PDF reports, and ZPL printable labels)
- [ ] **Installer** (Automated database structure generation and configuration wizard)
- [ ] **Containerization** (Docker support)

## 🛠️ Technology Stack

* **Backend:** PHP 8.x (Native/Vanilla)
* **Database:** MySQL / MariaDB
* **Frontend:** HTML5, CSS3, Bootstrap 5
* **Scripting:** jQuery, DataTables.js (for dynamic sorting/filtering)
* **Server:** Apache (XAMPP recommended for dev)

## 💾 Database Schema (Snapshot)

The system relies on a relational database structure designed for integrity and traceability.

**Core Entities:**
* `plant`, `section`, `machine` (Assets)
* `user` (Operators/Admins)
* `production_order`, `batch_log` (Execution)
* `machine_stop_log`, `reject` (Events)
* `api_keys`, `api_audit_log` (Security)

## 📦 Installation & Setup

Currently, the project is configured for a standard XAMPP/LAMP stack manual deployment.

### Prerequisites
* XAMPP (or Apache/PHP/MySQL stack) installed.
* Git.

### Steps

1.  **Clone the repository:**
    Navigate to your web server's root directory (e.g., `C:\xampp\htdocs`).
    ```bash
    cd C:\xampp\htdocs
    git clone [https://github.com/tadrian0/mes.git](https://github.com/tadrian0/mes.git) mes
    ```

2.  **Database Configuration:**
    * Create a new database named `xooiduyr_mes` (or similar).
    * **Import Data:** Please contact the repository author to request the `dummy_data.sql` file or the latest structure dump (e.g. `xooiduyr_mes.sql`).
    * Import the SQL file via phpMyAdmin or CLI:
        ```bash
        mysql -u root -p xooiduyr_mes < xooiduyr_mes.sql
        ```

3.  **App Configuration:**
    * Rename `includes/Config.example.php` to `includes/Config.php` (if applicable) and check DB credentials:
        ```php
        $host = '127.0.0.1';
        $db   = 'xooiduyr_mes';
        $user = 'root';
        $pass = '';
        ```

4.  **Access:**
    * Open your browser and navigate to: `http://localhost/mes/login.php`

## 🤝 Contributing

Contributions are welcome! Please follow these steps:
1.  Fork the repository.
2.  Create a feature branch (`git checkout -b feature/AmazingFeature`).
3.  Commit your changes (`git commit -m 'Add some AmazingFeature'`).
4.  Push to the branch (`git push origin feature/AmazingFeature`).
5.  Open a Pull Request.

## 📄 License

Distributed under the MIT License. See `LICENSE` for more information.

---
*Note: This project is under active development. Database structures are subject to change.*