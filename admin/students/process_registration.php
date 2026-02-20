<?php
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: student-registration.html');
    exit;
}

$student_id = isset($_POST['student_id']) ? (int) $_POST['student_id'] : 0;
$is_update = $student_id > 0;

// Create uploads directory if it doesn't exist
$upload_dir = '../../uploads/students/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Handle file uploads
$latest_results_file = null;
$id_document_file = null;

if ($is_update) {
    $existing_files = $conn->query("SELECT latest_results_file, id_document_file FROM students WHERE id=$student_id");
    if ($existing_files && $existing_files->num_rows > 0) {
        $existing = $existing_files->fetch_assoc();
        $latest_results_file = $existing['latest_results_file'];
        $id_document_file = $existing['id_document_file'];
    }
}

if (isset($_FILES['latestResults']) && $_FILES['latestResults']['error'] == 0) {
    $file_ext = pathinfo($_FILES['latestResults']['name'], PATHINFO_EXTENSION);
    $latest_results_file = 'results_' . time() . '_' . uniqid() . '.' . $file_ext;
    move_uploaded_file($_FILES['latestResults']['tmp_name'], $upload_dir . $latest_results_file);
}

if (isset($_FILES['idDocument']) && $_FILES['idDocument']['error'] == 0) {
    $file_ext = pathinfo($_FILES['idDocument']['name'], PATHINFO_EXTENSION);
    $id_document_file = 'id_' . time() . '_' . uniqid() . '.' . $file_ext;
    move_uploaded_file($_FILES['idDocument']['tmp_name'], $upload_dir . $id_document_file);
}

// Sanitize inputs (support both full and simple forms)
$post_value = function ($key) {
    return isset($_POST[$key]) ? trim((string) $_POST[$key]) : null;
};
$escape_or_null = function ($value) use ($conn) {
    if ($value === null || $value === '') {
        return null;
    }
    return $conn->real_escape_string($value);
};

$first_name = $escape_or_null($post_value('studentName'));
if ($first_name === null) {
    $first_name = $escape_or_null($post_value('first_name'));
}
$last_name = $escape_or_null($post_value('studentSurname'));
if ($last_name === null) {
    $last_name = $escape_or_null($post_value('last_name'));
}
$gender = $escape_or_null($post_value('gender'));
$dob = $escape_or_null($post_value('dob'));
$phone = $escape_or_null($post_value('phone'));
$email = $escape_or_null($post_value('email'));
$physical_address = $escape_or_null($post_value('physicalAddress'));
$home_language = $escape_or_null($post_value('homeLanguage'));

// Academic Information
$program = $escape_or_null($post_value('program'));
$grade_name = $escape_or_null($post_value('grade'));
$curriculum_name = $escape_or_null($post_value('curriculum'));
$learning_mode = $escape_or_null($post_value('learningMode'));
$school_name = $escape_or_null($post_value('schoolName'));

// Get grade_id and class_id from either IDs or names
$grade_id = null;
if (isset($_POST['grade_id']) && $_POST['grade_id'] !== '') {
    $grade_id = (int) $_POST['grade_id'];
} elseif ($grade_name !== null) {
    $grade_result = $conn->query("SELECT id FROM grades WHERE name='$grade_name' LIMIT 1");
    $grade_id = $grade_result->num_rows > 0 ? $grade_result->fetch_assoc()['id'] : null;
}

$class_id = null;
if (isset($_POST['class_id']) && $_POST['class_id'] !== '') {
    $class_id = (int) $_POST['class_id'];
} elseif ($curriculum_name !== null) {
    $class_result = $conn->query("SELECT id FROM classes WHERE class_name='$curriculum_name' LIMIT 1");
    $class_id = $class_result->num_rows > 0 ? $class_result->fetch_assoc()['id'] : null;
}

// Next of Kin 1
$kin1_name = $escape_or_null($post_value('nextKin1Name'));
$kin1_surname = $escape_or_null($post_value('nextKin1Surname'));
$kin1_personal_contact = $escape_or_null($post_value('nextKin1PersonalContact'));
$kin1_work_contact = $escape_or_null($post_value('nextKin1WorkContact'));
$kin1_email = $escape_or_null($post_value('nextKin1Email'));
$kin1_physical_address = $escape_or_null($post_value('nextKin1PhysicalAddress'));
$kin1_relation = $escape_or_null($post_value('nextKin1RelationToStudent'));
$kin1_occupation = $escape_or_null($post_value('nextKin1Occupation'));
$kin1_home_language = $escape_or_null($post_value('nextKin1HomeLanguage'));

