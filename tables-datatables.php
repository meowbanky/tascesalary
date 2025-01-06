
<?php include 'partials/main.php'; ?>

<head>
    <?php $title = "Data Table";
    include 'partials/title-meta.php'; ?>

    <!-- Gridjs Plugin css -->
    <link href="assets/libs/gridjs/theme/mermaid.min.css" rel="stylesheet" type="text/css">

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
                $subtitle = "Table";
                $pagetitle = "Data Table";
                include 'partials/page-title.php'; ?>

                <div class="flex flex-col gap-6">
                    <div class="card">
                        <div class="card-header">
                            <div class="flex justify-between items-center">
                                <h4 class="card-title">Basic</h4>
                            </div>
                        </div>
                        <div class="p-6">
                            <p class="text-sm text-slate-700 dark:text-slate-400 mb-4">The most basic list group is an unordered list with list items and the proper classes. Build upon it with the options that follow, or with your own CSS as needed.</p>

                            <div id="table-gridjs"></div>
                        </div>
                    </div>



                </div>

            </main>

            <?php include 'partials/footer.php'; ?>

        </div>

        <!-- ============================================================== -->
        <!-- End Page content -->
        <!-- ============================================================== -->

    </div>

    <?php include 'partials/customizer.php'; ?>

    <?php include 'partials/footer-scripts.php'; ?>

    <!-- Gridjs Plugin js -->
    <script src="assets/libs/gridjs/gridjs.umd.js"></script>

    <!-- Gridjs Demo js -->
    <script src="assets/js/pages/table-gridjs.js"></script>

</body>

</html>