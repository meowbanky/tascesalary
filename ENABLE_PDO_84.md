# Enabling PDO Extension for PHP 8.4 on CloudLinux/cPanel

Your server has PHP 8.4.15 and PDO is compiled but not enabled. Here's how to enable it:

## Method 1: cPanel MultiPHP INI Editor (Easiest - Recommended)

1. **Log into cPanel**
2. Navigate to **"MultiPHP INI Editor"** (or find it under "Software" section)
3. Select **PHP 8.4** version
4. Select your domain or use "Global Settings"
5. Look for the **"Extensions"** section or scroll to find extension settings
6. Enable/check the following extensions:
   - ✅ `pdo`
   - ✅ `pdo_mysql`
7. Click **"Save"** at the bottom
8. Wait a few seconds for changes to apply
9. Test again

## Method 2: Select PHP Version (Alternative in cPanel)

1. Go to **"Select PHP Version"** in cPanel
2. Select PHP 8.4
3. Click on **"Extensions"** or **"Options"** button
4. Enable `pdo` and `pdo_mysql`
5. Save changes

## Method 3: Create Extension INI File (Advanced)

If you have SSH access or file manager access:

1. Navigate to: `/opt/alt/php84/link/conf/`
2. Create a new file: `20-pdo.ini`
3. Add this content:
   ```ini
   extension=pdo.so
   extension=pdo_mysql.so
   ```
4. Save the file
5. Restart PHP (or wait for auto-reload)

## Method 4: Use .htaccess (Quick Fix)

Add these lines to your `.htaccess` file in `public_html`:

```apache
<IfModule mod_lsapi.c>
    php_value extension pdo.so
    php_value extension pdo_mysql.so
</IfModule>
```

Or try:

```apache
<IfModule mod_suphp.c>
    suPHP_ConfigPath /opt/alt/php84/etc
</IfModule>
php_value extension pdo.so
php_value extension pdo_mysql.so
```

## Method 5: Contact Hosting Support

If the above methods don't work, contact DoveServer support and ask them to:
- Enable PDO extension for PHP 8.4 on your account
- Enable PDO MySQL driver for PHP 8.4

## Verification

After enabling, verify by accessing:
- `https://tascesalary.com.ng/test2.php` - Should show PDO as loaded
- `https://tascesalary.com.ng/enable_pdo.php` - Detailed check

Or create a simple test:

```php
<?php
if (class_exists('PDO')) {
    echo "PDO is enabled!";
} else {
    echo "PDO is still not enabled";
}
?>
```

## Important Notes

- Extension files are located at: `/opt/alt/php84/usr/lib64/php/modules/`
- Configuration directory: `/opt/alt/php84/link/conf/`
- After enabling, you may need to wait 30-60 seconds for changes to take effect
- Clear any opcode cache if you're using one

