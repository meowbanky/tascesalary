<?php
require_once 'libs/App.php';
$App = new App();
$App->checkAuthentication();
require_once 'libs/middleware.php';
checkPermission('call_backup.php');
include_once 'partials/main.php';

?>

<head>
    <?php
    $title = "Database Backup";
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
            $pagetitle = "Database Backup";
            include 'partials/page-title.php';
            ?>

            <!-- Main Content -->
            <div id="loadContent">
                <div class="text-center py-8">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Loading backup management...</p>
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
        // Load the backup view with error handling
        $('#loadContent').load('view/view_backup.php', function(response, status, xhr) {
            if (status === "error") {
                console.error('Error loading backup view:', xhr.status, xhr.statusText);
                $('#loadContent').html(`
                    <div class="alert alert-danger" role="alert">
                        <h4 class="alert-heading">Error Loading Backup Management</h4>
                        <p>There was an error loading the backup management interface. Please try refreshing the page.</p>
                        <hr>
                        <p class="mb-0">
                            <button class="btn btn-primary" onclick="location.reload()">
                                <i class="fas fa-refresh me-2"></i>Refresh Page
                            </button>
                        </p>
                    </div>
                `);
            }
        });
    });
</script>

</body>

</html>