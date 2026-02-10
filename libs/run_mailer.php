// run_mailer.php
<?php
require_once 'AutomateMailer.php';
require_once 'App.php';

$app = new App();
$periods = $app->getPeriodToRun();
$period = $periods['periodId'];
$mailer = new AutomateMailer($app, $period);
$mailer->process();