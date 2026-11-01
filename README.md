# Tech Pulse — Computer Laboratory Monitoring System

Tech Pulse is a PHP and MariaDB application for managing computer laboratory usage, student accounts, and Time In / Time Out records. It includes separate student and administrator dashboards, responsive layouts, and light and dark themes.

The current installation supports **4 laboratories, 45 PCs per laboratory, and 180 PCs in total**. Laboratory choices and computer counts are loaded from the database.

## Features

### Students

- Register and log in using a Student ID.
- View account information and recent computer usage history.
- Choose ComLab 1, 2, 3, or 4 and view available, occupied, and maintenance PCs.
- Select an available PC to Time In and end the session with Time Out.
- Use the system on phone, tablet, and desktop screen sizes.

### Administrators

- View computer availability statistics and active sessions.
- Create, view, update, and delete student accounts.
- Browse student accounts in pages of 25 records.
- Mark unoccupied computers as under maintenance or available.
- End abandoned sessions using the administrator Time Out action.
- Review recent usage history, including anonymous records for deleted accounts.

### Validation and security

- MySQLi prepared statements for user-supplied query values.
- Password hashing and verification.
- Server-side account validation and database integrity constraints.
- CSRF protection, session ID regeneration after login, and administrator access checks.
- Escaped HTML output and HttpOnly / SameSite session cookies.
- Private local configuration and a database account restricted to CRUD permissions.
- Apache rules blocking direct access to configuration, SQL exports, Git metadata, and test files.

Availability updates when a dashboard is loaded or refreshed; the application does not continuously poll for changes.

## Technology

| Component | Technology |
|---|---|
| Backend | PHP with MySQLi |
| Database | MariaDB / InnoDB |
| Frontend | HTML, CSS, and vanilla JavaScript |
| Local server | Apache through XAMPP |
| Database administration | phpMyAdmin |
| Version control | Git and GitHub |

The project has been tested with **PHP 8.2** and **MariaDB 10.4** on Windows/XAMPP. PHP requires the `mysqli` and `mbstring` extensions. No Composer packages, npm dependencies, or build step are required.

## Project structure

```text
Tech Pulse - Comlab Monitoring System/
├── admindashboard/         # Admin dashboard, account CRUD, and styles
├── assets/
│   ├── css/               # Shared styles and responsive layout rules
│   ├── img/               # Images and theme icons
│   └── js/                # Shared interface scripts
├── auth/
│   ├── login/
│   ├── logout/
│   └── register/
├── config/
│   ├── local.example.php  # Configuration template
│   ├── local.php          # Private local credentials; Git-ignored
│   ├── monitoring.php     # Computer and session operations
│   ├── options.php        # Course/year choices from reference tables
│   ├── session.php        # Session initialization
│   ├── techpulse.php      # Database connection and application settings
│   └── validation.php     # Shared account validation
├── dashboard/             # Student dashboard and profile queries
├── database/
│   ├── README.md          # Schema, constraints, backup, and test notes
│   ├── techpulse.sql      # Complete fresh-install schema and synthetic data
│   ├── add_comlab4.sql    # ComLab 4 update for older monitoring databases
│   └── upgrade_rubric.sql # One-time database integrity/reference-table upgrade
├── tests/
│   ├── database.php       # Isolated database/helper regression checks
│   └── http.ps1           # Isolated HTTP workflow checks
├── .gitignore
├── .htaccess
├── AGENTS.md
├── index.php              # Public landing page
└── README.md
```

## Local installation

### 1. Clone the project

In PowerShell:

```powershell
Set-Location C:\xampp\htdocs
git clone https://github.com/clarxxinn/Tech-Pulse-Comlab-Monitoring-System.git "Tech Pulse - Comlab Monitoring System"
Set-Location "C:\xampp\htdocs\Tech Pulse - Comlab Monitoring System"
```

If the project is already present, use the existing folder.

### 2. Start XAMPP

