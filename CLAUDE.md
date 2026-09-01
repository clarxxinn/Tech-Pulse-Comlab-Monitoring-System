# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Stack & Running

Plain PHP 8.2 + MariaDB/MySQL served by XAMPP, with vanilla JS/CSS on the frontend. There is **no build system, dependency manager, or test framework** — no Composer, npm, linters, or test runner. Edit files and refresh the browser.

- **Run:** Start Apache and MySQL in the XAMPP control panel. The project lives in `htdocs`, so it is served at `http://localhost/Tech%20Pulse%20-%20Comlab%20Monitoring%20System/`.
- **Database setup:** In phpMyAdmin, create a database named `techpulse` and import `database/techpulse.sql`. Connection settings live in `config/techpulse.php` (defaults: host `localhost`, user `root`, empty password).

## Architecture

**Page controller + view in one file.** Each PHP page (`auth/register/register.php`, `auth/login/login.php`) runs its full request lifecycle at the top of the file — `session_start()`, `require_once` config and DB helpers, handle the POST, then fall through to render the HTML for that same page. There is no router, template engine, or framework.

**DB access is isolated in `*db.php` helpers.** Files like `auth/register/registerdb.php` and `auth/login/logindb.php` expose pure functions (`registerUser()`, `findUserByStudentId()`) that take `mysqli $conn` as their first argument, use prepared statements, and return plain arrays/null. Page files never write SQL inline. When adding data access, add a function to the relevant `*db.php` rather than querying from the page. mysqli is in exception mode, so DB helpers catch `mysqli_sql_exception` (e.g. code `1062` = duplicate key) rather than checking return values.

**Auth & session conventions** (mirror these in any new authenticated page):
- Logged-in state is `$_SESSION['StudentID']`. Guard entry with `if (!empty($_SESSION['StudentID'])) { header('Location: ...'); exit; }`.
- CSRF: a `csrf_token` is generated per session (`bin2hex(random_bytes(32))`), embedded as a hidden field, checked with `hash_equals()`, and `unset()` after a successful action (one-time use).
- Passwords use `password_hash(PASSWORD_DEFAULT)` / `password_verify()`. Login calls `session_regenerate_id(true)` on success.
- Includes use `require_once __DIR__ . '/...'` relative paths — preserve this when moving/adding files.

**Post-login area.** `dashboard/dashboard.php` is the authenticated landing page: it guards on `$_SESSION['StudentID']`, fetches the profile fresh via `findUserProfile()` in `dashboard/dashboarddb.php` (rather than trusting session fields), and renders a profile card. Logout is a CSRF-protected POST form targeting `auth/logout/logout.php`, which tears down the session and cookie only on a valid POST.

**Admin area.** There is a single hardcoded administrator — no admin row/table exists. Credentials live in `config/techpulse.php` as `ADMIN_USERNAME` + `ADMIN_PASSWORD_HASH` (a bcrypt hash; regenerate with `php -r "echo password_hash('...', PASSWORD_DEFAULT);"`). The admin signs in through the *same* `auth/login/login.php`: the POST handler detects the `admin` username (via `hash_equals(ADMIN_USERNAME, ...)`) *before* the numeric Student-ID validation, verifies the password, and sets `$_SESSION['is_admin'] = true` — a marker deliberately kept separate from `$_SESSION['StudentID']` so the two areas can't cross over. `admindashboard/admindashboard.php` guards on `is_admin` and provides CRUD over `users`: it **reuses** `registerUser()` (create) and `findUserProfile()` (edit prefill), and adds `getAllUsers()`/`adminUpdateUser()`/`deleteUser()` in `admindashboard/admindashboarddb.php`. Mutations are CSRF-protected and use Post/Redirect/Get with a one-time `$_SESSION['admin_flash']`; `editaccount.php` handles updates (StudentID is read-only — it's the PK). Logout reuses `auth/logout/logout.php`.

**Data model.** Single `users` table; `StudentID` (int) is the primary key and login identifier — validated with `ctype_digit`, never auto-increment. Allowed `course` and `yearlevel` values are hardcoded whitelists in `config/options.php` (`$allowedCourses` is a `code => label` map, validated by key; `$allowedYearLevels` a flat list) — shared by `register.php` and the admin create/edit pages, validated server-side against those arrays.

**Write-path validation is duplicated, not shared.** The three account-write pages — `register.php`, `admindashboard.php` (create), and `editaccount.php` (update) — each re-implement the *same* server-side field checks inline: first/last name non-empty and ≤67 chars (`mb_strlen`), `StudentID` `ctype_digit`, `course` via `array_key_exists($allowedCourses)`, `yearlevel` via strict `in_array($allowedYearLevels)`, password ≥8 chars (`strlen`). There is no shared validator, so a rule change or new field must be applied to all three. The deliberate differences: register also requires a matching `confirm_password`; edit treats a blank password as "keep current" (skips the length check and passes `null` to `adminUpdateUser()`, which then leaves the stored hash untouched).

**Theming.** CSS-variable design tokens in `assets/css/style.css` under `:root` and `[data-theme="dark"]`. Every page's `<head>` repeats a small inline script that reads `localStorage.theme` and sets `data-theme` before paint (no-flash). `assets/js/theme.js` handles the toggle button and persistence. Keep that inline bootstrap when creating new pages.

**Frontend JS** is vanilla and page-scoped: `assets/js/script.js` drives the landing page (IntersectionObserver reveals elements with `.animate-on-scroll` by adding `.animated`, plus nav behavior); the `auth/*/\*_auth.js` files handle password show/hide and submit-button disabling.

## Gotchas

- DB credentials are the XAMPP defaults (user `root`, empty password) hardcoded in `config/techpulse.php`.
- The seeded rows in `database/techpulse.sql` include test data with inconsistent `course`/`yearlevel` values (e.g. `2nd` vs `2nd Year`, junk course strings). The dashboard displays stored values verbatim, so expect messy legacy data.
- `editaccount.php` is the one place that reconciles that legacy data: if a row's stored `course`/`yearlevel` isn't in the current whitelist it shows a hint and forces the admin to pick a valid value, since an out-of-list value fails the shared validation and can't round-trip through a save.
- Self-healing session: `dashboard.php` re-fetches the profile every request and, if `$_SESSION['StudentID']` no longer maps to a row, destroys the session and redirects to login — so deleting a logged-in student via the admin area cleanly logs them out on their next request.
- The promised ComLab features (computer status, session tracking) advertised on the landing page have **no tables or code behind them yet** — only user auth and the profile dashboard exist.
