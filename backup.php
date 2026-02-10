<?php
// Security: Check if user is authenticated
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['SESS_MEMBER_ID']) || !isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    // Only allow admin users (role_id = 1)
    die('Unauthorized access');
}

// Include database configuration
require_once('config/config.php');

// Configuration
define('BACKUP_DIR', __DIR__ . '/backup/');
define('MAX_BACKUP_AGE', 30); // days
define('MAX_MEMORY_USAGE', '256M');
define('CHUNK_SIZE', 1000); // rows per chunk

// Tables to exclude from backup (large log tables, temporary data, etc.)
define('EXCLUDED_TABLES', ['operation_logs']);

// Set memory limit and execution time
ini_set('memory_limit', MAX_MEMORY_USAGE);
set_time_limit(0); // No time limit for backup process
ini_set('max_execution_time', 0); // Alternative way to set no time limit

class DatabaseBackup {
    private $conn;
    private $backupPath;
    private $logFile;
    private $database;
    private $errors = [];
    
    public function __construct($hostname = null, $username = null, $password = null, $database = null) {
        $this->backupPath = BACKUP_DIR;
        $this->logFile = $this->backupPath . 'backup_log.txt';
        
        // Use config constants if parameters are not provided
        $hostname = $hostname ?: HOST;
        $username = $username ?: USER;
        $password = $password ?: PASS;
        $database = $database ?: DBNAME;
        
        // Log connection details for debugging
        error_log("Database connection attempt: Host=$hostname, User=$username, DB=$database");
        
        // Create backup directory if it doesn't exist
        if (!is_dir($this->backupPath)) {
            error_log("Creating backup directory: " . $this->backupPath);
            if (!mkdir($this->backupPath, 0755, true)) {
                throw new Exception("Failed to create backup directory: " . $this->backupPath . " (Current directory: " . getcwd() . ")");
            }
        }
        
        // Check if directory is writable
        if (!is_writable($this->backupPath)) {
            throw new Exception("Backup directory is not writable: " . $this->backupPath . " (Permissions: " . substr(sprintf('%o', fileperms($this->backupPath)), -4) . ")");
        }
        
        // Connect to database
        error_log("Attempting database connection...");
        $this->conn = new mysqli($hostname, $username, $password, $database);
        if ($this->conn->connect_error) {
            throw new Exception("Connection failed: " . $this->conn->connect_error);
        }
        
        // Set charset
        $this->conn->set_charset("utf8");
        
        // Store database name
        $this->database = $database;
        
        error_log("Database connection successful");
    }
    
