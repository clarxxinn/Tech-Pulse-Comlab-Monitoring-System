# Database rubric evidence

Tech Pulse uses MariaDB 10.4 / MySQLi and InnoDB with utf8mb4. The schema has six related tables:

| Table | Primary key | Purpose and relationships |
|---|---|---|
| courses | course_code | One canonical course name per code; referenced by users.course |
| year_levels | year_name | Ordered year choices; referenced by users.yearlevel |
| users | StudentID | Student accounts; passwords are one-way hashes |
| laboratories | lab_id | Four named laboratories |
| computers | computer_id | References laboratories; UNIQUE(lab_id, pc_number) |
| computer_sessions | session_id | References computers and optionally users; completed history survives account deletion anonymously |

All non-key attributes describe their table's entity. Course and year descriptions/options are held in reference tables instead of duplicated lists in application code. Generated active_student and active_computer columns are deliberately indexed expressions for enforcing one active session per student and PC. They are NULL for completed sessions, allowing multiple history records.

Integrity rules include positive student/lab IDs, required nonblank names, non-null account fields, valid course/year foreign keys, PC numbers 1–45, unique lab/PC pairs, valid session times, and unique active sessions. Anonymous sessions must already be completed. Foreign keys use RESTRICT; the deletion helper first locks the account, refuses active sessions, anonymizes completed records, and deletes the account in one transaction.

## CRUD and indexing

- Create: auth/register/registerdb.php → registerUser (also used by admin create).
- Read: dashboard/dashboarddb.php → findUserProfile; admin lists fetch 25 accounts per page.
- Update: admindashboard/admindashboarddb.php → adminUpdateUser; blank passwords retain the existing hash.
- Delete: admindashboard/admindashboarddb.php → deleteUser; missing targets produce an error.
- All user-supplied SQL values are bound with MySQLi prepared statements.
- Primary keys support account/computer lookups. ix_user_created supports account paging; ix_student_history supports student history; ix_session_recent supports recent session ordering. Active queries use the generated-column unique indexes directly.
- Time In locks the student and computer in a transaction. Unique indexes enforce exclusivity even for simultaneous submissions.

## Installation, upgrades, export, and restore

Fresh installation: create an empty techpulse database and import techpulse.sql. This repository export includes all six table structures, lookup values, all four labs, 180 available PCs, and one synthetic demo student. It contains no personal student records. Demo student ID: 2000000100; password: DemoStudent!2026. Configure the administrator separately in config/local.php.

Existing pre-rubric database: back up first, then run upgrade_rubric.sql **once**. Do not rerun it on an upgraded database. It preserves accounts/history and standardizes abbreviated years such as “3rd” to “3rd Year”. Do not import the fresh-install SQL over an existing database.

Full working-database backups, including all current records, should be stored outside the web root. Example in PowerShell (enter the database account's password when prompted):

```powershell
& C:\xampp\mysql\bin\mysqldump.exe --user=root --password --single-transaction --routines --triggers --hex-blob --result-file=C:\path\outside-htdocs\techpulse-full-backup.sql techpulse
```

Restore into a new empty database using phpMyAdmin Import, or pipe the SQL file to mysql with the new database selected. The export does not switch databases with USE. Verify all six tables, row counts, foreign keys, and generated-column indexes after import. Never import the full backup into the working database as a test.

## Configuration and security

Copy config/local.example.php to config/local.php. Supply a dedicated database user with only SELECT, INSERT, UPDATE, DELETE on techpulse.*. Use schema-management credentials only for installation/migrations. Local config is Git-ignored, and config/database/tests plus .git and SQL archives are blocked from Apache HTTP requests. PHP logs detailed database failures server-side and displays a generic unavailable message. Sessions use HttpOnly, SameSite=Lax, and Secure cookies on HTTPS. Both PHP and MySQL use UTC+08:00 for new timestamps; old history is retained as stored.

## Verification

Import a full backup into a separate database named techpulse_rubric_verify. Set DB_NAME to that database, DB_USER/DB_PASSWORD to verification-only credentials, and ADMIN_PASSWORD_HASH to a generated test administrator hash. Run tests/database.php using the PHP CLI. This test refuses other database names and removes its synthetic records in a finally block.

tests/http.ps1 targets only the isolated PHP verification server on 127.0.0.1:8097, using the test administrator password RubricAdmin!2026. Configure its hash on that server only. Start PHP with a writable session.save_path outside the web root and the verification database environment. Never use that test password or administrator hash for the working system.
