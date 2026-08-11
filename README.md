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

---

## 📂 Project Directory Basic Structure

```text
FitCampus-Web-Application/
│
├── index.php                     # Public Landing Page View
│
├── assets/                       # Static Frontend Resources
│   ├── css/                      # Modular Custom CSS (main, glassmorphism, components)
│   └── js/                       # Vanilla JS Scripts (kiosk, booking, analytics, main)
│
├── includes/                     # Server-side Reusable Partials
│   ├── headers/                  # Auth, Student, Staff, and Admin Topbars
│   ├── sidebars/                 # Dynamic Role-based Sidebars
│   ├── footers/                  # Universal Global Footer
│   └── db_connection.php         # Database Connection Script
│
├── views/                        # Core Page Views
│   ├── auth/                     # Login & Step-by-step Registration Views
│   ├── student/                  # Student Member Views (Dashboard, Calories, Goals, etc.)
│   ├── captain/                  # Team Captain Specialization Views (Booking, Roster, Planner)
│   ├── instructor/               # Staff Console Views (Kiosk, Updates, Inventory, Rules)
│   └── admin/                    # Executive Admin Views (Analytics, Approvals, Users, Verification)
│
├── sql/
│   └── fitcampus_db.sql          # Master Database Creation Script
│
├── README.md                     # Project Documentation
└── .gitignore                    # Local Environment Ignore File