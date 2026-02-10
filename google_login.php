<?php
require_once 'vendor/autoload.php';

session_start();

// Load environment variables
require_once __DIR__ . '/config/env_loader.php';

// Replace with your client ID and client secret from environment variables
$clientID = getenv('GOOGLE_CLIENT_ID');
$clientSecret = getenv('GOOGLE_CLIENT_SECRET');
$redirectUri = getenv('GOOGLE_REDIRECT_URI');

// Create Client Request to access Google API
$client = new Google_Client();
$client->setClientId($clientID);
$client->setClientSecret($clientSecret);
$client->setRedirectUri($redirectUri);
$client->addScope("email");
$client->addScope("profile");

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $_SESSION['access_token'] = $token;
    $client->setAccessToken($token);

    // Get user profile data from google
    $google_oauth = new Google_Service_Oauth2($client);
    $google_account_info = $google_oauth->userinfo->get();
    $email =  $google_account_info->email;
    $name =  $google_account_info->name;

    // Now you can store the user data into your database
    // Redirect to your desired page
    header('Location: home.php');
    exit();
} else {
    $login_url = $client->createAuthUrl();
}
?>
