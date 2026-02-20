<?php
require_once '../../config/db.php';
require_once '../../templates/header.php';
require_once '../../templates/sidebar.php';

$alert = '';
$tableReady = false;
$subject = '';
$messageBody = '';
$selectedTutorIds = [];
$selectedStudentIds = [];
$recentOnly = (($_GET['view'] ?? '') === 'recent');

function buildAttachmentUrl(int $messageId, ?string $attachmentPath, ?string $storedName): string
{
    $path = trim((string) $attachmentPath);
    $stored = trim((string) $storedName);

    if ($messageId <= 0) {
        return '';
    }

    if ($path === '' && $stored === '') {
        return '';
    }

    if ($path !== '' && preg_match('/^https?:\/\//i', $path)) {
        return $path;
    }

    return rtrim(BASE_URL, '/') . '/admin/communication/download_message_attachment.php?id=' . $messageId;
}

$tableCheckSql = "SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'tutor_messages' LIMIT 1";
$tableCheckResult = $conn->query($tableCheckSql);
if ($tableCheckResult && $tableCheckResult->num_rows > 0) {
    $tableReady = true;
} else {
    $alert = '<div class="alert alert-danger">tutor_messages table not found. Please run messages.sql first.</div>';
}

$tutors = [];
$students = [];

$tutorResult = $conn->query("SELECT id, first_name, last_name, email FROM tutors ORDER BY first_name, last_name");
if ($tutorResult) {
    while ($row = $tutorResult->fetch_assoc()) {
        $tutors[] = $row;
    }
}

