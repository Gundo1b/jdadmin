<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$basePath = rtrim(BASE_URL, '/') . '/';
$adminBasePath = $basePath . 'admin/';
?>

<style>
    /* SIDEBAR STYLING */
    .sidebar {
        width: 250px;
        height: 100vh;
        background: #ffffff;
        position: fixed;
        top: 0;
        left: 0;
        padding-top: 60px;
        /* Space for the navbar */
        border-right: 1px solid #ddd;
        overflow-y: auto;
        transition: all 0.3s ease;
        z-index: 1000;
    }

    /* COLLAPSED STATE */
    body.sidebar-collapsed .sidebar {
        margin-left: -250px;
    }

    body.sidebar-collapsed .main-content {
        margin-left: 0 !important;
    }

    .main-content {
        transition: all 0.3s ease;
    }

    /* Adjust Navbar as well */
    .navbar {
        transition: all 0.3s ease;
        z-index: 1001;
        /* Stay above sidebar if needed, or just below */
    }

    body:not(.sidebar-collapsed) .navbar {
        padding-left: 250px;
    }

    body.sidebar-collapsed .navbar {
        padding-left: 0;
    }

    .sidebar a {
        display: block;
        padding: 12px 20px;
        color: #333;
        text-decoration: none;
        font-size: 15px;
        font-weight: 500;
    }

    .sidebar a:hover {
        background: #f0f0f0;
    }

    .sidebar .logo-section {
        padding: 30px 15px;
        display: flex;
        justify-content: center;
        align-items: center;
        border-bottom: 1px solid #f8f9fa;
        margin-bottom: 10px;
        background: #fff;
    }

    .sidebar .logo-section img {
        width: 180px;
        max-width: 100%;
        height: auto;
        display: block;
    }

    .sidebar .menu-title {
        padding: 10px 20px;
        font-size: 14px;
        color: #777;
        font-weight: bold;
        margin-top: 10px;
        text-transform: uppercase;
    }
</style>

<div class="sidebar">
    <div class="logo-section">
        <img src="<?php echo $basePath; ?>assets/images/logo.png" alt="logo">
    </div>


    <div class="menu-title">Main</div>
    <a href="<?php echo $adminBasePath; ?>dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>

    <div class="menu-title">Students</div>
    <a href="<?php echo $adminBasePath; ?>students/add_student.php"><i class="bi bi-person-plus"></i> Add Student</a>
    <a href="<?php echo $adminBasePath; ?>students/manage_students.php"><i class="bi bi-people"></i> Manage Students</a>

    <div class="menu-title">Tutors</div>
    <a href="<?php echo $adminBasePath; ?>tutors/add_tutor.php"><i class="bi bi-person-plus"></i> Add Tutor</a>
    <a href="<?php echo $adminBasePath; ?>tutors/manage_tutors.php"><i class="bi bi-people"></i> Manage Tutors</a>

    <div class="menu-title">Academic Info</div>
    <a href="<?php echo $adminBasePath; ?>academic/grades.php"><i class="bi bi-list-ol"></i> Grades</a>
    <a href="<?php echo $adminBasePath; ?>academic/subjects.php"><i class="bi bi-journal-bookmark"></i> Subjects</a>
    <a href="<?php echo $adminBasePath; ?>academic/classes.php"><i class="bi bi-building"></i> Curriculum</a>

    <div class="menu-title">Timetable</div>
    <a href="<?php echo $adminBasePath; ?>timetable/create_timetable.php"><i class="bi bi-calendar-plus"></i> Create
        Timetable</a>
    <a href="<?php echo $adminBasePath; ?>timetable/view_timetable.php"><i class="bi bi-calendar-week"></i> View
        Timetable</a>

    <div class="menu-title">Attendance System</div>
    <a href="<?php echo $adminBasePath; ?>attendance/daily_student.php"><i class="bi bi-clipboard-check"></i> Daily student attendance</a>
    <a href="<?php echo $adminBasePath; ?>attendance/view_teacher_attendance.php"><i class="bi bi-person-check"></i> Teacher attendance</a>

    <div class="menu-title">Fees & Payments</div>
    <a href="<?php echo $adminBasePath; ?>fees/fee_structures.php"><i class="bi bi-cash-coin"></i> Fee structures</a>
    <a href="<?php echo $adminBasePath; ?>fees/student_fee_records.php"><i class="bi bi-receipt"></i> Student fee records</a>
    <a href="<?php echo $adminBasePath; ?>fees/invoice.php"><i class="bi bi-file-earmark-medical"></i> Invoice</a>
    <a href="<?php echo $adminBasePath; ?>fees/outstanding_balances.php"><i class="bi bi-exclamation-circle"></i> Outstanding balance tracking</a>

    <div class="menu-title">Reports & Analytics</div>
    <a href="<?php echo $adminBasePath; ?>reports/attendance_reports.php"><i class="bi bi-clipboard-check"></i> Attendance reports</a>
    <a href="<?php echo $adminBasePath; ?>reports/academic_reports.php"><i class="bi bi-graph-up"></i> Academic performance reports</a>
    <a href="<?php echo $adminBasePath; ?>reports/fee_collection_reports.php"><i class="bi bi-cash-stack"></i> Fee collection reports</a>
    <a href="<?php echo $adminBasePath; ?>reports/export_pdf.php"><i class="bi bi-file-earmark-pdf"></i> Export reports to PDF</a>

    <div class="menu-title">Communication</div>
    <a href="<?php echo $adminBasePath; ?>communication/announcements.php"><i class="bi bi-megaphone"></i> Announcements & notices</a>
    <a href="<?php echo $adminBasePath; ?>communication/direct_messages.php"><i class="bi bi-chat-dots"></i> Direct messaging to parents & students</a>

    <div class="menu-title">Account</div>
    <a href="<?php echo $basePath; ?>logout.php" class="text-danger">
        <i class="bi bi-box-arrow-right"></i> Logout
    </a>

</div>

<!-- MAIN CONTENT WRAPPER (push content right of sidebar) -->
<div class="main-content" style="margin-left:250px; padding:20px;">

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleBtn = document.getElementById('sidebarToggle');
            const body = document.body;

            // Check for saved state
            if (localStorage.getItem('sidebar-state') === 'collapsed') {
                body.classList.add('sidebar-collapsed');
            }

            if (toggleBtn) {
                toggleBtn.addEventListener('click', function () {
                    body.classList.toggle('sidebar-collapsed');

                    // Save state
                    if (body.classList.contains('sidebar-collapsed')) {
                        localStorage.setItem('sidebar-state', 'collapsed');
                    } else {
                        localStorage.setItem('sidebar-state', 'expanded');
                    }
                });
            }
        });
    </script>
