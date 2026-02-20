<?php
require_once '../../config/db.php';

$selectedStudentId = isset($_GET['student_id']) ? (int) $_GET['student_id'] : 0;
$viewRecordId = isset($_GET['view']) ? (int) $_GET['view'] : 0;

$selectedStudentLabel = '';
$selectedStudent = null;
$invoiceRecord = null;
$studentSubjects = [];

if ($selectedStudentId > 0) {
    $stmtStudent = $conn->prepare(
        "SELECT s.id,
                s.first_name,
                s.last_name,
                s.email,
                g.name AS grade_name,
                c.class_name
         FROM students s
         LEFT JOIN grades g ON s.grade_id = g.id
         LEFT JOIN classes c ON s.class_id = c.id
         WHERE s.id = ?
         LIMIT 1"
    );
    $stmtStudent->bind_param("i", $selectedStudentId);
    $stmtStudent->execute();
    $studentRes = $stmtStudent->get_result();
    if ($studentRes && $studentRes->num_rows > 0) {
        $selectedStudent = $studentRes->fetch_assoc();
        $selectedStudentLabel = trim(($selectedStudent['first_name'] ?? '') . ' ' . ($selectedStudent['last_name'] ?? '')) . ' (#' . (int) $selectedStudent['id'] . ')';
    } else {
        $selectedStudentId = 0;
        $viewRecordId = 0;
    }
    $stmtStudent->close();

    if ($selectedStudentId > 0) {
        $stmtSubjects = $conn->prepare(
            "SELECT sub.subject_name
             FROM student_subjects ss
             JOIN subjects sub ON ss.subject_id = sub.id
             WHERE ss.student_id = ?
             ORDER BY sub.subject_name ASC"
        );
        $stmtSubjects->bind_param("i", $selectedStudentId);
        $stmtSubjects->execute();
        $subjectsRes = $stmtSubjects->get_result();
        if ($subjectsRes) {
            while ($subjectRow = $subjectsRes->fetch_assoc()) {
                $studentSubjects[] = $subjectRow['subject_name'];
            }
        }
        $stmtSubjects->close();
    }
}

$records = null;
if ($selectedStudentId > 0) {
    $stmtRecords = $conn->prepare(
        "SELECT id, period, amount_due, amount_paid, notes, created_at, updated_at
         FROM student_fee_records
         WHERE student_id = ?
         ORDER BY id DESC"
    );
    $stmtRecords->bind_param("i", $selectedStudentId);
    $stmtRecords->execute();
    $records = $stmtRecords->get_result();
    $stmtRecords->close();
}

if ($selectedStudentId > 0 && $viewRecordId > 0) {
    $stmtInvoice = $conn->prepare(
        "SELECT id, period, amount_due, amount_paid, notes, created_at, updated_at
         FROM student_fee_records
         WHERE id = ? AND student_id = ?
         LIMIT 1"
    );
    $stmtInvoice->bind_param("ii", $viewRecordId, $selectedStudentId);
    $stmtInvoice->execute();
    $invoiceRes = $stmtInvoice->get_result();
    if ($invoiceRes && $invoiceRes->num_rows > 0) {
        $invoiceRecord = $invoiceRes->fetch_assoc();
    } else {
        $viewRecordId = 0;
    }
    $stmtInvoice->close();
}

require_once '../../templates/header.php';
require_once '../../templates/sidebar.php';
?>

