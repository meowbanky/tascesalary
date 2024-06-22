
<?php include 'partials/main.php'; ?>
<?php
    $_SESSION['error'] = null;
    if(isset($_POST['email'])){
        $email = $_POST['email'];
        if (strlen($email)==0) {
            $_SESSION['error'] =  "Please enter a email";
            $_POST['email'] = null;
        }else{
        if(checkAuth($email)===true){
            header('Location: index.php');
            die();
        }else{
            $_SESSION['error'] =  "Email is not valid";
        }
    }
    }

?>
<head>
    <?php $title = "Login";
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
                    <form id="loginForm" name= "loginForm" class="p-6" method="POST">
                        <a href="index.php" class="block mb-8">
                            <img class="h-6 block dark:hidden" src="assets/images/logo-dark.png" alt="">
                            <img class="h-6 hidden dark:block" src="assets/images/logo-light.png" alt="">
                        </a>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-200 mb-2" for="username">User</label>
                            <input id="username" name="username" class="form-input" type="text" value="" placeholder="Enter your username">
                            <span class="text-danger"><?php echo $_SESSION['error'] ?></span>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-200 mb-2" for="loggingPassword">Password</label>
                            <input id="loggingPassword" name="loggingPassword" class="form-input" type="password" placeholder="Enter your password">
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
                </div>
            </div>
        </div>

    </div>

    <!-- ============================================================== -->
    <!-- End Page content -->
    <!-- ============================================================== -->
    <script>


        $("#loginForm").submit(function(event) {
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
                    url: "libs/controller.php?act=login",
                    data: $(this).serialize(),
                    dataType: "json",
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