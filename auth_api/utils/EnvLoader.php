<?php
/**
 * Environment Loader for SACOETEC
 * 
 * This class handles loading environment variables from .env files
 * and provides fallback values for configuration.
 */

class EnvLoader {
    private static $loaded = false;
    private static $envFile = null;
    
    /**
     * Load environment variables from .env file
     * 
     * @param string $envFile Path to .env file (optional)
     * @return bool True if loaded successfully, false otherwise
     */
    public static function load($envFile = null) {
        if (self::$loaded) {
            return true;
        }
        
        // Determine .env file path
        if ($envFile === null) {
            $envFile = self::findEnvFile();
        }
        
        if (!$envFile || !file_exists($envFile)) {
            error_log("Warning: .env file not found at: " . ($envFile ?? 'default location'));
            self::$loaded = true;
            return false;
        }
        
        self::$envFile = $envFile;
        
        // Read and parse .env file
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Skip comments and empty lines
            if (strpos(trim($line), '#') === 0 || empty(trim($line))) {
                continue;
            }
            
            // Parse key=value pairs
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remove quotes if present
                if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                    (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                    $value = substr($value, 1, -1);
                }
                
                // Set environment variable
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
        
        self::$loaded = true;
        error_log("Environment loaded from: $envFile");
        return true;
    }
    
    /**
     * Find .env file in common locations
     * 
     * @return string|null Path to .env file or null if not found
     */
    private static function findEnvFile() {
        $possiblePaths = [
            __DIR__ . '/.env',
            __DIR__ . '/../.env',
            __DIR__ . '/../../.env',
            dirname(__DIR__) . '/.env',
        ];
        
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        
        return null;
    }
    
    /**
     * Get environment variable with fallback
     * 
     * @param string $key Environment variable key
     * @param mixed $default Default value if not found
     * @return mixed Environment variable value or default
     */
    public static function get($key, $default = null) {
        // Load .env if not already loaded
        if (!self::$loaded) {
            self::load();
        }
        
        // Check $_ENV first (PHP 7.1+)
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }
        
        // Check getenv() as fallback
        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }
        
        return $default;
    }
    
    /**
     * Check if .env file was loaded
     * 
     * @return bool True if .env was loaded
     */
    public static function isLoaded() {
        return self::$loaded;
    }
    
    /**
     * Get the path of the loaded .env file
     * 
     * @return string|null Path to .env file or null if not loaded
     */
    public static function getEnvFilePath() {
        return self::$envFile;
    }
    
    /**
     * Reload environment variables
     * 
     * @param string $envFile Path to .env file (optional)
     * @return bool True if reloaded successfully
     */
    public static function reload($envFile = null) {
        self::$loaded = false;
        return self::load($envFile);
    }
} 