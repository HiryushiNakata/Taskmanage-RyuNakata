<?php
require_once "db_connect.php";
require_once "includes/auth.php";
require_login();

$user_id = $_SESSION["user_id"];

// ---------- SAVE CHANGES (form submitted) ----------
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_check();

    $task_id      = (int) ($_POST["task_id"] ?? 0);
    $subject      = trim($_POST["subject"] ?? "");
    $task_details = trim($_POST["task_details"] ?? "");
    $deadline     = $_POST["deadline"] ?? "";

    if ($subject === "" || strlen($subject) > 50 || $task_details === "" || !is_valid_date($deadline)) {
        set_flash("danger", "Please fill in all fields correctly (subject max 50 characters).");
        redirect("edit.php?task_id=" . $task_id);
    }

    // UPDATE the three editable columns of this one task (only if it is yours)
    $stmt = mysqli_prepare(
        $conn,
        "UPDATE assignments SET subject = ?, task_details = ?, deadline = ?
         WHERE task_id = ? AND user_id = ?"
    );
    // "sssii" = string, string, string, integer, integer
    mysqli_stmt_bind_param($stmt, "sssii", $subject, $task_details, $deadline, $task_id, $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    set_flash("success", "Task updated.");
    redirect("index.php");
}

// ---------- SHOW THE FORM (page opened normally) ----------
$task_id = (int) ($_GET["task_id"] ?? 0);

// Fetch the existing task so the form can be pre-filled.
// "AND user_id = ?" stops users from opening someone else's task by guessing an ID.
$stmt = mysqli_prepare($conn, "SELECT * FROM assignments WHERE task_id = ? AND user_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $task_id, $user_id);
mysqli_stmt_execute($stmt);
$task = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

// No such task (or it isn't yours) -> back to the dashboard
if (!$task) {
    set_flash("danger", "Task not found.");
    redirect("index.php");
}

$page_title = "Edit task";
require "includes/header.php";
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="panel panel-violet">
            <div class="panel-title">Edit task</div>

            <form action="edit.php" method="POST">
                <?= csrf_field() ?>
                <!-- Hidden field tells the save code which task to update -->
                <input type="hidden" name="task_id" value="<?= (int) $task["task_id"] ?>">

                <div class="row g-3">
                    <div class="col-md-7">
                        <label for="subject" class="form-label">Subject</label>
                        <input type="text" class="form-control" id="subject" name="subject"
                               maxlength="50" required value="<?= e($task["subject"]) ?>">
                    </div>
                    <div class="col-md-5">
                        <label for="deadline" class="form-label">Deadline</label>
                        <input type="date" class="form-control" id="deadline" name="deadline"
                               required value="<?= e($task["deadline"]) ?>">
                    </div>
                    <div class="col-12">
                        <label for="task_details" class="form-label">Task details</label>
                        <textarea class="form-control" id="task_details" name="task_details"
                                  rows="4" required><?= e($task["task_details"]) ?></textarea>
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-white">Save changes</button>
                        <a href="index.php" class="btn btn-glass">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require "includes/footer.php"; ?>
