<?php
// automate_mailer.php

require_once 'PayslipMailer.php';

class AutomateMailer {
    private $app;
    private $statusFile;
    private $lockFile;
    private $maxEmailsPerHour = 50;
    private $period;
    private $maxEmailsPerBatch = 10;
    private $maxExecutionTime = 240;

    public function __construct(App $app, string $period) {
        $this->app = $app;
        $this->period = $period;
        // Store status and lock files in a writable directory
        $this->statusFile = __DIR__ . '/mailer_status.json';
        $this->lockFile = __DIR__ . '/mailer_lock.txt';
        set_time_limit($this->maxExecutionTime);
    }

    private function isLocked(): bool {
        if (!file_exists($this->lockFile)) {
            return false;
        }

        $lockTime = filectime($this->lockFile);
        // Consider lock stale after 1 hour
        if (time() - $lockTime > 3600) {
            unlink($this->lockFile);
            return false;
        }
        return true;
    }

    private function lock(): void {
        file_put_contents($this->lockFile, date('Y-m-d H:i:s'));
    }

    private function unlock(): void {
        if (file_exists($this->lockFile)) {
            unlink($this->lockFile);
        }
    }

    private function getStatus(): array {
        if (!file_exists($this->statusFile)) {
            return [
                'last_offset' => 0,
                'last_run' => null,
                'completed' => false,
                'period' => $this->period,
                'emails_sent' => 0
            ];
        }
        return json_decode(file_get_contents($this->statusFile), true);
    }

    private function updateStatus(array $status): void {
        file_put_contents($this->statusFile, json_encode($status));
    }

    private function hasMoreStaffToProcess(int $currentOffset): bool {
        // Get the next batch of staff to check if there are more
        $nextBatch = $this->app->getStaffList($this->maxEmailsPerHour, $currentOffset);
        return !empty($nextBatch);
    }

    public function process(): array {
        if ($this->isLocked()) {
            return ['status' => 'error', 'message' => "Process is locked"];
        }

        try {
            $this->lock();
            $status = $this->getStatus();

            // Check if process is completed
            if ($status['completed']) {
                return ['status' => 'completed', 'message' => "Mailing process already completed"];
            }

            // Initialize mailer
            $mailer = new PayslipMailer($this->app, $this->maxEmailsPerBatch, 2);
            $mailer->setPeriod($this->period);

            // Process small batch
            $startTime = time();
            $processed = $mailer->processBatch();

            // Update status
            $status['last_offset'] += $processed;
            $status['last_run'] = date('Y-m-d H:i:s');
            $status['emails_sent'] += $processed;

            // Check if we've processed all staff
            if (!$this->hasMoreStaffToProcess($status['last_offset'])) {
                $status['completed'] = true;
                $message = "All emails have been sent successfully";
            } else {
                $message = "Processed {$processed} emails. Total processed: {$status['emails_sent']}";
            }

            $this->updateStatus($status);

            return [
                'status' => 'success',
                'message' => $message,
                'processed' => $processed,
                'total_processed' => $status['emails_sent'],
                'completed' => $status['completed']
            ];

        } catch (Exception $e) {
            error_log("Error processing mail batch: " . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        } finally {
            $this->unlock();
        }
    }

    public function reset(): void {
        if (file_exists($this->statusFile)) {
            unlink($this->statusFile);
        }
        if (file_exists($this->lockFile)) {
            unlink($this->lockFile);
        }
        error_log("Mailer process has been reset");
    }
}

// Execute if run from command line
if (php_sapi_name() === 'cli') {
    // You need to specify the period when creating the mailer
    $period = '202311'; // Example period, modify as needed
    $app = new App();
    $mailer = new AutomateMailer($app, $period);
    $mailer->process();
}