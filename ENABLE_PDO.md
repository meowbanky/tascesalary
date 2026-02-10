# Enabling PDO Extension on cPanel/CloudLinux

Based on your phpinfo output, PDO is compiled but not enabled. Here's how to enable it:

## Method 1: Using cPanel MultiPHP INI Editor (Recommended)

1. **Log into cPanel**
2. Navigate to **"MultiPHP INI Editor"** or **"Select PHP Version"**
3. Select your domain or use the global settings
4. Find **"extension=pdo"** and enable it
5. Find **"extension=pdo_mysql"** and enable it
6. Click **"Save"**
7. Clear any caches and test again

## Method 2: Using .htaccess (If you have file access)

Add these lines to your `.htaccess` file in the public_html directory:

```apache
<IfModule mod_php.c>
    php_flag extension pdo
    php_flag extension pdo_mysql
</IfModule>
```

Or for CloudLinux/LiteSpeed:

```apache
<IfModule Litespeed>
    php_value extension pdo.so
    php_value extension pdo_mysql.so
</IfModule>
```

## Method 3: Create php.ini in your directory

Create a file named `php.ini` in your `public_html` directory with:

```ini
extension=pdo.so
extension=pdo_mysql.so
```

## Method 4: Contact Hosting Support

If the above methods don't work, contact your hosting provider (DoveServer) and ask them to:
- Enable PDO extension for PHP 8.1
- Enable PDO MySQL driver for PHP 8.1

## Verification

After enabling, you can verify by:
1. Upload `check_pdo.php` to your server
2. Access it via browser: `https://tascesalary.com.ng/check_pdo.php`
3. You should see both extensions marked as "LOADED"

## Quick Test

After enabling PDO, test with this simple script:

```php
<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=test", "user", "pass");
    echo "PDO is working!";
} catch (PDOException $e) {
    echo "PDO Error: " . $e->getMessage();
}
?>
```

