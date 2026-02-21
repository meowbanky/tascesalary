<?php
require_once 'libs/App.php';
$App = new App();
$App->checkAuthentication();

$businessInfo = $App->getBusinessName();
$_SESSION['businessname'] = $businessInfo['business_name'];
?>
<?php include 'partials/main.php'; ?>
<head>
    <?php $title = 'Analysis Report'; include 'partials/title-meta.php'; ?>
    <?php include 'partials/head-css.php'; ?>
</head>
<body>
<div class="flex wrapper">
    <?php include 'partials/menu.php'; ?>
    <div class="page-content">
        <?php include 'partials/topbar.php'; ?>
        <main class="flex-grow p-6">
            <div id="content" class="container mx-auto">
                <div class="text-xl font-bold mb-4">Analysis Report</div>
                <div id="dynamic-content">
                    <?php include 'view/view_report_analysis.php'; ?>
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
