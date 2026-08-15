# 🏋️‍♂️ FitCampus - Centralized Gym Management System
> **University of Colombo - Physical Education Department & Sports Council Portal**

FitCampus is an all-in-one web-based fitness and facility management platform designed specifically for the students, varsity athletes, team captains, gym instructors, and physical department administrators at the University of Colombo.

---

## 🌟 Key Features & Modules

### 1. 🔑 Authentication & Onboarding
* Multi-step registration for Students and Faculty with Academic Credentials verification (UCSC, Science, Arts, Law, Medicine).
* Identity verification with Student ID Card uploads (Front/Back) and NIC validation.
* Institutional Email Authentication & Password Security.

### 2. 🎓 Student & Gym Member Console
* **Live Occupancy Monitor:** Real-time headcount and gender ratio tracking for Gym 01 (Main) & Gym 02 (Cardio).
* **Nutrition & Calorie Tracker:** Daily food log intake vs activity calories burned calculator with interactive history calendar.
* **Custom Workout Builder:** Build personal exercise routines or access verified Instructor-approved plans.
* **Goals Manager:** Progress tracking for Weight Loss, Strength, and Endurance metrics.
* **Gamified Leaderboard:** Faculty rankings, monthly/all-time leaderboards, achievement badges, and podium standings.
* **Instructor Motivation Feed & Alerts:** Live motivational updates and facility capacity notifications.

### 3. 🏐 Team Captain Specialization Console
*(Integrated seamlessly into the Student Interface for promoted Captains)*
* **Interactive Facility Booking:** Weekly availability grid with time-slot selection, capacity limit checks, and special request handling.
* **Team Roster Management:** Roster tracking by Student ID and attendance logging (Present/Absent).
* **Team Workout Planner:** Schedule, draft, and publish team-specific training routines directly to player feeds.

### 4. 🎛️ Gym Instructor Console
* **Front-Desk Attendance Terminal / QR Kiosk:** Instant QR code scanner check-in/out and live Life Bar deduction.
* **Staff Updates Console:** Motivational broadcast composer and emergency system alerts center.
* **Equipment Maintenance Console:** Inventory asset tracking, condition updates (Available, Maintenance, Damaged).
* **Common Workouts Manager:** Multi-tier routine builder (Beginner, Intermediate, Advanced).
* **Facility Rules Manager:** Severity-based rules editor and penalty guidelines manager.

### 5. 👑 Executive Administrator Console
* **Executive Analytics Dashboard:** KPI stats, peak usage tracking, 7-day facility utilization bar charts, and system audit logs.
* **Booking Approvals & Conflict Manager:** Slot overlap resolution, limit overrides, and displaced team notifications.
* **User Verification Console:** Review student registration submissions and ID card verification.
* **User Management & Captain Promotion Panel:** Role filtering, user suspension, manual instructor creation, and promoting students to Captains.
* **Broadcast Alerts Center:** Push notifications to staff and student mobile devices.

---

## 🏗️ Technical Architecture & Tech Stack

* **Architecture Pattern:** 3-Tier Modular Monolith Architecture
* **Frontend:** HTML5, CSS3 (Modern Dark Theme, Glassmorphism), Vanilla JavaScript
* **Backend:** Native PHP 8.x (No external heavy frameworks)
* **Database:** MySQL Relational Database (Prepared Statements / PDO)
* **UI Design Standard:** Hanken Grotesk & JetBrains Mono Typography, Custom Design Token System


## 📂 Project Directory Basic Structure

```text
FitCampus-Web-Application/
│
├── index.php                         # Public Landing Page & Route Dispatcher
│
├── assets/                           # Pure Static Frontend Assets
│   ├── css/                          # Modular Vanilla CSS
│   │   ├── base/                     # Shared Design Tokens & Global Styles
│   │   │   ├── main.css              # Reset, typography, and :root color variables
│   │   │   ├── components.css        # Common UI components (buttons, inputs, cards)
│   │   │   └── glassmorphism.css     # Glass effects, gradients, and keyframe animations
│   │   │
│   │   └── roles/                    # Actor-specific Stylesheets
│   │       ├── auth.css              # Login & Multi-step registration forms
│   │       ├── member.css            # Member dashboard, workouts, calorie tracking
│   │       ├── captain.css           # Slot bookings, roster grids, session planner
│   │       ├── instructor.css        # Kiosk check-in, inventory, workout builder
│   │       └── admin.css             # Verification tables, approvals, analytics
│   │
│   ├── js/                           # Pure Vanilla JavaScript Handlers
│   │   ├── auth/                     # Auth Flow Scripts (auth.js, register-wizard.js)
│   │   ├── member/                   # Member Scripts (calorie-calculator.js, workout-timer.js)
│   │   ├── captain/                  # Team & Booking Scripts (slot-booking.js, roster-filter.js)
│   │   ├── instructor/               # Staff Management Scripts (kiosk-scanner.js, inventory-manager.js)
│   │   ├── admin/                    # Admin Dashboard Scripts (user-approval.js, analytics-charts.js)
│   │   └── main.js                   # Universal DOM utilities, toast notifications & sidebar toggles
│   │
│   └── images/                       # Static UI Visuals & Crests (uoc-logo.png, etc.)
│
├── includes/                         # Reusable PHP Partials & Database Drivers
│   ├── headers/                      # Role-specific HTML Heads (header_auth, header_member, etc.)
│   ├── sidebars/                     # Dynamic Navigation Sidebars (sidebar_member.php, etc.)
│   ├── footers/                      # Common Footers & Script Injections
│   └── db_connection.php             # PDO-based MySQL Database Connection
│
├── backend/                          # Server-side Request Handlers & Business Logic
│   ├── auth/                         # Authentication Logic (login_process, register_process, logout)
│   ├── member/                       # Member Operations (log_calories, update_goals)
│   ├── captain/                      # Team Operations (book_slot, update_roster)
│   ├── instructor/                   # Staff Operations (add_inventory, create_workout)
│   └── admin/                        # Administrative Operations (approve_user, update_role)
│
├── views/                            # Frontend View Templates (Semantic HTML)
│   ├── auth/                         # Login & Registration Wizard Views
│   ├── member/                       # General Gym Member Views
│   ├── captain/                      # Team Captain Console Views
│   ├── instructor/                   # Instructor Console Views
│   └── admin/                        # Executive Admin Dashboard Views
│
├── sql/
│   └── fitcampus_db.sql              # Database Schema & Seed Data Script
│
├── README.md                         # Project Documentation
└── .gitignore                        # Git Exclusion Rules
```

