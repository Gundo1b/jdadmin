<?php
// Database setup script
$host = "localhost";
$user = "root";
$pass = "";

// Connect to MySQL without database
$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS school_db";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

// Select the database
$conn->select_db("school_db");

// Import SQL files
$sqlFiles = ['programs.sql', 'students.sql', 'tutor.sql'];

foreach ($sqlFiles as $file) {
    $sql = file_get_contents(__DIR__ . '/' . $file);
    if ($conn->multi_query($sql)) {
        echo "Imported $file successfully<br>";
        // Consume all results
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->more_results() && $conn->next_result());
    } else {
        echo "Error importing $file: " . $conn->error . "<br>";
    }
}

$conn->close();
echo "Setup complete.";
?>