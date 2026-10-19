Tech Pulse – Computer Laboratory Monitoring System

A modern, web-based computer laboratory monitoring system built with PHP, MySQL, HTML, CSS, and JavaScript. Designed to streamline laboratory management, it features student Time In/Time Out tracking, computer availability monitoring, account management, and responsive dashboards with light and dark themes.

📖 About the Project

Tech Pulse is a web-based system designed to simplify computer laboratory monitoring and student computer usage management. It replaces manual usage records with a database-driven system that tracks computer availability, active sessions, and student activity.

The system manages 3 computer laboratories with 45 PCs each (135 computers total) and provides separate dashboards for students and administrators.

🖥️ Computer monitoring displays available, occupied, and maintenance computers across all laboratories.

⏱️ Session tracking records student Time In and Time Out, including computer assignments and usage history.

🛡️ Administrator controls provide account management, maintenance management, and laboratory activity monitoring.

✨ Features

Student Dashboard

🏫 Laboratory selection: switch between ComLab 1, 2, and 3, each containing 45 computers.

🖥️ Computer availability: view available, occupied, and maintenance PCs through color-coded indicators.

⏱️ Time In / Time Out: start and end computer usage sessions.

📜 Session history: view previous computer usage records.

👤 Account management: registration, login, profile, and logout.

Administrator Dashboard

📊 Monitoring statistics: view total, available, occupied, and maintenance computer counts.

🖥️ Laboratory monitoring: monitor all 135 computers across three laboratories.

🛠️ Maintenance controls: mark computers as under maintenance or restore their availability.

👥 Student management: create, edit, and delete student accounts.

📋 Session records: view active sessions, student information, and recent usage history.

Interface & Security

🌗 Light / Dark theme: switch between light and dark interfaces.

📱 Responsive design: adaptable layouts for desktop and mobile browsers.

🔐 Authentication: session-based login with administrator access restrictions.

🛡️ Data protection: password hashing, input validation, and protected database operations.

🛠️ Tech Stack

Category

Technology

💻 Backend

PHP

🎨 Frontend

HTML5, CSS3, JavaScript

🗄️ Database

MySQL

🔌 Database Access

MySQLi

🖥️ Local Server

Apache, XAMPP

🛢️ Database Tool

phpMyAdmin

🔧 Version Control

Git, GitHub

🏗️ How It Works (Architecture)

Tech-Pulse-Comlab-Monitoring-System/
├── admindashboard/     # Admin monitoring and account management
├── assets/             # Shared assets
├── auth/               # Login and registration
├── config/             # Database connection and monitoring logic
├── dashboard/          # Student dashboard and computer sessions
├── database/           # MySQL database export
├── index.php           # Application entry point
└── reset_password.php  # Password reset functionality

The system uses three main components:

🧠 Student Module — handles authentication, laboratory selection, computer assignments, Time In/Time Out, and session history.

🛡️ Administrator Module — manages student accounts, monitors computer statuses, and controls maintenance availability.

🗄️ Database Module — stores user information, laboratory records, computer statuses, and session history using MySQL.

Computer availability is retrieved from the database when the dashboard loads or refreshes.

🚀 Installation

To run Tech Pulse locally, follow these steps:

Clone the repository:

git clone https://github.com/clarxxinn/Tech-Pulse-Comlab-Monitoring-System.git

Move the project folder into C:\xampp\htdocs\.

Start Apache and MySQL using the XAMPP Control Panel.

Open phpMyAdmin, create a database named techpulse, and import database/techpulse.sql.

Configure the database connection in config/techpulse.php.

Open the system in your browser:

http://localhost/Tech-Pulse-Comlab-Monitoring-System/

📱 Usage

Students can register or log in, select a computer laboratory, and view PC availability.

Select an available PC and click Time In to begin a session.

Click Time Out when finished. The session is saved in the usage history.

Administrators can log in to access the monitoring dashboard.

Select a laboratory to monitor its computers, manage maintenance status, and view active sessions.

Administrators can also create, edit, and delete student accounts.

🤝 How to Contribute

Contributions are welcome! To contribute:

Fork the repository.

Create a new branch for your changes.

Make your changes and commit them.

Push your branch to your forked repository.

Submit a pull request with a description of your changes.

📄 License

This project is developed for educational purposes. No open-source license has been specified.