# College Complaint Management System

**Version:** 2.0  
**Project Type:** Academic Web Development Project (BCA Level)  
**Architecture:** PHP-Based Client-Server Application (Core PHP 8.3, MySQL 8.0, Apache)  
**Deployment:** Docker & Docker Compose  
**Design Standards:** Crafted with institutional clarity, 4px spacing scale, and zero frontend bloat (`unslop` craft principles)

---

## 1. Project Overview

The **College Complaint Management System** is a secure, web-based platform developed to streamline the submission, management, and tracking of student grievances within a college campus.

Traditional grievance systems rely on paper applications and verbal reporting, resulting in lost complaints, lack of accountability, and an inability for students to track resolution progress. This system establishes a centralized digital ledger where:
- **Students** can register, log in, submit complaints across 7 institutional categories, attach supporting photographs, and track progress through live status badges.
- **Administrators** can review submissions, inspect attached evidence, filter and search grievance records, update complaint resolution states (`Pending` &rarr; `In Progress` &rarr; `Resolved`), and view student rosters.

---

## 2. Technology Stack & Strict Architectural Rules

| Component | Technology |
|---|---|
| Frontend | HTML5 (Semantic, Accessible) |
| Styling | Pure CSS3 (4px grid, Ink neutral ramp, fully responsive) |
| Server-Side Language | Core PHP 8.3 (Object-Oriented PDO database layer) |
| Database | MySQL 8.0 (InnoDB, Foreign Key Constraints) |
| Web Server | Apache 2.4 (with `mod_rewrite`) |
| Containerization | Docker & Docker Compose |

### Technology Restrictions Strictly Enforced:
- **Zero JavaScript:** No client-side JS, jQuery, AJAX, React, Vue, or Bootstrap JS.
- All interactions utilize standard HTML5 `<form>` submissions with HTTP POST/GET requests and server-side page reloads.
- State and feedback messages are safely preserved across requests using PHP Sessions (`$_SESSION`).

---

## 3. Key Features

### For Students
- **Account Registration & Login:** Password hashing with `password_hash()` (Bcrypt).
- **Complaint Submission:** Category selector, description textarea, and optional image upload (JPG, JPEG, PNG up to 2 MB).
- **Sequential Tracking ID:** Every complaint receives a unique institutional tracking number (e.g., `CMP-2026-0001`).
- **Student Dashboard:** Real-time metrics counting Total, Pending, In Progress, and Resolved complaints.
- **My Complaints Ledger:** Search and filter personal grievance history.
- **Access Control:** Students cannot view or tamper with complaints submitted by other students.

### For Administrators
- **Dedicated Admin Login:** Isolated administrative authentication.
- **Admin Dashboard:** High-level institutional metrics (Total Students, Total Grievances, Pending, In Progress, Resolved).
- **Complaint Management:** Search by tracking ID, student name, or title; filter by category and status.
- **Status Progression:** Update status from `Pending` &rarr; `In Progress` &rarr; `Resolved`.
- **Student Directory:** Read-only directory of all registered students and their complaint counts.

---

## 4. Default Login Credentials

| Role | Identifier / Email | Password | Notes |
|---|---|---|---|
| **Administrator** | `admin` | `admin123` | Seeded automatically by `database/grievance_system.sql` |
| **Student (Demo)** | Register via `/student/register.php` | Chosen during signup | Any valid student account |

---

## 5. Directory Structure

```text
college-grievance-system/
├── Dockerfile                  # PHP 8.3 + Apache + PDO MySQL container configuration
├── docker-compose.yml          # Multi-container orchestration (App + MySQL 8.0)
├── .env                        # Active environment variables (DB host, user, port)
├── .env.example                # Template for environment configuration
├── .gitignore                  # Git ignore rules
├── README.md                   # Complete documentation
│
├── config/
│   └── database.php            # Secure PDO connection helper with env fallback
│
├── database/
│   └── grievance_system.sql    # Schema, foreign keys, and default admin seed
│
├── includes/
│   ├── auth.php                # Role verification & session route guards
│   ├── functions.php           # CSRF tokens, image upload validator, tracking ID generator
│   ├── header.php              # Shared institutional navigation header
│   └── footer.php              # Shared footer
│
└── public/
    ├── index.php               # Public homepage & system workflow overview
    ├── assets/
    │   └── css/
    │       └── style.css       # Custom unslop CSS3 stylesheet (4px grid, responsive)
    ├── student/
    │   ├── register.php        # Student account creation
    │   ├── login.php           # Student login
    │   ├── logout.php          # Student logout
    │   ├── dashboard.php       # Student metrics & recent submissions
    │   ├── submit-complaint.php# Complaint submission with file upload
    │   ├── my-complaints.php   # Personal complaints list & filtering
    │   └── complaint-details.php# Detail view of single grievance
    ├── admin/
    │   ├── login.php           # Admin login
    │   ├── logout.php          # Admin logout
    │   ├── dashboard.php       # Administrative metrics & recent feed
    │   ├── complaints.php      # Search, filter, and review all complaints
    │   ├── complaint-details.php# Status update management & evidence review
    │   └── students.php        # Registered student directory (read-only)
    └── uploads/                # Uploaded complaint photographs (persisted via Docker volume)
        └── .gitkeep
```

---

## 6. Execution with Docker Compose

### Prerequisites
- Docker Engine installed.
- Docker Compose installed.

### 1. Start the System
From the project root directory, run:
```bash
docker compose up -d --build
```

### 2. Access the Application
Open your web browser and navigate to:
```text
http://localhost:8080
```

### 3. Check Container Status
```bash
docker compose ps
```

### 4. View Container Logs
```bash
docker compose logs -f
```

### 5. Stop the System
```bash
docker compose down
```
*(Note: Uploaded images and database records persist across restarts in `uploads_data` and `db_data` volumes).*

---

## 7. Security Implementations

1. **Prepared SQL Statements:** All queries utilize `PDO::prepare()` and parameterized inputs, mitigating SQL injection risks.
2. **CSRF Defense:** State-changing POST forms incorporate unique session-validated CSRF tokens.
3. **Password Security:** Student and administrator passwords are encrypted via PHP's `password_hash(..., PASSWORD_BCRYPT)`.
4. **File Upload Hardening:**
   - Whitelisted extensions (`.jpg`, `.jpeg`, `.png`).
   - Binary MIME inspection using `finfo` / `mime_content_type`.
   - Dimension validation using `getimagesize()`.
   - Randomized collision-resistant filenames (`comp_YYYYMMDD_...`) to thwart path traversal.
   - Strict 2 MB file ceiling.
5. **Ownership Enforcement:** Students can only view grievances tied to their specific `student_id`.

---

## 8. Academic Evaluation Notes (BCA Viva / Project Defense)

- **Beginner-Friendly Procedural Logic:** The code avoids convoluted frameworks or magical abstractions, ensuring a student can easily explain every SQL query, session variable, and header redirect during viva examinations.
- **Clean Architecture:** Configuration files and database credentials live outside the public document root (`public/`), following enterprise security guidelines.
- **Relational Integrity:** Foreign key `CONSTRAINT fk_complaints_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE` ensures relational data consistency.
