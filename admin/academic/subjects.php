<?php
require_once '../../config/db.php';
require_once '../../templates/header.php';
require_once '../../templates/sidebar.php';

$message = '';

// Handle Delete Action
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($conn->query("DELETE FROM subjects WHERE id = $id")) {
        $message = '<div class="alert alert-success">Subject deleted successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error deleting subject: ' . $conn->error . '</div>';
    }
}

// Handle Add Action
if (isset($_POST['add_subject'])) {
    $subject_name = $conn->real_escape_string($_POST['subject_name']);
    $description = $conn->real_escape_string($_POST['description']);

    if ($conn->query("INSERT INTO subjects (subject_name, description) VALUES ('$subject_name', '$description')")) {
        $message = '<div class="alert alert-success">Subject added successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error adding subject: ' . $conn->error . '</div>';
    }
}

// Handle Update Action
if (isset($_POST['update_subject'])) {
    $id = intval($_POST['subject_id']);
    $subject_name = $conn->real_escape_string($_POST['subject_name']);
    $description = $conn->real_escape_string($_POST['description']);

    if ($conn->query("UPDATE subjects SET subject_name='$subject_name', description='$description' WHERE id=$id")) {
        $message = '<div class="alert alert-success">Subject updated successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error updating subject: ' . $conn->error . '</div>';
    }
}
?>

<div class="container-fluid">
    <h3 class="mb-4"><i class="bi bi-journal-bookmark"></i> Manage Subjects</h3>

    <?php echo $message; ?>

    <div class="row">
        <!-- ADD SUBJECT FORM -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Add New Subject</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Subject Name</label>
                            <input type="text" name="subject_name" class="form-control" placeholder="e.g., Mathematics"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <button type="submit" name="add_subject" class="btn btn-primary w-100">
                            <i class="bi bi-save"></i> Save Subject
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- VIEW SUBJECTS TABLE -->
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Existing Subjects</h5>
                </div>
                <div class="card-body">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th width="80">#</th>
                                <th>Subject Name</th>
                                <th>Description</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $subjects = $conn->query("SELECT s.* 
                                                      FROM subjects s 
                                                      ORDER BY s.subject_name");
                            if ($subjects->num_rows > 0):
                                $i = 1;
                                while ($row = $subjects->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $i++; ?></td>
                                        <td><strong><?php echo $row['subject_name']; ?></strong></td>
                                        <td><small class="text-muted"><?php echo $row['description']; ?></small></td>
                                        <td>
                                            <button class="btn btn-warning btn-sm text-white edit-btn"
                                                data-id="<?php echo $row['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($row['subject_name']); ?>"
                                                data-desc="<?php echo htmlspecialchars($row['description']); ?>"
                                                data-bs-toggle="modal" data-bs-target="#editModal">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm"
                                                onclick="return confirm('Are you sure you want to delete this subject?');">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile;
                            else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No subjects found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- EDIT MODAL -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">Edit Subject</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="subject_id" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Subject Name</label>
                        <input type="text" name="subject_name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Description</label>
                        <textarea name="description" id="edit_desc" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_subject" class="btn btn-warning text-white">Update
                        Subject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function () {
            document.getElementById('edit_id').value = this.dataset.id;
            document.getElementById('edit_name').value = this.dataset.name;
            document.getElementById('edit_desc').value = this.dataset.desc;
        });
    });
</script>

<?php require_once '../../templates/footer.php'; ?>