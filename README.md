# Tech Pulse — ComLab Monitoring System

A school project built with PHP and MariaDB for monitoring computer laboratory use and managing student accounts.

The system currently has **4 laboratories with 45 PCs each — 180 PCs total**.

## Features

- Student registration, login, profile, and logout.
- Student account CRUD: create, view, edit, and delete.
- Computer availability and maintenance status.
- Student Time In / Time Out and usage history.
- Admin Time Out for abandoned sessions.
- Responsive layouts with light and dark themes.

Availability updates when the dashboard is loaded or refreshed.

## Requirements

- XAMPP with Apache and MariaDB/MySQL.
- PHP with the MySQLi and mbstring extensions.
- A web browser.

Tested using PHP 8.2 and MariaDB 10.4. No build step is needed.

## Project folders

```text
Tech Pulse - Comlab Monitoring System/
├── admindashboard/   # Admin pages and student account CRUD
├── assets/           # CSS, JavaScript, and images
├── auth/             # Registration, login, and logout
├── config/           # Connection, validation, sessions, and monitoring
├── dashboard/        # Student dashboard
├── database/
│   └── techpulse.sql # Database structure and demo records
├── .gitignore
├── .htaccess
├── index.php
└── README.md
```

## Setup

1. Place the project in `C:\xampp\htdocs\Tech Pulse - Comlab Monitoring System`.
2. Start **Apache** and **MySQL** in XAMPP.
3. Open [phpMyAdmin](http://localhost/phpmyadmin/) and create an empty database named `techpulse`.
4. Select the database and import [database/techpulse.sql](database/techpulse.sql).
5. Copy `config/local.example.php` to `config/local.php`. Enter your database credentials and administrator username/password hash. Keep the existing local.php if this installation is already working.
6. Open [Tech Pulse](http://localhost/Tech%20Pulse%20-%20Comlab%20Monitoring%20System/).

Generate a hash for your chosen administrator password using PowerShell:

```powershell
& C:\xampp\php\php.exe -r "echo password_hash('YOUR_ADMIN_PASSWORD', PASSWORD_DEFAULT), PHP_EOL;"
```

Paste the result into `ADMIN_PASSWORD_HASH` in `config/local.php`. That file is ignored by Git. Use a database account with SELECT, INSERT, UPDATE, and DELETE permissions for the application.

The SQL installer includes this demo student:

| Student ID | Password |
|---|---|
| `2000000100` | `DemoStudent!2026` |

Import the installer into an empty database. It contains demo records, not a copy of your current student accounts or usage history.

## Database

| Table | Purpose |
|---|---|
| `users` | Student profiles and hashed passwords |
| `courses` | Valid courses |
| `year_levels` | Valid year levels |
| `laboratories` | Laboratory names |
| `computers` | PC numbers, laboratory assignments, and maintenance status |
| `computer_sessions` | Time In / Time Out history |

The database uses primary keys, foreign keys, unique constraints, checks, and indexes. Prepared statements handle user input. A student and PC can each have only one active session.

An active student must Time Out before account deletion. Completed usage records remain anonymous after deleting the account.

## Manual checking

1. Register a student and try registering the same Student ID again.
2. Test valid and invalid logins, logout, and protected pages.
3. As admin, create, view, edit, and delete a disposable student account.
4. Test Time In / Time Out and occupied/maintenance PC restrictions.
5. Check mobile and desktop layouts in both themes.
6. Export the database and test importing it into a separate empty database.

Check PHP syntax from the project folder:

```powershell
Get-ChildItem -Recurse -Filter *.php | ForEach-Object {
    & C:\xampp\php\php.exe -l $_.FullName
}
```

## SQL export for submission

In phpMyAdmin, select **techpulse → Export → Quick → SQL → Export**. This includes the current table structures and records required by the rubric. Keep the exported file for submission; the repository's demo installer is a separate file. Do not publish personal student records in a public repository.

To verify the export, import it into another empty database and check that all six tables and their records are present.

## Purpose

Developed for educational use as a database-connected CRUD project. No open-source license has been specified.
