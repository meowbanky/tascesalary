<?php

require_once 'libs/App.php';
$App = new App();
$App->checkAuthentication();

require_once 'libs/middleware.php';
checkPermission('upload_profile_picture.php');

include 'partials/main.php';

?>

<head>
    <?php
    $title = "Upload Profile Picture";
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
            $pagetitle = "Upload Profile Picture";
            include 'partials/page-title.php';
            ?>

            <!-- Main Content -->
            <div class="flex justify-center items-center min-h-screen ">


                    <div class="card overflow-hidden sm:rounded-md rounded-none">
                        <div class="p-6">
                                <form id="uploadForm" action="libs/upload.php" method="post" enctype="multipart/form-data">
                                    <div class="mb-4">
                                        <label for="profilePicture" class="block text-sm font-medium text-gray-700">Choose Profile Picture:</label>
                                        <input type="file" id="profilePicture" name="profilePicture" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    </div>
                                    <div>
                                        <button type="button" id="send" class="w-full py-2 px-4 bg-blue-600 text-white font-semibold rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-75">Upload</button>
                                    </div>
                                </form>
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
            var formData = new FormData($('#uploadForm')[0]);
            $.ajax({
                dataType: 'json',
                url: 'libs/upload.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if(response.status === 'success'){
                        alert(response.message);
                        window.location.href = 'home.php';
                    }else{
                        alert(response.message);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('AJAX Error: ', textStatus, errorThrown);
                }
            });
        });
    });
</script>

</body>

</html>
