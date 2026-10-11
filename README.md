[README.md](https://github.com/user-attachments/files/33301425/README.md)
# EduTrack Academic Center — Task Management System

**Business:** EduTrack Academic Center
**Description:** A student organization and tutoring center offering a centralized task management system to help enrolled students easily organize their assignments and deadlines.

## Problem Being Solved
Students often lose track of deadlines across multiple subjects. This system provides a unified dashboard to track Pending, Completed, and Overdue tasks, preventing missed assignments and improving academic performance.

## Features
- Register / login / logout (passwords are hashed, sessions keep you logged in)
- Every student sees **only their own** tasks
- Dashboard with summary cards (Total, Pending, Completed, Overdue) and a progress bar
- Add, edit, complete, reopen and delete tasks (full CRUD)
- Tasks sorted by deadline, closest first
- Overdue tasks highlighted in red, tasks due today get a badge
- Search by subject/details and filter by status
- Settings page with six color themes (Twilight, Cocoa, Forest, Ocean, Rose, Honey)

## Design
Bootstrap 5 handles the grid and forms; `assets/style.css` adds the look: slate page frame, purple-to-blue gradient cards, glass panels, pill buttons, and a progress ring drawn with a CSS `conic-gradient`. Every color comes from CSS variables, so a theme is just a block that changes those variables; the Settings page switches `data-theme` on `<html>` and saves the choice in `localStorage`. Fonts (Sora and DM Sans) load from Google Fonts, so the app needs an internet connection like the Bootstrap CDN does.

## Setup (XAMPP)
1. Copy this folder to `C:\xampp\htdocs\student_planner\`
2. Start **Apache** and **MySQL** in the XAMPP Control Panel
3. Open `http://localhost/phpmyadmin`, click the **SQL** tab, paste the contents of `database.sql`, click **Go**
4. Open `http://localhost/student_planner/`
5. Click **Register**, create an account, and log in

If your MySQL root user has a password, change it in `db_connect.php`.

## Project structure
```text
student_planner/
├── database.sql        Creates the database, users table and assignments table
├── db_connect.php      Database connection
├── assets/
│   └── style.css       Custom theme (gradients, glass panels, pill buttons) on top of Bootstrap
├── includes/
│   ├── auth.php        Session, login check, CSRF token and small helper functions
│   ├── hero_art.php    Decorative stack of task cards shown in the banner
│   ├── header.php      Shared top of every page (top bar, flash messages)
│   └── footer.php      Shared bottom of every page
├── register.php        Create an account
├── login.php           Log in
├── logout.php          Log out
├── index.php           Dashboard: stats, add form, search/filter, task table  (READ)
├── add_task.php        Handles the add form                                   (CREATE)
├── edit.php            Edit form + save changes                               (UPDATE)
├── update.php          Marks a task as Completed                              (UPDATE)
├── settings.php        Color theme picker (saved in the browser)
├── reopen.php          Sets a completed task back to Pending                  (UPDATE)
└── delete.php          Deletes a task                                         (DELETE)
How it worksA visitor must log in. require_login() at the top of each page redirects everyone else to login.php.After login, the user's id is stored in $_SESSION["user_id"].Every task query includes WHERE user_id = ?, so a student can never see, edit or delete another student's task.Form scripts (add_task.php, update.php, delete.php, edit.php) do their work and then redirect back, so refreshing the page does not resubmit the form.Security measures (good points for your defense)ThreatHow it is handledSQL injectionPrepared statements (mysqli_prepare) everywhereXSSOutput escaped with e() (htmlspecialchars)Stolen passwordspassword_hash() / password_verify() — plain passwords are never storedSession fixationsession_regenerate_id(true) after loginCSRF (forged forms)Secret token in every form, checked with csrf_check()Accessing others' dataEvery query filters by the logged-in user_idChanging data via linksUpdate, delete and logout use POST, not GETBypassing browser checksServer-side validation of subject, details and dateDatabase designusers (1) ──── (many) assignmentsassignments.user_id is a foreign key to users.user_id with ON DELETE CASCADEIdeas for future improvementsEmail or on-screen reminders for tasks due soonPagination for very long task listsPassword resetAI Tools UsedClaude / Gemini: Used as thought partners for vibe coding the PHP backend logic, designing the CSS dashboard, and structuring the MySQL relational database.Test AccountsTest User Email: student@test.comTest User Password: password123
