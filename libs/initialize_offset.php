<?php
// initialize_offset.php
require_once '../libs/App.php';

$App = new App();

// Initialize the offset to 0
$App->initializeOffset();

echo 'Offset initialized and batch processing started.';
