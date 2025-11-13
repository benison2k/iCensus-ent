# iCensus System

A web-based census management system for barangays featuring role-based access, resident profiling, and analytics.

## 📋 Prerequisites

Before setting up the project, ensure you have the following installed:
* **XAMPP** (or any PHP/MySQL environment)
* **Composer** (Dependency Manager for PHP)
* **Web Browser** (Chrome, Edge, etc.)

## 🚀 Installation Guide

Follow these steps to deploy the project on a new machine.

### 1. Database Setup
1.  Start **Apache** and **MySQL** in XAMPP.
2.  Open **phpMyAdmin** (`http://localhost/phpmyadmin`).
3.  Create a new database named `icensus_db`.
4.  Import the latest SQL file located in the `/backups/` folder (e.g., `icensus_db_2025-09-25...sql`).

### 2. Install Dependencies
Open your terminal (PowerShell or Command Prompt), navigate to the project root folder, and run:

```bash
composer install