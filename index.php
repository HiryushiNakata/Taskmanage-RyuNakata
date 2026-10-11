<?php
require_once "db_connect.php";
require_once "includes/auth.php";
require_login();                       // visitors who aren't logged in go to login.php

$user_id = $_SESSION["user_id"];
$today   = date("Y-m-d");              // e.g. 2026-10-10

// ---------- Read the search box and status filter from the URL ----------
$search  = trim($_GET["q"] ?? "");
$filter  = $_GET["status"] ?? "all";
$allowed = ["all", "pending", "completed", "overdue"];
if (!in_array($filter, $allowed, true)) {
    $filter = "all";
}

// ---------- 1) Summary numbers for the cards at the top ----------
// One query counts everything. COALESCE turns NULL into 0 when there are no tasks.
$stmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total,
            COALESCE(SUM(status = 'Pending'), 0)                    AS pending,
            COALESCE(SUM(status = 'Completed'), 0)                  AS completed,
            COALESCE(SUM(status = 'Pending' AND deadline < ?), 0)   AS overdue
     FROM assignments
     WHERE user_id = ?"
);
mysqli_stmt_bind_param($stmt, "si", $today, $user_id);
mysqli_stmt_execute($stmt);
$stats = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

$percent_done = $stats["total"] > 0
    ? round($stats["completed"] / $stats["total"] * 100)
    : 0;

// ---------- 2) The task list (only THIS user's tasks) ----------
// We build the SQL piece by piece depending on the search and filter.
$sql    = "SELECT * FROM assignments WHERE user_id = ?";
$types  = "i";                         // data types of the ? placeholders: i = integer, s = string
$params = [$user_id];

if ($search !== "") {
    $sql    .= " AND (subject LIKE ? OR task_details LIKE ?)";
    $like    = "%" . $search . "%";
    $types  .= "ss";
    $params[] = $like;
    $params[] = $like;
}

if ($filter === "pending") {
    $sql .= " AND status = 'Pending'";
} elseif ($filter === "completed") {
    $sql .= " AND status = 'Completed'";
} elseif ($filter === "overdue") {
    $sql    .= " AND status = 'Pending' AND deadline < ?";
    $types  .= "s";
    $params[] = $today;
}

// Closest deadline first (ASC = ascending)
$sql .= " ORDER BY deadline ASC, task_id ASC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);   // ... spreads the array into arguments
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// ---------- 3) Text for the banner ----------
$first_name = explode(" ", trim($_SESSION["full_name"]))[0];

// The greeting changes with the time of day
$hour = (int) date("G");
if ($hour < 12) {
    $greeting = "Good morning";
} elseif ($hour < 18) {
    $greeting = "Good afternoon";
} else {
    $greeting = "Good evening";
}

// The pending task with the closest deadline ("up next")
$next_stmt = mysqli_prepare(
    $conn,
    "SELECT subject, deadline FROM assignments
     WHERE user_id = ? AND status = 'Pending'
     ORDER BY deadline ASC, task_id ASC LIMIT 1"
);
mysqli_stmt_bind_param($next_stmt, "i", $user_id);
mysqli_stmt_execute($next_stmt);
$next_task = mysqli_fetch_assoc(mysqli_stmt_get_result($next_stmt));
mysqli_stmt_close($next_stmt);

$next_tone = "";   // colors the small dot: "overdue", "today" or empty
$next_text = "";
if ($next_task) {
    // Days from today until the deadline (negative = already past)
    $days_left = (int) round((strtotime($next_task["deadline"]) - strtotime($today)) / 86400);

    if ($days_left < 0) {
        $next_tone = "overdue";
        $next_text = "was due " . plural(abs($days_left), "day") . " ago";
    } elseif ($days_left === 0) {
        $next_tone = "today";
        $next_text = "is due today";
    } elseif ($days_left === 1) {
        $next_text = "is due tomorrow";
    } else {
        $next_text = "is due in " . $days_left . " days";
    }
}

// One short sentence that sums up the workload
$overdue_count = (int) $stats["overdue"];
$on_time_count = (int) $stats["pending"] - $overdue_count;

if ($stats["total"] == 0) {
    $summary = "Your list is empty. Add your first task whenever you're ready.";
} elseif ($overdue_count > 0 && $on_time_count > 0) {
    $summary = plural($overdue_count, "task") . " past due, " . $on_time_count . " more on the way.";
} elseif ($overdue_count > 0) {
    $summary = plural($overdue_count, "task") . " past due. Finish " . ($overdue_count == 1 ? "it" : "them") . " to get back on track.";
} elseif ($stats["pending"] > 0) {
    $summary = "Nothing is overdue. " . plural((int) $stats["pending"], "task") . " left to finish.";
} else {
    $summary = "Every task is done. Enjoy the break.";
}

