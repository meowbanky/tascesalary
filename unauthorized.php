<?php
require_once 'libs/App.php';
$App = new App();
$App->checkAuthentication();
include 'partials/main.php';

?>

<head>
    <?php
    $title = "Unauthorised Access";
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
            $subtitle = "Un-authozided Page";
            $pagetitle = "";
            include 'partials/page-title.php';
            ?>

            <!-- Main Content -->
            <div id="loadContent" class="bg-red-500 flex items-center justify-center h-screen">
                <div class="text-center p-6 bg-red-500 rounded">
                    <h1 class="text-2xl font-bold mb-4">Unauthorized Access</h1>
                    <p class="mb-4">You do not have permission to access this page.</p>
                    <a href="index.php" class="text-blue-500 hover:underline">Login</a>
                </div>
            </div>

        </main>

        <?php include 'partials/footer.php'; ?>

    </div>
</div>

<?php include 'partials/customizer.php'; ?>
<?php include 'partials/footer-scripts.php'; ?>


</body>

</html>
