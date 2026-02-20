<?php
require_once '../../config/db.php';
require_once '../../templates/header.php';
require_once '../../templates/sidebar.php';

$conn->query(
    "CREATE TABLE IF NOT EXISTS progress_tracking (
        id INT(11) NOT NULL AUTO_INCREMENT,
        tutor_id INT(11) NOT NULL,
        student_id INT(11) NOT NULL,
        academic_year SMALLINT UNSIGNED NOT NULL,
        january_marks TEXT DEFAULT NULL,
        february_marks TEXT DEFAULT NULL,
        march_marks TEXT DEFAULT NULL,
        april_marks TEXT DEFAULT NULL,
        may_marks TEXT DEFAULT NULL,
        june_marks TEXT DEFAULT NULL,
        july_marks TEXT DEFAULT NULL,
        august_marks TEXT DEFAULT NULL,
        september_marks TEXT DEFAULT NULL,
        october_marks TEXT DEFAULT NULL,
        november_marks TEXT DEFAULT NULL,
        december_marks TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uniq_progress_tutor_student_year (tutor_id, student_id, academic_year),
        CONSTRAINT fk_progress_tutor FOREIGN KEY (tutor_id) REFERENCES tutors(id) ON DELETE CASCADE,
        CONSTRAINT fk_progress_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

$studentNameFilter = trim((string)($_GET['student_name'] ?? ''));
$tutorIdFilter = (int)($_GET['tutor_id'] ?? 0);

$tutors = [];
$tutorsResult = $conn->query("SELECT id, first_name, last_name FROM tutors ORDER BY first_name, last_name");
if ($tutorsResult) {
    $tutors = $tutorsResult->fetch_all(MYSQLI_ASSOC);
}

$where = [];
$types = '';
$params = [];

if ($studentNameFilter !== '') {
    $where[] = "CONCAT(s.first_name, ' ', s.last_name) LIKE ?";
    $types .= 's';
    $params[] = '%' . $studentNameFilter . '%';
}

if ($tutorIdFilter > 0) {
    $where[] = 'p.tutor_id = ?';
    $types .= 'i';
    $params[] = $tutorIdFilter;
}

$sql = "SELECT
            p.id,
            p.academic_year,
            p.january_marks, p.february_marks, p.march_marks, p.april_marks,
            p.may_marks, p.june_marks, p.july_marks, p.august_marks,
            p.september_marks, p.october_marks, p.november_marks, p.december_marks,
            p.updated_at,
            CONCAT(s.first_name, ' ', s.last_name) AS student_name,
            CONCAT(t.first_name, ' ', t.last_name) AS tutor_name
        FROM progress_tracking p
        INNER JOIN students s ON s.id = p.student_id
        INNER JOIN tutors t ON t.id = p.tutor_id";

if (!empty($where)) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

$sql .= ' ORDER BY p.academic_year DESC, t.first_name ASC, s.first_name ASC, p.id DESC';

$records = [];
$stmt = $conn->prepare($sql);
if ($stmt) {
    if ($types !== '') {
        $bindValues = [$types];
        foreach ($params as $idx => $value) {
            $bindValues[] = &$params[$idx];
        }
        call_user_func_array([$stmt, 'bind_param'], $bindValues);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        $records = $result->fetch_all(MYSQLI_ASSOC);
    }
}

$monthKeys = [
    'january', 'february', 'march', 'april', 'may', 'june',
    'july', 'august', 'september', 'october', 'november', 'december'
];

$monthLabels = [
    'january' => 'January',
    'february' => 'February',
    'march' => 'March',
    'april' => 'April',
    'may' => 'May',
    'june' => 'June',
    'july' => 'July',
    'august' => 'August',
    'september' => 'September',
    'october' => 'October',
    'november' => 'November',
    'december' => 'December'
];

function summarizeRecordMarks(array $record, array $monthKeys): string
{
    $scores = [];

    foreach ($monthKeys as $month) {
        $raw = (string)($record[$month . '_marks'] ?? '');
        if ($raw === '') {
            continue;
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            continue;
        }

        foreach ($decoded as $value) {
            if (is_numeric($value)) {
                $numeric = (float)$value;
                if ($numeric >= 0 && $numeric <= 100) {
                    $scores[] = $numeric;
                }
            }
        }
    }

    if (empty($scores)) {
        return 'No marks captured';
    }

    $average = array_sum($scores) / count($scores);
    return number_format($average, 1) . "% average (" . count($scores) . " test)";
}

function monthTrackDisplay(array $record, string $month): string
{
    $raw = (string)($record[$month . '_marks'] ?? '');
    if ($raw === '') {
        return 'T1: -, T2: -, T3: -, T4: -, T5: -';
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        return 'T1: -, T2: -, T3: -, T4: -, T5: -';
    }

    $parts = [];
    for ($i = 0; $i < 5; $i++) {
        $value = $decoded[$i] ?? null;
        $parts[] = 'T' . ($i + 1) . ': ' . (is_numeric($value) ? (string)((int)$value) : '-');
    }

    return implode(', ', $parts);
}

function monthTrackArray(array $record, string $month): array
{
    $raw = (string)($record[$month . '_marks'] ?? '');
    $out = ['-', '-', '-', '-', '-'];

    if ($raw === '') {
        return $out;
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        return $out;
    }

    for ($i = 0; $i < 5; $i++) {
        $value = $decoded[$i] ?? null;
        if (is_numeric($value)) {
            $out[$i] = (string)((int)$value);
        }
    }

    return $out;
}
?>

<div class="container-fluid">
    <h3 class="mb-4"><i class="bi bi-graph-up"></i> Academic Performance Reports</h3>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label for="student_name" class="form-label">Student Name</label>
                    <input
                        type="text"
                        id="student_name"
                        name="student_name"
                        class="form-control"
                        placeholder="Search by student name"
                        value="<?php echo htmlspecialchars($studentNameFilter); ?>">
                </div>

                <div class="col-md-5">
                    <label for="tutor_id" class="form-label">Tutor</label>
                    <select id="tutor_id" name="tutor_id" class="form-select">
                        <option value="">All tutors</option>
                        <?php foreach ($tutors as $tutor): ?>
                            <option value="<?php echo (int)$tutor['id']; ?>" <?php echo ($tutorIdFilter === (int)$tutor['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars(trim($tutor['first_name'] . ' ' . $tutor['last_name'])); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                    <a href="academic_reports.php" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <?php if (empty($records)): ?>
                <div class="alert alert-info mb-0">No progress tracking records found for the selected filters.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle mb-0">
                        <thead class="table-light">
                        <tr>
                            <th>Student</th>
                            <th>Tutor</th>
                            <th>Academic Year</th>
                            <th>Performance Summary</th>
                            <th>Last Updated</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($records as $record): ?>
                            <?php
                            $tracksPayload = [];
                            foreach ($monthKeys as $month) {
                                $trackValues = monthTrackArray($record, $month);
                                $tracksPayload[] = [
                                    'month' => $monthLabels[$month],
                                    'track1' => $trackValues[0],
                                    'track2' => $trackValues[1],
                                    'track3' => $trackValues[2],
                                    'track4' => $trackValues[3],
                                    'track5' => $trackValues[4]
                                ];
                            }
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($record['student_name']); ?></td>
                                <td><?php echo htmlspecialchars($record['tutor_name']); ?></td>
                                <td><?php echo (int)$record['academic_year']; ?></td>
                                <td><?php echo htmlspecialchars(summarizeRecordMarks($record, $monthKeys)); ?></td>
                                <td><?php echo htmlspecialchars((string)$record['updated_at']); ?></td>
                                <td>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-primary btn-view-tracks"
                                        data-bs-toggle="modal"
                                        data-bs-target="#allTracksModal"
                                        data-tracks="<?php echo htmlspecialchars(json_encode($tracksPayload), ENT_QUOTES); ?>"
                                        data-student="<?php echo htmlspecialchars($record['student_name'], ENT_QUOTES); ?>"
                                        data-tutor="<?php echo htmlspecialchars($record['tutor_name'], ENT_QUOTES); ?>"
                                        data-year="<?php echo (int)$record['academic_year']; ?>"
                                        data-summary="<?php echo htmlspecialchars(summarizeRecordMarks($record, $monthKeys), ENT_QUOTES); ?>">
                                        View
                                    </button>
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

<div class="modal fade" id="allTracksModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="allTracksContent"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('allTracksModal');
    var content = document.getElementById('allTracksContent');

    if (!modal || !content) {
        return;
    }

    modal.addEventListener('show.bs.modal', function (event) {
        var trigger = event.relatedTarget;
        if (!trigger) {
            content.innerHTML = '';
            return;
        }

        var student = trigger.getAttribute('data-student') || '';
        var tutor = trigger.getAttribute('data-tutor') || '';
        var year = trigger.getAttribute('data-year') || '';
        var summary = trigger.getAttribute('data-summary') || '';
        var raw = trigger.getAttribute('data-tracks') || '[]';
        var rows = [];
        try {
            rows = JSON.parse(raw);
        } catch (e) {
            rows = [];
        }

        if (!Array.isArray(rows) || rows.length === 0) {
            content.innerHTML = '<p class="mb-0 text-muted">No tracks found.</p>';
            return;
        }

        function monthAverage(row) {
            var vals = [row.track1, row.track2, row.track3, row.track4, row.track5];
            var nums = vals
                .map(function (v) { return parseFloat(v); })
                .filter(function (n) { return !isNaN(n) && n >= 0 && n <= 100; });
            if (!nums.length) {
                return '-';
            }
            var total = nums.reduce(function (a, b) { return a + b; }, 0);
            return (total / nums.length).toFixed(1) + '%';
        }

        var html = ''
            + '<div class="border rounded p-3">'
            +   '<div class="text-center mb-3">'
            +     '<h5 class="mb-1">JD Tutoring - Learner Progress Report</h5>'
            +     '<div class="small text-muted">Academic Year ' + year + '</div>'
            +   '</div>'
            +   '<div class="row mb-3">'
            +     '<div class="col-md-6"><strong>Learner:</strong> ' + student + '</div>'
            +     '<div class="col-md-6"><strong>Tutor:</strong> ' + tutor + '</div>'
            +   '</div>'
            +   '<div class="table-responsive">'
            +     '<table class="table table-bordered table-sm mb-2">'
            +       '<thead class="table-light">'
            +         '<tr>'
            +           '<th>Period</th>'
            +           '<th>Test 1</th>'
            +           '<th>Test 2</th>'
            +           '<th>Test 3</th>'
            +           '<th>Test 4</th>'
            +           '<th>Test 5</th>'
            +           '<th>Average</th>'
            +         '</tr>'
            +       '</thead>'
            +       '<tbody>';

        rows.forEach(function (row) {
            var month = (row && row.month) ? row.month : '-';
            var t1 = (row && row.track1) ? row.track1 : '-';
            var t2 = (row && row.track2) ? row.track2 : '-';
            var t3 = (row && row.track3) ? row.track3 : '-';
            var t4 = (row && row.track4) ? row.track4 : '-';
            var t5 = (row && row.track5) ? row.track5 : '-';
            var avg = monthAverage(row || {});
            html += '<tr>'
                + '<td>' + month + '</td>'
                + '<td>' + t1 + '</td>'
                + '<td>' + t2 + '</td>'
                + '<td>' + t3 + '</td>'
                + '<td>' + t4 + '</td>'
                + '<td>' + t5 + '</td>'
                + '<td><strong>' + avg + '</strong></td>'
                + '</tr>';
        });
        html += ''
            +       '</tbody>'
            +     '</table>'
            +   '</div>'
            +   '<div><strong>Overall:</strong> ' + summary + '</div>'
            + '</div>';

        content.innerHTML = html;
    });
});
</script>

<?php require_once '../../templates/footer.php'; ?>
