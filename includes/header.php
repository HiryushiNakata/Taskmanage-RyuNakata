<?php
// Top part shared by every page (HTML head + navigation bar).
// Set $page_title before including this file.
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(isset($page_title) ? $page_title . " | " . APP_NAME : APP_NAME) ?></title>

    <script>
        // Apply the saved color theme (chosen on the Settings page) before the page
        // is drawn, so there is no flash of the default colors.
        try {
            var savedTheme = localStorage.getItem("planner_theme");
            if (savedTheme) { document.documentElement.setAttribute("data-theme", savedTheme); }
        } catch (error) { /* storage blocked: use the default theme */ }
    </script>

    <!-- Bootstrap 5 CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Fonts: Sora (headings) and DM Sans (text) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
    <!-- Our own theme: loaded last so it can override Bootstrap -->
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>

<div class="app-frame">

    <header class="topbar">
        <a class="brand" href="index.php">
            <span class="brand-mark"><?= icon("check", 18) ?></span>
            <?= e(APP_NAME) ?>
        </a>

        <?php if (is_logged_in()): ?>
            <nav class="topnav d-none d-md-flex">
                <a href="index.php">Dashboard</a>
                <a href="index.php#add-task">Add task</a>
                <a href="index.php#tasks">My tasks</a>
            </nav>

            <div class="user-chip">
                <span class="avatar"><?= e(initial($_SESSION["full_name"])) ?></span>
                <span class="d-none d-lg-inline"><?= e($_SESSION["full_name"]) ?></span>
                <a href="settings.php" class="btn btn-glass btn-sm"><?= icon("sliders", 15) ?>Settings</a>
                <!-- Logout is a POST form so a random link can't log you out -->
                <form action="logout.php" method="POST" class="m-0">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-glass btn-sm"><?= icon("log-out", 15) ?>Log out</button>
                </form>
            </div>
        <?php else: ?>
            <div class="d-flex gap-2">
                <a href="login.php" class="btn btn-glass btn-sm">Log in</a>
                <a href="register.php" class="btn btn-white btn-sm">Register</a>
            </div>
        <?php endif; ?>
    </header>

    <main>

    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash["type"]) ?> alert-dismissible fade show" role="alert">
            <?= e($flash["message"]) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
