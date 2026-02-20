# NAIROBI CITY COUNCIL SMART WASTE MANAGEMENT AND REPORTING SYSTEM

A web-based waste management system built with PHP, MySQL, Bootstrap 5, and Chart.js.  
Designed for Nairobi City Council to manage waste reports, assign collectors, and track resolution.

---

## 🚀 How to Run on XAMPP / WAMP

### Prerequisites

- **XAMPP** or **WAMP** installed and running (Apache + MySQL)
- Web browser (Chrome, Firefox, Edge)

### Step-by-Step Setup

1. **Copy project folder**  
   Place the `GABU` folder inside your web server root:
   - XAMPP: `C:\xampp\htdocs\GABU\`
   - WAMP: `C:\wamp64\www\GABU\`

2. **Create the database**
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Click **"Import"** tab
   - Select the file `database.sql` from the GABU folder
   - Click **"Go"** to execute

3. **Generate password hashes**
   - Visit: `http://localhost/GABU/setup_passwords.php`
   - Copy the generated SQL UPDATE statements
   - Run them in phpMyAdmin → SQL tab
   - **Delete `setup_passwords.php` after use**

4. **Open the application**
   - Visit: `http://localhost/GABU/`
   - You should see the login page

### Default Login Credentials

| Role      | Email               | Password      |
| --------- | ------------------- | ------------- |
| Admin     | admin@ncc.go.ke     | Admin@123     |
| Collector | collector@ncc.go.ke | Collector@123 |
| Resident  | jane@example.com    | Resident@123  |

---

## 📁 Project Structure

```
GABU/
├── index.php                  # Landing page with login
├── database.sql               # Database schema + seed data
├── setup_passwords.php        # Password hash generator (delete after use)
├── README.md                  # This file
│
├── config/
│   └── database.php           # PDO database connection
│
├── includes/
│   ├── header.php             # Shared header + navigation
│   ├── footer.php             # Shared footer + scripts
│   └── auth_check.php         # Role-based access control
│
├── auth/
│   ├── login.php              # Login handler
│   ├── register.php           # Registration page
│   └── logout.php             # Logout handler
│
├── resident/
│   ├── dashboard.php          # Resident overview
│   ├── submit_report.php      # New waste report form
│   ├── my_reports.php         # View own reports
│   └── feedback.php           # Submit feedback on completed tasks
│
├── admin/
│   ├── dashboard.php          # Admin overview + Chart.js + Image Map
│   ├── reports.php            # Manage all reports
│   ├── assign_task.php        # Assign report to collector
│   ├── manage_users.php       # User management
│   └── archive.php            # Archived reports
│
├── collector/
│   ├── dashboard.php          # View assigned tasks
│   └── update_task.php        # Update task status
│
├── assets/
│   ├── css/
│   │   └── style.css          # Custom stylesheet
│   ├── js/
│   │   └── main.js            # Form validation + interactions
│   └── images/
│       └── nairobi_map.svg    # Nairobi zones map for image map
│
└── uploads/                   # Uploaded report images
    └── index.php              # Directory placeholder
```

---

## 🏗 Architecture

**Three-Tier Architecture:**

| Tier         | Technology                           |
| ------------ | ------------------------------------ |
| Presentation | HTML5, CSS3, Bootstrap 5, JS         |
| Application  | PHP (vanilla, no framework)          |
| Database     | MySQL with PDO + prepared statements |

---

## 🔒 Security Features

- `password_hash()` / `password_verify()` for passwords
- PDO prepared statements (SQL injection prevention)
- Role-based access control via PHP sessions
- Input validation (server-side + client-side)
- File upload type and size validation

---

## 📊 Features

- **Resident**: Submit reports, track status, give feedback
- **Admin**: Dashboard analytics (Chart.js), manage reports/users, assign collectors, archive reports
- **Collector**: View assigned tasks, update status (assigned → in-progress → completed)
- **Image Map**: Clickable Nairobi zone map to filter reports by location
- **CSS Animations**: Fade-in effect on alerts and notifications
- **Responsive**: Bootstrap 5 grid layout on all pages

---

## 🛠 Technologies Used

| Technology | Version | Purpose                 |
| ---------- | ------- | ----------------------- |
| PHP        | 7.4+    | Server-side logic       |
| MySQL      | 5.7+    | Database                |
| Bootstrap  | 5.3     | Responsive UI framework |
| Chart.js   | 4.4     | Analytics charts        |
| JavaScript | ES6     | Client-side validation  |
| HTML5/CSS3 | –       | Structure + styling     |
