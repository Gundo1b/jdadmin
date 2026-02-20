<?php
require_once '../../config/db.php';

$alert = '';
$editingRecordId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$selectedStudentId = isset($_POST['student_id']) ? (int) $_POST['student_id'] : 0;
$selectedStudentLabel = '';
$periodValue = '';
$amountDueValue = '';
$amountPaidValue = '0';
$notesValue = '';

if ($selectedStudentId > 0) {
    $stmtStudent = $conn->prepare("SELECT id, first_name, last_name FROM students WHERE id = ? LIMIT 1");
    $stmtStudent->bind_param("i", $selectedStudentId);
    $stmtStudent->execute();
    $studentRes = $stmtStudent->get_result();
    if ($studentRes && $studentRes->num_rows > 0) {
        $studentRow = $studentRes->fetch_assoc();
        $selectedStudentLabel = trim(($studentRow['first_name'] ?? '') . ' ' . ($studentRow['last_name'] ?? '')) . ' (#' . (int) $studentRow['id'] . ')';
    }
    $stmtStudent->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $record_id = isset($_POST['record_id']) ? (int) $_POST['record_id'] : 0;
    $student_id = isset($_POST['student_id']) ? (int) $_POST['student_id'] : 0;
    $period = trim($_POST['period'] ?? '');
    $amount_due = isset($_POST['amount_due']) ? (float) $_POST['amount_due'] : 0;
    $amount_paid = isset($_POST['amount_paid']) ? (float) $_POST['amount_paid'] : 0;
    $notes = trim($_POST['notes'] ?? '');

    $periodValue = $period;
    $amountDueValue = $amount_due;
    $amountPaidValue = $amount_paid;
    $notesValue = $notes;

    if ($student_id <= 0 || $period === '' || $amount_due < 0 || $amount_paid < 0) {
        $alert = '<div class="alert alert-warning">Please search and select a student, then fill Period and valid amounts.</div>';
    } else {
        if (isset($_POST['update_record']) && $record_id > 0) {
            $stmt = $conn->prepare(
                "UPDATE student_fee_records
                 SET student_id = ?, period = ?, amount_due = ?, amount_paid = ?, notes = ?
                 WHERE id = ?"
            );
            $stmt->bind_param("isddsi", $student_id, $period, $amount_due, $amount_paid, $notes, $record_id);
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO student_fee_records (student_id, period, amount_due, amount_paid, notes)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("isdds", $student_id, $period, $amount_due, $amount_paid, $notes);
        }

        if ($stmt->execute()) {
            $alert = isset($_POST['update_record'])
                ? '<div class="alert alert-success">Student fee record updated successfully.</div>'
                : '<div class="alert alert-success">Student fee record saved successfully.</div>';
            $editingRecordId = 0;
            $selectedStudentId = 0;
            $selectedStudentLabel = '';
            $periodValue = '';
            $amountDueValue = '';
            $amountPaidValue = '0';
            $notesValue = '';
        } else {
            $alert = '<div class="alert alert-danger">Failed to save record: ' . htmlspecialchars($stmt->error, ENT_QUOTES, 'UTF-8') . '</div>';
        }
        $stmt->close();
    }
}

if ($editingRecordId > 0 && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $stmtEdit = $conn->prepare(
        "SELECT id, student_id, period, amount_due, amount_paid, notes
         FROM student_fee_records
         WHERE id = ?
         LIMIT 1"
    );
    $stmtEdit->bind_param("i", $editingRecordId);
    $stmtEdit->execute();
    $editRes = $stmtEdit->get_result();
    if ($editRes && $editRes->num_rows > 0) {
        $editRow = $editRes->fetch_assoc();
        $selectedStudentId = (int) $editRow['student_id'];
        $periodValue = $editRow['period'] ?? '';
        $amountDueValue = (string) ($editRow['amount_due'] ?? '');
        $amountPaidValue = (string) ($editRow['amount_paid'] ?? '0');
        $notesValue = $editRow['notes'] ?? '';
    } else {
        $editingRecordId = 0;
    }
    $stmtEdit->close();

    if ($selectedStudentId > 0) {
        $stmtStudent = $conn->prepare("SELECT id, first_name, last_name FROM students WHERE id = ? LIMIT 1");
        $stmtStudent->bind_param("i", $selectedStudentId);
        $stmtStudent->execute();
        $studentRes = $stmtStudent->get_result();
        if ($studentRes && $studentRes->num_rows > 0) {
            $studentRow = $studentRes->fetch_assoc();
            $selectedStudentLabel = trim(($studentRow['first_name'] ?? '') . ' ' . ($studentRow['last_name'] ?? '')) . ' (#' . (int) $studentRow['id'] . ')';
        }
        $stmtStudent->close();
    }
}

