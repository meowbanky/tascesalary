<?php
if(isset($_GET['logout'])){
    $cookieName = 'token';
    $cookiePath = '/';
    $cookieDomain = '';
    $cookieSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    $cookieHttpOnly = true;
    setcookie($cookieName, '', [
        'expires' => time() - 3600,
        'path' => $cookiePath,
        'domain' => $cookieDomain,
        'secure' => $cookieSecure,
        'httponly' => $cookieHttpOnly
    ]);

// Unset the cookie from the $_COOKIE superglobal
    unset($_COOKIE[$cookieName]);
    unset($_COOKIE['rememberMe']);
}

$error = '';
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'invalid_token':
            $error = 'Invalid token. Please try again.';
            break;
        case 'no_token':
            $error = 'No token received. Please try again.';
            break;
        case 'Email not Found':
            $error = 'Email not Found. Please try again.';
            break;
        default:
            $error = 'An unknown error occurred. Please try again.';
    }
}

require_once 'libs/App.php';
$App = new App();
//$App->checkAuthentication();

include 'partials/main.php'; ?>
<head>
    <?php $title = "Login";
    include 'partials/title-meta.php'; ?>

    <?php include 'partials/head-css.php'; ?>

    <!-- Add Google API script -->
    <script src="https://accounts.google.com/gsi/client" async defer></script>
</head>
<style>
    .password-container {
        position: relative;
    }
    .password-container input {
        padding-right: 2.5rem; /* Add padding to the right for the icon */
    }
    .password-container .toggle-password {
        position: absolute;
        right: 10px;
        top: 70%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #9ca3af; /* Adjust the color to match your design */
    }
</style>
<body>

