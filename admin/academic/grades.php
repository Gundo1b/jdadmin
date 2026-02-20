<?php
require_once '../../config/db.php';
require_once '../../templates/header.php';
require_once '../../templates/sidebar.php';

$message = '';

// Handle Delete Action
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($conn->query("DELETE FROM grades WHERE id = $id")) {
        $message = '<div class="alert alert-success">Grade deleted successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error deleting grade: ' . $conn->error . '</div>';
    }
}

// Handle Add Action
if (isset($_POST['add_grade'])) {
    $name = $conn->real_escape_string($_POST['name']);
    if ($conn->query("INSERT INTO grades (name) VALUES ('$name')")) {
        $message = '<div class="alert alert-success">Grade added successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error adding grade: ' . $conn->error . '</div>';
    }
}

// Handle Reset/Cleanup Action (Sequential numbering)
if (isset($_POST['reset_ids'])) {
    $conn->query("SET @count = 0");
    $conn->query("UPDATE grades SET id = (@count := @count + 1)");
    $conn->query("ALTER TABLE grades AUTO_INCREMENT = 1");
    $message = '<div class="alert alert-info">IDs have been cleaned up and reset to start from 1!</div>';
}
?>

<div class="container-fluid">
    <h3 class="mb-4"><i class="bi bi-list-ol"></i> Manage Grades</h3>

    <?php echo $message; ?>

    <div class="row">
        <!-- ADD GRADE FORM -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Add New Grade</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Grade Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g., Grade 1" required>
                        </div>
                        <button type="submit" name="add_grade" class="btn btn-primary w-100">
                            <i class="bi bi-save"></i> Save Grade
                        </button>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="mb-0 text-secondary small">Maintenance</h5>
                </div>
                <div class="card-body">
                    <form method="POST"
                        onsubmit="return confirm('This will re-number all grades to start from 1. Proceed?');">
                        <button type="submit" name="reset_ids" class="btn btn-outline-secondary btn-sm w-100">
                            <i class="bi bi-arrow-repeat"></i> Clean Up & Reset IDs
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- VIEW GRADES TABLE -->
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Existing Grades</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th width="80">#</th>
                                    <th>Grade Name</th>
                                    <th width="100">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $grades = $conn->query("SELECT * FROM grades ORDER BY id ASC");
                                if ($grades->num_rows > 0):
                                    $i = 1;
                                    while ($row = $grades->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $i++; ?></td>
                                            <td><strong><?php echo $row['name']; ?></strong></td>
                                            <td>
                                                <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Are you sure you want to delete this grade?');">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile;
                                else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">No grades found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../templates/footer.php'; ?>