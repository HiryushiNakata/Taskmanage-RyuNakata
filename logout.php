<?php
require_once "includes/auth.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_check();

    // Wipe everything stored in the session, then destroy it
    $_SESSION = [];
    session_destroy();

    // Start a fresh session just to carry the goodbye message
    session_start();
    set_flash("info", "You have been logged out.");
}

redirect("login.php");
