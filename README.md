<div align="center">

# TASCE Staff Salary & Payroll System

[![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-3.0-38B2AC?logo=tailwind-css&logoColor=white)](https://tailwindcss.com/)
[![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Google Auth](https://img.shields.io/badge/Auth-Google%20SSO-4285F4?logo=google&logoColor=white)](https://developers.google.com/identity)

**Enterprise-grade payroll automation system for educational institutions.**  
Streamlines salary computation, statutory deductions, and comprehensive reporting with a modern interface.

[Live Demo](https://tascesalary.com.ng/) • [Documentation](#) • [Report Bug](https://github.com/meowbanky/tascesalary/issues)

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

- **Bank Reconciliation**: Export formats compatible with banking systems for direct payment processing (`report_net2bank.php`)
- **Department Analytics**: Payroll breakdown by departments with comparative analysis (`report_payrollbydept.php`)
- **Variance Analysis**: Month-over-month salary variance tracking and reporting (`variance.php`)
- **Pension Reports**: Comprehensive PFA (Pension Fund Administrator) analysis with individual and summary reports
  - Aggregate pension reports by PFA with suspended staff tracking
  - Individual staff pension history with period range queries
- **Gross Pay Reports**: Detailed breakdown of all allowances and earnings (`report_grosspay.php`)
- **Deduction Lists**: Complete listing of all deductions and allowances (`report_deductionlist.php`)
- **Tax Exports**: Standardized formats for tax filing and compliance (`exportfortax.php`)
- **Payroll Summary**: Consolidated payroll overview with totals and statistics

### Document Generation

- **Bulk Payslip Generation**: PDF payslips for all staff in batch operations with automated email delivery
- **Individual Payslips**: On-demand payslip generation with secure PDF protection
- **Excel Exports**: All reports exportable in Excel format (`.xlsx`) for further analysis
- **PDF Reports**: Professional PDF generation for all financial reports (Pension, Bank Summary, Gross Pay, etc.)
- **Print-Ready Formats**: Professional formatting with institutional branding suitable for official documentation
- **Email Integration**: Automated email delivery of payslips and reports via SMTP

### Security & Access Control

- **Google OAuth Integration**: Single Sign-On (SSO) using Google accounts
- **Role-Based Access**: Granular permissions for Bursary, Audit, HR, and Staff roles
- **Session Management**: Secure session handling with token-based authentication
- **Audit Logging**: Comprehensive activity logs for compliance and security

### Data Management

- **Excel Import**: Bulk staff data updates via Excel templates (`template.xlsx`)
- **Profile Management**: Staff profiles with photo uploads, grade/step tracking, and metadata
- **Compensation Management**: Flexible allowance and deduction configuration per staff member
- **Suspension Handling**: Support for staff suspension with prorated salary calculations
- **Grade & Step System**: Salary table management with grade/step-based calculations
- **Database Backups**: Automated backup scheduling and management with export capabilities
- **Audit Logging**: Comprehensive activity logs tracking all system changes and user actions
- **Data Integrity**: Validation and consistency checks across all operations

## 🛠️ Technology Stack

- **Backend**: PHP 8.2+ (Procedural & OOP)
- **Frontend**: Tailwind CSS 3.0, Alpine.js
- **Database**: MySQL 5.7+
- **Authentication**: Google OAuth 2.0, JWT tokens, Session-based authentication
- **PDF Generation**: TCPDF, FPDF (with custom headers, footers, and branding)
- **Excel Processing**: PhpSpreadsheet (import/export capabilities)
- **Email**: PHPMailer with SMTP (bulk email delivery for payslips)
- **Mobile App**: Flutter/Dart application for staff access (`auth_app/`)
- **REST API**: JSON API endpoints for mobile app integration (`auth_api/`)
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

The system provides comprehensive reporting capabilities accessible from the Reports menu:

- **Payslips**: Individual (`report_payslipone.php`) or bulk (`report_payslipall.php`) generation
- **Bank Summary**: Export bank schedules for payment processing with account numbers
- **Net to Bank**: Detailed net pay exports for bank reconciliation
- **Pension Reports**:
  - Aggregate pension by PFA (all PFAs or individual)
  - Individual staff pension history across date ranges
- **Gross Pay Summary**: Complete breakdown of all earnings and allowances
- **Deduction Lists**: Comprehensive listing of all deductions
- **Payroll by Department**: Department-wise salary breakdown
- **Variance Analysis**: Compare salary changes between pay periods
- **Tax Exports**: Standardized formats for tax compliance

All reports support PDF and Excel export formats with email delivery options.

### Staff Management

- **Bulk Import**: Import staff data via Excel template (`template.xlsx`)
- **Profile Management**: Staff profiles with photo uploads, personal details, and employment information
- **Compensation Setup**: Configure grades, steps, allowances, and deductions per staff member
- **Suspension Management**: Handle staff suspensions with automatic proration calculations
- **Bank Account Management**: Associate bank accounts and account numbers with staff
- **PFA Assignment**: Link staff to Pension Fund Administrators (PFAs) with PIN tracking

### Mobile Access

The system includes a Flutter mobile application (`auth_app/`) that provides:

- Staff authentication via REST API
- Payslip viewing and download
- Payroll history access
- Secure token-based authentication

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
├── assets/              # Static assets (CSS, JS, images, fonts)
├── auth_api/           # REST API for mobile app authentication
│   ├── api/            # API endpoints
│   ├── config/         # API configuration
│   └── models/         # Data models
├── auth_app/           # Flutter mobile application
│   ├── lib/            # Dart source code
│   ├── android/        # Android build configuration
│   └── ios/            # iOS build configuration
├── config/             # Configuration files
│   ├── config.php      # Main configuration (uses .env)
│   └── env_loader.php  # Environment variable loader
├── libs/               # Core PHP libraries
│   ├── App.php         # Main application class
│   ├── controller.php  # Request handler
│   ├── PayslipMailer.php  # Payslip email delivery
│   ├── generate_*.php  # Report generators (PDF/Excel)
│   └── get_report_*.php # Report data handlers
├── partials/           # Reusable UI components
│   ├── sidenav.php     # Navigation sidebar
│   ├── topbar.php      # Top navigation bar
│   └── footer.php      # Page footer
├── view/               # View templates
│   └── view_*.php      # Report and data views
├── report_*.php        # Report page controllers
├── vendor/             # Composer dependencies
├── .env.example        # Environment variables template
├── ENV_SETUP.md        # Environment setup documentation
└── README.md           # This file
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

- **Bankole Abiodun** - Lead Developer

## 🙏 Acknowledgments

- TCPDF community for PDF generation capabilities
- PhpSpreadsheet for Excel processing
- Tailwind CSS for the modern UI framework

---

<div align="center">
Made with ❤️ for TASCE
</div>
