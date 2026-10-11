<?php
require_once "db_connect.php";
require_once "includes/auth.php";
require_login();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_check();

    // (int) forces the ID to be a whole number
    $task_id = (int) ($_POST["task_id"] ?? 0);
    $user_id = $_SESSION["user_id"];

    // Change the status of this one task to 'Completed'.
    // "AND user_id = ?" makes sure you can only change YOUR OWN tasks.
    $stmt = mysqli_prepare(
        $conn,
        "UPDATE assignments SET status = 'Completed' WHERE task_id = ? AND user_id = ?"
    );

    // "ii" = two integers
    mysqli_stmt_bind_param($stmt, "ii", $task_id, $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    set_flash("success", "Task marked as completed.");
}

redirect("index.php");
