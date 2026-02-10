<?php
require_once 'libs/App.php';
require_once 'vendor/autoload.php';
$App = new App();

// Load environment variables
require_once __DIR__ . '/config/env_loader.php';

// Replace with your client ID from environment variables
$clientID = getenv('GOOGLE_CLIENT_ID');
$clientSecret = getenv('GOOGLE_CLIENT_SECRET');

// Initialize Google Client
$client = new Google_Client(['client_id' => $clientID]);  // Specify the CLIENT_ID of the app that accesses the backend

if (isset($_POST['credential'])) {
    $id_token = $_POST['credential'];

    // Verify the ID token
    $payload = $client->verifyIdToken($id_token);
    if ($payload) {
        $userid = $payload['sub'];
        $email = $payload['email'];
        $name = $payload['name'];
        $picture = $payload['picture']; // Retrieve the profile picture URL

        // Store user data into session
        $_SESSION['email'] = $email;
        $_SESSION['name'] = $name;
        $_SESSION['picture'] = $picture;
        $App->googlelogin($email);
        exit();
    } else {
        // Invalid ID token
        header('Location: index.php?error=invalid_token');
        exit();
    }
} else {
    // No ID token received
    header('Location: index.php?error=no_token');
    exit();
}
?>
