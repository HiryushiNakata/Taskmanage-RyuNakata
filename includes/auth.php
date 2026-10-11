<?php
// Shared helpers used by every page.
// Include this file at the top of each page, AFTER db_connect.php.

// The name shown in the top bar, page titles and footer
define("APP_NAME", "Taskmanage");

// Sessions let PHP remember who is logged in between page loads
session_start();

// Makes "today" correct for the deadline checks
date_default_timezone_set("Asia/Manila");

// e() = escape. Prevents XSS by turning <script> into harmless text.
function e($text)
{
    return htmlspecialchars((string) $text, ENT_QUOTES, "UTF-8");
}

// First letter of a name, shown inside the round avatar in the top bar
function initial($name)
{
    $name = trim((string) $name);
    return function_exists("mb_substr")
        ? mb_strtoupper(mb_substr($name, 0, 1))
        : strtoupper(substr($name, 0, 1));
}

// plural(2, "task") -> "2 tasks",  plural(1, "task") -> "1 task"
function plural($count, $word)
{
    return $count . " " . $word . ($count == 1 ? "" : "s");
}

// initials("Data Structures") -> "DS"   (used inside the colored subject badge)
function initials($text)
{
    $words = preg_split('/\s+/', trim((string) $text));
    $result = "";
    foreach (array_slice($words, 0, 2) as $word) {
        $result .= initial($word);
    }
    return $result;
}

// How many days from $today until $deadline? (negative = already past)
function days_until($deadline, $today)
{
    return (int) round((strtotime($deadline) - strtotime($today)) / 86400);
}

// Turns a number of days into friendly text: "Due today", "Due in 3 days", "2 days overdue"
function due_label($days)
{
    if ($days < 0) {
        return plural(abs($days), "day") . " overdue";
    }
    if ($days === 0) {
        return "Due today";
    }
    if ($days === 1) {
        return "Due tomorrow";
    }
    return "Due in " . $days . " days";
}

// CSS class used to color the label: "overdue", "today" or "" (normal)
function due_tone($days)
{
    if ($days < 0) {
        return "overdue";
    }
    return $days === 0 ? "today" : "";
}

// Small inline SVG icons (Feather icon set, MIT license).
// Usage: echo icon("check");  -> a line icon that takes the surrounding text color
function icon($name, $size = 18)
{
    $paths = [
        "check"        => '<polyline points="20 6 9 17 4 12"/>',
        "check-circle" => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
        "alert"        => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>',
        "clock"        => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
        "list"         => '<line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>',
        "calendar"     => '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
        "plus"         => '<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>',
        "pencil"       => '<path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/>',
        "undo"         => '<polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/>',
        "trash"        => '<polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>',
        "sliders"      => '<line x1="4" y1="21" x2="4" y2="14"/><line x1="4" y1="10" x2="4" y2="3"/><line x1="12" y1="21" x2="12" y2="12"/><line x1="12" y1="8" x2="12" y2="3"/><line x1="20" y1="21" x2="20" y2="16"/><line x1="20" y1="12" x2="20" y2="3"/><line x1="1" y1="14" x2="7" y2="14"/><line x1="9" y1="8" x2="15" y2="8"/><line x1="17" y1="16" x2="23" y2="16"/>',
        "log-out"      => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>',
        "coffee"       => '<path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/>',
        "search"       => '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>',
    ];

    if (!isset($paths[$name])) {
        return "";
    }

    $size = (int) $size;
    return '<svg class="icon" xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size
         . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
         . $paths[$name] . '</svg>';
}

// Send the browser to another page and stop running this script
function redirect($page)
{
    header("Location: " . $page);
    exit;
}

// Is someone logged in?
function is_logged_in()
{
    return isset($_SESSION["user_id"]);
}

// Put this at the top of any page that needs a login
function require_login()
{
    if (!is_logged_in()) {
        redirect("login.php");
    }
}

// ---------- Flash messages (one-time notices like "Task added!") ----------
function set_flash($type, $message)
{
    $_SESSION["flash"] = ["type" => $type, "message" => $message];
}

function get_flash()
{
    if (isset($_SESSION["flash"])) {
        $flash = $_SESSION["flash"];
        unset($_SESSION["flash"]);   // show it only once
        return $flash;
    }
    return null;
}

// ---------- CSRF protection ----------
// A secret token is placed in every form. A forged request from another
// website cannot know the token, so it gets rejected.
function csrf_token()
{
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }
    return $_SESSION["csrf_token"];
}

// Prints the hidden input to place inside a <form>
function csrf_field()
{
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

// Call this at the top of every script that handles a POST form
function csrf_check()
{
    $sent = $_POST["csrf_token"] ?? "";
    if (!hash_equals(csrf_token(), $sent)) {
        http_response_code(403);
        die("Invalid request. Please go back and try again.");
    }
}

// Checks that a string is a real date in YYYY-MM-DD format
function is_valid_date($date)
{
    $d = DateTime::createFromFormat("Y-m-d", $date);
    return $d && $d->format("Y-m-d") === $date;
}
