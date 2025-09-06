<?php
// Database configuration
$host = 'localhost';
$dbname = 'db_9009';
$username = 'root'; // Default XAMPP username
$password = ''; // Default XAMPP password

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset("utf8");
?>