    public function createBackup() {
        try {
            $this->log("Starting database backup...");
            
            $backupFile = $this->backupPath . 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $compressedFile = $backupFile . '.gz';
            
            // Clean old backups
            $this->log("Cleaning old backups...");
            $this->cleanOldBackups();
            
            // Try mysqldump first (faster for large databases)
            if ($this->tryMysqldump($backupFile)) {
                $this->log("Used mysqldump for backup");
            } else {
                // Fallback to PHP-based backup
                $this->log("Using PHP-based backup (mysqldump not available)");
                $this->generateBackupFile($backupFile);
            }
            
            // Compress backup file
            $this->log("Compressing backup file...");
            $this->compressFile($backupFile, $compressedFile);
            
            // Verify backup
            $this->log("Verifying backup...");
            if (!$this->verifyBackup($compressedFile)) {
                throw new Exception("Backup verification failed");
            }
            
            // Delete uncompressed file
            if (file_exists($backupFile)) {
                unlink($backupFile);
                $this->log("Cleaned up uncompressed file");
            }
            
            $this->log("Backup completed successfully: " . basename($compressedFile));
            
            return basename($compressedFile);
            
        } catch (Exception $e) {
            $this->log("Backup failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function tryMysqldump($backupFile) {
        try {
            // Check if mysqldump is available
            $mysqldumpPath = $this->findMysqldump();
            if (!$mysqldumpPath) {
                return false;
            }
            
            $this->log("Using mysqldump: " . $mysqldumpPath);
            
            // Build exclude tables parameter
            $excludeTables = EXCLUDED_TABLES;
            $ignoreTables = '';
            foreach ($excludeTables as $table) {
                $ignoreTables .= " --ignore-table=" . DBNAME . "." . $table;
            }
            
            // Build mysqldump command with table exclusions
            $command = sprintf(
                '%s --host=%s --user=%s --password=%s --single-transaction --routines --triggers%s %s > %s 2>&1',
                escapeshellarg($mysqldumpPath),
                escapeshellarg(HOST),
                escapeshellarg(USER),
                escapeshellarg(PASS),
                $ignoreTables,
                escapeshellarg(DBNAME),
                escapeshellarg($backupFile)
            );
            
            $this->log("Executing: " . str_replace(PASS, '***', $command));
            
            // Execute command
            $output = [];
            $returnCode = 0;
            exec($command, $output, $returnCode);
            
            if ($returnCode === 0 && file_exists($backupFile) && filesize($backupFile) > 0) {
                $this->log("mysqldump completed successfully");
                return true;
            } else {
                $this->log("mysqldump failed with return code: " . $returnCode);
                $this->log("Output: " . implode("\n", $output));
                return false;
            }
            
        } catch (Exception $e) {
            $this->log("mysqldump error: " . $e->getMessage());
            return false;
        }
    }
    
    private function findMysqldump() {
        $possiblePaths = [
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
            '/opt/mysql/bin/mysqldump',
            'mysqldump' // Try PATH
        ];
        
        foreach ($possiblePaths as $path) {
            if ($path === 'mysqldump') {
                // Check if it's in PATH
                $output = [];
                exec('which mysqldump 2>/dev/null', $output);
                if (!empty($output)) {
                    return trim($output[0]);
                }
            } else {
                if (file_exists($path) && is_executable($path)) {
                    return $path;
                }
            }
        }
        
        return false;
    }
    
    private function generateBackupFile($backupFile) {
        $fp = fopen($backupFile, 'w');
        if (!$fp) {
            throw new Exception("Failed to create backup file");
        }
        
        // Write header
        fwrite($fp, "-- Database Backup\n");
        fwrite($fp, "-- Generated: " . date('Y-m-d H:i:s') . "\n");
        fwrite($fp, "-- Database: " . $this->database . "\n\n");
        
        // Get all tables
        $tables = $this->getTables();
        
        foreach ($tables as $table) {
            $this->log("Processing table: $table");
            
            // Write table structure
            $this->writeTableStructure($fp, $table);
            
            // Write table data
            $this->writeTableData($fp, $table);
            
            // Check memory usage
            if (memory_get_usage() > 50 * 1024 * 1024) { // 50MB
                gc_collect_cycles();
            }
        }
        
        fclose($fp);
    }
    
    public function getTables() {
        $tables = [];
        $result = $this->conn->query('SHOW TABLES');
        
        if (!$result) {
            throw new Exception("Error fetching tables: " . $this->conn->error);
        }
        
        $excludedTables = EXCLUDED_TABLES;
        
        while ($row = $result->fetch_row()) {
            $tableName = $row[0];
            
            // Skip excluded tables
            if (in_array($tableName, $excludedTables)) {
                $this->log("Skipping excluded table: $tableName");
                continue;
            }
            
            $tables[] = $tableName;
        }
        
        $this->log("Found " . count($tables) . " tables to backup (excluded " . count($excludedTables) . " tables)");
        
        return $tables;
    }
    
    private function writeTableStructure($fp, $table) {
        fwrite($fp, "-- Table structure for table `$table`\n");
        fwrite($fp, "DROP TABLE IF EXISTS `$table`;\n");
        
        $result = $this->conn->query("SHOW CREATE TABLE `$table`");
        if (!$result) {
            throw new Exception("Error getting structure for table $table: " . $this->conn->error);
        }
        
        $row = $result->fetch_row();
        fwrite($fp, $row[1] . ";\n\n");
    }
    
    private function writeTableData($fp, $table) {
        fwrite($fp, "-- Data for table `$table`\n");
        
        // Get total rows
        $countResult = $this->conn->query("SELECT COUNT(*) as count FROM `$table`");
        $totalRows = $countResult->fetch_assoc()['count'];
        
        if ($totalRows == 0) {
            fwrite($fp, "-- Table is empty\n\n");
            return;
        }
        
        $this->log("Processing $totalRows rows from table: $table");
        
        // Process in chunks with progress reporting
        $offset = 0;
        $processedRows = 0;
        
        while ($offset < $totalRows) {
            $query = "SELECT * FROM `$table` LIMIT " . CHUNK_SIZE . " OFFSET $offset";
            $result = $this->conn->query($query);
            
            if (!$result) {
                throw new Exception("Error fetching data from table $table: " . $this->conn->error);
            }
            
            $rows = [];
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
            
            if (!empty($rows)) {
                // Write INSERT statement for this chunk
                $columns = array_keys($rows[0]);
                fwrite($fp, "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES\n");
                
                $values = [];
                foreach ($rows as $row) {
                    $rowValues = array_map(function($value) {
                        if ($value === null) {
                            return 'NULL';
                        }
                        return "'" . $this->conn->real_escape_string($value) . "'";
                    }, array_values($row));
                    
                    $values[] = "(" . implode(', ', $rowValues) . ")";
                }
                
                fwrite($fp, implode(",\n", $values) . ";\n\n");
            }
            
            $processedRows += count($rows);
            $offset += CHUNK_SIZE;
            
            // Report progress every 10 chunks
            if ($offset % (CHUNK_SIZE * 10) == 0 || $offset >= $totalRows) {
                $this->log("Processed $processedRows/$totalRows rows from table: $table");
            }
            
            // Force output buffer flush to show progress
            if (ob_get_level()) {
                ob_flush();
                flush();
            }
            
            // Check memory usage and clean up if needed
            if (memory_get_usage() > 50 * 1024 * 1024) { // 50MB
                gc_collect_cycles();
            }
        }
        
        $this->log("Completed processing table: $table");
    }
    
    private function compressFile($source, $destination) {
        $this->log("Compressing backup file...");
        
        $sourceFile = fopen($source, 'rb');
        $destFile = gzopen($destination, 'wb9');
        
        if (!$sourceFile || !$destFile) {
            throw new Exception("Failed to open files for compression");
        }
        
        while (!feof($sourceFile)) {
            gzwrite($destFile, fread($sourceFile, 4096));
        }
        
        fclose($sourceFile);
        gzclose($destFile);
    }
    
    private function verifyBackup($backupFile) {
        $this->log("Verifying backup file...");
        
        if (!file_exists($backupFile)) {
            return false;
        }
        
        $fileSize = filesize($backupFile);
        if ($fileSize < 1024) { // Less than 1KB
            return false;
        }
        
        return true;
    }
    
    private function cleanOldBackups() {
        $this->log("Cleaning old backups...");
        
        $files = glob($this->backupPath . 'backup_*.sql.gz');
        $cutoff = time() - (MAX_BACKUP_AGE * 24 * 60 * 60);
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
                $this->log("Deleted old backup: " . basename($file));
            }
        }
    }
    
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] $message\n";
        
        // Write to log file (with error handling)
        if ($this->logFile && is_writable(dirname($this->logFile))) {
            file_put_contents($this->logFile, $logMessage, FILE_APPEND | LOCK_EX);
        }
        
        // Also output for immediate feedback
        echo $logMessage;
        flush();
    }
    
    public function downloadBackup($filename) {
        $filepath = $this->backupPath . $filename;
        
        if (!file_exists($filepath)) {
            throw new Exception("Backup file not found");
        }
        
        // Clear any output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set headers for download
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));
        
        // Read and output file
        $handle = fopen($filepath, 'rb');
        while (!feof($handle)) {
            echo fread($handle, 8192);
            flush();
        }
        fclose($handle);
        
        exit;
    }
    
    public function deleteBackup($filename) {
        $filepath = $this->backupPath . $filename;
        
        // Security: Only allow deletion of backup files
        if (!preg_match('/^backup_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.sql\.gz$/', $filename)) {
            throw new Exception("Invalid backup filename");
        }
        
        if (!file_exists($filepath)) {
            throw new Exception("Backup file not found");
        }
        
        if (!is_writable($filepath)) {
            throw new Exception("Backup file is not writable");
        }
        
        if (!unlink($filepath)) {
            throw new Exception("Failed to delete backup file");
        }
        
        return true;
    }
    
    public function getBackupList() {
        $files = glob($this->backupPath . 'backup_*.sql.gz');
        $backups = [];
        
        foreach ($files as $file) {
            $backups[] = [
                'filename' => basename($file),
                'size' => $this->formatBytes(filesize($file)),
                'date' => date('Y-m-d H:i:s', filemtime($file))
            ];
        }
        
        // Sort by date (newest first)
        usort($backups, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        
        return $backups;
    }
    
    private function formatBytes($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}

// Handle backup creation
if (isset($_POST['action']) && $_POST['action'] === 'create_backup') {
    // Set headers to prevent timeout
    header('Content-Type: application/json');
    header('Cache-Control: no-cache');
    
    // Start output buffering to capture log messages
    ob_start();
    
    try {
        // Log the attempt
        error_log("Backup creation started at " . date('Y-m-d H:i:s'));
        
        $backup = new DatabaseBackup();
        $filename = $backup->createBackup();
        
        // Get the log output
        $logOutput = ob_get_clean();
        
        error_log("Backup creation successful: " . $filename);
        
        echo json_encode([
            'success' => true,
            'message' => 'Backup created successfully',
            'filename' => $filename,
            'log' => $logOutput
        ]);
    } catch (Exception $e) {
        // Get any log output before the error
        $logOutput = ob_get_clean();
        
        // Log the error
        error_log("Backup creation failed: " . $e->getMessage());
        error_log("Backup error trace: " . $e->getTraceAsString());
        
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage(),
            'log' => $logOutput,
            'error_details' => [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]
        ]);
    }
    exit;
}

// Handle backup download
if (isset($_GET['action']) && $_GET['action'] === 'download' && isset($_GET['file'])) {
    try {
        $backup = new DatabaseBackup();
        $backup->downloadBackup($_GET['file']);
    } catch (Exception $e) {
        die('Download failed: ' . $e->getMessage());
    }
}

// Handle backup deletion
if (isset($_POST['action']) && $_POST['action'] === 'delete_backup' && isset($_POST['file'])) {
    header('Content-Type: application/json');
    
    try {
        $backup = new DatabaseBackup();
        $backup->deleteBackup($_POST['file']);
        
        echo json_encode([
            'success' => true,
            'message' => 'Backup file deleted successfully'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Delete failed: ' . $e->getMessage()
        ]);
    }
    exit;
}
?>