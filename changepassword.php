<?php

require_once 'libs/App.php';
$App = new App();
$App->checkAuthentication();

require_once 'libs/middleware.php';
checkPermission('changepassword.php');

include 'partials/main.php';

?>

<head>
    <?php
    $title = "Change Password";
    include 'partials/title-meta.php';
    ?>
    <?php include 'partials/head-css.php'; ?>
</head>

<body>
<!-- Begin page -->
<div class="flex wrapper">
    <?php include 'partials/menu.php'; ?>

    <!-- ============================================================== -->
    <!-- Start Page Content here -->
    <!-- ============================================================== -->
    <div class="page-content">
        <?php include 'partials/topbar.php'; ?>

        <main class="flex-grow p-6">
            <?php
            $subtitle = "Home";
            $pagetitle = "Change Password";
            include 'partials/page-title.php';
            ?>

            <!-- Main Content -->
            <div class="flex justify-center items-center min-h-screen ">

                <div class="2xl:w-1/4 lg:w-1/3 md:w-1/2 w-full">
                    <div class="card overflow-hidden sm:rounded-md rounded-none">
                        <div class="p-6">
                            <form id="formReset" method="POST" class="space-y-4">
                                <div>
                                    <label for="cpassword" class="block text-sm font-medium text-gray-700">Current Password:</label>
                                    <input type="password" id="cpassword" name="cpassword" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="password" class="block text-sm font-medium text-gray-700">New Password:</label>
                                    <input type="password" id="password" name="password" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm New Password:</label>
                                    <input type="password" id="confirm_password" name="confirm_password" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                                <button id="send" type="button" class="w-full py-2 px-4 bg-blue-600 text-white font-semibold rounded-md shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-75">
                                    Change Password
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <?php include 'partials/footer.php'; ?>

    </div>
</div>

<?php include 'partials/customizer.php'; ?>
<?php include 'partials/footer-scripts.php'; ?>
<script>
    $(document).ready(function() {

        $('#send').click(function(event) {
            $formData = $('#formReset').serialize();
            $.ajax({
                dataType: 'json',
                url: 'libs/change_password.php',
                method: 'POST',
                data:$formData,
                success: function(response) {
                    if(response.status === 'success'){
                        displayAlert(response.message,'center','success');
                        window.location.href = 'index.php';
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
