<div align="center">

# TASCE Staff Salary & Payroll System

[![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-3.0-38B2AC?logo=tailwind-css&logoColor=white)](https://tailwindcss.com/)
[![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Google Auth](https://img.shields.io/badge/Auth-Google%20SSO-4285F4?logo=google&logoColor=white)](https://developers.google.com/identity)

**Enterprise-grade payroll automation system for educational institutions.**  
Streamlines salary computation, statutory deductions, and comprehensive reporting with a modern interface.

[Live Demo](#) • [Documentation](#) • [Report Bug](https://github.com/meowbanky/tascesalary/issues)

</div>

---

## ✨ Overview

The TASCE Payroll System is a comprehensive solution designed to automate salary management for educational institutions. It handles complex payroll calculations including tax deductions, pension contributions, allowances, and generates detailed financial reports with enterprise-level security and user management.

## 🎯 Key Features

### Payroll Automation
- **Batch Processing**: Process payroll for thousands of staff members in a single operation
- **Automatic Calculations**: Handles consolidated salary, allowances, and deductions based on grade and step levels
- **Statutory Compliance**: Automated tax, pension, and other statutory deductions
- **Period Management**: Flexible pay period configuration and historical tracking

### Financial Reporting
- **Bank Reconciliation**: Export formats compatible with banking systems for direct processing
- **Department Analytics**: Breakdown by departments with comparative analysis
- **Variance Analysis**: Month-over-month salary variance tracking and reporting
- **Pension Reports**: Detailed PFA (Pension Fund Administrator) analysis and summaries
- **Tax Exports**: Standardized formats for tax filing and compliance

### Document Generation
- **Bulk Payslip Generation**: PDF payslips for all staff in batch operations
- **Individual Payslips**: On-demand payslip generation with email delivery
- **Excel Exports**: Exportable reports in Excel format for further analysis
- **Print-Ready Formats**: Professional formatting suitable for official documentation

### Security & Access Control
- **Google OAuth Integration**: Single Sign-On (SSO) using Google accounts
- **Role-Based Access**: Granular permissions for Bursary, Audit, HR, and Staff roles
- **Session Management**: Secure session handling with token-based authentication
- **Audit Logging**: Comprehensive activity logs for compliance and security

### Data Management
- **Excel Import**: Bulk staff data updates via Excel templates
- **Profile Management**: Staff profiles with photo uploads and metadata
- **Database Backups**: Automated backup scheduling and management
- **Data Integrity**: Validation and consistency checks across all operations

## 🛠️ Technology Stack

- **Backend**: PHP 8.2+ (Procedural & OOP)
- **Frontend**: Tailwind CSS 3.0, Alpine.js
- **Database**: MySQL 5.7+
- **Authentication**: Google OAuth 2.0, JWT tokens
- **PDF Generation**: TCPDF, FPDF
- **Excel Processing**: PhpSpreadsheet
- **Email**: PHPMailer with SMTP
- **Package Management**: Composer

## 📋 Prerequisites

- PHP 8.2 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Composer
- Node.js & npm (for frontend assets)

## 🚀 Installation

### 1. Clone the Repository

```bash
git clone https://github.com/meowbanky/tascesalary.git
cd tascesalary
```

### 2. Install Dependencies

```bash
# PHP dependencies
composer install

# Frontend dependencies (optional, for Tailwind compilation)
npm install
```

### 3. Environment Configuration

Create a `.env` file in the root directory:

```bash
cp .env.example .env
```

Configure your environment variables:

```env
# Database Configuration
DB_HOST=localhost
DB_NAME=your_database_name
DB_USER=your_database_user
DB_PASS=your_database_password

# Email Configuration (SMTP)
MAIL_HOST=your_smtp_host
MAIL_USERNAME=your_email@example.com
MAIL_PASSWORD=your_email_password
MAIL_PORT=587
MAIL_ENCRYPTION=PHPMailer::ENCRYPTION_STARTTLS
MAIL_SENDER_NAME=Your Institution Name

# JWT Configuration (for API)
JWT_SECRET=your_jwt_secret_key_here
JWT_EXPIRY=3600
```

See [ENV_SETUP.md](ENV_SETUP.md) for detailed configuration instructions.

### 4. Database Setup

Create the database and import the schema:

```bash
mysql -u root -p
CREATE DATABASE tasce_payroll CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;
```

Import the database schema (if provided):

```bash
mysql -u root -p tasce_payroll < database/schema.sql
```

### 5. Web Server Configuration

#### Apache
Ensure mod_rewrite is enabled and point your document root to the project directory.

#### Nginx
Configure your server block to point to the project directory.

### 6. Permissions

Ensure the web server has write permissions to necessary directories:

```bash
chmod -R 755 uploads/
chmod -R 755 backup/
```

## 📖 Usage

### Running Payroll

1. Navigate to **Run Payroll** from the main menu
2. Select the pay period
3. Review calculations
4. Execute payroll processing

### Generating Reports

- **Payslips**: Individual or bulk generation available
- **Bank Summary**: Export bank schedules for payment processing
- **Tax Reports**: Generate tax-compliant export files
- **Variance Analysis**: Compare salary changes between periods

### Staff Management

- Import staff data via Excel template (`template.xlsx`)
- Manage staff profiles, grades, and steps
- Update allowances and deductions per staff member

## 🔒 Security Best Practices

- Never commit `.env` files to version control
- Use strong JWT secrets for API authentication
- Regularly rotate database credentials
- Implement SSL/TLS for production deployments
- Review audit logs regularly
- Follow principle of least privilege for user roles

## 📁 Project Structure

```
tascesalary/
├── assets/              # Static assets (CSS, JS, images)
├── auth_api/           # REST API for authentication
├── auth_app/           # Mobile application source
├── config/             # Configuration files
│   ├── config.php      # Main configuration
│   └── env_loader.php  # Environment variable loader
├── libs/               # Core PHP libraries
│   ├── App.php         # Main application class
│   └── *.php           # Feature-specific modules
├── partials/           # Reusable UI components
├── view/               # View templates
├── vendor/             # Composer dependencies
└── README.md
```

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 License

This project is proprietary software developed for TASCE (Tai Solarin College of Education).

## 👥 Authors

- **Development Team** - [TASCE IT Department]

## 🙏 Acknowledgments

- TCPDF community for PDF generation capabilities
- PhpSpreadsheet for Excel processing
- Tailwind CSS for the modern UI framework

---

<div align="center">
Made with ❤️ for TASCE
</div>
