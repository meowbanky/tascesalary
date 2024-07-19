# Payroll Management System

A comprehensive Payroll Management System for managing employee salaries, allowances, deductions, and generating payslips. This project is built using PHP and MySQL.

## Features

- Employee Management
- Payroll Processing
- Payslip Generation
- Tax Calculation
- Department Statistics
- Secure PDF Generation
- Email Notifications

## Installation

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Composer
- Web server (Apache, Nginx)

### Step-by-Step Guide

1. **Clone the repository:**
   
   ```sh
   git clone https://github.com/yourusername/payroll-management-system.git
   cd payroll-management-system
2. **CInstall dependencies using Composer:**

   ```sh
   composer install
   
3. **Set up the database:**

   ```sh
   CREATE DATABASE payroll_db;
* 
  **Import the database schema:**
  
  ```sh
  mysql -u yourusername -p payroll_db < database/schema.sql;

4.  **Configure the application:**
   ```sh
  cp config/config.example.php config/config.php

###  Update the configuration file:
define('DB_HOST', 'localhost');
define('DB_NAME', 'payroll_db');
define('DB_USER', 'yourusername');
define('DB_PASS', 'yourpassword');