$page_title = "Dashboard";
require "includes/header.php";

// Soft hues (for hsl colors) used by the little subject badges in the table
$badge_hues = [262, 200, 150, 28, 340, 48];
?>

<!-- ========== BANNER + PROGRESS (top row of the bento layout) ========== -->
<section class="row g-3 mb-3">
    <div class="col-lg-8">
        <div class="hero hero-has-art h-100">
            <span class="hero-chip"><?= e(date("l, j F")) ?></span>
            <h1><?= e($greeting) ?>, <?= e($first_name) ?></h1>
            <p><?= e($summary) ?></p>

            <?php if ($next_task): ?>
                <div class="hero-next">
                    <span class="dot <?= e($next_tone) ?>"></span>
                    <span><strong><?= e($next_task["subject"]) ?></strong> <?= e($next_text) ?></span>
                </div>
            <?php endif; ?>

            <a href="#add-task" class="btn btn-ink"><?= icon("plus", 16) ?>Add a task</a>

            <?php include "includes/hero_art.php"; ?>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="panel panel-violet h-100 text-center">
            <div class="panel-title">Overall progress</div>
            <!-- --p is read by the CSS to draw the circle -->
            <div class="ring-wrap">
                <div class="ring" style="--p: <?= (int) $percent_done ?>"></div>
                <div class="ring-label"><strong><?= (int) $percent_done ?>%</strong></div>
            </div>
            <div class="text-white-50">
                <?= (int) $stats["completed"] ?> of <?= (int) $stats["total"] ?> tasks completed
            </div>
        </div>
    </div>
</section>

<!-- ========== SUMMARY TILES (click one to filter the table) ========== -->
<section class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <a href="index.php#tasks" class="stat-tile tile-total">
            <span class="stat-icon"><?= icon("list", 20) ?></span>
            <span class="stat-number"><?= (int) $stats["total"] ?></span>
            <span class="stat-name">Total tasks</span>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="index.php?status=pending#tasks" class="stat-tile tile-pending">
            <span class="stat-icon"><?= icon("clock", 20) ?></span>
            <span class="stat-number"><?= (int) $stats["pending"] ?></span>
            <span class="stat-name">Pending</span>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="index.php?status=completed#tasks" class="stat-tile tile-completed">
            <span class="stat-icon"><?= icon("check-circle", 20) ?></span>
            <span class="stat-number"><?= (int) $stats["completed"] ?></span>
            <span class="stat-name">Completed</span>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="index.php?status=overdue#tasks" class="stat-tile tile-overdue">
            <span class="stat-icon"><?= icon("alert", 20) ?></span>
            <span class="stat-number"><?= (int) $stats["overdue"] ?></span>
            <span class="stat-name">Overdue</span>
        </a>
    </div>
</section>

<!-- ========== ADD TASK FORM ========== -->
<section class="panel panel-violet mb-3" id="add-task">
    <div class="panel-title">
        <span class="title-text"><span class="title-icon"><?= icon("plus", 18) ?></span>Add a new task</span>
    </div>
    <form action="add_task.php" method="POST">
        <?= csrf_field() ?>
        <div class="row g-3">
            <div class="col-md-7">
                <label for="subject" class="form-label">Subject</label>
                <input type="text" class="form-control" id="subject"
                       name="subject" maxlength="50" required>
            </div>
            <div class="col-md-5">
                <label for="deadline" class="form-label">Deadline</label>
                <input type="date" class="form-control" id="deadline"
                       name="deadline" required>
            </div>
            <div class="col-12">
                <label for="task_details" class="form-label">Task details</label>
                <textarea class="form-control" id="task_details"
                          name="task_details" rows="3" required></textarea>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-white"><?= icon("plus", 16) ?>Add task</button>
            </div>
        </div>
    </form>
</section>

