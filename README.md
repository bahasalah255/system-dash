# Academic Events Portal

Student and manager dashboard built with PHP (PDO) + MySQL + vanilla HTML/CSS/JS.

---

## Requirements

- PHP 8.1+
- MySQL 5.7+ or MariaDB 10.4+
- Apache with `mod_rewrite` enabled (XAMPP / WAMP / native Apache)

---

## Setup on a new machine

### 1. Database

Import the schema (creates the database automatically):

```bash
mysql -u root -p < database/schema.sql
```

Optionally load sample data:

```bash
mysql -u root -p < database/seed.sql
```

Default accounts after seeding:

| Role    | Email                | Password     |
|---------|----------------------|--------------|
| Manager | admin@college.edu    | Admin1234    |
| Student | student@college.edu  | Student1234  |

---

### 2. Environment

```bash
cp backend/.env.example backend/.env
```

Open `backend/.env` and set your MySQL password:

```
DB_HOST=localhost
DB_NAME=academic_events
DB_USER=root
DB_PASS=your_mysql_password_here
```

---

### 3. Apache virtual host

Edit `system-dash.conf` — change the two paths to match your machine:

```apache
DocumentRoot /path/to/system-dash
<Directory /path/to/system-dash>
```

Then enable it:

```bash
# Linux
sudo cp system-dash.conf /etc/apache2/sites-available/system-dash.conf
sudo a2ensite system-dash
sudo systemctl reload apache2
```

Add to your hosts file (`/etc/hosts` on Linux/Mac, `C:\Windows\System32\drivers\etc\hosts` on Windows):

```
127.0.0.1   system-dash.local
```

---

### 4. Open in browser

```
http://system-dash.local/frontend/pages/login.html
```

---

## Project layout

```
backend/        PHP API endpoints + config
database/       SQL schema and seed data
frontend/       HTML pages, CSS, JS
  pages/        dashboard-student.html, dashboard-manager.html, login.html ...
  css/          styles.css
  js/           page scripts
docs/           site map and report template
tests/          manual test checklist
```
