<?php
require_once 'libs/App.php';
require_once 'vendor/autoload.php';
$App = new App();

// Replace with your client ID and client secret
$clientID = '215488989335-d2afm4pgheil97akasq223v1mnq6k6i4.apps.googleusercontent.com';
$clientSecret = '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';

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
