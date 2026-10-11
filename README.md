# Taskmanage - Academic Task Management System

A multi-user student assignment tracker built with **vanilla PHP, MySQL (XAMPP), HTML and Bootstrap 5**.

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

```
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
```

## How it works

1. A visitor must **log in**. `require_login()` at the top of each page redirects everyone else to `login.php`.
2. After login, the user's id is stored in `$_SESSION["user_id"]`.
3. Every task query includes `WHERE user_id = ?`, so a student can never see, edit or delete another student's task.
4. Form scripts (`add_task.php`, `update.php`, `delete.php`, `edit.php`) do their work and then **redirect** back, so refreshing the page does not resubmit the form.

## Security measures (good points for your defense)

| Threat | How it is handled |
|---|---|
| SQL injection | Prepared statements (`mysqli_prepare`) everywhere |
| XSS | Output escaped with `e()` (`htmlspecialchars`) |
| Stolen passwords | `password_hash()` / `password_verify()` — plain passwords are never stored |
| Session fixation | `session_regenerate_id(true)` after login |
| CSRF (forged forms) | Secret token in every form, checked with `csrf_check()` |
| Accessing others' data | Every query filters by the logged-in `user_id` |
| Changing data via links | Update, delete and logout use POST, not GET |
| Bypassing browser checks | Server-side validation of subject, details and date |

## Database design

- `users` (1) ──── (many) `assignments`
- `assignments.user_id` is a **foreign key** to `users.user_id` with `ON DELETE CASCADE`

## Ideas for future improvements

- Email or on-screen reminders for tasks due soon
- Pagination for very long task lists
- Password reset
