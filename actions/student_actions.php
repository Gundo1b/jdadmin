<?php
require_once '../config/db.php';

if (!isset($_GET['action'])) {
    header('Location: ../admin/students/manage_students.php');
    exit;
}

$action = $_GET['action'];

// ADD STUDENT
if ($action == 'add' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $last_name = $conn->real_escape_string($_POST['last_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $grade_id = (int) $_POST['grade_id'];
    $class_id = (int) $_POST['class_id'];

    $conn->query("INSERT INTO students (first_name, last_name, email, phone, grade_id, class_id) 
                  VALUES ('$first_name', '$last_name', '$email', '$phone', $grade_id, $class_id)");

    header('Location: ../admin/students/add_student.php?success=1');
    exit;
}

// EDIT STUDENT
if ($action == 'edit' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = (int) $_POST['id'];
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $last_name = $conn->real_escape_string($_POST['last_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $grade_id = (int) $_POST['grade_id'];
    $class_id = (int) $_POST['class_id'];

    $conn->query("UPDATE students SET 
                  first_name='$first_name', 
                  last_name='$last_name', 
                  email='$email', 
                  phone='$phone', 
                  grade_id=$grade_id, 
                  class_id=$class_id 
                  WHERE id=$id");

    header('Location: ../admin/students/manage_students.php?updated=1');
    exit;
}

// DELETE STUDENT
if ($action == 'delete' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $conn->query("DELETE FROM students WHERE id=$id");

    header('Location: ../admin/students/manage_students.php?deleted=1');
    exit;
}

// Default redirect
header('Location: ../admin/students/manage_students.php');
exit;
?>