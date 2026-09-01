Tech Pulse - Computer Laboratory Monitoring System

A web-based system for monitoring computer laboratory usage — students can register, log in, and (in future updates) view computer availability and session records.

## Setup

1. Import `database/techpulse.sql` into phpMyAdmin.
2. Create a database named `techpulse`.
3. Make sure Apache and MySQL are running in XAMPP.

## Database

The database connection is in:

`config/techpulse.php`

It connects the system to the `techpulse` database.

## Login

After logging in, the system redirects to `dashboard.php`.

The dashboard is not yet developed, so this error may appear:

`Not Found - The requested URL was not found on this server.`

Note: This is because the dashboard is still under development.