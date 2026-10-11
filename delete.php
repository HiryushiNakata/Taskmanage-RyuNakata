<?php
require_once "db_connect.php";
require_once "includes/auth.php";
require_login();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_check();

    $task_id = (int) ($_POST["task_id"] ?? 0);
    $user_id = $_SESSION["user_id"];

    // Remove the row with this task_id, but only if it belongs to the logged-in user
    $stmt = mysqli_prepare($conn, "DELETE FROM assignments WHERE task_id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $task_id, $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    set_flash("success", "Task deleted.");
}

redirect("index.php");
