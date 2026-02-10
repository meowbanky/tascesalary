<?php
require_once 'libs/App.php';
$App = new App();
$App->checkAuthentication();

require_once 'libs/middleware.php';
checkPermission('report_staff_pension.php');
include 'partials/main.php';
?>

<head>
    <?php
    $title = "Staff Pension Report";
    include 'partials/title-meta.php';
    ?>
    <?php include 'partials/head-css.php'; ?>
</head>

<body>
    <!-- Begin page -->
    <div class="flex wrapper">
        <?php include 'partials/menu.php'; ?>

        <div class="page-content">
            <?php include 'partials/topbar.php'; ?>

            <main class="flex-grow p-6">
                <?php
            $subtitle = "Home";
            $pagetitle = "Staff Pension Report";
            include 'partials/page-title.php';
            ?>

                <div id="loadContent">
                    <div class="flex animate-pulse">
                        <div class="flex-shrink-0">
                            <span class="w-12 h-12 block bg-gray-200 rounded-full dark:bg-gray-700"></span>
                        </div>
                        <div class="ms-4 mt-2 w-full">
                            <h3 class="h-4 bg-gray-200 rounded-md dark:bg-gray-700" style="width: 40%;"></h3>
                            <ul class="mt-5 space-y-3">
                                <li class="w-full h-4 bg-gray-200 rounded-md dark:bg-gray-700"></li>
                                <li class="w-full h-4 bg-gray-200 rounded-md dark:bg-gray-700"></li>
                                <li class="w-full h-4 bg-gray-200 rounded-md dark:bg-gray-700"></li>
                                <li class="w-full h-4 bg-gray-200 rounded-md dark:bg-gray-700"></li>
                            </ul>
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
        $('#loadContent').load('view/view_report_staff_pension.php');
    });
    </script>

</body>

</html>