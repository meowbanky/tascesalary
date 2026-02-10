<?php
require_once '../libs/App.php';
$App = new App();
$App->checkAuthentication();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log View</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Flatpickr CSS CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>
<body class="bg-gray-100 p-6">
<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="container mx-auto py-8">
        <div class="flex justify-between">
            <h1 class="text-2xl font-bold mb-6">Log</h1>
        </div>
        <form id="logForm" method="POST" >
            <div class="mb-4">
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h2 class="text-2xl font-bold mb-4">Select Date Range</h2>
                    <div class="flex space-x-4">
                        <input id="startDate" name="startDate" type="date" placeholder="Start Date" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <input id="endDate" name="endDate" type="date" placeholder="End Date" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-1">
                <button type="submit" id="saveButton" class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto">Submit</button>
            </div>
        </form>
        <div id="result3" class="overflow-auto min-w-full bg-white">
        </div>
    </div>
</div>
<div class="backdrop" id="backdrop">
    <div class="spinner"></div>
</div>
<!-- Flatpickr JS CDN -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/default.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        flatpickr("#startDate", {
            dateFormat: "Y-m-d",
            onChange: function(selectedDates, dateStr, instance) {
                endDatePicker.set('minDate', dateStr);
            }
        });

        const endDatePicker = flatpickr("#endDate", {
            dateFormat: "Y-m-d",
            onChange: function(selectedDates, dateStr, instance) {
                startDatePicker.set('maxDate', dateStr);
            }
        });

        const startDatePicker = flatpickr("#startDate");
    });
</script>
<script>
    $(document).ready(function() {
        $('#logForm').submit(function(event) {
            document.getElementById("backdrop").style.display = "flex";
            event.preventDefault();

            var formData = $(this).serialize();

            $.ajax({
                type: 'POST',
                url: 'libs/getlog.php', // PHP script to handle form submission
                data: formData,
                success: function(response) {
                    $('#result3').html(response);
                    // Apply styles to the result table
                    $('#result table').addClass('min-w-full bg-white border border-gray-200');
                    $('#result th').addClass('bg-gray-800 text-white py-2 px-4 border border-gray-300');
                    $('#result td').addClass('py-2 px-4 border border-gray-300');
                    document.getElementById("backdrop").style.display = "none";
                },
                error: function(xhr, status, error) {
                    console.log(error);
                    document.getElementById("backdrop").style.display = "none";
                }
            });
        });
    });
</script>
</body>
</html>
