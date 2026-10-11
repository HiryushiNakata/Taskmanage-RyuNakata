<?php
require_once "db_connect.php";
require_once "includes/auth.php";

// Already logged in? Go straight to the dashboard.
if (is_logged_in()) {
    redirect("index.php");
}

$error = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_check();

    $email    = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    // Look up the user by email
    $stmt = mysqli_prepare(
        $conn,
        "SELECT user_id, full_name, password_hash FROM users WHERE email = ?"
    );
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    // password_verify() compares the typed password with the stored hash
    if ($user && password_verify($password, $user["password_hash"])) {

        // New session ID after login blocks "session fixation" attacks
        session_regenerate_id(true);

        // Remember who this is for the rest of the visit
        $_SESSION["user_id"]   = (int) $user["user_id"];
        $_SESSION["full_name"] = $user["full_name"];

        redirect("index.php");
    }

    // Same message for "no such email" and "wrong password" on purpose,
    // so attackers can't find out which emails are registered.
    $error = "Incorrect email or password.";
}

$page_title = "Log in";
require "includes/header.php";
?>

<div class="row g-3">
    <!-- Left: big banner -->
    <div class="col-lg-7">
        <div class="hero hero-has-art h-100">
            <span class="hero-chip">Welcome back</span>
            <h1>Plan it. Track it. Finish it.</h1>
            <p>Log in to see your deadlines, mark tasks complete and keep your coursework under control.</p>
            <ul class="hero-points">
                <li>Closest deadline always on top</li>
                <li>Overdue tasks stand out in red</li>
                <li>Your tasks are private to your account</li>
            </ul>
            <?php include "includes/hero_art.php"; ?>
        </div>
    </div>

    <!-- Right: login form -->
    <div class="col-lg-5">
        <div class="panel h-100">
            <div class="panel-title">Log in</div>

            <?php if ($error !== ""): ?>
                <div class="alert alert-danger"><?= e($error) ?></div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email"
                           required value="<?= e($email) ?>">
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password"
                           name="password" required>
                </div>
                <button type="submit" class="btn btn-white w-100">Log in</button>
            </form>

            <p class="text-center mt-4 mb-0 text-secondary">
                No account yet? <a href="register.php">Register</a>
            </p>
        </div>
    </div>
</div>

<?php require "includes/footer.php"; ?>
