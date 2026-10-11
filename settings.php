<?php
require_once "db_connect.php";
require_once "includes/auth.php";
require_login();

// The available color themes: key => [name, short description, page color, accent color, banner color]
// The three colors are only used to draw the little preview stripes.
$themes = [
    "twilight" => ["Twilight", "Soft purple and blue", "#2a3142", "#8d7bf0", "#c6c8f7"],
    "cocoa"    => ["Cocoa",    "Warm chocolate brown", "#2b211c", "#c58b5c", "#e6c8a3"],
    "forest"   => ["Forest",   "Calm sage green",      "#1f2a26", "#8fb89a", "#c4dcc8"],
    "ocean"    => ["Ocean",    "Cool, clear blue",     "#1d2733", "#6fb1e0", "#b9d8f3"],
    "rose"     => ["Rose",     "Dusty blush pink",     "#2e2328", "#e08fa6", "#f0c6d3"],
    "honey"    => ["Honey",    "Golden and sunny",     "#2a2418", "#e0b24f", "#f1d89d"],
];

$first_name = explode(" ", trim($_SESSION["full_name"]))[0];

$page_title = "Settings";
require "includes/header.php";
?>

<div class="row justify-content-center">
    <div class="col-lg-9">

        <section class="panel mb-3">
            <div class="panel-title">Color theme</div>
            <p class="text-secondary mb-4">
                Pick the colors that feel most comfortable. The change applies right away
                and is remembered on this device.
            </p>

            <div class="row g-3" id="theme-list">
                <?php foreach ($themes as $key => $t): ?>
                    <div class="col-6 col-md-4">
                        <!-- data-theme tells the script which theme this button selects -->
                        <button type="button" class="theme-option" data-theme="<?= e($key) ?>" aria-pressed="false">
                            <span class="swatch" style="--s1: <?= e($t[2]) ?>; --s2: <?= e($t[3]) ?>; --s3: <?= e($t[4]) ?>;"></span>
                            <span class="theme-name"><?= e($t[0]) ?></span>
                            <span class="theme-desc"><?= e($t[1]) ?></span>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-3 mt-4">
                <button type="button" class="btn btn-glass btn-sm" id="theme-reset">Reset to default</button>
                <!-- aria-live makes screen readers announce the message when it changes -->
                <span class="text-secondary small" id="theme-status" aria-live="polite"></span>
            </div>
        </section>

        <!-- Live preview: these cards use the current theme's colors -->
        <section class="row g-3">
            <div class="col-md-7">
                <div class="hero preview-hero h-100">
                    <h2>Good day, <?= e($first_name) ?></h2>
                    <p>This is how your banner looks.</p>
                    <a href="index.php" class="btn btn-ink btn-sm">Back to dashboard</a>
                </div>
            </div>
            <div class="col-md-5">
                <div class="panel panel-violet h-100">
                    <div class="panel-title">Buttons and status</div>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="btn btn-edit btn-sm">Edit</span>
                        <span class="btn btn-success btn-sm">Mark Complete</span>
                        <span class="btn btn-danger btn-sm">Delete</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge-soft pending">Pending</span>
                        <span class="badge-soft completed">Completed</span>
                        <span class="badge-soft overdue">Overdue</span>
                    </div>
                </div>
            </div>
        </section>

    </div>
</div>

<script>
// ---------- Color theme picker ----------
// The chosen theme name is saved in the browser (localStorage), so it stays
// after a refresh. includes/header.php re-applies it on every page.
var STORAGE_KEY   = "planner_theme";
var DEFAULT_THEME = "twilight";

var buttons = document.querySelectorAll(".theme-option");
var statusText = document.getElementById("theme-status");

// Highlights the button of the active theme
function markSelected(theme) {
    buttons.forEach(function (button) {
        button.setAttribute("aria-pressed", button.dataset.theme === theme ? "true" : "false");
    });
}

// Switches the whole page to a theme and remembers the choice
function applyTheme(theme) {
    document.documentElement.setAttribute("data-theme", theme);
    try {
        localStorage.setItem(STORAGE_KEY, theme);
    } catch (error) {
        // Private windows can block storage; the theme still changes for this visit
    }
    markSelected(theme);
    statusText.textContent = "Theme saved on this device.";
}

buttons.forEach(function (button) {
    button.addEventListener("click", function () {
        applyTheme(button.dataset.theme);
    });
});

document.getElementById("theme-reset").addEventListener("click", function () {
    applyTheme(DEFAULT_THEME);
});

// When the page opens, show which theme is active right now
markSelected(document.documentElement.getAttribute("data-theme") || DEFAULT_THEME);
</script>

<?php require "includes/footer.php"; ?>
