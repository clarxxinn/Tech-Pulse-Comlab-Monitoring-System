🖥️ Tech Pulse – Computer Laboratory Monitoring System

A modern, web-based Computer Laboratory Monitoring System built with PHP, MySQL, HTML, CSS, and JavaScript. Designed to simplify computer laboratory management, Tech Pulse allows students to monitor PC availability, record computer usage through Time In and Time Out, and view session history — while administrators can manage student accounts, monitor laboratory activity, and control computer maintenance status.

📖 About the Project

Tech Pulse is a web-based monitoring system developed to provide an organized and efficient way of managing computer laboratory usage in an educational environment.

The system supports 3 computer laboratories with 45 computers each, providing centralized monitoring for a total of 135 computers.

Rather than relying on manual attendance logs or computer usage records, Tech Pulse uses a MySQL database to track student sessions, computer availability, and laboratory activity.

The system features two primary user roles:

🎓 Students — can register, log in, select a computer laboratory, view PC availability, record Time In and Time Out, and review their computer usage history.

🛡️ Administrators — can monitor computer availability across all laboratories, manage student accounts, view active sessions, and update computer maintenance status.

Tech Pulse also features a responsive interface with light and dark themes, making the system accessible across desktop and mobile browsers.

✨ Features

🎓 Student Features

🔐 Student Registration & Login — secure account creation and authentication.

🏠 Student Dashboard — centralized interface for accessing computer laboratory information.

🏫 Laboratory Selection — switch between ComLab 1, ComLab 2, and ComLab 3.

🖥️ Computer Availability — view the current status of computers in the selected laboratory.

🟢 Available Computers — identify computers ready for student use.

🔴 Occupied Computers — identify computers currently assigned to active student sessions.

🟡 Maintenance Computers — identify computers temporarily unavailable for use.

⏱️ Time In — start a computer usage session.

⏹️ Time Out — end an active computer usage session.

📜 Session History — review previous computer usage records.

👤 Student Profile — access account information.

🚪 Logout — securely end the current session.

🛡️ Administrator Features

🔑 Admin Authentication — restricted access to administrator functions.

📊 Admin Dashboard — overview of student accounts and computer laboratory activity.

🖥️ Computer Monitoring — monitor all 135 computers across three laboratories.

🏫 Laboratory Selector — display the 45 computers belonging to a selected laboratory.

🟢 Available Status — identify computers ready for use.

🔴 Occupied Status — identify computers currently in use.

🟡 Maintenance Status — identify computers unavailable due to maintenance.

🛠️ Maintenance Management — mark available computers as under maintenance and restore them when ready.

👨‍🎓 Student Identification — view the student assigned to an occupied computer.

⏳ Active Sessions — view ongoing student computer usage.

📋 Session History — review recent Time In and Time Out records.

👥 Account Management — create, edit, and delete student accounts.

📈 Monitoring Statistics — view total, available, occupied, and maintenance computer counts.

🎨 Interface & User Experience

🌗 Light / Dark Theme — switch between two visual themes.

📱 Responsive Design — layouts adapt to different screen sizes.

🎯 Color-Coded PC Status — distinguish computer availability through visual indicators.

🧩 Card-Based Interface — organized dashboards and monitoring panels.

⚡ Interactive Laboratory Selection — display computers from a selected laboratory without navigating to another page.

🏫 Computer Laboratory Configuration

Tech Pulse manages three computer laboratories containing a total of 135 computers.

Laboratory

Computers

PC Numbers

🏫 ComLab 1

45

PC 1–45

🏫 ComLab 2

45

PC 1–45

🏫 ComLab 3

45

PC 1–45

Total

135



🚦 Computer Status Indicators

Status

Color

Description

🟢 Available

Green

Computer is available for student use

🔴 Occupied

Red

Computer has an active student session

🟡 Maintenance

Yellow

Computer is temporarily unavailable

Computer statuses and session information are stored in the database. Dashboard monitoring information is updated when the page is refreshed.

🛠️ Tech Stack

Category

Technology

💻 Backend Language

PHP

🎨 Frontend

HTML5, CSS3, JavaScript

🗄️ Database

MySQL

🔌 Database Connection

MySQLi

🖥️ Local Server

Apache (XAMPP)

🛢️ Database Management

phpMyAdmin

🔧 Development Tools

Visual Studio Code

📦 Version Control

Git, GitHub

🏗️ How It Works (Architecture)

Tech Pulse uses a PHP-based web application architecture, with separate modules for authentication, student operations, administrator operations, and database access.

📂 Project Structure

Tech-Pulse-Comlab-Monitoring-System/
│
├── admindashboard/
│   └── Administrator dashboard and account management
│
├── assets/
│   └── Shared styles, scripts, and other assets
│
├── auth/
│   └── Registration and login modules
│
├── config/
│   ├── techpulse.php
│   ├── monitoring.php
│   └── Other configuration files
│
├── dashboard/
│   └── Student dashboard and session management
│
├── database/
│   └── techpulse.sql
│
├── AGENTS.md
├── README.md
├── index.php
└── reset_password.php

🧠 System Components

🔐 Authentication Module

