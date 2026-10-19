# Repository Guidelines

## Project Structure & Module Organization

Tech Pulse is a PHP/MySQL application with vanilla JavaScript and CSS.

- `index.php`: public landing page.
- `auth/login/`, `auth/register/`, `auth/logout/`: authentication pages, handlers, and supporting assets.
- `dashboard/`: student profile dashboard; `admindashboard/`: administrator account management.
- `*db.php`: database helpers colocated with their feature pages. Keep SQL operations in these helpers.
- `config/techpulse.php`: MySQL connection and administrator authentication configuration; `config/options.php`: allowed courses and year levels.
- `assets/css/`, `assets/js/`, `assets/img/`: shared styles, scripts, and images.
- `database/techpulse.sql`: database schema and seed records. No test directory currently exists.

## Build, Test, and Development Commands

No build step, Composer dependencies, or npm scripts are configured. Use XAMPP with PHP and MySQL/MariaDB.

1. Run `& C:\xampp\xampp-control.exe` from PowerShell and start Apache and MySQL.
2. In phpMyAdmin, create `techpulse`, then import `database/techpulse.sql`. Check connection settings in `config/techpulse.php`.
3. Open `http://localhost/Tech%20Pulse%20-%20Comlab%20Monitoring%20System/`.

From the repository root, check PHP syntax with:

```powershell
Get-ChildItem -Recurse -Filter *.php | ForEach-Object {
    & C:\xampp\php\php.exe -l $_.FullName
}
```

Keep the current directory name when using XAMPP; theme image URLs include that base path.

## Coding Style & Naming Conventions

Follow `index.php`: plain page markup, four-space indentation, and simple control flow. PHP helpers use `declare(strict_types=1)`, typed parameters and returns, and descriptive camelCase names such as `findUserByStudentId`. Preserve database identifiers such as `StudentID`. Use `__DIR__` for PHP includes, lowercase feature filenames, camelCase JavaScript identifiers, and hyphenated CSS classes. Share repeated styles and scripts in `assets/`. No formatter or linter is configured.

## Testing Guidelines

No automated framework, test naming convention, or coverage target is configured. Run the syntax check above and manually verify registration, duplicate Student IDs, valid/invalid login, logout, protected-page access, and administrator create/edit/delete operations using disposable local records. For interface changes, check mobile layouts and both themes. Record checks and outcomes in the pull request.

## Commit & Pull Request Guidelines

History uses short descriptions such as `Fixed css` and `Added some features`; no formal prefix convention is evident. Write focused, descriptive subjects such as `Fix login redirect after logout`. Pull requests should explain the change, link related issues, list validation performed, and include screenshots for interface changes. Identify database or configuration changes explicitly.

## Security & Configuration

Preserve prepared statements, password hashing/verification, CSRF checks, session regeneration, and administrator access checks. Escape dynamic HTML output. Do not commit production credentials or real student records; use synthetic fixtures for database changes.
