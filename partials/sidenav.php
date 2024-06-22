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
                <a href="#" class="menu-link">
                    <span class="menu-icon"><i class="mgc_task_2_line"></i></span>
                    <span class="menu-text">Kanban</span>
                </a>
            </li>

            <li class="menu-item">
                <a href="javascript:void(0)" data-fc-type="collapse" class="menu-link">
                    <span class="menu-icon"><i class="mgc_building_2_line"></i></span>
                    <span class="menu-text"> Project </span>
                    <span class="menu-arrow"></span>
                </a>

                <ul class="sub-menu hidden">
                    <li class="menu-item">
                        <a href="apps-project-list.php" class="menu-link">
                            <span class="menu-text">List</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="apps-project-detail.php" class="menu-link">
                            <span class="menu-text">Detail</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="apps-project-create.php" class="menu-link">
                            <span class="menu-text">Create</span>
                        </a>
                    </li>
                </ul>
            </li>

            <li class="menu-item">
                <a href="index.php" class="menu-link">
                    <span class="menu-icon"><i class="mgc_exit_line"></i></span>
                    <span class="menu-text">Logout</span>
                </a>
            </li>
        </ul>

        <!-- Help Box Widget -->

    </div>
</div>
<!-- Sidenav Menu End  -->