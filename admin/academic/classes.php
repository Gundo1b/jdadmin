<?php
require_once '../../config/db.php';

$alert = '';
$editClass = null;

if (isset($_POST['add_class'])) {
    $class_name = trim($_POST['class_name'] ?? '');
    if ($class_name !== '') {
        $stmt = $conn->prepare("INSERT INTO classes (class_name) VALUES (?)");
        $stmt->bind_param("s", $class_name);
        if ($stmt->execute()) {
            $alert = '<div class="alert alert-success">Curriculum added successfully.</div>';
        } else {
            $alert = '<div class="alert alert-danger">Failed to add curriculum.</div>';
        }
        $stmt->close();
    } else {
        $alert = '<div class="alert alert-warning">Curriculum name is required.</div>';
    }
}

if (isset($_POST['update_class'])) {
    $class_id = (int) ($_POST['class_id'] ?? 0);
    $class_name = trim($_POST['class_name'] ?? '');
    if ($class_id > 0 && $class_name !== '') {
        $stmt = $conn->prepare("UPDATE classes SET class_name = ? WHERE id = ?");
        $stmt->bind_param("si", $class_name, $class_id);
        if ($stmt->execute()) {
            $alert = '<div class="alert alert-success">Curriculum updated successfully.</div>';
        } else {
            $alert = '<div class="alert alert-danger">Failed to update curriculum.</div>';
        }
        $stmt->close();
    } else {
        $alert = '<div class="alert alert-warning">Valid curriculum ID and name are required.</div>';
    }
}

if (isset($_GET['delete'])) {
    $class_id = (int) $_GET['delete'];
    if ($class_id > 0) {
        $stmt = $conn->prepare("DELETE FROM classes WHERE id = ?");
        $stmt->bind_param("i", $class_id);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $alert = '<div class="alert alert-success">Curriculum deleted successfully.</div>';
            } else {
                $alert = '<div class="alert alert-warning">Curriculum not found.</div>';
            }
        } else {
            $alert = '<div class="alert alert-danger">Cannot delete curriculum currently in use.</div>';
        }
        $stmt->close();
    }
}

if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    if ($edit_id > 0) {
        $stmt = $conn->prepare("SELECT id, class_name FROM classes WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $edit_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $editClass = $result->fetch_assoc();
        }
        $stmt->close();
    }
}

require_once '../../templates/header.php';
require_once '../../templates/sidebar.php';
?>

<div class="container-fluid">
    <h3 class="mb-4"><i class="bi bi-building"></i> Manage Curriculum</h3>
    <?php echo $alert; ?>

    <div class="row">
        <!-- ADD CLASS FORM -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><?php echo $editClass ? 'Edit Curriculum' : 'Add New Curriculum'; ?></h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <?php if ($editClass): ?>
                            <input type="hidden" name="class_id" value="<?php echo (int) $editClass['id']; ?>">
                        <?php endif; ?>
                        <div class="mb-3">
                            <label class="form-label">Curriculum Name</label>
                            <input type="text" name="class_name" class="form-control" placeholder="e.g., Class A"
                                value="<?php echo htmlspecialchars($editClass['class_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                required>
                        </div>
                        <?php if ($editClass): ?>
                            <button type="submit" name="update_class" class="btn btn-warning w-100 mb-2">
                                <i class="bi bi-pencil-square"></i> Update Curriculum
                            </button>
                            <a href="<?php echo BASE_URL; ?>admin/academic/classes.php" class="btn btn-secondary w-100">
                                Cancel Edit
                            </a>
                        <?php else: ?>
                            <button type="submit" name="add_class" class="btn btn-primary w-100">
                                <i class="bi bi-save"></i> Save Curriculum
                            </button>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <!-- VIEW CLASSES TABLE -->
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Existing Curriculum</h5>
                </div>
                <div class="card-body">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Curriculum Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $classes = $conn->query("SELECT c.*
                                                     FROM classes c
                                                     ORDER BY c.class_name");
                            if ($classes->num_rows > 0):
                                $i = 1;
                                while ($row = $classes->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $i++; ?></td>
                                        <td><?php echo htmlspecialchars($row['class_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <a href="<?php echo BASE_URL; ?>admin/academic/classes.php?edit=<?php echo (int) $row['id']; ?>"
                                                class="btn btn-sm btn-outline-warning">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>admin/academic/classes.php?delete=<?php echo (int) $row['id']; ?>"
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Delete this curriculum?');">
                                                <i class="bi bi-trash"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile;
                            else: ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No classes found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../templates/footer.php'; ?>
