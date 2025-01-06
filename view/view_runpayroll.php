<?php
require_once '../libs/App.php';
$App = new App();
$App->checkAuthentication();
?>
<style>
    .progress-bar {
        transition: width 0.3s;
    }
</style>
<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="container mx-auto py-8">
        <div class="flex justify-between">
            <h1 class="text-2xl font-bold mb-6">Run Payroll</h1>

        </div>
        <div class="overflow-x-auto font-xs">
            <div class="min-h-screen flex flex-col items-center justify-center bg-gray-100">
                <div class="w-full max-w-4xl mx-auto bg-white p-6 shadow-md rounded-md">
                    <h1 class="text-4xl font-bold text-center mb-6">Payroll Processing</h1>
                    <div class="bg-blue-100 border border-blue-500 text-blue-700 px-4 py-3 rounded relative mb-6" role="alert">
                        <strong class="font-bold">Run Final Payroll Processing Sequence</strong>
                    </div>
                    <div class="bg-gray-200 p-4 rounded-md mb-6">
                        <p class="text-center font-medium text-gray-700">Before running the final payroll sequence, please ensure all pre-requisites regarding employee earnings and deductions have been fulfilled.</p>
                    </div>
                    <div class="bg-white p-4 rounded-md shadow-sm">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Current Active Payroll Period</label>
                        <input type="text" value="<?php echo  $_SESSION['activeperiodDescription']; ?>" readonly class="w-full text-center p-2 bg-gray-200 border border-gray-300 rounded-md mb-4">
                        <div id="sample_1">
                            <div id="progress" style="border:1px solid #ccc; border-radius: 5px;"></div>
                            <div id="information" style="width:500px"> </div>
                        </div>
                        <div class="w-full bg-gray-200 h-1 mb-6">
                            <div class="bg-blue-500 h-1" style="width: 0%;"></div>
                        </div>
                        <div class="flex justify-center">
                            <button id="process" class="bg-red-600 text-white font-bold py-2 px-4 rounded-md hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-opacity-50">
                                PROCESS PAYROLL
                                <span class="ml-2 spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>






<script>
    $(document).ready(function() {
        function setModalMaxHeight() {
            var screenHeight = window.innerHeight;
            var modalMaxHeight = screenHeight - 60; // Subtract 60px from screen height
            $('.modal-content').css('max-height', 70 + 'vh');
        }

        $('#process').click(function(event) {
            event.preventDefault();

            Swal.fire({
                title: "Are you sure you want to run the payroll?",
                text: "",
                icon: "info",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, run it!"
            }).then((result) => {
                if (result.isConfirmed) {

                    $('#process').attr('disabled', true);
                    $('#process').html("Transaction is processing");
                    $('#process').attr('val', 'Please wait while your transaction is Processing');
                    submitting = false;
                    $.ajax({
                        type: "GET",
                        dataType: "json",
                        url: 'libs/runpayslipall.php',
                        xhrFields: {
                            onprogress: function(e) {
                                $('#sample_1').html(e.target.responseText);
                                //console.log(e.target.responseText);
                            }
                        },
                        success: function(response, message) {
                            console.log(response);
                            alert(response.status);
                            if (response.status == 'success') {

                                $('#payprocessbtn').attr('disabled', false);
                                displayAlert(response.message, 'center', 'success');
                                location.reload();
                            } else {
                                console.log(response);

                            }

                            $('#payprocessbtn').attr('disabled', false);
                            $('#payprocessbtn').html("Payroll Process");


                        }
                    })
                }

            })

        })



    });
</script>