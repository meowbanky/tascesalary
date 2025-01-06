<?php
// mailer_interface.php

header('Content-Type: application/json');
require_once 'automate_mailer.php';

class MailerInterface {
    private $app;
    private $mailer;

    public function __construct() {
        $this->app = new App();
        $this->mailer = new AutomateMailer($this->app, date('Ym'));
    }

    public function handleRequest() {
        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'process_batch':
                return $this->mailer->process();

            case 'reset':
                $this->mailer->reset();
                return ['status' => 'success', 'message' => 'Process reset'];

            case 'status':
                return $this->getStatus();

            default:
                return ['status' => 'error', 'message' => 'Invalid action'];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $interface = new MailerInterface();
    echo json_encode($interface->handleRequest());
}