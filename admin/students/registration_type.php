<?php
require_once '../../config/db.php';
require_once '../../templates/header.php';
require_once '../../templates/sidebar.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-person-plus"></i> Registration Type</h3>
        <a href="manage_students.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <div class="row g-3">
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">Matric Upgrade</h5>
                    <p class="card-text text-muted">Register for a matric upgrade.</p>
                    <a href="student-registration.html" class="btn btn-primary mt-auto">
                        Continue
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">Matric Amended</h5>
                    <p class="card-text text-muted">Register for matric amended.</p>
                    <a href="student-registration.html" class="btn btn-primary mt-auto">
                        Continue
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">Supplementary</h5>
                    <p class="card-text text-muted">Register for supplementary.</p>
                    <a href="student-registration.html" class="btn btn-primary mt-auto">
                        Continue
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">Extra Class</h5>
                    <p class="card-text text-muted">Go to extra class page.</p>
                    <a href="extra_class.php" class="btn btn-primary mt-auto">
                        Continue
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../templates/footer.php'; ?>
