<?php
// PayslipControl.php
class PayslipControl {
    private $app;

    public function __construct(App $app) {
        $this->app = $app;
    }

    /**
     * Get total active employees and current sending progress
     */
    public function getProgress(): array {
        $sql = "SELECT COUNT(*) as total_employees 
                FROM employee 
                WHERE email IS NOT NULL AND STATUSCD = 'A'";
        $totalResult = $this->app->selectOne($sql);

        $sql2 = "SELECT last_offset 
                 FROM email_offset 
                 ORDER BY id DESC LIMIT 1";
        $offsetResult = $this->app->selectOne($sql2);

        $sql3 = "SELECT status 
                 FROM email_process_status 
                 ORDER BY id DESC LIMIT 1";
        $statusResult = $this->app->selectOne($sql3);

        $totalEmployees = (int)$totalResult['total_employees'];
        $processedCount = (int)($offsetResult['last_offset'] ?? 0);

        return [
            'total_employees' => $totalEmployees,
            'processed_count' => $processedCount,
            'remaining_count' => $totalEmployees - $processedCount,
            'progress_percentage' => $totalEmployees > 0
                ? round(($processedCount / $totalEmployees) * 100, 2)
                : 0,
            'status' => $statusResult['status'] ?? 'stopped'
        ];
    }

    /**
     * Reset the sending progress
     */
    public function resetProgress(): bool {
        try {
            // Reset the offset
            $sql1 = "INSERT INTO email_offset (last_offset) VALUES (0)";
            $this->app->executeNonSelect($sql1);

            // Update process status
            $sql2 = "INSERT INTO email_process_status (status, updated_at) 
                     VALUES ('stopped', NOW())";
            $this->app->executeNonSelect($sql2);

            return true;
        } catch (Exception $e) {
            error_log("Failed to reset progress: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Stop the sending process
     */
    public function stopProcess(): bool {
        try {
            $sql = "INSERT INTO email_process_status (status, updated_at) 
                    VALUES ('stopped', NOW())";
            $this->app->executeNonSelect($sql);
            return true;
        } catch (Exception $e) {
            error_log("Failed to stop process: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Start the sending process
     */
    public function startProcess(): bool {
        try {
            $sql = "INSERT INTO email_process_status (status, updated_at) 
                    VALUES ('running', NOW())";
            $this->app->executeNonSelect($sql);
            return true;
        } catch (Exception $e) {
            error_log("Failed to start process: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if the process should continue running
     */
    public function shouldContinue(): bool {
        $sql = "SELECT status FROM email_process_status ORDER BY id DESC LIMIT 1";
        $result = $this->app->selectOne($sql);
        return $result['status'] === 'running';
    }
}