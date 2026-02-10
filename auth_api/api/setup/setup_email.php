<?php
/**
 * SACOETEC Email Service Setup Script
 * 
 * This script helps you configure and test the email service.
 * Run this script to:
 * 1. Install PHPMailer dependencies
 * 2. Test email configuration
 * 3. Verify SMTP settings
 */

// Load environment variables if .env file exists
if (file_exists(__DIR__ . '/.env')) {
    $envContent = file_get_contents(__DIR__ . '/.env');
    $lines = explode("\n", $envContent);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && !str_starts_with(trim($line), '#')) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

echo "=== SACOETEC Email Service Setup ===\n\n";

// Check if Composer is installed
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "❌ Composer dependencies not found.\n";
    echo "Please run: composer install\n\n";
    echo "If Composer is not installed, download it from: https://getcomposer.org/\n";
    exit(1);
}

// Load Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Check if PHPMailer is available
if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    echo "❌ PHPMailer not found. Please run: composer require phpmailer/phpmailer\n";
    exit(1);
}

echo "✅ Composer dependencies loaded successfully.\n\n";

// Test email configuration
try {
    require_once __DIR__ . '/EmailService.php';
    $emailService = new EmailService();
    
    echo "✅ EmailService initialized successfully.\n\n";
    
    // Test email configuration
    echo "Testing email configuration...\n";
    $testResult = $emailService->testEmailConfiguration();
    echo "Result: $testResult\n\n";
    
    if (strpos($testResult, 'successful') !== false) {
        echo "🎉 Email configuration is working correctly!\n\n";
    } else {
        echo "⚠️  Email configuration test failed. Please check your SMTP settings.\n\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
}

// Display configuration help
echo "=== Configuration Help ===\n\n";

echo "To configure email settings, you can:\n\n";

echo "1. Set environment variables in a .env file:\n";
echo "   SMTP_HOST=smtp.gmail.com\n";
echo "   SMTP_USERNAME=your-email@gmail.com\n";
echo "   SMTP_PASSWORD=your-app-password\n";
echo "   SMTP_PORT=587\n";
echo "   FROM_EMAIL=noreply@sacoetec.edu.ng\n";
echo "   FROM_NAME=SACOETEC\n\n";

echo "2. For Gmail, you'll need to:\n";
echo "   - Enable 2-factor authentication\n";
echo "   - Generate an App Password\n";
echo "   - Use the App Password instead of your regular password\n\n";

echo "3. For other providers:\n";
echo "   - Gmail: smtp.gmail.com:587\n";
echo "   - Outlook: smtp-mail.outlook.com:587\n";
echo "   - Yahoo: smtp.mail.yahoo.com:587\n";
echo "   - SendGrid: smtp.sendgrid.net:587\n\n";

echo "4. Test the configuration:\n";
echo "   php setup_email.php\n\n";

echo "=== Installation Complete ===\n";
echo "Your SACOETEC email service is now ready to use!\n"; 