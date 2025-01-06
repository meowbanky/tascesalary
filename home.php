<?php
require_once 'libs/App.php';
$App = new App();
$App->checkAuthentication();
include 'partials/main.php';
?>
<head>
    <?php $title = "Dashboard";
    include 'partials/title-meta.php'; ?>

    <?php include 'partials/head-css.php'; ?>
</head>

<body>

<div class="flex wrapper">

    <?php include 'partials/menu.php'; ?>

    <!-- ============================================================== -->
    <!-- Start Page Content here -->
    <!-- ============================================================== -->

    <div class="page-content">

        <?php include 'partials/topbar.php'; ?>

        <main class="flex-grow p-6">

            <?php
            $subtitle = "Menu";
            $pagetitle = "Dashboard";
            include 'partials/page-title.php'; ?>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6 max-w-full">

                <!-- Gender Card -->
                <div class="card">
                    <div class="p-6">
                        <h4 class="card-title">Gender</h4>

                        <div id="gender-target" class="apex-charts my-8" data-colors="#0acf97,#3073F1"></div>

                        <div class="flex justify-center">
                            <div class="w-1/2 text-center">
                                <h5>Female</h5>
                                <p class="fw-semibold text-muted">
                                    <i class="mgc_round_fill text-primary"></i>
                                </p>
                            </div>
                            <div class="w-1/2 text-center">
                                <h5>Male</h5>
                                <p class="fw-semibold text-muted">
                                    <i class="mgc_round_fill text-success"></i>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Finance Card -->
                <div class="card">
                    <div class="p-6">
                        <h4 class="card-title">Finance</h4>

                        <div id="monthly-chart" class="apex-charts my-8" data-colors="#3073F1,#0acf97"></div>
                    </div>
                </div>
            </div> <!-- Grid End -->

            <!-- Department Card -->
            <div class="grid grid-cols-1 gap-6 mb-6 max-w-full">
                <div class="card">
                    <div class="p-6">
                        <h4 class="card-title">Department</h4>

                        <div id="department-chart" class="apex-charts my-8" data-colors="#3073F1,#0acf97"></div>
                    </div>
                </div>
            </div> <!-- Department Grid End -->

        </main>

        <?php include 'partials/footer.php'; ?>

    </div>

    <!-- ============================================================== -->
    <!-- End Page content -->
    <!-- ============================================================== -->

</div>

<?php include 'partials/customizer.php'; ?>

<?php include 'partials/footer-scripts.php'; ?>

<!-- Apexcharts js -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<!-- Dashboard Project Page js -->
<script src="assets/js/pages/dashboard.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
    fetch('libs/chart.php?type=finance')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error(data.error);
                return;
            }

            // Check if data.labels, data.allowances, and data.deductions are defined
            if (!data.labels || !data.allowances || !data.deductions) {
                console.error('Invalid data format:', data);
                return;
            }

            // Get labels and series from the fetched data
            var labels = data.labels;
            var allowances = data.allowances;
            var deductions = data.deductions;

            console.log('Fetched Labels:', labels);
            console.log('Fetched Allowances:', allowances);
            console.log('Fetched Deductions:', deductions);

            // Default colors
            var colors = ["#3073F1", "#0acf97"];

            // Get data-colors attribute from the container
            var dataColors = document.querySelector("#monthly-chart").dataset.colors;

            // If data-colors attribute is set, use its values
            if (dataColors) {
                colors = dataColors.split(",");
            }

            // Formatter function for currency
            var currencyFormatter = new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD',
            });

            // Chart options
            var options = {
                chart: {
                    height: 350,
                    type: 'bar',
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '55%',
                        endingShape: 'rounded'
                    },
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                series: [
                    {
                        name: 'Allowances',
                        data: allowances
                    },
                    {
                        name: 'Deductions',
                        data: deductions
                    }
                ],
                xaxis: {
                    categories: labels,
                },
                yaxis: {
                    title: {
                        text: 'Amount (₦)'
                    },
                    labels: {
                        formatter: function (val) {
                            return '₦' + val.toLocaleString();
                        }
                    }
                },
                fill: {
                    opacity: 1
                },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return '₦' + val.toLocaleString(); // Tooltip formatting with Naira sign
                        }
                    }
                },
                colors: colors,
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: 300
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }]
            };

            // Initialize the chart
            var chart = new ApexCharts(document.querySelector("#monthly-chart"), options);

            // Render the chart
            chart.render();
        })
        .catch(error => console.error('Error fetching data:', error));

    fetch('libs/chart.php?type=dept')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error(data.error);
                return;
            }

            // Check if data.labels and data.series are defined
            if (!data.labels || !data.series) {
                console.error('Invalid data format:', data);
                return;
            }

            // Get labels and series from the fetched data
            var labels = data.labels; // Using series as labels
            var series = data.series; // Using labels as series

            console.log('Fetched Labels:', labels);
            console.log('Fetched Series:', series);

            // Default colors
            var colors = ["#3073F1", "#0acf97"];

            // Get data-colors attribute from the container
            var dataColors = document.querySelector("#department-chart").dataset.colors;

            // If data-colors attribute is set, use its values
            if (dataColors) {
                colors = dataColors.split(",");
            }

            // Chart options
            var options = {
                chart: {
                    height: 350,
                    type: 'bar',
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '55%',
                        endingShape: 'rounded'
                    },
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                series: [{
                    name: 'Number of Staff',
                    data: series
                }],
                xaxis: {
                    categories: labels,
                },
                yaxis: {
                    title: {
                        text: 'Number of Staff'
                    }
                },
                fill: {
                    opacity: 1
                },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return val;
                        }
                    }
                },
                colors: colors,
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: 300
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }]
            };

            // Initialize the chart
            var chart = new ApexCharts(document.querySelector("#department-chart"), options);

            // Render the chart
            chart.render();
        })
        .catch(error => console.error('Error fetching data:', error));


    // Fetch the data from the server
    fetch('libs/chart.php?type=gender')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error(data.error);
                return;
            }

            // Check if data.labels and data.series are defined
            if (!data.labels || !data.series) {
                console.error('Invalid data format:', data);
                return;
            }

            // Get labels and series from the fetched data
            var labels = data.labels;
            var series = data.series;

            console.log('Fetched Labels:', labels);
            console.log('Fetched Series:', series);

            // Default colors
            var colors = ["#3073F1", "#0acf97"];

            // Get data-colors attribute from the container
            var dataColors = document.querySelector("#gender-target").dataset.colors;

            // If data-colors attribute is set, use its values
            if (dataColors) {
                colors = dataColors.split(",");
            }

            // Chart options
            var options = {
                chart: {
                    height: 280,
                    type: 'donut',
                },
                legend: {
                    show: false
                },
                stroke: {
                    colors: ['transparent']
                },
                series: series,
                labels: labels,
                colors: colors,
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: 200
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }]
            };

            // Initialize the chart
            var chart = new ApexCharts(document.querySelector("#gender-target"), options);

            // Render the chart
            chart.render();
        })
        .catch(error => console.error('Error fetching data:', error));
</script>
</body>

</html>
