<?php
// Database connection settings (XAMPP defaults)
$host     = "localhost";
$username = "root";
$password = "";                    // XAMPP's root user has no password by default
$database = "student_planner_db";

// mysqli_connect() opens a connection to the MySQL server
$conn = mysqli_connect($host, $username, $password, $database);

// If the connection fails, stop the script and show the error
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Use UTF-8 so special characters (ñ, é, emoji) are saved correctly
mysqli_set_charset($conn, "utf8mb4");