$studentResult = $conn->query("SELECT id, first_name, last_name, email FROM students ORDER BY first_name, last_name");
if ($studentResult) {
    while ($row = $studentResult->fetch_assoc()) {
        $students[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tableReady) {
    $action = $_POST['action'] ?? 'send';

    if ($action === 'delete') {
        $messageId = (int) ($_POST['message_id'] ?? 0);
        if ($messageId <= 0) {
            $alert = '<div class="alert alert-warning">Invalid message selected for deletion.</div>';
        } else {
            $deleteStmt = $conn->prepare("DELETE FROM tutor_messages WHERE id = ?");
            if (!$deleteStmt) {
                $alert = '<div class="alert alert-danger">Unable to prepare delete statement: ' . htmlspecialchars($conn->error) . '</div>';
            } else {
                $deleteStmt->bind_param('i', $messageId);
                if ($deleteStmt->execute()) {
                    if ($deleteStmt->affected_rows > 0) {
                        $alert = '<div class="alert alert-success">Message deleted successfully.</div>';
                    } else {
                        $alert = '<div class="alert alert-warning">Message not found or already deleted.</div>';
                    }
                } else {
                    $alert = '<div class="alert alert-danger">Failed to delete message: ' . htmlspecialchars($deleteStmt->error) . '</div>';
                }
                $deleteStmt->close();
            }
        }
    } elseif ($action === 'reply') {
        $originalMessageId = (int) ($_POST['original_message_id'] ?? 0);
        $replySubject = trim($_POST['reply_subject'] ?? '');
        $replyBody = trim($_POST['reply_body'] ?? '');

        if ($originalMessageId <= 0) {
            $alert = '<div class="alert alert-warning">Invalid message selected for reply.</div>';
        } elseif ($replySubject === '' || $replyBody === '') {
            $alert = '<div class="alert alert-warning">Reply subject and message are required.</div>';
        } else {
            $sourceStmt = $conn->prepare("SELECT id, tutor_id, recipient_type, student_id, direction FROM tutor_messages WHERE id = ? LIMIT 1");
            if (!$sourceStmt) {
                $alert = '<div class="alert alert-danger">Unable to prepare source message lookup: ' . htmlspecialchars($conn->error) . '</div>';
            } else {
                $sourceStmt->bind_param('i', $originalMessageId);
                $sourceStmt->execute();
                $sourceResult = $sourceStmt->get_result();
                $sourceMessage = $sourceResult ? $sourceResult->fetch_assoc() : null;
                $sourceStmt->close();

                if (!$sourceMessage) {
                    $alert = '<div class="alert alert-warning">Original message not found.</div>';
                } else {
                    $tutorId = (int) $sourceMessage['tutor_id'];
                    $recipientType = $sourceMessage['recipient_type'] === 'student' ? 'student' : 'admin';
                    $studentId = $recipientType === 'student' ? (int) ($sourceMessage['student_id'] ?? 0) : null;
                    $originalDirection = $sourceMessage['direction'] === 'inbound' ? 'inbound' : 'outbound';
                    // Reply goes to the original sender: reverse the original message direction.
                    $direction = $originalDirection === 'inbound' ? 'outbound' : 'inbound';

                    if ($recipientType === 'student' && $studentId <= 0) {
                        $alert = '<div class="alert alert-warning">Cannot reply: student recipient is missing.</div>';
                    } else {
                        $replyStmt = $conn->prepare(
                            "INSERT INTO tutor_messages (tutor_id, recipient_type, student_id, message_subject, message_body, direction)
                             VALUES (?, ?, ?, ?, ?, ?)"
                        );

                        if (!$replyStmt) {
                            $alert = '<div class="alert alert-danger">Unable to prepare reply statement: ' . htmlspecialchars($conn->error) . '</div>';
                        } else {
                            $replyStmt->bind_param('isisss', $tutorId, $recipientType, $studentId, $replySubject, $replyBody, $direction);
                            if ($replyStmt->execute()) {
                                $alert = '<div class="alert alert-success">Reply sent successfully.</div>';
                            } else {
                                $alert = '<div class="alert alert-danger">Failed to send reply: ' . htmlspecialchars($replyStmt->error) . '</div>';
                            }
                            $replyStmt->close();
                        }
                    }
                }
            }
        }
    } else {
        $subject = trim($_POST['subject'] ?? '');
        $messageBody = trim($_POST['message_body'] ?? '');

        $tutorIds = array_map('intval', $_POST['tutor_ids'] ?? []);
        $studentIds = array_map('intval', $_POST['student_ids'] ?? []);

        $tutorIds = array_values(array_unique(array_filter($tutorIds, function ($id) {
            return $id > 0;
        })));
        $studentIds = array_values(array_unique(array_filter($studentIds, function ($id) {
            return $id > 0;
        })));
        $selectedTutorIds = $tutorIds;
        $selectedStudentIds = $studentIds;

        if ($subject === '' || $messageBody === '') {
            $alert = '<div class="alert alert-warning">Subject and message are required.</div>';
        } elseif (empty($tutorIds) && empty($studentIds)) {
            $alert = '<div class="alert alert-warning">Select at least one tutor or student.</div>';
        } elseif (!empty($studentIds) && empty($tutorIds)) {
            $alert = '<div class="alert alert-warning">Select at least one tutor when sending to students (required by messages.sql schema).</div>';
        } else {
            $insertSql = "INSERT INTO tutor_messages (tutor_id, recipient_type, student_id, message_subject, message_body, direction) VALUES (?, ?, ?, ?, ?, ?)";
            $insertStmt = $conn->prepare($insertSql);

            if (!$insertStmt) {
                $alert = '<div class="alert alert-danger">Unable to prepare insert statement: ' . htmlspecialchars($conn->error) . '</div>';
            } else {
                $sentCount = 0;

                try {
                    $conn->begin_transaction();

                    foreach ($tutorIds as $tutorId) {
                        $recipientType = 'admin';
                        $studentId = null;
                        $direction = 'inbound';
                        $insertStmt->bind_param('isisss', $tutorId, $recipientType, $studentId, $subject, $messageBody, $direction);
                        if (!$insertStmt->execute()) {
                            throw new Exception($insertStmt->error);
                        }
                        $sentCount++;
                    }

                    if (!empty($studentIds)) {
                        foreach ($tutorIds as $tutorId) {
                            foreach ($studentIds as $studentId) {
                                $recipientType = 'student';
                                $direction = 'outbound';
                                $insertStmt->bind_param('isisss', $tutorId, $recipientType, $studentId, $subject, $messageBody, $direction);
                                if (!$insertStmt->execute()) {
                                    throw new Exception($insertStmt->error);
                                }
                                $sentCount++;
                            }
                        }
                    }

                    $conn->commit();
                    $alert = '<div class="alert alert-success">Message sent successfully to ' . $sentCount . ' recipient record(s).</div>';
                } catch (Throwable $e) {
                    $conn->rollback();
                    $alert = '<div class="alert alert-danger">Failed to send message: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }

                $insertStmt->close();
            }
        }
    }
}

$recentMessages = [];
$sentMessages = [];
if ($tableReady) {
    $recentSql = "SELECT tm.id, tm.recipient_type, tm.message_subject, tm.message_body, tm.sent_at,
        tm.attachment_original_name, tm.attachment_stored_name, tm.attachment_path,
        CONCAT(t.first_name, ' ', t.last_name) AS tutor_name,
        CONCAT(s.first_name, ' ', s.last_name) AS student_name
    FROM tutor_messages tm
    LEFT JOIN tutors t ON tm.tutor_id = t.id
    LEFT JOIN students s ON tm.student_id = s.id
    ORDER BY tm.sent_at DESC, tm.id DESC
    LIMIT 20";

    $recentResult = $conn->query($recentSql);
    if ($recentResult) {
        while ($row = $recentResult->fetch_assoc()) {
            $row['attachment_url'] = buildAttachmentUrl((int) ($row['id'] ?? 0), $row['attachment_path'] ?? '', $row['attachment_stored_name'] ?? '');
            $recentMessages[] = $row;
        }
    }

    $sentSql = "SELECT tm.id, tm.recipient_type, tm.message_subject, tm.message_body, tm.sent_at,
        tm.attachment_original_name, tm.attachment_stored_name, tm.attachment_path,
        CONCAT(t.first_name, ' ', t.last_name) AS tutor_name,
        CONCAT(s.first_name, ' ', s.last_name) AS student_name
    FROM tutor_messages tm
    LEFT JOIN tutors t ON tm.tutor_id = t.id
    LEFT JOIN students s ON tm.student_id = s.id
    WHERE tm.direction = 'outbound'
    ORDER BY tm.sent_at DESC, tm.id DESC
    LIMIT 20";

    $sentResult = $conn->query($sentSql);
    if ($sentResult) {
        while ($row = $sentResult->fetch_assoc()) {
            $row['attachment_url'] = buildAttachmentUrl((int) ($row['id'] ?? 0), $row['attachment_path'] ?? '', $row['attachment_stored_name'] ?? '');
            $sentMessages[] = $row;
        }
    }
}
?>

<div class="container-fluid">
    <style>
        .recipient-panel {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            max-height: 280px;
            overflow-y: auto;
            padding: 10px 12px;
            background: #fafbfc;
        }

        .recipient-panel .form-check {
            padding: 8px 0 8px 1.75rem;
            border-bottom: 1px solid #eceff3;
        }

        .recipient-panel .form-check:last-child {
            border-bottom: 0;
        }

        .recipient-tools {
            display: flex;
            gap: 8px;
            margin-bottom: 8px;
        }
    </style>
    <h3 class="mb-4"><i class="bi bi-chat-dots"></i> Direct Messaging</h3>

    <?php echo $alert; ?>

    <?php if (!$recentOnly): ?>
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">Send Message</h5>
        </div>
        <div class="card-body">
            <form method="post">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label" for="subject">Subject</label>
                        <input type="text" class="form-control" id="subject" name="subject" maxlength="255" value="<?php echo htmlspecialchars($subject); ?>" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="message_body">Message</label>
                        <textarea class="form-control" id="message_body" name="message_body" rows="5" required><?php echo htmlspecialchars($messageBody); ?></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Choose Tutor(s)</label>
                        <div class="recipient-tools">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleGroup('tutor', true)">Select all</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleGroup('tutor', false)">Clear</button>
                        </div>
                        <div class="recipient-panel">
                            <?php if (empty($tutors)): ?>
                                <p class="text-muted mb-0">No tutors found.</p>
                            <?php else: ?>
                                <?php foreach ($tutors as $tutor): ?>
                                    <?php $tid = (int)$tutor['id']; ?>
                                    <div class="form-check">
                                        <input
                                            class="form-check-input tutor-check"
                                            type="checkbox"
                                            value="<?php echo $tid; ?>"
                                            id="tutor_<?php echo $tid; ?>"
                                            name="tutor_ids[]"
                                            <?php echo in_array($tid, $selectedTutorIds, true) ? 'checked' : ''; ?>
                                        >
                                        <label class="form-check-label" for="tutor_<?php echo $tid; ?>">
                                            <?php echo htmlspecialchars(trim($tutor['first_name'] . ' ' . $tutor['last_name']) . ' (' . $tutor['email'] . ')'); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Choose Student(s)</label>
                        <div class="recipient-tools">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleGroup('student', true)">Select all</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleGroup('student', false)">Clear</button>
                        </div>
                        <div class="recipient-panel">
                            <?php if (empty($students)): ?>
                                <p class="text-muted mb-0">No students found.</p>
                            <?php else: ?>
                                <?php foreach ($students as $student): ?>
                                    <?php $sid = (int)$student['id']; ?>
                                    <div class="form-check">
                                        <input
                                            class="form-check-input student-check"
                                            type="checkbox"
                                            value="<?php echo $sid; ?>"
                                            id="student_<?php echo $sid; ?>"
                                            name="student_ids[]"
                                            <?php echo in_array($sid, $selectedStudentIds, true) ? 'checked' : ''; ?>
                                        >
                                        <label class="form-check-label" for="student_<?php echo $sid; ?>">
                                            <?php echo htmlspecialchars(trim($student['first_name'] . ' ' . $student['last_name']) . ' (' . ($student['email'] ?: 'No email') . ')'); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> Send Message
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white">
            <h5 class="mb-0">Recent Messages</h5>
        </div>
        <div class="card-body">
            <?php if (empty($recentMessages)): ?>
                <p class="text-muted mb-0">No direct messages sent yet.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Recipient Type</th>
                                <th>Tutor</th>
                                <th>Student</th>
                                <th>Subject</th>
                                <th>Message</th>
                                <th>Document</th>
                                <th>Sent At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentMessages as $msg): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars(ucfirst($msg['recipient_type'])); ?></td>
                                    <td><?php echo htmlspecialchars($msg['tutor_name'] ?: 'Unknown Tutor'); ?></td>
                                    <td><?php echo htmlspecialchars($msg['student_name'] ?: '-'); ?></td>
                                    <td><?php echo htmlspecialchars($msg['message_subject']); ?></td>
                                    <td><?php echo nl2br(htmlspecialchars($msg['message_body'])); ?></td>
                                    <td>
                                        <?php if (!empty($msg['attachment_original_name']) || !empty($msg['attachment_url'])): ?>
                                            <span class="badge text-bg-info">Attached</span>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($msg['sent_at']); ?></td>
                                    <td>
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-secondary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#viewMessageModal"
                                            data-subject="<?php echo htmlspecialchars($msg['message_subject'], ENT_QUOTES); ?>"
                                            data-message="<?php echo htmlspecialchars($msg['message_body'], ENT_QUOTES); ?>"
                                            data-recipient-type="<?php echo htmlspecialchars($msg['recipient_type'], ENT_QUOTES); ?>"
                                            data-tutor="<?php echo htmlspecialchars($msg['tutor_name'] ?: 'Unknown Tutor', ENT_QUOTES); ?>"
                                            data-student="<?php echo htmlspecialchars($msg['student_name'] ?: '-', ENT_QUOTES); ?>"
                                            data-sent-at="<?php echo htmlspecialchars($msg['sent_at'], ENT_QUOTES); ?>"
                                            data-attachment-name="<?php echo htmlspecialchars($msg['attachment_original_name'] ?: 'Open attachment', ENT_QUOTES); ?>"
                                            data-attachment-url="<?php echo htmlspecialchars($msg['attachment_url'] ?? '', ENT_QUOTES); ?>"
                                        >
                                            Open
                                        </button>
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#replyModal"
                                            data-message-id="<?php echo (int)$msg['id']; ?>"
                                            data-subject="<?php echo htmlspecialchars($msg['message_subject'], ENT_QUOTES); ?>"
                                        >
                                            Reply
                                        </button>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="message_id" value="<?php echo (int)$msg['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this message?');">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card shadow-sm border-0 mt-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">Sent Messages</h5>
        </div>
        <div class="card-body">
            <?php if (empty($sentMessages)): ?>
                <p class="text-muted mb-0">No sent messages yet.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Recipient Type</th>
                                <th>Tutor</th>
                                <th>Student</th>
                                <th>Subject</th>
                                <th>Message</th>
                                <th>Sent At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sentMessages as $msg): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars(ucfirst($msg['recipient_type'])); ?></td>
                                    <td><?php echo htmlspecialchars($msg['tutor_name'] ?: 'Unknown Tutor'); ?></td>
                                    <td><?php echo htmlspecialchars($msg['student_name'] ?: '-'); ?></td>
                                    <td><?php echo htmlspecialchars($msg['message_subject']); ?></td>
                                    <td><?php echo nl2br(htmlspecialchars($msg['message_body'])); ?></td>
                                    <td><?php echo htmlspecialchars($msg['sent_at']); ?></td>
                                    <td>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="message_id" value="<?php echo (int)$msg['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this message?');">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="viewMessageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Message Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2"><strong>Recipient Type:</strong> <span id="view_recipient_type"></span></p>
                <p class="mb-2"><strong>Tutor:</strong> <span id="view_tutor"></span></p>
                <p class="mb-2"><strong>Student:</strong> <span id="view_student"></span></p>
                <p class="mb-2"><strong>Sent At:</strong> <span id="view_sent_at"></span></p>
                <p class="mb-2"><strong>Subject:</strong> <span id="view_subject"></span></p>
                <div class="mb-2">
                    <strong>Message:</strong>
                    <div id="view_message_body" class="border rounded p-3 mt-1 bg-light"></div>
                </div>
                <div id="view_attachment_wrap" class="mt-3" style="display:none;">
                    <strong>Document:</strong>
                    <div class="mt-1">
                        <a id="view_attachment_link" class="btn btn-sm btn-outline-primary" href="#" target="_blank" rel="noopener">Open document</a>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="replyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title">Reply to Message</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="reply">
                    <input type="hidden" id="reply_original_message_id" name="original_message_id" value="">
                    <div class="mb-3">
                        <label for="reply_subject" class="form-label">Subject</label>
                        <input type="text" class="form-control" id="reply_subject" name="reply_subject" maxlength="255" required>
                    </div>
                    <div class="mb-3">
                        <label for="reply_body" class="form-label">Message</label>
                        <textarea class="form-control" id="reply_body" name="reply_body" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send Reply</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleGroup(group, checked) {
        const selector = group === 'tutor' ? '.tutor-check' : '.student-check';
        document.querySelectorAll(selector).forEach((el) => {
            el.checked = checked;
        });
    }

    const replyModal = document.getElementById('replyModal');
    if (replyModal) {
        replyModal.addEventListener('show.bs.modal', function (event) {
            const trigger = event.relatedTarget;
            const messageId = trigger.getAttribute('data-message-id') || '';
            const subject = trigger.getAttribute('data-subject') || '';

            document.getElementById('reply_original_message_id').value = messageId;
            document.getElementById('reply_subject').value = subject ? ('Re: ' + subject) : 'Re:';
            document.getElementById('reply_body').value = '';
        });
    }

    const viewModal = document.getElementById('viewMessageModal');
    if (viewModal) {
        viewModal.addEventListener('show.bs.modal', function (event) {
            const trigger = event.relatedTarget;

            const subject = trigger.getAttribute('data-subject') || '';
            const message = trigger.getAttribute('data-message') || '';
            const recipientType = trigger.getAttribute('data-recipient-type') || '';
            const tutor = trigger.getAttribute('data-tutor') || '';
            const student = trigger.getAttribute('data-student') || '';
            const sentAt = trigger.getAttribute('data-sent-at') || '';
            const attachmentName = trigger.getAttribute('data-attachment-name') || 'Open document';
            const attachmentUrl = trigger.getAttribute('data-attachment-url') || '';

            document.getElementById('view_subject').textContent = subject;
            document.getElementById('view_recipient_type').textContent = recipientType;
            document.getElementById('view_tutor').textContent = tutor;
            document.getElementById('view_student').textContent = student;
            document.getElementById('view_sent_at').textContent = sentAt;
            document.getElementById('view_message_body').textContent = message;

            const wrap = document.getElementById('view_attachment_wrap');
            const link = document.getElementById('view_attachment_link');
            if (attachmentUrl) {
                link.setAttribute('href', attachmentUrl);
                link.textContent = attachmentName;
                wrap.style.display = 'block';
            } else {
                link.setAttribute('href', '#');
                link.textContent = 'Open document';
                wrap.style.display = 'none';
            }
        });
    }
</script>

<?php require_once '../../templates/footer.php'; ?>
