# Environment Variables Setup

This project uses environment variables to store sensitive configuration data. Follow these steps to set up your environment:

## Setup Instructions

1. **Create a `.env` file** in the root directory (`tasceSalary/.env`)

2. **Copy the example file** to get started:
   ```bash
   cp .env.example .env
   ```

3. **Edit the `.env` file** with your actual configuration values:

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
   MAIL_DEBUG=0
   MAIL_SENDER_NAME=Your Company Name

   # JWT Configuration (for API)
   JWT_SECRET=your_jwt_secret_key_here
   JWT_EXPIRY=3600

   # Google API Key (if used)
   GOOGLE_API_KEY=your_google_api_key_here
   ```

4. **Never commit the `.env` file** to version control. It is already included in `.gitignore`.

## Security Notes

- The `.env` file contains sensitive information and should be kept secure
- Do not share your `.env` file publicly
- Each environment (development, staging, production) should have its own `.env` file
- The `.env.example` file is safe to commit as it contains only placeholder values

## Configuration Files

The following files have been updated to read from environment variables:

- `config/config.php` - Main application configuration
- `auth_api/config/config.php` - API configuration
- `auth_api/config/Database.php` - Database connection

If environment variables are not set, these files will fall back to default values (which are now safe defaults, not production values).

## Fallback Behavior

The configuration files will:
1. First try to load values from environment variables (`.env` file)
2. If not found, use safe default values
3. This ensures the application works even if `.env` is not configured, but you should always configure it for production use

