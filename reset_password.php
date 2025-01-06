
<?php include 'partials/main.php'; ?>

<head>
    <?php $title = "Reset Password";
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
                        <a href="index.php" class="block mb-8">
                            <img class="h-6 block dark:hidden" src="assets/images/logo-dark.png" alt="">
                            <img class="h-6 hidden dark:block" src="assets/images/logo-light.png" alt="">
                        </a>

                        <form id="formReset" method="POST" class="space-y-4">
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700">New Password:</label>
                                <input type="password" id="password" name="password" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm New Password:</label>
                                <input type="password" id="confirm_password" name="confirm_password" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>
                            <button id="send" type="button" class="w-full py-2 px-4 bg-blue-600 text-white font-semibold rounded-md shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-75">
                                Reset Password
                            </button>
                        </form>

                        <div class="flex items-center my-6">
                            <div class="flex-auto mt-px border-t border-dashed border-gray-200 dark:border-slate-700"></div>
                            <div class="mx-4 text-secondary">Or</div>
                            <div class="flex-auto mt-px border-t border-dashed border-gray-200 dark:border-slate-700"></div>
                        </div>
                    
                        <p class="text-gray-500 dark:text-gray-400 text-center">Back to<a href="index.php" class="text-primary ms-1"><b>Log In</b></a></p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ============================================================== -->
    <!-- End Page content -->
    <!-- ============================================================== -->

    <script type="text/javascript">

        $(document).ready(function() {
            $('#send').click(function(event) {
                $formData = $('#formReset').serialize();
                $.ajax({
                    dataType: 'json',
                    url: 'libs/reset_password.php',
                    method: 'POST',
                    data:$formData,
                    success: function(response) {
                    if(response.status === 'success'){
                        displayAlert(response.message,'center','success');
                    }else{
                        displayAlert(response.message,'center','error');
                    }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error('AJAX Error: ', textStatus, errorThrown);
                    }
                });
            });
        })
    </script>
</body>

</html>