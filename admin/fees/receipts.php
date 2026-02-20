<?php
require_once '../../config/db.php';
header('Location: ' . BASE_URL . 'admin/dashboard.php');
exit;
require_once '../../templates/header.php';
require_once '../../templates/sidebar.php';
?>

<div class="container-fluid">
    <h3 class="mb-4"><i class="bi bi-file-earmark-text"></i> Receipts</h3>
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">Add Receipt</h5>
        </div>
        <div class="card-body">
            <form action="#" method="POST">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Student</label>
                        <select name="student_id" class="form-select">
                            <option value="">Select Student</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Receipt #</label>
                        <input type="text" name="receipt_no" class="form-control" placeholder="e.g. RCP-0001">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Date</label>
                        <input type="date" name="receipt_date" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Amount</label>
                        <input type="number" name="amount" class="form-control" placeholder="e.g. 500">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Method</label>
                        <select name="method" class="form-select">
                            <option value="">Select Method</option>
                            <option value="Cash">Cash</option>
                            <option value="Card">Card</option>
                            <option value="Transfer">Transfer</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <input type="text" name="notes" class="form-control" placeholder="Optional">
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Save Receipt
                </button>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white">
            <h5 class="mb-0">Receipts</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th>Student</th>
                            <th>Receipt #</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="7" class="text-center text-muted">No records yet.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../templates/footer.php'; ?>
