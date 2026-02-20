<?php
require_once '../../config/db.php';

$alert = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $structure_name = trim($_POST['structure_name'] ?? '');
    $amount = isset($_POST['amount']) ? (float) $_POST['amount'] : 0;
    $grade_id = isset($_POST['grade_id']) && $_POST['grade_id'] !== '' ? (string) ((int) $_POST['grade_id']) : '';
    $class_id = isset($_POST['class_id']) && $_POST['class_id'] !== '' ? (string) ((int) $_POST['class_id']) : '';

    if ($structure_name === '' || $amount <= 0) {
        $alert = '<div class="alert alert-warning">Please provide a valid name and amount.</div>';
    } else {
        $stmt = $conn->prepare("INSERT INTO fee_structures (structure_name, amount, grade_id, class_id) VALUES (?, ?, NULLIF(?, ''), NULLIF(?, ''))");
        $stmt->bind_param("sdss", $structure_name, $amount, $grade_id, $class_id);

        if ($stmt->execute()) {
            $alert = '<div class="alert alert-success">Fee structure saved successfully.</div>';
        } else {
            $alert = '<div class="alert alert-danger">Failed to save fee structure: ' . htmlspecialchars($stmt->error, ENT_QUOTES, 'UTF-8') . '</div>';
        }
        $stmt->close();
    }
}

$grades = $conn->query("SELECT * FROM grades ORDER BY name ASC");
$classes = $conn->query("SELECT id, class_name
                         FROM classes
                         ORDER BY class_name");
$structures = $conn->query("SELECT fs.id, fs.structure_name, fs.amount, g.name AS grade_name, c.class_name
                            FROM fee_structures fs
                            LEFT JOIN grades g ON fs.grade_id = g.id
                            LEFT JOIN classes c ON fs.class_id = c.id
                            ORDER BY fs.id DESC");

require_once '../../templates/header.php';
require_once '../../templates/sidebar.php';
?>

<div class="container-fluid">
    <h3 class="mb-4"><i class="bi bi-cash-coin"></i> Fee Structures</h3>
    <?php echo $alert; ?>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white">
            <h5 class="mb-0">Add Fee Structure</h5>
        </div>
        <div class="card-body">
            <form action="#" method="POST">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Name</label>
                        <input type="text" name="structure_name" class="form-control" placeholder="e.g. Term 1 Fees" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Amount</label>
                        <input type="number" name="amount" class="form-control" placeholder="e.g. 1500" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Grade</label>
                        <select name="grade_id" class="form-select">
                            <option value="">Select Grade</option>
                            <?php if ($grades): ?>
                                <?php while ($g = $grades->fetch_assoc()): ?>
                                    <option value="<?php echo $g['id']; ?>">
                                        <?php echo $g['name']; ?>
                                    </option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Curriculum</label>
                        <select name="class_id" class="form-select">
                            <option value="">Select Curriculum</option>
                            <?php if ($classes): ?>
                                <?php while ($c = $classes->fetch_assoc()): ?>
                                    <option value="<?php echo $c['id']; ?>">
                                        <?php echo $c['class_name']; ?>
                                    </option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Save Fee Structure
                </button>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0 mt-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">Saved Fee Structures</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Amount</th>
                            <th>Grade</th>
                            <th>Curriculum</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($structures && $structures->num_rows > 0): ?>
                            <?php $i = 1; ?>
                            <?php while ($row = $structures->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?php echo htmlspecialchars($row['structure_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo number_format((float) $row['amount'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($row['grade_name'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($row['class_name'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">No fee structures saved yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../templates/footer.php'; ?>
