# System-Dash — Academic Events Portal

A web-based dashboard for managing academic events, built for both **students** and **managers**. The system provides role-based access, event scheduling, and user management through a clean and straightforward interface.

---

## Tech Stack

| Layer     | Technology                          |
|-----------|-------------------------------------|
| Backend   | PHP 8.1+ with PDO                   |
| Database  | MySQL 5.7+ / MariaDB 10.4+          |
| Frontend  | HTML, CSS, JavaScript (Vanilla)     |
| Server    | Apache with `mod_rewrite`           |

---

## Features

- **Authentication** — Login, registration, and password reset
- **Role-based dashboards** — Separate views for students and managers
- **Event management** — Create, edit, and list academic events
- **Student management** — Manager-facing student records
- **Sitemap generator** — Python script to auto-generate a sitemap

---

## Project Structure

```
system-dash/
├── backend/          # PHP API endpoints & config (.env)
├── database/         # SQL schema and seed data
├── frontend/
│   └── pages/        # dashboard-student.html, dashboard-manager.html
├── docs/             # Site map and report template
├── tests/            # Manual test checklist
├── index.php         # Entry point
├── login.php         # Login page
├── register.php      # Registration page
├── dashboard.php     # Main dashboard
├── events.php        # Events overview
├── add_event.php     # Add new event
├── edit_event.php    # Edit existing event
├── students.php      # Student management
├── profile.php       # User profile
└── system-dash.conf  # Apache virtual host config
```

---

## Setup & Installation

### Prerequisites

- PHP 8.1+
- MySQL 5.7+ or MariaDB 10.4+
- Apache with `mod_rewrite` enabled (XAMPP / WAMP / native Apache)

---

### 1. Clone the Repository

```bash
git clone https://github.com/bahasalah255/system-dash.git
cd system-dash
```

### 2. Set Up the Database

Import the schema (creates the database automatically):

```bash
mysql -u root -p < database/schema.sql
```

Optionally load sample data:

```bash
mysql -u root -p < database/seed.sql
```

**Default accounts after seeding:**

| Role    | Email                  | Password      |
|---------|------------------------|---------------|
| Manager | admin@college.edu      | Admin1234     |
| Student | student@college.edu    | Student1234   |

---

### 3. Configure Environment

```bash
cp backend/.env.example backend/.env
```

Edit `backend/.env` with your database credentials:

```env
DB_HOST=localhost
DB_NAME=academic_events
DB_USER=root
DB_PASS=your_mysql_password_here
```

---

### 4. Configure Apache Virtual Host

Edit `system-dash.conf` and update the paths to match your machine:

```apache
DocumentRoot /path/to/system-dash
<Directory /path/to/system-dash>
```

Enable the site:

```bash
# Linux
sudo cp system-dash.conf /etc/apache2/sites-available/system-dash.conf
sudo a2ensite system-dash
sudo systemctl reload apache2
```

Add the following to your hosts file:
- **Linux/Mac:** `/etc/hosts`
- **Windows:** `C:\Windows\System32\drivers\etc\hosts`

```
127.0.0.1   system-dash.local
```

---

### 5. Open in Browser

```
http://system-dash.local/login.php
```

---

## Testing

Manual test cases are available in the `tests/` directory. Review the checklist before submitting changes.

---

## License

This project was developed as part of an academic assignment. All rights reserved.
