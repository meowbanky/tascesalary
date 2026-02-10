<?php
require_once 'libs/App.php';
$App = new App();
$App->checkAuthentication();
require 'libs/middleware.php';
checkPermission('report_payslipall.php');

include 'partials/main.php';

?>

<head>
    <?php
    $title = "Payslip for Individual";
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
            $pagetitle = "Payslip for Individual";
            include 'partials/page-title.php';
            ?>

            <!-- Main Content -->
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
            <div class="backdrop" id="backdrop">
                <div class="spinner"></div>
            </div>
        </main>

        <?php include 'partials/footer.php'; ?>

    </div>
</div>

<?php include 'partials/customizer.php'; ?>
<?php include 'partials/footer-scripts.php'; ?>
<script>
    $(document).ready(function() {

        $('#loadContent').load('view/view_report_payslipall.php');
        $("#search").focus();
        $("#search").select();
        $("#search").autocomplete({
            source: 'libs/searchstaff.php',
            type: 'POST',
            delay: 10,
            autoFocus: false,
            minLength: 3,
            select: function (event, ui) {
                event.preventDefault();
                $("#search").val(ui.item.label);
                $("#staff_id").val(ui.item.value);
                $("#ogno").val(ui.item.OGNO);
                $("#name").val(ui.item.label);
                $("#email").val(ui.item.EMAIL);
                $('#submit').click();
            }
        });


    })
</script>

</body>

</html>
