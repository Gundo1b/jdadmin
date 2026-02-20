<?php
require_once '../../config/db.php';

$selected_grade_id = isset($_GET['grade_id']) && $_GET['grade_id'] !== '' ? (int) $_GET['grade_id'] : 0;
$selected_class_id = isset($_GET['class_id']) && $_GET['class_id'] !== '' ? (int) $_GET['class_id'] : 0;
$selected_status = trim($_GET['status'] ?? '');

$grades = $conn->query("SELECT id, name FROM grades ORDER BY name ASC");
$classes = $conn->query("SELECT id, class_name FROM classes ORDER BY class_name ASC");

$where = [];
if ($selected_grade_id > 0) {
    $where[] = "s.grade_id = " . $selected_grade_id;
}
if ($selected_class_id > 0) {
    $where[] = "s.class_id = " . $selected_class_id;
}
$whereSql = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

$baseQuery = "
    SELECT
        s.id AS student_id,
        CONCAT(s.first_name, ' ', s.last_name) AS student_name,
        COALESCE(SUM(sfr.amount_due), 0) AS total_due,
        COALESCE(SUM(sfr.amount_paid), 0) AS total_paid,
        COALESCE(SUM(sfr.amount_due - sfr.amount_paid), 0) AS balance,
        MAX(sfr.updated_at) AS last_payment_date
    FROM student_fee_records sfr
    JOIN students s ON s.id = sfr.student_id
    $whereSql
    GROUP BY s.id, s.first_name, s.last_name
";

$having = "HAVING balance > 0";
if ($selected_status === 'Cleared') {
    $having = "HAVING balance <= 0";
}

$outstanding = $conn->query($baseQuery . " " . $having . " ORDER BY balance DESC, student_name ASC");

require_once '../../templates/header.php';
require_once '../../templates/sidebar.php';
?>

<div class="container-fluid">
    <h3 class="mb-4"><i class="bi bi-exclamation-circle"></i> Outstanding Balance Tracking</h3>
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">Filters</h5>
        </div>
        <div class="card-body">
            <form action="#" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Grade</label>
                    <select name="grade_id" class="form-select">
                        <option value="">All Grades</option>
                        <?php if ($grades): ?>
                            <?php while ($g = $grades->fetch_assoc()): ?>
                                <option value="<?php echo (int) $g['id']; ?>" <?php echo $selected_grade_id === (int) $g['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($g['name'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Curriculum</label>
                    <select name="class_id" class="form-select">
                        <option value="">All Curriculums</option>
                        <?php if ($classes): ?>
                            <?php while ($c = $classes->fetch_assoc()): ?>
                                <option value="<?php echo (int) $c['id']; ?>" <?php echo $selected_class_id === (int) $c['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($c['class_name'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Outstanding Only</option>
                        <option value="Overdue" <?php echo $selected_status === 'Overdue' ? 'selected' : ''; ?>>Overdue</option>
                        <option value="Due" <?php echo $selected_status === 'Due' ? 'selected' : ''; ?>>Due</option>
                        <option value="Cleared" <?php echo $selected_status === 'Cleared' ? 'selected' : ''; ?>>Cleared</option>
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
            <h5 class="mb-0">Outstanding Balances</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th>Student</th>
                            <th>Total Due</th>
                            <th>Total Paid</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th>Last Payment Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($outstanding && $outstanding->num_rows > 0): ?>
                            <?php $i = 1; ?>
                            <?php while ($row = $outstanding->fetch_assoc()): ?>
                                <?php
                                $balance = (float) $row['balance'];
                                $lastPaymentTs = $row['last_payment_date'] ? strtotime($row['last_payment_date']) : false;
                                $status = 'Due';
                                if ($balance <= 0) {
                                    $status = 'Cleared';
                                } elseif ($lastPaymentTs && $lastPaymentTs < strtotime('-30 days')) {
                                    $status = 'Overdue';
                                }
                                ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?php echo htmlspecialchars($row['student_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo number_format((float) $row['total_due'], 2); ?></td>
                                    <td><?php echo number_format((float) $row['total_paid'], 2); ?></td>
                                    <td class="text-danger fw-bold"><?php echo number_format($balance, 2); ?></td>
                                    <td><?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
                                        <?php
                                        echo $row['last_payment_date']
                                            ? htmlspecialchars(date('Y-m-d H:i', strtotime($row['last_payment_date'])), ENT_QUOTES, 'UTF-8')
                                            : '-';
                                        ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">No records yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../templates/footer.php'; ?>
