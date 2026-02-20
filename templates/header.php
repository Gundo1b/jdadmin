<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

// --- SECURITY: Admin must be logged in ---
$adminUnreadMessages = 0;
$adminMessagesQuery = "
    SELECT COUNT(*) AS unread_count
    FROM tutor_messages
    WHERE recipient_type = 'admin'
      AND is_read = 0
";
$adminMessagesResult = $conn->query($adminMessagesQuery);
if ($adminMessagesResult) {
    $adminUnreadMessages = (int) ($adminMessagesResult->fetch_assoc()['unread_count'] ?? 0);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Dashboard</title>

    <!-- BOOTSTRAP -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

    <!-- ICONS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">

    <!-- CUSTOM CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>

<body style="background:#f4f6f9;">

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">

            <button class="btn btn-outline-light me-2" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>

            <a class="navbar-brand" href="<?php echo BASE_URL; ?>admin/dashboard.php">
                <i></i>jdtoturing
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarContent">

                <ul class="navbar-nav ms-auto">

                    <!-- Logged in admin -->
                    <li class="nav-item">
                        <span class="nav-link">
                            <i class="bi bi-person-circle"></i>
                            <?php echo $_SESSION['admin_name'] ?? 'Admin'; ?>
                        </span>
                    </li>

                    <!-- Direct message notifications -->
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="<?php echo BASE_URL; ?>admin/communication/direct_messages.php?view=recent" title="Direct messages">
                            <i class="bi bi-bell"></i>
                            <?php if ($adminUnreadMessages > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    <?php echo $adminUnreadMessages; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>

                    <!-- Logout -->
                    <li class="nav-item">
                        <a class="nav-link text-warning" href="<?php echo BASE_URL; ?>logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>

                </ul>
            </div>

        </div>
    </nav>

    <div class="container-fluid mt-3">