<div class="container-fluid">
    <h3 class="mb-4"><i class="bi bi-file-earmark-medical"></i> Invoice</h3>

    <div class="card shadow-sm border-0 mb-4 no-print">
        <div class="card-header bg-white">
            <h5 class="mb-0">Select Student</h5>
        </div>
        <div class="card-body">
            <form method="GET" autocomplete="off">
                <div class="row g-3">
                    <div class="col-md-8 position-relative">
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
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Load
                        </button>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <a href="<?php echo BASE_URL; ?>admin/fees/invoice.php" class="btn btn-outline-secondary w-100">
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4 no-print">
        <div class="card-header bg-white">
            <h5 class="mb-0">Student Fee Records</h5>
        </div>
        <div class="card-body">
            <?php if ($selectedStudentId <= 0): ?>
                <div class="text-muted">Select a student to view invoice records.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th style="width:60px;">#</th>
                                <th>Period</th>
                                <th>Amount Due</th>
                                <th>Amount Paid</th>
                                <th>Balance</th>
                                <th>Updated</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($records && $records->num_rows > 0): ?>
                                <?php $i = 1; ?>
                                <?php while ($row = $records->fetch_assoc()): ?>
                                    <?php $balance = (float) $row['amount_due'] - (float) $row['amount_paid']; ?>
                                    <tr>
                                        <td><?php echo $i++; ?></td>
                                        <td><?php echo htmlspecialchars($row['period'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo number_format((float) $row['amount_due'], 2); ?></td>
                                        <td><?php echo number_format((float) $row['amount_paid'], 2); ?></td>
                                        <td class="<?php echo $balance > 0 ? 'text-danger fw-bold' : 'text-success fw-bold'; ?>">
                                            <?php echo number_format($balance, 2); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($row['updated_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <a href="<?php echo BASE_URL; ?>admin/fees/invoice.php?student_id=<?php echo (int) $selectedStudentId; ?>&view=<?php echo (int) $row['id']; ?>"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No fee records for this student.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($invoiceRecord && $selectedStudent): ?>
        <?php
        $amountDue = (float) $invoiceRecord['amount_due'];
        $amountPaid = (float) $invoiceRecord['amount_paid'];
        $balance = $amountDue - $amountPaid;
        $studentName = trim(($selectedStudent['first_name'] ?? '') . ' ' . ($selectedStudent['last_name'] ?? ''));
        $studentEmail = trim($selectedStudent['email'] ?? '');
        $gradeName = trim($selectedStudent['grade_name'] ?? '');
        $className = trim($selectedStudent['class_name'] ?? '');
        $subjectsText = count($studentSubjects) > 0 ? implode(', ', $studentSubjects) : 'No subjects assigned';
        $subject = rawurlencode('Invoice - ' . $invoiceRecord['period']);
        $bodyText = "Hello {$studentName},\n\n"
            . "Invoice details:\n"
            . "Grade: " . ($gradeName !== '' ? $gradeName : '-') . "\n"
            . "Curriculum: " . ($className !== '' ? $className : '-') . "\n"
            . "Subjects: {$subjectsText}\n"
            . "Period: {$invoiceRecord['period']}\n"
            . "Amount Due: " . number_format($amountDue, 2) . "\n"
            . "Amount Paid: " . number_format($amountPaid, 2) . "\n"
            . "Balance: " . number_format($balance, 2) . "\n\n"
            . "Thank you.";
        $body = rawurlencode($bodyText);
        ?>
        <style>
            .invoice-card {
                border: 1px solid #dbe2ea;
                border-radius: 14px;
                overflow: hidden;
                background: #fff;
            }
            .invoice-head {
                background: linear-gradient(135deg, #0b1220, #1f2a44);
                color: #f8fafc;
                padding: 20px 22px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 16px;
            }
            .invoice-brand {
                display: flex;
                align-items: center;
                gap: 12px;
            }
            .invoice-logo {
                width: 52px;
                height: 52px;
                object-fit: contain;
                background: #fff;
                border-radius: 10px;
                padding: 6px;
            }
            .invoice-head .title {
                font-size: 1.15rem;
                font-weight: 700;
            }
            .invoice-ref {
                font-size: 0.9rem;
                color: #cbd5e1;
            }
            .invoice-section {
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                background: #f8fafc;
                padding: 14px;
            }
            .invoice-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 12px 20px;
            }
            .invoice-label {
                color: #64748b;
                font-size: 0.75rem;
                text-transform: uppercase;
                letter-spacing: 0.04em;
            }
            .invoice-value {
                color: #0f172a;
                font-weight: 600;
            }
            .amount-grid {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 12px;
                margin-top: 12px;
            }
            .amount-card {
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                background: #ffffff;
                padding: 12px;
            }
            .amount-value {
                font-size: 1.1rem;
                font-weight: 700;
                color: #0f172a;
            }
            .subjects-wrap {
                line-height: 1.8;
                margin-top: 6px;
            }
            .subject-chip {
                display: inline-block;
                background: #e2e8f0;
                color: #1e293b;
                border-radius: 999px;
                padding: 3px 10px;
                font-size: 0.82rem;
                margin: 2px 4px 2px 0;
            }
            .invoice-actions {
                display: flex;
                gap: 8px;
            }
            @media (max-width: 767px) {
                .invoice-grid, .amount-grid {
                    grid-template-columns: 1fr;
                }
            }
            @media print {
                .sidebar, .navbar, .no-print, .no-print *, .invoice-actions, .btn, button, a.btn {
                    display: none !important;
                }
                .container-fluid.mt-3, .container-fluid, .main-content {
                    margin: 0 !important;
                    padding: 0 !important;
                }
                .main-content {
                    margin-left: 0 !important;
                    padding: 0 !important;
                }
                #invoice-view {
                    box-shadow: none !important;
                    border: 1px solid #ddd !important;
                    border-radius: 0 !important;
                }
                .invoice-head {
                    background: #fff !important;
                    color: #111 !important;
                    border-bottom: 2px solid #111;
                }
                .invoice-ref {
                    color: #333 !important;
                }
            }
        </style>
        <div class="card shadow-sm border-0 invoice-card" id="invoice-view">
            <div class="invoice-head">
                <div class="invoice-brand">
                    <img src="<?php echo BASE_URL; ?>assets/images/logo.png" alt="JD Tutoring" class="invoice-logo">
                    <div>
                        <div class="title">JD Tutoring Invoice</div>
                        <div class="invoice-ref">Ref: INV-<?php echo (int) $invoiceRecord['id']; ?> | Period: <?php echo htmlspecialchars($invoiceRecord['period'], ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                </div>
                <div class="invoice-actions no-print">
                    <button type="button" class="btn btn-outline-dark btn-sm" onclick="window.print();">
                        <i class="bi bi-printer"></i> Print
                    </button>
                    <?php if ($studentEmail !== ''): ?>
                        <a href="mailto:<?php echo htmlspecialchars($studentEmail, ENT_QUOTES, 'UTF-8'); ?>?subject=<?php echo $subject; ?>&body=<?php echo $body; ?>"
                            class="btn btn-outline-success btn-sm">
                            <i class="bi bi-send"></i> Send
                        </a>
                    <?php else: ?>
                        <button type="button" class="btn btn-outline-secondary btn-sm" disabled>
                            <i class="bi bi-send"></i> Send
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="invoice-section">
                            <div class="invoice-label mb-2">Student Details</div>
                            <div class="invoice-grid">
                                <div>
                                    <div class="invoice-label">Student</div>
                                    <div class="invoice-value"><?php echo htmlspecialchars($studentName, ENT_QUOTES, 'UTF-8'); ?></div>
                                </div>
                                <div>
                                    <div class="invoice-label">Email</div>
                                    <div class="invoice-value"><?php echo htmlspecialchars($studentEmail !== '' ? $studentEmail : '-', ENT_QUOTES, 'UTF-8'); ?></div>
                                </div>
                                <div>
                                    <div class="invoice-label">Grade</div>
                                    <div class="invoice-value"><?php echo htmlspecialchars($gradeName !== '' ? $gradeName : '-', ENT_QUOTES, 'UTF-8'); ?></div>
                                </div>
                                <div>
                                    <div class="invoice-label">Curriculum</div>
                                    <div class="invoice-value"><?php echo htmlspecialchars($className !== '' ? $className : '-', ENT_QUOTES, 'UTF-8'); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="invoice-section">
                            <div class="invoice-label mb-2">Pricing Summary</div>
                            <div class="amount-grid">
                                <div class="amount-card">
                                    <div class="invoice-label">Amount Due</div>
                                    <div class="amount-value"><?php echo number_format($amountDue, 2); ?></div>
                                </div>
                                <div class="amount-card">
                                    <div class="invoice-label">Amount Paid</div>
                                    <div class="amount-value"><?php echo number_format($amountPaid, 2); ?></div>
                                </div>
                                <div class="amount-card">
                                    <div class="invoice-label">Balance</div>
                                    <div class="amount-value <?php echo $balance > 0 ? 'text-danger' : 'text-success'; ?>">
                                        <?php echo number_format($balance, 2); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="invoice-section">
                            <div class="invoice-label">Subjects</div>
                            <div class="subjects-wrap mt-1">
                                <?php if (count($studentSubjects) > 0): ?>
                                    <?php foreach ($studentSubjects as $subjectName): ?>
                                        <span class="subject-chip"><?php echo htmlspecialchars($subjectName, ENT_QUOTES, 'UTF-8'); ?></span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="text-muted">No subjects assigned</span>
                                <?php endif; ?>
                            </div>
                            <div class="invoice-label mt-3">Notes</div>
                            <div class="invoice-value"><?php echo htmlspecialchars($invoiceRecord['notes'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
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
