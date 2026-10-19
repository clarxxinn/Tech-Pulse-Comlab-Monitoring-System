# Tech Pulse – Computer Laboratory Monitoring System

A web-based Computer Laboratory Monitoring System designed to manage and monitor computer usage, student sessions, and laboratory availability.

Tech Pulse provides separate dashboards for students and administrators, allowing students to record their computer usage while administrators monitor laboratory activity and manage user accounts.

## Project Overview

Tech Pulse is developed using PHP, MySQL, HTML, CSS, and JavaScript. The system runs locally using XAMPP.

The system supports three computer laboratories, each containing 45 computers, for a total of 135 computers.

## Features

### Student Features
- Student registration and login
- Student dashboard
- Computer laboratory selection
- View computer availability
- Computer Time In and Time Out
- View personal computer usage history
- Student profile
- Logout

### Administrator Features
- Administrator authentication
- Admin dashboard
- Student account management
- Create, edit, and delete student accounts
- Monitor all three computer laboratories
- View computer availability and occupancy
- View student information for occupied computers
- Set computers to Maintenance or Available
- View active computer sessions
- View recent session history
- Dashboard statistics

### Additional Features
- Light and dark theme
- Responsive user interface
- Session-based authentication
- Password hashing
- Server-side validation
- Database-driven computer monitoring

## Computer Laboratory Configuration

| Laboratory | Computers |
|---|---:|
| ComLab 1 | 45 |
| ComLab 2 | 45 |
| ComLab 3 | 45 |
| **Total** | **135** |

### Computer Status

- **Available (Green):** Computer is available for student use.
- **Occupied (Red):** Computer is currently being used by a student.
- **Maintenance (Yellow):** Computer is temporarily unavailable.

Monitoring information is updated when the dashboard is refreshed. Automatic live updates are not currently enabled.

## Technologies Used

| Technology | Purpose |
|---|---|
| PHP | Backend development |
| MySQL | Database management |
| HTML5 | Webpage structure |
| CSS3 | Styling and responsive design |
| JavaScript | Client-side interactions |
| XAMPP | Local development server |
| phpMyAdmin | Database administration |
| Git & GitHub | Version control |

## Project Structure

```text
Tech-Pulse-Comlab-Monitoring-System/
│
├── admindashboard/
│   └── Administrator dashboard files
│
├── assets/
│   └── Shared assets
│
├── auth/
│   └── Authentication files
│
├── config/
│   └── Database configuration and utilities
│
├── dashboard/
│   └── Student dashboard files
│
├── database/
│   └── techpulse.sql
│
├── AGENTS.md
├── README.txt
├── index.php
└── reset_password.php
```

## Installation and Setup

### Requirements

- XAMPP with Apache and MySQL
- PHP with MySQLi support
- Modern web browser
- Git (optional)

### Step 1: Download the Project

Clone the repository:

```bash
git clone https://github.com/clarxxinn/Tech-Pulse-Comlab-Monitoring-System.git
```

Alternatively, download the repository as a ZIP file and extract it.

### Step 2: Move to XAMPP

Place the project folder inside:

```text
C:\xampp\htdocs\
```

### Step 3: Start XAMPP

Open the XAMPP Control Panel and start:

- Apache
- MySQL

### Step 4: Set Up the Database

1. Open `http://localhost/phpmyadmin/`.
2. Create a database named `techpulse`.
3. Select the database.
4. Open the Import tab.
5. Import `database/techpulse.sql`.
6. Verify that the required tables and initial computer records exist.

The system uses these main tables:

- `users`
- `laboratories`
- `computers`
- `computer_sessions`

**Important:** The SQL file must contain the current database schema and required initial records. If the repository contains an older database export, update it before installing on another computer.

### Step 5: Configure Database Connection

Open:

```text
config/techpulse.php
```

Ensure that the database configuration matches your local MySQL installation.

Typical XAMPP settings:

```text
Host: localhost
Username: root
Password: (empty by default)
Database: techpulse
```

Do not publish production database credentials or private passwords.

### Step 6: Open the System

Visit:

```text
http://localhost/Tech-Pulse-Comlab-Monitoring-System/
```

Register or log in using an existing student account. Administrator access requires an account configured with the appropriate administrator privileges.

## How to Use

### For Students

1. Log in to the system.
2. Open the Student Dashboard.
3. Select ComLab 1, ComLab 2, or ComLab 3.