<div class="bg-gradient-to-r from-rose-100 to-teal-100 dark:from-gray-700 dark:via-gray-900 dark:to-black">

    <!-- ============================================================== -->
    <!-- Start Page Content here -->
    <!-- ============================================================== -->

    <div class="h-screen w-screen flex justify-center items-center">

        <div class="2xl:w-1/4 lg:w-1/3 md:w-1/2 w-full">
            <div class="card overflow-hidden sm:rounded-md rounded-none">
                <form id="loginForm" name="loginForm" class="p-6" method="POST">
                    <a href="index.php" class="block mb-8">
                        <img class="h-6 block dark:hidden" src="assets/images/logo-dark.png" alt="">
                        <img class="h-6 hidden dark:block" src="assets/images/logo-light.png" alt="">
                    </a>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-200 mb-2" for="username">User</label>
                        <input id="username" name="username" class="form-input" type="text" value="" placeholder="Enter your username">
                        <span class="text-danger"></span>
                    </div>

                    <div class="mb-4 password-container">
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-200 mb-2" for="loggingPassword">Password</label>
                        <input id="loggingPassword" name="loggingPassword" class="form-input pr-10" type="password" placeholder="Enter your password">
                        <span class="toggle-password" onclick="togglePassword()">
                                <i class="fas fa-eye" id="togglePasswordIcon"></i>
                        </span>
                    </div>

                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <input type="checkbox" class="form-checkbox rounded" id="checkbox-signin">
                            <label class="ms-2" for="checkbox-signin">Remember me</label>
                        </div>
                        <a href="auth-recoverpw.php" class="text-sm text-primary border-b border-dashed border-primary">Forget Password ?</a>
                    </div>

                    <div class="flex justify-center mb-6">
                        <button class="btn w-full text-white bg-primary"> Log In </button>
                    </div>
                </form>
                <?php if ($error): ?>
                    <div id="error" class="text-center text-red-500 mb-8 font-semibold"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <!-- Google Sign-In Button -->
                <div class="flex justify-center mb-6">
                    <div id="g_id_onload"
                         data-client_id="215488989335-d2afm4pgheil97akasq223v1mnq6k6i4.apps.googleusercontent.com"
                         data-login_uri="https://tascesalary.com.ng/callback.php"
                         data-auto_prompt="false">
                    </div>
                    <div class="g_id_signin"
                         data-type="standard"
                         data-shape="rectangular"
                         data-theme="outline"
                         data-text="sign_in_with"
                         data-size="large"
                         data-logo_alignment="left">
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ============================================================== -->
<!-- End Page content -->
<!-- ============================================================== -->
<script>
    function  displayAlert(title, position, icon) {
        Swal.fire({
            position: position,
            icon: icon,
            title: title,
            showConfirmButton: false,
            timer: 3000
        });
    }

    const rememberMeCheckbox = $('#checkbox-signin');
    var  tokenCheck;

    // Load the remember me state
    if ($.cookie("rememberMe") === 'true') {
        rememberMeCheckbox.prop('checked', true);
    }

    // Save the remember me state
    rememberMeCheckbox.on('change', function() {
        if($(this).is(":checked")) {
                displayAlert("Remember Me is enabled. This means that your login information will be saved and \n " +
                    "automatically entered on this device. Be cautious when using shared or public computers.",
                    "center",
                    "error");
        }
        $.cookie("rememberMe",$(this).is(':checked'));
    });


    // If the "Remember Me" checkbox is checked, retrieve the token from the cookie and send it to the server
    if (rememberMeCheckbox.is(':checked')) {
        checkToken('token', function(tokenCheck) {

        if (tokenCheck) {

            // Here you would send the token to your server using AJAX, for example:
            $.ajax({
                url: 'libs/controller.php?act=tokenlogin',
                type: 'POST',
                // data: { token: rememberMeToken },
                dataType: "json",
                success: function(response) {

                    if (response.message === "Login successful") {
                        displayAlert(response.message, 'center', 'success');
                        window.location.href = "home.php";
                    } else {
                        displayAlert(response.message, 'center', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    // alert(xhr)
                }
            });
        }
        });
    }
    function checkToken(token,callback){
        $.ajax({
            url: 'libs/controller.php?act=checkToken',
            type: 'POST',
            data: { token: token },
            dataType: "json",
            success: function(response) {
                tokenCheck = response.token === true;
                callback(tokenCheck);
            },
            error: function(xhr, status, error) {
                console.error("An error occurred: " + error);
                callback(false);

            }
        });

    }

    function getCookie(name) {
        const nameEQ = name + "=";
        const cookies = document.cookie.split(';');
        // console.log('Cookies:', document.cookie); // Debugging line to see all cookies
        for (let i = 0; i < cookies.length; i++) {
            let c = cookies[i];
            while (c.charAt(0) === ' ') c = c.substring(1);
            if (c.indexOf(nameEQ) === 0) {
                // console.log('Found cookie:', c); // Debugging line to see the matched cookie
                return decodeURIComponent(c.substring(nameEQ.length, c.length));
            }
        }
        return undefined;
    }



    function togglePassword() {
        var passwordInput = document.getElementById("loggingPassword");
        var passwordIcon = document.getElementById("togglePasswordIcon");
        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            passwordIcon.classList.remove("fa-eye");
            passwordIcon.classList.add("fa-eye-slash");
        } else {
            passwordInput.type = "password";
            passwordIcon.classList.remove("fa-eye-slash");
            passwordIcon.classList.add("fa-eye");
        }
    }

    $('#username').focus(function() {
       $('#error').addClass('hidden');
    })

    $("#loginForm").submit(function(event) {
        event.preventDefault();
        var username = $("#username").val().trim();
        var password = $("#loggingPassword").val().trim();

        if (username === "") {
            displayAlert('Username is empty', 'center', 'error');
        } else if (password === "") {
            displayAlert('Password field is empty', 'center', 'error');
        } else {
            $.ajax({
                type: "POST",
                url: "libs/controller.php?act=login",
                data: $(this).serialize()+'&rememberMeCheckbox='+encodeURIComponent(rememberMeCheckbox.is(":checked")),
                dataType: "json",
                success: function(response) {
                    if (response.message === "Login successful") {
                        displayAlert(response.message, 'center', 'success');
                        window.location.href = "home.php";
                    } else {
                        displayAlert(response.message, 'center', 'error');
                    }
                },
            });
        }
    });
</script>
</body>

</html>
