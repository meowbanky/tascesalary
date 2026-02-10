<?php

include '../libs/getemployees.php';
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="container mx-auto py-8">
        <div class="flex justify-between">
        <h1 class="text-2xl font-bold mb-6">Employee List</h1>
            <div>
        <button id="reload-button" class="ml-2 mb-2 px-4 py-2 bg-green-600 text-white rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
            <i class="fas fa-sync-alt"></i>Reload
        </button>
            <button id="download-excel-button" class="ml-2 mb-2 px-4 py-2 bg-green-600 text-white rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                <i class="fas fa-download"></i> Download Excel
            </button>
            <button id="add-employee-button" class="ml-2 mb-2 px-4 py-2 bg-green-600 text-white rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                <i class="fas fa-plus"></i> Add New
            </button>
            </div>
        </div>
        <div class="overflow-x-auto font-xs">
            <table class="min-w-full bg-white border border-gray-200">
                <thead>
                <tr class="w-full bg-gray-800 text-white">
                    <th class="py-2 px-4 border border-gray-300">Staff ID</th>
                    <th class="py-2 px-4 border border-gray-300">Name</th>
                    <th class="py-2 px-4 border border-gray-300">Department</th>
                    <th class="py-2 px-4 border border-gray-300">Bank</th>
                    <th class="py-2 px-4 border border-gray-300">Account No.</th>
                    <th class="py-2 px-4 border border-gray-300">Grade</th>
                    <th class="py-2 px-4 border border-gray-300">Step</th>
                    <th class="py-2 px-4 border border-gray-300">Status</th>
                    <th class="py-2 px-4 border border-gray-300">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php
                if (isset($employees['staff_id'])) {
                    $employees = [$employees];
                }
                foreach ($employees as $employee) {
                    $statusClass = $employee['STATUSCD'] == 'A' ? 'bg-green-500' : 'bg-red-500';
                    echo "
                        <tr class='bg-gray-50'>
                            <td class='py-2 px-4 border border-gray-300'>{$employee['staff_id']}</td>
                            <td class='py-2 px-4 border border-gray-300'>{$employee['NAME']}</td>
                            <td class='py-2 px-4 border border-gray-300'>{$employee['dept']}</td>
                            <td class='py-2 px-4 border border-gray-300'>{$employee['BNAME']}</td>
                            <td class='py-2 px-4 border border-gray-300'>{$employee['ACCTNO']}</td>
                            <td class='py-2 px-4 border border-gray-300'>{$employee['GRADE']}</td>
                            <td class='py-2 px-4 border border-gray-300'>{$employee['STEP']}</td>
                            <td class='py-2 px-4 border border-gray-300 $statusClass text-center'>{$employee['STATUSCD']}</td>
                            <td class='py-2 px-4 border border-gray-300 text-center'>
                                <div class='flex justify-center'>
                                   <button class='edit-button text-blue-500 hover:text-blue-700 mx-1' data-gender='{$employee['GENDER']}' data-salarytype='{$employee['SALARY_TYPE']}' data-dob='{$employee['DOB']}' data-empdate='{$employee['EMPDATE']}' data-ogno='{$employee['OGNO']}' data-email='{$employee['EMAIL']}' data-pfacode='{$employee['PFACODE']}' data-pfapin='{$employee['PFAACCTNO']}' data-staff_id='{$employee['staff_id']}' data-bankcode='{$employee['BANK_ID']}' data-deptcode='{$employee['DEPTCD']}' data-statuscode='{$employee['STATUSCD']}'  data-name='{$employee['NAME']}' data-dept='{$employee['dept']}' data-bank='{$employee['BNAME']}' data-acctno='{$employee['ACCTNO']}' data-grade='{$employee['GRADE']}' data-step='{$employee['STEP']}' data-status='{$employee['STATUS']}'><i class='fas fa-edit'></i></button>
                                    <form action='libs/set_staff_id.php' method='post' class='inline'>
                                    <input type='hidden' name='staff_id' value='{$employee['staff_id']}'>
                                    <button type='submit' class='text-yellow-500 hover:text-yellow-700 mx-1'><i class='fas fa-exchange-alt'></i></button>
                         
                                </form>
                                    <a href='empearnings.php?staff_id={$employee['staff_id']}' class='text-green-500 hover:text-green-700 mx-1'><i class='fas fa-dollar-sign'></i></a>
                                </div>
                                </td>
                        </tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit Employee Modal -->
<div id="editModal" class="fixed inset-0 flex items-center justify-center bg-gray-500 bg-opacity-50 hidden">
    <div class="scrollable-content bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg w-full p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Edit Employee</h2>
            <button id="closeModalButton" class="closeModalButton text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="addEmployeeForm" action="edit_employee.php" method="POST">
            <input type="hidden" id="editStaffId" name="staff_id">
            <div class="mb-4">
                <label for="editOgo" class="block text-sm font-medium text-gray-700">OG No</label>
                <input type="text" id="edit_ogno" name="ogno" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            <div class="mb-4">
                <label for="editName" class="block text-sm font-medium text-gray-700">Name</label>
                <input type="text" id="editName" name="name" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            <div class="mb-4">
                <label for="editempdate" class="block text-sm font-medium text-gray-700">Employment Date</label>
                <input type="date" id="editempdate" name="empdate" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            <div class="mb-4">
                <label for="editdob" class="block text-sm font-medium text-gray-700">Date of Birth</label>
                <input type="date" id="editdob" name="dob" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            <div class="mb-4">
                <label for="editDept" class="block text-sm font-medium text-gray-700">Gender</label>
                <select id="editgender" name="gender" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="">Select Item</option>
                    <option value="M">Male</option>
                    <option value="F">Female</option>
                </select>

            </div>
            <div class="mb-4">
                <label for="editDept" class="block text-sm font-medium text-gray-700">Department</label>
                <select id="editDept" name="deptcd" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="">Select Item</option>
                    <?php
                    foreach ($selectDepts as $selectDept) {
                        echo '<option value="' . $selectDept['dept_auto'] . '">' . $selectDept['dept'] . '</option>';
                    }
                    ?>
                </select>

            </div>
            <div class="mb-4">
                <label for="editPFA" class="block text-sm font-medium text-gray-700">PFA</label>
                <select id="editPFA" name="pfacode" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="">Select Item</option>
                    <?php
                    foreach ($selectPfas as $selectPfa) {
                        echo '<option value="' . $selectPfa['PFACAUTO'] . '">' . $selectPfa['PFANAME'] . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="mb-4">
                <label for="editPFAPIN" class="block text-sm font-medium text-gray-700">PFA PIN</label>
                <input type="number" id="editPFAPIN" name="pfaacctno" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>

            <div class="mb-4">
                <label for="editBank" class="block text-sm font-medium text-gray-700">Bank</label>
                <select id="editBank" name="bankcode" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="">Select Item</option>
                    <?php
                    foreach ($selectBanks as $selectBank) {
                        echo '<option value="' . $selectBank['bank_ID'] . '">' . $selectBank['BNAME'] . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="mb-4">
                <label for="editAcctNo" class="block text-sm font-medium text-gray-700">Account No.</label>
                <input type="text" id="editAcctNo" name="acctno" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            <div class="mb-4">
                <label for="editGrade" class="block text-sm font-medium text-gray-700">Grade</label>
                <input type="text" id="editGrade" name="grade" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            <div class="mb-4">
                <label for="editStep" class="block text-sm font-medium text-gray-700">Step</label>
                <input type="text" id="editStep" name="step" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            <div class="mb-4">
                <label for="editEmail" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="editEmail" name="email" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required="required">
            </div>
            <div class="mb-4">
                <label for="editsalarytype" class="block text-sm font-medium text-gray-700">Salary Type</label>
                <select id="editsalarytype" name="salarytype" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="">Select Item</option>
                    <<?php
                    foreach ($selectSalaryTypes as $selectSalaryType) {
                        echo '<option value="' . $selectSalaryType['salaryType_id'] . '">' . $selectSalaryType['SalaryType'] . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="mb-4">
                <label for="editStatus" class="block text-sm font-medium text-gray-700">Status</label>
                <select id="editStatus" name="status" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">Select Item</option>
                        <?php
                        foreach ($selectStatuss as $selectStatus) {
                            echo '<option value="' . $selectStatus['STATUSCD'] . '">' . $selectStatus['STATUS'] . '</option>';
                        }
                        ?>
                </select>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 focus:outline-none">Save Changes</button>
            </div>

        <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-1">
             <button type="submit" id="saveButton" class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto">Save</button>
            <button type="button" class="closeModalButton mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto closebutton">Cancel</button>
        </div>
    </form>
    </div>
</div>


    <script>

        $(document).ready(function() {
        function setModalMaxHeight() {
            var screenHeight = window.innerHeight;
            var modalMaxHeight = screenHeight - 60; // Subtract 60px from screen height
            $('.modal-content').css('max-height', 70 + 'vh');
        }

            $('#addEmployeeForm').submit(function(event) {
                event.preventDefault();

                var formData = $(this).serialize();

                $.ajax({
                    type: 'POST',
                    url: 'libs/add_employee.php', // PHP script to handle form submission
                    data: formData,
                    success: function(response) {
                        if(response == 'Employee added successfully.') {
                            $('#addModal').addClass('hidden');
                            displayAlert(response,'center', 'success');
                            $('#loadContent', window.parent.document).load('view/view_employees.php');
                        }else{
                            displayAlert(response,'center', 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        // Handle error response
                        console.log(error);
                    }
                });
            });

            $('#download-excel-button').click(function() {
                window.location.href = 'libs/generate_excel.php';
            });

            $('#add-employee-button').click(function() {

                $('#editModal').removeClass('hidden');
            })

            $('.edit-button').click(function() {
                var staff_id = $(this).data('staff_id');
                var name = $(this).data('name');
                var dept = $(this).data('deptcode');
                var bank = $(this).data('bankcode');
                var acctno = $(this).data('acctno');
                var grade = $(this).data('grade');
                var step = $(this).data('step');
                var status = $(this).data('statuscode');
                var pfa = $(this).data('PFACODE')
                var pfapin = $(this).data('pfapin')
                var email = $(this).data('email')
                var ogno = $(this).data('ogno')
                var dob = $(this).data('dob')
                var empdate = $(this).data('empdate')
                var salarytype = $(this).data('salarytype')
                var gender = $(this).data('gender')


                $('#editStaffId').val(staff_id);
                $('#editName').val(name);
                $('#editDept').val(dept);
                $('#editBank').val(bank);
                $('#editAcctNo').val(acctno);
                $('#editGrade').val(grade);
                $('#editStep').val(step);
                $('#editStatus').val(status);
                $('#editPFA').val(pfa);
                $('#editPFAPIN').val(pfapin);
                $('#editEmail').val(email);
                $('#edit_ogno').val(ogno);
                $('#editempdate').val(empdate);
                $('#editdob').val(dob);
                $('#editsalarytype').val(salarytype);
                $('#editgender').val(gender);


                $('#editModal').removeClass('hidden');
            });

            // Close Modal
            $('.closeModalButton').click(function() {
                $('#editModal').addClass('hidden');
            });


            $('#reload-button').on('click', function(event) {
                event.preventDefault();
                $('#loadContent', window.parent.document).load('view/view_employees.php');
            })



            $('#emailButton').on('click', function() {
                const payslipData = $('#payslipModal .modal-content').html();
                const email = prompt("Please enter the employee's email address:");
                if (email) {
                    $.ajax({
                        url: 'libs/send_payslip.php',
                        type: 'POST',
                        data: { payslip: payslipData,
                            email: email ,
                        staff_id: $('#staff_id').val(),}
                        ,
                        success: function(response) {
                            alert('Payslip emailed successfully.');
                        },
                        error: function(xhr, status, error) {
                            alert('Error sending email: ' + error);
                        }
                    });
                }
            });


            $('#deletepayslip').click(function(event) {
                event.preventDefault();
                Swal.fire({
                    title: "Are you sure you want to delete these Items?",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Yes, delete it!"
                }).then((result) => {
                    if (result.isConfirmed) {

                        $.ajax({
                            url: 'libs/deletePayslip.php',
                            method: 'POST',
                            dataType: 'json',
                            data: {
                                staff_id: $('#staff_id').val(),
                            },
                            success: function(response) {
                                if(response.status === 'success'){
                                    displayAlert(response.message, 'center', 'success');
                                    $('#loadContent', window.parent.document).load('view/view_empearning.php');
                                }else{
                                    displayAlert(response.message, 'center', 'error');
                                    $('#loadContent', window.parent.document).load('view/view_empearning.php');
                                }
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                console.error('AJAX Error: ', textStatus, errorThrown);
                            }
                        });
                    }
                })
            })



            // Hide the modal-prorate
            $('.closebutton').click(function() {
                $('.closemodal').addClass('hidden');
            });




        });

</script>