// Next of Kin 2 (Optional)
$kin2_name = $escape_or_null($post_value('nextKin2Name'));
$kin2_surname = $escape_or_null($post_value('nextKin2Surname'));
$kin2_personal_contact = $escape_or_null($post_value('nextKin2PersonalContact'));
$kin2_work_contact = $escape_or_null($post_value('nextKin2WorkContact'));
$kin2_email = $escape_or_null($post_value('nextKin2Email'));
$kin2_physical_address = $escape_or_null($post_value('nextKin2PhysicalAddress'));
$kin2_relation = $escape_or_null($post_value('nextKin2RelationToStudent'));
$kin2_occupation = $escape_or_null($post_value('nextKin2Occupation'));
$kin2_home_language = $escape_or_null($post_value('nextKin2HomeLanguage'));

// Additional Information
$referral_source = $escape_or_null($post_value('referralSource'));
$referral_other = $escape_or_null($post_value('referralOther'));
$additional_notes = $escape_or_null($post_value('additionalNotes'));

if ($is_update) {
    $update_sql = "UPDATE students SET
        first_name='" . ($first_name ?? '') . "',
        last_name='" . ($last_name ?? '') . "',
        gender=" . ($gender ? "'$gender'" : 'NULL') . ",
        date_of_birth=" . ($dob ? "'$dob'" : 'NULL') . ",
        phone=" . ($phone ? "'$phone'" : 'NULL') . ",
        email=" . ($email ? "'$email'" : 'NULL') . ",
        physical_address=" . ($physical_address ? "'$physical_address'" : 'NULL') . ",
        home_language=" . ($home_language ? "'$home_language'" : 'NULL') . ",
        program=" . ($program ? "'$program'" : 'NULL') . ",
        grade_id=" . ($grade_id ? $grade_id : 'NULL') . ",
        class_id=" . ($class_id ? $class_id : 'NULL') . ",
        learning_mode=" . ($learning_mode ? "'$learning_mode'" : 'NULL') . ",
        school_name=" . ($school_name ? "'$school_name'" : 'NULL') . ",
        latest_results_file=" . ($latest_results_file ? "'$latest_results_file'" : 'NULL') . ",
        id_document_file=" . ($id_document_file ? "'$id_document_file'" : 'NULL') . ",
        kin1_name=" . ($kin1_name ? "'$kin1_name'" : 'NULL') . ",
        kin1_surname=" . ($kin1_surname ? "'$kin1_surname'" : 'NULL') . ",
        kin1_personal_contact=" . ($kin1_personal_contact ? "'$kin1_personal_contact'" : 'NULL') . ",
        kin1_work_contact=" . ($kin1_work_contact ? "'$kin1_work_contact'" : 'NULL') . ",
        kin1_email=" . ($kin1_email ? "'$kin1_email'" : 'NULL') . ",
        kin1_physical_address=" . ($kin1_physical_address ? "'$kin1_physical_address'" : 'NULL') . ",
        kin1_relation=" . ($kin1_relation ? "'$kin1_relation'" : 'NULL') . ",
        kin1_occupation=" . ($kin1_occupation ? "'$kin1_occupation'" : 'NULL') . ",
        kin1_home_language=" . ($kin1_home_language ? "'$kin1_home_language'" : 'NULL') . ",
        kin2_name=" . ($kin2_name ? "'$kin2_name'" : 'NULL') . ",
        kin2_surname=" . ($kin2_surname ? "'$kin2_surname'" : 'NULL') . ",
        kin2_personal_contact=" . ($kin2_personal_contact ? "'$kin2_personal_contact'" : 'NULL') . ",
        kin2_work_contact=" . ($kin2_work_contact ? "'$kin2_work_contact'" : 'NULL') . ",
        kin2_email=" . ($kin2_email ? "'$kin2_email'" : 'NULL') . ",
        kin2_physical_address=" . ($kin2_physical_address ? "'$kin2_physical_address'" : 'NULL') . ",
        kin2_relation=" . ($kin2_relation ? "'$kin2_relation'" : 'NULL') . ",
        kin2_occupation=" . ($kin2_occupation ? "'$kin2_occupation'" : 'NULL') . ",
        kin2_home_language=" . ($kin2_home_language ? "'$kin2_home_language'" : 'NULL') . ",
        referral_source=" . ($referral_source ? "'$referral_source'" : 'NULL') . ",
        referral_other=" . ($referral_other ? "'$referral_other'" : 'NULL') . ",
        additional_notes=" . ($additional_notes ? "'$additional_notes'" : 'NULL') . "
        WHERE id=$student_id";

    if ($conn->query($update_sql)) {
        $conn->query("DELETE FROM student_subjects WHERE student_id=$student_id");
        if (isset($_POST['subjects']) && is_array($_POST['subjects'])) {
            foreach ($_POST['subjects'] as $subject_value) {
                $subject_id = 0;
                if (is_numeric($subject_value)) {
                    $subject_id = (int) $subject_value;
                } else {
                    $subject_name = $conn->real_escape_string($subject_value);
                    $subject_result = $conn->query("SELECT id FROM subjects WHERE subject_name='$subject_name' LIMIT 1");
                    if ($subject_result->num_rows > 0) {
                        $subject_id = (int) $subject_result->fetch_assoc()['id'];
                    }
                }
                if ($subject_id > 0) {
                    $conn->query("INSERT INTO student_subjects (student_id, subject_id) VALUES ($student_id, $subject_id)");
                }
            }
        }
        header('Location: manage_students.php?updated=1');
    } else {
        header('Location: edit_student.php?id=' . $student_id . '&error=1');
    }
} else {
    $sql = "INSERT INTO students (
        first_name, last_name, gender, date_of_birth, phone, email, physical_address, home_language,
        program, grade_id, class_id, learning_mode, school_name,
        latest_results_file, id_document_file,
        kin1_name, kin1_surname, kin1_personal_contact, kin1_work_contact, kin1_email, 
        kin1_physical_address, kin1_relation, kin1_occupation, kin1_home_language,
        kin2_name, kin2_surname, kin2_personal_contact, kin2_work_contact, kin2_email,
        kin2_physical_address, kin2_relation, kin2_occupation, kin2_home_language,
        referral_source, referral_other, additional_notes
    ) VALUES (
        '" . ($first_name ?? '') . "', '" . ($last_name ?? '') . "', " . ($gender ? "'$gender'" : 'NULL') . ", " . ($dob ? "'$dob'" : 'NULL') . ",
        " . ($phone ? "'$phone'" : 'NULL') . ", " . ($email ? "'$email'" : 'NULL') . ", " . ($physical_address ? "'$physical_address'" : 'NULL') . ",
        " . ($home_language ? "'$home_language'" : 'NULL') . ",
        " . ($program ? "'$program'" : 'NULL') . ", " . ($grade_id ? $grade_id : 'NULL') . ", " . ($class_id ? $class_id : 'NULL') . ",
        " . ($learning_mode ? "'$learning_mode'" : 'NULL') . ", " . ($school_name ? "'$school_name'" : 'NULL') . ",
        " . ($latest_results_file ? "'$latest_results_file'" : 'NULL') . ", " . ($id_document_file ? "'$id_document_file'" : 'NULL') . ",
        " . ($kin1_name ? "'$kin1_name'" : 'NULL') . ", " . ($kin1_surname ? "'$kin1_surname'" : 'NULL') . ",
        " . ($kin1_personal_contact ? "'$kin1_personal_contact'" : 'NULL') . ", " . ($kin1_work_contact ? "'$kin1_work_contact'" : 'NULL') . ",
        " . ($kin1_email ? "'$kin1_email'" : 'NULL') . ", " . ($kin1_physical_address ? "'$kin1_physical_address'" : 'NULL') . ",
        " . ($kin1_relation ? "'$kin1_relation'" : 'NULL') . ", " . ($kin1_occupation ? "'$kin1_occupation'" : 'NULL') . ",
        " . ($kin1_home_language ? "'$kin1_home_language'" : 'NULL') . ",
        " . ($kin2_name ? "'$kin2_name'" : 'NULL') . ", " . ($kin2_surname ? "'$kin2_surname'" : 'NULL') . ",
        " . ($kin2_personal_contact ? "'$kin2_personal_contact'" : 'NULL') . ", " . ($kin2_work_contact ? "'$kin2_work_contact'" : 'NULL') . ",
        " . ($kin2_email ? "'$kin2_email'" : 'NULL') . ", " . ($kin2_physical_address ? "'$kin2_physical_address'" : 'NULL') . ",
        " . ($kin2_relation ? "'$kin2_relation'" : 'NULL') . ", " . ($kin2_occupation ? "'$kin2_occupation'" : 'NULL') . ",
        " . ($kin2_home_language ? "'$kin2_home_language'" : 'NULL') . ",
        " . ($referral_source ? "'$referral_source'" : 'NULL') . ", " . ($referral_other ? "'$referral_other'" : 'NULL') . ",
        " . ($additional_notes ? "'$additional_notes'" : 'NULL') . "
    )";

    if ($conn->query($sql)) {
        $student_id = $conn->insert_id;

        // Insert subjects
        if (isset($_POST['subjects']) && is_array($_POST['subjects'])) {
            foreach ($_POST['subjects'] as $subject_value) {
                $subject_id = 0;
                if (is_numeric($subject_value)) {
                    $subject_id = (int) $subject_value;
                } else {
                    $subject_name = $conn->real_escape_string($subject_value);
                    $subject_result = $conn->query("SELECT id FROM subjects WHERE subject_name='$subject_name' LIMIT 1");
                    if ($subject_result->num_rows > 0) {
                        $subject_id = (int) $subject_result->fetch_assoc()['id'];
                    }
                }
                if ($subject_id > 0) {
                    $conn->query("INSERT INTO student_subjects (student_id, subject_id) VALUES ($student_id, $subject_id)");
                }
            }
        }

        header('Location: registration_success.php?student_id=' . $student_id);
    } else {
        header('Location: student-registration.html?error=1');
    }
}

exit;
?>