Start **Apache** and **MySQL** in the XAMPP Control Panel. Open [phpMyAdmin](http://localhost/phpmyadmin/).

### 3. Import the database

Create an empty database named `techpulse` with the `utf8mb4_general_ci` collation. Select that database, open **Import**, and import [database/techpulse.sql](database/techpulse.sql).

The fresh-install export includes all six table structures, course/year options, four laboratories, 180 available PCs, and one synthetic student account. It does not contain the working installation's personal student records.

### 4. Create an application database user

For a new installation, run the following in phpMyAdmin as a database administrator, replacing the password placeholder:

```sql
CREATE USER 'techpulse_app'@'localhost'
    IDENTIFIED BY 'REPLACE_WITH_YOUR_DATABASE_PASSWORD';

GRANT SELECT, INSERT, UPDATE, DELETE
    ON techpulse.* TO 'techpulse_app'@'localhost';
```

If this account already exists, use its configured credentials. Schema imports and upgrades require a separate database account with the necessary schema-management permissions.

### 5. Configure local credentials

```powershell
Copy-Item config/local.example.php config/local.php
```

Edit `config/local.php` and set the database credentials, administrator username, and administrator password hash. Generate the hash using your chosen password:

```powershell
& C:\xampp\php\php.exe -r "echo password_hash('REPLACE_WITH_YOUR_ADMIN_PASSWORD', PASSWORD_DEFAULT), PHP_EOL;"
```

Paste the resulting hash into `ADMIN_PASSWORD_HASH` in `config/local.php`. The database password must match the account created in the previous step. Keep local credentials out of Git; `config/local.php` is already ignored.

The application also accepts the corresponding environment variables: `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`, `ADMIN_USERNAME`, and `ADMIN_PASSWORD_HASH`. Environment variables take precedence over local configuration.

### 6. Open the system

[Open Tech Pulse locally](http://localhost/Tech%20Pulse%20-%20Comlab%20Monitoring%20System/).

The URL above matches the folder name used in the clone command. If you choose another folder name, adjust the URL accordingly. After interface updates, use **Ctrl+F5** to refresh cached styles and scripts.

## Demo account

The following student exists **only after importing the fresh-install SQL**:

| Field | Value |
|---|---|
| Student ID | `2000000100` |
| Password | `DemoStudent!2026` |

Administrator credentials are configured separately in `config/local.php`; the repository does not provide a default working administrator password.

## Database design

| Table | Purpose |
|---|---|
| `courses` | Canonical course codes and names |
| `year_levels` | Valid year-level choices and display order |
| `users` | Student accounts, profiles, and password hashes |
| `laboratories` | Laboratory identifiers and names |
| `computers` | Laboratory assignments, PC numbers, and maintenance status |
| `computer_sessions` | Time In / Time Out records and computer assignments |

Foreign keys connect students to course/year choices, computers to laboratories, and sessions to students and computers. Occupied status is derived from active sessions.

Unique generated-column indexes enforce one active session per student and per computer. Other indexes support student history, recent sessions, and account pagination. Database checks reject invalid IDs, blank names, invalid PC numbers, and inconsistent session times.

Students with an active session must Time Out before their accounts can be deleted. Deleting an account anonymizes its completed session records and retains them for laboratory usage history.

See [database/README.md](database/README.md) for the schema explanation and implementation details.

## Existing installations

Back up the existing database before applying migrations.

| File | When to use it |
|---|---|
| `database/techpulse.sql` | A fresh installation into an empty database |
| `database/add_comlab4.sql` | An older monitoring database that needs ComLab 4 and its 45 PCs; safe to rerun |
| `database/upgrade_rubric.sql` | An existing pre-upgrade monitoring database; run **once** |

The integrity upgrade adds reference tables, validation constraints, and indexes, and standardizes abbreviated year values. Do not rerun it on an already upgraded database. Do not import the fresh-install SQL over an existing working database.

## Backup and restore

Create a complete export of the working database using phpMyAdmin **Export**, or use PowerShell with an existing destination folder outside `htdocs`:

```powershell
& C:\xampp\mysql\bin\mysqldump.exe --user=root --password --single-transaction --routines --triggers --hex-blob --result-file="C:\path\outside-htdocs\techpulse-full-backup.sql" techpulse
```

Use the database administration account appropriate to your installation. A full backup includes current records; the repository's fresh-install SQL contains synthetic data instead.

To verify a backup, create a separate empty database and import the SQL into it. Compare all six tables, record counts, relationships, and indexes. Keep the working database separate from restore tests.

## Testing

### PHP syntax checks

From the project root:

```powershell
Get-ChildItem -Recurse -Filter *.php | ForEach-Object {
    & C:\xampp\php\php.exe -l $_.FullName
}
```

### Isolated database checks

Create a disposable database named `techpulse_rubric_verify` and import `database/techpulse.sql` into it. In a dedicated PowerShell terminal, configure verification credentials and run:

```powershell
$env:DB_NAME = 'techpulse_rubric_verify'
$env:DB_USER = 'YOUR_VERIFICATION_DATABASE_USER'
$env:DB_PASSWORD = 'YOUR_VERIFICATION_DATABASE_PASSWORD'
$env:ADMIN_PASSWORD_HASH = (& C:\xampp\php\php.exe -r "echo password_hash('RubricAdmin!2026', PASSWORD_DEFAULT);")

& C:\xampp\php\php.exe tests/database.php
```

The database test refuses to run against another database name. It covers account CRUD, password handling, input validation, database constraints, session exclusivity, history preservation, and pagination, and removes its synthetic fixtures afterward.

### HTTP workflow checks

In a dedicated terminal, set the same verification environment variables shown above. Also set `ADMIN_USERNAME` to `admin`. Start a loopback-only verification server with a writable session directory outside the project:

```powershell
$env:ADMIN_USERNAME = 'admin'
$sessionPath = Join-Path $env:TEMP 'techpulse-verification-sessions'
New-Item -ItemType Directory -Path $sessionPath -Force | Out-Null

& C:\xampp\php\php.exe -d "session.save_path=$sessionPath" -S 127.0.0.1:8097 -t .
```

From another PowerShell terminal in the project root:

```powershell
& ./tests/http.ps1
```

The HTTP suite targets only `127.0.0.1:8097`. Its test administrator password is `RubricAdmin!2026`, matching the verification-only hash generated above. Use these settings only for the disposable verification environment. Stop the server with **Ctrl+C** and close verification terminals when finished. Apache remains the normal application server; PHP's development server does not enforce `.htaccess` rules.

### Recorded verification

- 74 database/helper checks passed.
- 23 HTTP workflow checks passed.
- Full working-database export restored successfully, with every restored row compared against the source.
- Six pages checked at 44 viewport sizes in light and dark themes: 528 layout checks without horizontal page overflow.

Responsive checks used browser viewports, including mobile and landscape sizes; physical-device/browser-engine testing remains a separate check.

## Contributing

1. Fork the repository and create a focused branch.
2. Make the change and run the relevant checks.
3. Open a pull request describing the behavior, database changes, and validation performed.

Use synthetic fixtures for shared database exports. Do not commit private configuration or personal student records.

## License

This project is developed for educational purposes. No open-source license has been specified in this repository.
