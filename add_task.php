<?php
require_once "db_connect.php";
require_once "includes/auth.php";
require_login();

// Only run if the form was submitted using POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_check();

    // trim() removes extra spaces at the start and end
    $subject      = trim($_POST["subject"] ?? "");
    $task_details = trim($_POST["task_details"] ?? "");
    $deadline     = $_POST["deadline"] ?? "";
    $user_id      = $_SESSION["user_id"];

    // Server-side validation (the browser's "required" can be bypassed)
    if ($subject === "" || strlen($subject) > 50 || $task_details === "" || !is_valid_date($deadline)) {
        set_flash("danger", "Please fill in all fields correctly (subject max 50 characters).");
        redirect("index.php");
    }

    // Prepared statement: the ? placeholders keep user input separate from the SQL,
    // which protects against SQL injection
    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO assignments (user_id, subject, task_details, deadline) VALUES (?, ?, ?, ?)"
    );

    // "isss" = integer, string, string, string
    mysqli_stmt_bind_param($stmt, "isss", $user_id, $subject, $task_details, $deadline);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    set_flash("success", "Task added.");
}

// Go back to the dashboard
redirect("index.php");