```markdown
## ⚙️ Local Development Setup Guide

### 1. Prerequisites
* **Local Web Server Stack:** XAMPP, WAMP, or LAMP with PHP 8.x and MySQL / MariaDB installed.
* **Version Control:** Git.
* **Modern Web Browser:** Chrome, Edge, or Firefox.

---

### 2. Step-by-Step Installation

1. **Clone the Repository:**
   Clone the repository directly inside your local web server root directory (`C:/xampp/htdocs/` for XAMPP):
   ```bash
   cd C:/xampp/htdocs/
   git clone [https://github.com/ravindu-09/FitCampus-Web-Application.git](https://github.com/username/FitCampus-Web-Application.git)


2. **Start Services:**
* Open the XAMPP Control Panel.
* Start both the **Apache** and **MySQL** modules.


3. **Setup Database:**
* Open your browser and navigate to `http://localhost/phpmyadmin`.
* Create a new database named **`fitcampus_db`** with Collation `utf8mb4_general_ci`.
* Click on the newly created database, go to the **Import** tab, choose `sql/fitcampus_db.sql` from the project directory, and click **Import**.


4. **Verify Database Configuration:**
* Ensure `includes/db_connection.php` matches your local server credentials:
```php
$host = 'localhost';
$db   = 'fitcampus_db';
$user = 'root';
$pass = ''; // Default XAMPP password is blank


5. **Launch Application:**
* Open your browser and access the application at:
```text
http://localhost/FitCampus-Web-Application/

```

* Access the authentication interface directly via:
```text
http://localhost/FitCampus-Web-Application/views/auth/login.php

```

---

## 📐 Developer Team Guidelines & Coding Rules

All team members must strictly follow these rules to keep the codebase maintainable, secure, and conflict-free:

### 1. Zero External Framework Policy

* **No CSS/JS Frameworks:** Do not install or link Tailwind CDN, Bootstrap, jQuery, React, or any other UI/JS libraries.
* All interfaces must be built purely with **Semantic HTML5, Vanilla CSS3, and Native Vanilla JavaScript (ES6+)**.

### 2. Strict Separation of Concerns

* **No SQL / Business Logic in Views:** The `views/` directory is strictly for layout rendering. Never write raw database queries or direct business processing in view files.
* **Form Action Endpoints:** All form submissions and asynchronous operations must point to their dedicated controllers inside the `backend/` directory.

### 3. Modular Asset Management

* **Shared Styles:** Place global variables, typography, and reusable buttons/inputs in `assets/css/base/`.
* **Role-Specific Isolation:** Always place actor-specific stylesheets (`student.css`, `admin.css`, etc.) and scripts (`student/`, `admin/`, etc.) in their respective role folders. Never edit another role's stylesheet for local adjustments.

### 4. Database Security & Standards

* **Prepared Statements Only:** Always use PDO prepared statements (`$stmt->prepare()` and `$stmt->execute()`) for dynamic queries. Never concatenate variables directly into SQL strings.
* **Secure Authentication:** Passwords must be hashed using `password_hash($pass, PASSWORD_BCRYPT)` and validated strictly with `password_verify()`.

### 5. Git Branching & Commit Workflow

* **Do Not Push Directly to `main` or `dev`:** Always create a feature branch for your assigned task:
```bash
git checkout -b feature/<role>-<feature-name>
# Example: feature/student-calorie-tracker

```


* **Conventional Commits:** Write clean, descriptive commit messages:
```bash
git commit -m "feat(student): implement calorie calculation form and controller"

```


* **Pull Requests:** Push your feature branch and open a Pull Request against `dev` for review before merging.
