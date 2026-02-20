<?php
require_once '../../config/db.php';
header('Location: ' . BASE_URL . 'admin/dashboard.php');
exit;
require_once '../../templates/header.php';
require_once '../../templates/sidebar.php';
?>

<div class="container-fluid">
    <h3 class="mb-4"><i class="bi bi-clipboard-data"></i> Payment Status Tracking</h3>
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">Filters</h5>
        </div>
        <div class="card-body">
            <form action="#" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">From</label>
                    <input type="date" name="from_date" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">To</label>
                    <input type="date" name="to_date" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Grade</label>
                    <select name="grade_id" class="form-select">
                        <option value="">All Grades</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Curriculum</label>
                    <select name="class_id" class="form-select">
                        <option value="">All Curriculums</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="Paid">Paid</option>
                        <option value="Partial">Partial</option>
                        <option value="Unpaid">Unpaid</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Period Type</label>
                    <select name="period_type" class="form-select">
                        <option value="">All</option>
                        <option value="Monthly">Monthly</option>
                        <option value="Term">Term</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white">
            <h5 class="mb-0">Payment Status</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th>Student</th>
                            <th>Grade</th>
                            <th>Curriculum</th>
                            <th>Period Type</th>
                            <th>Month/Term</th>
                            <th>Amount Due</th>
                            <th>Amount Paid</th>
                            <th>Balance</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="10" class="text-center text-muted">No records yet.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../templates/footer.php'; ?>