Handles student registration, login, logout, and session-based access control. Administrator-only pages require the appropriate authorization.

🎓 Student Dashboard

Allows students to select a laboratory, inspect computer availability, start or end computer sessions, and review their usage history.

🛡️ Administrator Dashboard

Provides account management, computer monitoring, maintenance controls, active session information, and recent session history.

🗄️ Database Module

Stores student accounts, laboratory information, computer records, and computer usage sessions.

🗃️ Database Structure

The system uses four main database tables:

Table

Purpose

users

Stores student account information

laboratories

Stores laboratory records

computers

Stores computer information and availability status

computer_sessions

Records student Time In and Time Out sessions

🔄 Computer Session Workflow

Student Login
      │
      ▼
Student Dashboard
      │
      ▼
Select Computer Laboratory
      │
      ▼
View Available Computers
      │
      ▼
Select Available PC
      │
      ▼
     Time In
      │
      ▼
Computer Becomes Occupied
      │
      ▼
  Active Session
      │
      ▼
    Time Out
      │
      ▼
Session Saved in History
      │
      ▼
Computer Becomes Available

🚀 Installation

Follow these steps to run Tech Pulse on your local computer.

1. Clone the Repository

git clone https://github.com/clarxxinn/Tech-Pulse-Comlab-Monitoring-System.git

Alternatively, download the repository as a ZIP file from GitHub.

2. Move the Project to XAMPP

Place the project folder inside the XAMPP htdocs directory:

C:\xampp\htdocs\Tech-Pulse-Comlab-Monitoring-System\

3. Start Apache and MySQL

Open the XAMPP Control Panel and start:

Apache

MySQL

4. Create the Database

Open phpMyAdmin:

http://localhost/phpmyadmin/

Create a database named:

CREATE DATABASE techpulse;

Select the database, open the Import tab, and import:

database/techpulse.sql

Ensure the SQL file contains the latest database schema and required initial laboratory and computer records.

5. Configure the Database Connection

Open:

config/techpulse.php

Configure the database connection according to your local MySQL settings.

Typical XAMPP configuration:

Setting

Value

Host

localhost

Username

root

Password

Empty by default

Database

techpulse

6. Launch the System

Open your browser and visit:

http://localhost/Tech-Pulse-Comlab-Monitoring-System/

You can now access the system using an existing account or register as a student.

Administrator access requires an account with administrator privileges.

📱 Usage

🎓 For Students

Open Tech Pulse in your browser.

Register or log in using your student account.

Navigate to the Student Dashboard.

Select ComLab 1, ComLab 2, or ComLab 3.

View the available computers in the selected laboratory.

Select an available PC and click Time In.

When finished, click Time Out.

Review your previous computer usage in Session History.

🛡️ For Administrators

Log in using an administrator account.

Open the Admin Dashboard.

View the computer monitoring statistics.

Select a computer laboratory to display its 45 PCs.

Monitor available, occupied, and maintenance computers.

View student information associated with occupied computers.

Set available computers to Maintenance when necessary.

Restore computers to Available after maintenance.

Review active sessions and recent session history.

Create, edit, or delete student accounts as needed.

🔒 Security

Tech Pulse incorporates several security practices:

🔐 Password Hashing — protects stored account passwords.

🛡️ Session-Based Authentication — restricts access to authenticated users.

👮 Role-Based Access — separates student and administrator functions.

🧾 Input Validation — checks submitted data before processing.

🔑 CSRF Protection — protects supported administrative form actions.

🗄️ Prepared SQL Statements — help protect supported database operations from SQL injection.

🚫 Session Conflict Prevention — prevents conflicting computer usage sessions.

The system is intended for local academic use. Additional security review and deployment configuration are recommended before making it publicly accessible.

🧪 Testing

The following functionality should be verified during final testing:

Student registration and login

Administrator login and access restrictions

Student profile and logout

Laboratory selection

Display of 45 PCs per laboratory

Student Time In and Time Out

Prevention of duplicate active sessions

Prevention of Time In on occupied computers

Prevention of Time In on maintenance computers

Administrator computer monitoring

Maintenance status updates

Active session tracking

Session history recording

Account creation, editing, and deletion

Light and dark theme switching

Responsive layouts

Fresh database import and installation

📌 Development Status

Core system features have been implemented.

Tech Pulse currently supports student computer usage tracking, administrator laboratory monitoring, maintenance management, and account administration.

The system uses page refreshes to retrieve updated monitoring information. Automatic live updates are not included in the current implementation.

Final testing, database export verification, and project documentation review remain recommended before submission.

🤝 How to Contribute

Contributions and suggestions are welcome!

Fork the repository.

Create a new branch for your changes.

Make your improvements and commit them.

Push your changes to your fork.

Submit a pull request describing your changes.

👨‍💻 Developer

GitHub: @clarxxinn

Repository: Tech-Pulse-Comlab-Monitoring-System

📄 License

This project was developed for educational and academic purposes.

No separate open-source license has been specified. Reuse and redistribution are subject to the repository owner's permission.

🖥️ Tech Pulse – Computer Laboratory Monitoring System

Making computer laboratory monitoring smarter, simpler, and more organized.