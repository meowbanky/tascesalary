<?php

require_once 'libs/App.php';
$App = new App();
$App->checkAuthentication();

unset($_SESSION['SESS_MEMBER_ID']);
include 'partials/main.php'; ?>

<head>
    <?php $title = "Lock Screen";
    include 'partials/title-meta.php'; ?>

    <?php include 'partials/head-css.php'; ?>
</head>

<body>

    <div class="bg-gradient-to-r from-rose-100 to-teal-100 dark:from-gray-700 dark:via-gray-900 dark:to-black">

        <!-- ============================================================== -->
        <!-- Start Page Content here -->
        <!-- ============================================================== -->

        <div class="h-screen w-screen flex justify-center items-center">

            <div class="2xl:w-1/4 lg:w-1/3 md:w-1/2 w-full">
                <div class="card overflow-hidden sm:rounded-md rounded-none">
                    <div class="p-6">
                        <div class="flex justify-between">
                            <div class="flex flex-col gap-4 mb-6">
                                <a href="index.php" class="block">
                                    <img class="h-6 block dark:hidden" src="assets/images/logo-dark.png">
                                    <img class="h-6 hidden dark:block" src="assets/images/logo-light.png">
                                </a>
                                <h4 class="text-slate-900 dark:text-slate-200/50 font-semibold">Hi ! <?php echo  $_SESSION['SESS_FIRST_NAME']; ?></h4>
                            </div>

                            <img src="assets/images/users/avatar-1.jpg" alt="user-image" class="h-16 w-16 rounded-full shadow">
                        </div>

                        <div class="mb-4">
                            <input type="hidden" name="username" id="username" value="<?php echo $_SESSION['user']; ?>">
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-200 mb-2" for="loggingPassword">Password</label>
                            <input id="loggingPassword" class="form-input" type="password" placeholder="Enter your password">
                        </div>

                        <div class="flex justify-center mb-6">
                            <button id="submit" class="btn w-full text-white bg-primary"> Log In </button>
                        </div>

                        <div class="flex items-center my-6">
                            <div class="flex-auto mt-px border-t border-dashed border-gray-200 dark:border-slate-700"></div>
                            <div class="mx-4 text-secondary">Or</div>
                            <div class="flex-auto mt-px border-t border-dashed border-gray-200 dark:border-slate-700"></div>
                        </div>


                        <p class="text-gray-500 dark:text-gray-400 text-center">Not you ? return<a href="index.php" class="text-primary ms-1"><b>Log In</b></a></p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ============================================================== -->
    <!-- End Page content -->
    <!-- ============================================================== -->
    <script>
        $("#submit").click(function(event) {
            event.preventDefault(); // Prevent the default form submission
            var username = $("#username").val().trim();
            var password = $("#loggingPassword").val().trim();

            if (username === "") {
                displayAlert('Username is empty', 'center', 'error')
            } else if (password === "") {
                displayAlert('Password field if empty', 'center', 'error')
            } else {
                $.ajax({
                    type: "POST",
                    dataType: 'json',
                    url: "libs/controller.php?act=login",
                    data: {
                        username: username,
                        loggingPassword: password
                    },
                    success: function(response) {
                        if (response.message === "Login successful") {
                            displayAlert(response.message, 'center', 'success')
                            window.location.href = "home.php";
                        } else {
                            displayAlert(response.message, 'center', 'error')
                        }
                    },

                });
            }
        });
    </script>
</body>

</html>