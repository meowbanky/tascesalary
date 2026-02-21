<div class="app-menu">

    <!-- Sidenav Brand Logo -->
    <a href="index.php" class="logo-box">
        <!-- Light Brand Logo -->
        <div class="logo-light">
            <img src="assets/images/logo-light.png" class="logo-lg h-6" alt="Light logo">
            <img src="assets/images/logo-sm.png" class="logo-sm" alt="Small logo">
        </div>

        <!-- Dark Brand Logo -->
        <div class="logo-dark">
            <img src="assets/images/logo-dark.png" class="logo-lg h-6" alt="Dark logo">
            <img src="assets/images/logo-sm.png" class="logo-sm" alt="Small logo">
        </div>
    </a>

    <!-- Sidenav Menu Toggle Button -->
    <button id="button-hover-toggle" class="absolute top-5 end-2 rounded-full p-1.5">
        <span class="sr-only">Menu Toggle Button</span>
        <i class="mgc_round_line text-xl"></i>
    </button>

    <!--- Menu -->
    <div class="srcollbar" data-simplebar>
        <ul class="menu" data-fc-type="accordion">
            <li class="menu-title">Menu</li>

            <li class="menu-item">
                <a href="home.php" class="menu-link">
                    <span class="menu-icon"><i class="mgc_home_3_line"></i></span>
                    <span class="menu-text"> Dashboard </span>
                </a>
            </li>

            <li class="menu-item">
                <a href="empearnings.php" class="menu-link">
                    <span class="menu-icon"><i class="mgc_wallet_4_line"></i></span>
                    <span class="menu-text"> Compensation </span>
                </a>
            </li>

            <li class="menu-item">
                <a href="employee.php" class="menu-link">
                    <span class="menu-icon"><i class="mgc_user_1_line"></i></span>
                    <span class="menu-text"> Employee </span>
                </a>
            </li>

            <li class="menu-item">
                <a href="upload.php" class="menu-link">
                    <span class="menu-icon"><i class="mgc_upload_2_line"></i></span>
                    <span class="menu-text"> Upload </span>
                </a>
            </li>
            <li class="menu-item">
                <a href="runpayroll.php" class="menu-link">
                    <span class="menu-icon"><i class="mgc_run_line"></i></span>
                    <span class="menu-text"> Run Payroll </span>
                </a>
            </li>
            <li class="menu-item">
                <a href="javascript:void(0)" data-fc-type="collapse" class="menu-link">
                    <span class="menu-icon"><i class="mgc_settings_4_line"></i></span>
                    <span class="menu-text"> Settings </span>
                    <span class="menu-arrow"></span>
                </a>

                <ul class="sub-menu hidden">
                    <li class="menu-item">
                        <a href="exportfortax.php" class="menu-link">
                            <span class="menu-text">💰Export for Tax</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="user.php" class="menu-link">
                            <span class="menu-text"><i class="mgc_user_add_line"></i>Users</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="pension.php" class="menu-link">
                            <span class="menu-text"><i class="mgc_user_add_line"></i>PFA</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="bank.php" class="menu-link">
                            <span class="menu-text"><i class="mgc_bank_line"></i>Bank</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="payperiods.php" class="menu-link">
                            <span class="menu-text"><i class="mgc_calendar_add_line"></i>Pay-period</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="dept.php" class="menu-link">
                            <span class="menu-text"><i class="mgc_building_2_line"></i>Dept</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="permissions.php" class="menu-link">
                            <span class="menu-text"><i class="mgc_key_2_line"></i>Permissions</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="salarytable.php" class="menu-link">
                            <span class="menu-text"><i class="mgc_table_line"></i>Salary Table</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="call_backup.php" class="menu-link">
                            <span class="menu-text"><i class="mgc_database_line"></i>Database Backup</span>
                        </a>
                    </li>
                <li class="menu-item">
                        <a href="salary_estimator.php" class="menu-link">
                            <span class="menu-text"><i class="mgc_calculator_line"></i>Salary Estimator</span>
                        </a>
                    </li>
                </ul>
            </li>

            <li class="menu-item">
                <a href="javascript:void(0)" data-fc-type="collapse" class="menu-link">
                    <span class="menu-icon"><i class="mgc_chart_line_line"></i></span>
                    <span class="menu-text"> Report </span>
                    <span class="menu-arrow"></span>
                </a>

                <ul class="sub-menu hidden">
                    <li class="menu-item">
                        <a href="report_payroll_summary.php" class="menu-link">
                            <span class="menu-text">Gross Pay summary</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="exportforgross.php" class="menu-link">
                            <span class="menu-text">Gross Pay Export</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="report_payslipone.php" class="menu-link">
                            <span class="menu-text"><i class="mgc_paypal_line"></i>Payslip</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="report_payslipall.php" class="menu-link">
                            <span class="menu-text"><i class="mgc_paypal_line"></i>Payslip All</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="report_payrollbydept.php" class="menu-link">
                            <span class="menu-text"><i class="mgc_bank_line"></i>Payroll by Dept</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="report_banksummary.php" class="menu-link">
                            <span class="menu-text"><i class="mgc_bank_line"></i>Bank Summary</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="report_deductionlist.php" class="menu-link">
                            <span class="menu-text"><i class="mgc_building_2_line"></i>All/Ded List</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="report_grosspay.php" class="menu-link">
                            <span class="menu-text"><i class="mgc_wallet_3_line"></i>Payroll Summary</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="report_net2bank.php" class="menu-link">
                            <span class="menu-text"><i class="mgc_bank_line"></i>Net Bank</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="variance.php" class="menu-link">
                            <span class="menu-text"><i class="mgc_git_compare_line"></i>Variance</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="report_analysis.php" class="menu-link">
                            <span class="menu-text"><i class="mgc_chart_bar_line"></i>Subvention Analysis</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="report_pension.php" class="menu-link">
                            <span class="menu-text"><i class="mgc_home_4_line"></i>Pension</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="report_staff_pension.php" class="menu-link">
                            <span class="menu-text"><i class="mgc_idcard_line"></i>Staff Pension</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="log.php" class="menu-link">
                            <span class="menu-text"><i class="mgc_list_check_2_line"></i>Audit Log</span>
                        </a>
                    </li>
                </ul>
            </li>

            <li class="menu-item">
                <a href="index.php?logout=logout" class="menu-link">
                    <span class="menu-icon"><i class="mgc_exit_line"></i></span>
                    <span class="menu-text">Logout</span>
                </a>
            </li>
        </ul>

        <!-- Help Box Widget -->

    </div>
</div>
<!-- Sidenav Menu End  -->