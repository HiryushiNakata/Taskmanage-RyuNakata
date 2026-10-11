<?php
require_once "db_connect.php";
require_once "includes/auth.php";

// Already logged in? No need to register again.
if (is_logged_in()) {
    redirect("index.php");
}

$errors    = [];
$full_name = "";
$email     = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_check();

    $full_name = trim($_POST["full_name"] ?? "");
    $email     = trim($_POST["email"] ?? "");
    $password  = $_POST["password"] ?? "";
    $confirm   = $_POST["confirm_password"] ?? "";

    // ----- Validate the input -----
    if ($full_name === "" || strlen($full_name) > 100) {
        $errors[] = "Please enter your full name (max 100 characters).";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }
    if ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    }

    // ----- Is the email already taken? -----
    if (empty($errors)) {
        $stmt = mysqli_prepare($conn, "SELECT user_id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors[] = "That email is already registered. Try logging in.";
        }
        mysqli_stmt_close($stmt);
    }

    // ----- Save the new user -----
    if (empty($errors)) {
        // password_hash() scrambles the password so even the database
        // owner cannot read it. We never store the plain password.
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO users (full_name, email, password_hash) VALUES (?, ?, ?)"
        );
        mysqli_stmt_bind_param($stmt, "sss", $full_name, $email, $hash);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        set_flash("success", "Account created! You can now log in.");
        redirect("login.php");
    }
}

$page_title = "Register";
require "includes/header.php";
?>

<div class="row g-3">
    <!-- Left: big banner -->
    <div class="col-lg-5 order-lg-1">
        <div class="hero h-100">
            <span class="hero-chip">New here?</span>
            <h1>Start your task list</h1>
            <p>Create a free account and keep every assignment, deadline and status in one place.</p>
        </div>
    </div>

    <!-- Right: register form -->
    <div class="col-lg-7 order-lg-2">
        <div class="panel h-100">
            <div class="panel-title">Create an account</div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= e($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST">
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="full_name" class="form-label">Full name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name"
                               maxlength="100" required value="<?= e($full_name) ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email"
                               maxlength="100" required value="<?= e($email) ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password"
                               minlength="6" required>
                        <div class="form-text">At least 6 characters.</div>
                    </div>
                    <div class="col-md-6">
                        <label for="confirm_password" class="form-label">Confirm password</label>
                        <input type="password" class="form-control" id="confirm_password"
                               name="confirm_password" minlength="6" required>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-white w-100">Create account</button>
                    </div>
                </div>
            </form>

            <p class="text-center mt-4 mb-0 text-secondary">
                Already have an account? <a href="login.php">Log in</a>
            </p>
        </div>
    </div>
</div>

<?php require "includes/footer.php"; ?>