$records = $conn->query("SELECT sfr.id,
                                sfr.student_id,
                                CONCAT(s.first_name, ' ', s.last_name) AS student_name,
                                sfr.period,
                                sfr.amount_due,
                                sfr.amount_paid,
                                sfr.notes
                         FROM student_fee_records sfr
                         JOIN students s ON s.id = sfr.student_id
                         ORDER BY sfr.id DESC");

require_once '../../templates/header.php';
require_once '../../templates/sidebar.php';
?>

<div class="container-fluid">
    <h3 class="mb-4"><i class="bi bi-receipt"></i> Student Fee Records</h3>
    <?php echo $alert; ?>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><?php echo $editingRecordId > 0 ? 'Edit Fee Record' : 'Add Fee Record'; ?></h5>
        </div>
        <div class="card-body">
            <form action="#" method="POST" autocomplete="off">
                <?php if ($editingRecordId > 0): ?>
                    <input type="hidden" name="record_id" value="<?php echo (int) $editingRecordId; ?>">
                <?php endif; ?>
                <div class="row mb-3">
                    <div class="col-md-6 position-relative">
                        <label class="form-label">Student</label>
                        <input
                            type="text"
                            id="student_search"
                            class="form-control"
                            placeholder="Type name or ID (min 2 chars)"
                            value="<?php echo htmlspecialchars($selectedStudentLabel, ENT_QUOTES, 'UTF-8'); ?>"
                            required
                        >
                        <input type="hidden" name="student_id" id="student_id" value="<?php echo (int) $selectedStudentId; ?>">
                        <div id="student_results" class="list-group position-absolute w-100" style="z-index:1050;"></div>
                        <small class="text-muted">Search and click a student from the list.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Period</label>
                        <input type="text" name="period" class="form-control" placeholder="e.g. Term 1 / March 2026" value="<?php echo htmlspecialchars($periodValue, ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Amount Due</label>
                        <input type="number" step="0.01" min="0" name="amount_due" class="form-control" placeholder="e.g. 1500" value="<?php echo htmlspecialchars((string) $amountDueValue, ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Amount Paid</label>
                        <input type="number" step="0.01" min="0" name="amount_paid" class="form-control" placeholder="e.g. 500" value="<?php echo htmlspecialchars((string) $amountPaidValue, ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Notes</label>
                        <input type="text" name="notes" class="form-control" placeholder="Optional" value="<?php echo htmlspecialchars($notesValue, ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                </div>

                <?php if ($editingRecordId > 0): ?>
                    <button type="submit" name="update_record" class="btn btn-warning">
                        <i class="bi bi-pencil-square"></i> Update Record
                    </button>
                    <a href="<?php echo BASE_URL; ?>admin/fees/student_fee_records.php" class="btn btn-secondary">Cancel</a>
                <?php else: ?>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Save Record
                    </button>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white">
            <h5 class="mb-0">Fee Records</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th>Student</th>
                            <th>Period</th>
                            <th>Amount Due</th>
                            <th>Amount Paid</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($records && $records->num_rows > 0): ?>
                            <?php $i = 1; ?>
                            <?php while ($row = $records->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?php echo htmlspecialchars($row['student_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($row['period'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo number_format((float) $row['amount_due'], 2); ?></td>
                                    <td><?php echo number_format((float) $row['amount_paid'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($row['notes'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
                                        <a href="<?php echo BASE_URL; ?>admin/fees/student_fee_records.php?edit=<?php echo (int) $row['id']; ?>" class="btn btn-sm btn-outline-warning">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </a>
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

<script>
(function() {
    const input = document.getElementById('student_search');
    const hidden = document.getElementById('student_id');
    const results = document.getElementById('student_results');
    let timer = null;

    function clearResults() {
        results.innerHTML = '';
        results.style.display = 'none';
    }

    function renderItems(items) {
        if (!items.length) {
            results.innerHTML = '<div class="list-group-item text-muted">No students found.</div>';
            results.style.display = 'block';
            return;
        }

        results.innerHTML = items.map(item => (
            '<button type="button" class="list-group-item list-group-item-action" data-id="' + item.id + '" data-label="' + item.label.replace(/"/g, '&quot;') + '">' +
            item.label +
            '</button>'
        )).join('');
        results.style.display = 'block';
    }

    async function searchStudents(query) {
        try {
            const res = await fetch('<?php echo BASE_URL; ?>admin/fees/search_students.php?q=' + encodeURIComponent(query));
            const data = await res.json();
            renderItems(Array.isArray(data) ? data : []);
        } catch (e) {
            results.innerHTML = '<div class="list-group-item text-danger">Search failed.</div>';
            results.style.display = 'block';
        }
    }

    input.addEventListener('input', function() {
        hidden.value = '';
        const query = input.value.trim();
        if (query.length < 2) {
            clearResults();
            return;
        }
        clearTimeout(timer);
        timer = setTimeout(() => searchStudents(query), 250);
    });

    results.addEventListener('click', function(e) {
        const btn = e.target.closest('button[data-id]');
        if (!btn) return;
        hidden.value = btn.getAttribute('data-id');
        input.value = btn.getAttribute('data-label');
        clearResults();
    });

    input.addEventListener('blur', function() {
        setTimeout(clearResults, 150);
    });

    document.addEventListener('click', function(e) {
        if (!results.contains(e.target) && e.target !== input) {
            clearResults();
        }
    });
})();
</script>

<?php require_once '../../templates/footer.php'; ?>