<!-- ========== SEARCH + FILTER ========== -->
<form action="index.php#tasks" method="GET" class="filter-bar row g-2 mx-0 mb-3">
    <div class="col-md-6">
        <div class="search-wrap">
            <?= icon("search", 18) ?>
            <input type="text" name="q" class="form-control"
                   placeholder="Search subject or details" aria-label="Search tasks"
                   value="<?= e($search) ?>">
        </div>
    </div>
    <div class="col-md-3">
        <select name="status" class="form-select" aria-label="Filter by status">
            <option value="all"       <?= $filter === "all"       ? "selected" : "" ?>>All statuses</option>
            <option value="pending"   <?= $filter === "pending"   ? "selected" : "" ?>>Pending</option>
            <option value="completed" <?= $filter === "completed" ? "selected" : "" ?>>Completed</option>
            <option value="overdue"   <?= $filter === "overdue"   ? "selected" : "" ?>>Overdue</option>
        </select>
    </div>
    <div class="col-md-3 d-flex gap-2">
        <button type="submit" class="btn btn-white flex-grow-1">Apply</button>
        <a href="index.php#tasks" class="btn btn-glass">Reset</a>
    </div>
</form>

<!-- ========== TASKS TABLE ========== -->
<section class="panel table-panel" id="tasks">
    <div class="panel-title">
        <span class="title-text"><span class="title-icon"><?= icon("list", 18) ?></span>Tasks</span>
        <span class="count-pill"><?= mysqli_num_rows($result) ?></span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Task details</th>
                    <th>Deadline</th>
                    <th>Status</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <?php
                    // Overdue = still Pending AND deadline is before today.
                    // YYYY-MM-DD strings can be compared directly with "<".
                    $is_overdue = ($row["status"] !== "Completed" && $row["deadline"] < $today);
                    $is_today   = ($row["status"] !== "Completed" && $row["deadline"] === $today);

                    // Friendly text under the date, e.g. "2 days overdue" (not shown once completed)
                    $days_left = days_until($row["deadline"], $today);
                    $when      = $row["status"] === "Completed" ? "" : due_label($days_left);
                    $when_tone = due_tone($days_left);

                    // The same subject always gets the same badge color
                    $hue = $badge_hues[abs(crc32($row["subject"])) % count($badge_hues)];
                    ?>
                    <tr class="<?= $is_overdue ? "row-overdue" : "" ?>">
                        <td>
                            <div class="subject-cell">
                                <span class="subject-badge" style="--hue: <?= (int) $hue ?>"><?= e(initials($row["subject"])) ?></span>
                                <span class="task-subject"><?= e($row["subject"]) ?></span>
                            </div>
                        </td>
                        <td class="task-details"><?= nl2br(e($row["task_details"])) ?></td>
                        <td class="text-nowrap">
                            <span class="date-main"><?= icon("calendar", 15) ?><?= e(date("D, j M Y", strtotime($row["deadline"]))) ?></span>
                            <?php if ($when !== ""): ?>
                                <span class="date-when <?= e($when_tone) ?>"><?= e($when) ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($row["status"] === "Completed"): ?>
                                <span class="badge-soft completed">Completed</span>
                            <?php elseif ($is_overdue): ?>
                                <span class="badge-soft overdue">Overdue</span>
                            <?php elseif ($is_today): ?>
                                <span class="badge-soft today">Due today</span>
                            <?php else: ?>
                                <span class="badge-soft pending">Pending</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="actions">
                                <!-- Edit (blue) -->
                                <a href="edit.php?task_id=<?= (int) $row["task_id"] ?>"
                                   class="btn btn-edit btn-sm"><?= icon("pencil", 14) ?>Edit</a>

                                <?php if ($row["status"] === "Completed"): ?>
                                    <!-- Reopen (grey): sets a completed task back to Pending -->
                                    <form action="reopen.php" method="POST">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="task_id" value="<?= (int) $row["task_id"] ?>">
                                        <button type="submit" class="btn btn-secondary btn-sm"><?= icon("undo", 14) ?>Reopen</button>
                                    </form>
                                <?php else: ?>
                                    <!-- Mark Complete (green) -->
                                    <form action="update.php" method="POST">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="task_id" value="<?= (int) $row["task_id"] ?>">
                                        <button type="submit" class="btn btn-success btn-sm"><?= icon("check", 14) ?>Mark Complete</button>
                                    </form>
                                <?php endif; ?>

                                <!-- Delete (red) with a confirmation popup -->
                                <form action="delete.php" method="POST"
                                      onsubmit="return confirm('Delete this task?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="task_id" value="<?= (int) $row["task_id"] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm"><?= icon("trash", 14) ?>Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="empty-state">
                        <?php if ($search !== "" || $filter !== "all"): ?>
                            <span class="empty-icon"><?= icon("search", 26) ?></span>
                            <div>No tasks match your search or filter. Try Reset.</div>
                        <?php else: ?>
                            <span class="empty-icon"><?= icon("coffee", 26) ?></span>
                            <div>No tasks yet. Add your first one above.</div>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php
mysqli_stmt_close($stmt);
require "includes/footer.php";
?>
