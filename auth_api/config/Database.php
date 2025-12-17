<?php
// config/Database.php

// Load .env file if env_loader exists
$envLoaderPath = dirname(__DIR__, 2) . '/config/env_loader.php';
if (file_exists($envLoaderPath)) {
    require_once $envLoaderPath;
}

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;

    public function __construct() {
        // Load from environment variables (required)
        $this->host = getenv('DB_HOST') ?: 'localhost';
        $this->db_name = getenv('DB_NAME') ?: '';
        $this->username = getenv('DB_USER') ?: '';
        $this->password = getenv('DB_PASS') ?: '';
        
        // Validate required values
        if (empty($this->db_name) || empty($this->username) || $this->password === false) {
            throw new Exception('Database configuration is incomplete. Please set DB_NAME, DB_USER, and DB_PASS in your .env file.');
        }
    }

    public function getConnection() {
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $this->conn;
        } catch(PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }
}