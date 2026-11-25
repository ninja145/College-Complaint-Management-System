# College Complaint Management System — Presentation Guide & Viva Notes

**Project Title:** Design and Development of a Web-Based College Complaint Management System  
**Presentation Files:**  
- `College_Complaint_Management_System_Diagram_Deck.pptx` (Diagram-Heavy & High-Font Deck)
- `College_Complaint_Management_System_Presentation.pptx`
**Aspect Ratio:** 16:9 Widescreen (High-Contrast Academic Theme)  
**Target:** BCA Final Year / Semester Project Defense & Viva Voce  

---

## Visual Diagram Overview & Slide Walkthrough

### Slide 1: Title Slide (Hero Dark Navy)
- **Visuals:** 44pt prominent title, candidate details card, and 4 large technology badges: `Core PHP 8.3`, `MySQL 8.0`, `Docker Compose`, and `Zero JavaScript`.
- **Speaker Script:**
  > "Respected examiners, good morning. Today, I am presenting my BCA web development project: the *College Complaint Management System*. This application provides a centralized, digital client-server platform for student grievance redressal, built with Core PHP 8.3, MySQL 8.0, and Docker containerization."

---

### Slide 2: Manual Process vs. Digital Redressal Workflow (Side-by-Side Diagram)
- **Visuals:**
  - **Left Box (Red):** Traditional Manual Process &rarr; 4 sequential breakdown cards showing physical paper, misplaced documents, zero tracking, and lack of accountability.
  - **Right Box (Green):** Proposed Digital Platform &rarr; 4 sequential achievement cards showing instant digital submission with 2MB photo, automatic sequential tracking ID (`CMP-YYYY-XXXX`), real-time status progression, and centralized MySQL records.
- **Speaker Script:**
  > "As shown in this visual comparison diagram, manual grievance handling fails due to misplaced paperwork and zero visibility. Our digital platform introduces an auditable, real-time lifecycle where complaints are permanently recorded and tracked."

---

### Slide 3: System Architecture & Docker Multi-Container Diagram
- **Visuals:** 3 large architectural blocks connected by directional network arrows:
  1. **Client Layer:** Browser &rarr; HTTP Port `8080:80`
  2. **Application Container (`grievance_app`):** Apache 2.4, Core PHP 8.3, PDO MySQL, Session/CSRF Guards, 2MB Upload Validator.
  3. **Database Container (`grievance_db`):** MySQL 8.0, internal DNS `db:3306`, InnoDB engine.
  4. **Persistent Storage Volumes:** Direct visual boxes showing `uploads_data` mounted to `/uploads` and `db_data` mounted to `/var/lib/mysql`.
- **Speaker Script:**
  > "Here is our complete system architecture diagram. Notice how client browsers communicate with the Apache PHP container on port 8080. The app communicates with MySQL over an isolated Docker network. Below each container, you can see persistent volumes ensuring zero data loss across container recreations."

---

### Slide 4: End-to-End Grievance Redressal Pipeline
- **Visuals:** 4 horizontal connected stage boxes with directional arrows:
  - **Stage 1 (Register & Sign In):** Profile creation with Bcrypt password hashing.
  - **Stage 2 (Submit Grievance):** Category selection, description, optional 2MB photo.
  - **Stage 3 (System Processing):** Automated ID assignment (`CMP-2026-0001`) and MySQL storage.
  - **Stage 4 (Admin Resolution):** Investigation and status progression.
  - **Bottom Banner:** Visual breakdown of the 7 Institutional Categories.
- **Speaker Script:**
  > "This diagram details the student grievance pipeline from initial registration through submission to administrative closure across our 7 standardized institutional categories."

---

### Slide 5: Complaint Status State Machine & Lifecycle
- **Visuals:** 3 large status cards connected by transition arrows:
  - **PENDING (Amber):** Initial queue state upon student submission.
  - &rarr; *Admin reviews details & photo evidence* &rarr;
  - **IN PROGRESS (Blue):** Active inquiry, inspection, or facility order underway.
  - &rarr; *Corrective action completed* &rarr;
  - **RESOLVED (Green):** Concluded and closed. Student dashboard badge updates in real time.
- **Speaker Script:**
  > "This state machine diagram illustrates the complaint lifecycle. Each status is distinctly color-coded and reflects real administrative action without confusion."

---

### Slide 6: Database Entity-Relationship (ER) Diagram
- **Visuals:** Visual schema cards with Primary Keys (🔑) and Foreign Keys (🔗):
  - `students` entity (PK: `id`, Unique: `student_id`, `email`)
  - &rarr; **[1 : N Relational Connector]** &rarr;
  - `complaints` entity (PK: `id`, FK: `student_id` with `ON DELETE CASCADE`, Unique: `tracking_id`)
  - `admins` entity (PK: `id`, Unique: `username`)
- **Speaker Script:**
  > "Our database design enforces strict relational integrity in 3NF. The one-to-many relationship between students and complaints includes a cascade delete rule, ensuring database cleanliness."

---

### Slide 7: Supporting Image Upload & Security Pipeline
- **Visuals:** 5 sequential security gate cards:
  - **Gate 1:** File Size Check (strict &le; 2MB limit)
  - **Gate 2:** Extension Whitelist (`.jpg`, `.jpeg`, `.png` only; executable formats blocked)
  - **Gate 3:** Binary MIME Verification (checks file magic bytes with PHP `finfo`)
  - **Gate 4:** Image Dimension Check (verifies real image headers with `getimagesize()`)
  - **Gate 5:** Randomized Hash Filename (`comp_YYYYMMDD_...` prevents directory traversal)
- **Speaker Script:**
  > "File uploads represent a significant attack vector in web applications. Our 5-gate security pipeline inspects file size, extension, binary MIME signatures, and dimensions before generating a collision-resistant filename."

---

### Slide 8: Dual-Role Access Control & Security Boundaries
- **Visuals:** Side-by-side boundary cards:
  - **Student Security Boundary:** Session guard, ownership isolation (`WHERE student_id = :id`), 302 rejection on URL tampering.
  - **Administrator Security Boundary:** Isolated login route, global management, exclusive status update permissions.
  - **Bottom Security Shield:** 100% PDO prepared statements, Bcrypt password hashing, and anti-session fixation.

---

### Slide 9: Technology Stack (Zero-JS Architecture)
- **Visuals:** 4 large visual cards detailing Core PHP 8.3, MySQL 8.0, Pure CSS3 & HTML5, and Docker Compose.

---

### Slide 10: Verification & Acceptance Scorecard
- **Visuals:**
  - 4 massive metric blocks: `100% Test Pass Rate`, `0 JS Dependencies`, `2 Docker Containers`, `3 Relational Tables`.
  - 5 visual checklist rows with green `✓ PASSED` verification badges.

---

### Slide 11: Future Roadmap Diagram
- **Visuals:** 4-phase horizontal roadmap from Core Engine (Completed) to Automated Notifications (Phase 2), Department Routing (Phase 3), and Analytics/Rating (Phase 4).

---

### Slide 12: Conclusion & Viva Voce Q&A
- **Visuals:** High-contrast navy closing slide with 3 takeaway pillars (Architecture, Security, Simplicity) and prominent invitation for questions.

---

## Top 10 Anticipated Viva Questions & Examiner Answers

1. **Why did you use Core PHP PDO instead of a framework like Laravel?**
   - *Answer:* For an academic BCA project, using Core PHP with PDO demonstrates a direct, foundational understanding of the HTTP request lifecycle, session handling, relational queries, and security mechanisms without hiding the logic behind framework magic.
2. **How does the system prevent SQL Injection?**
   - *Answer:* Every database query uses PDO prepared statements with parameterized tokens (`:placeholder`). User input is never concatenated directly into SQL strings.
3. **How does the sequential tracking ID work?**
   - *Answer:* The helper function `generateTrackingId()` looks up the highest tracking ID for the current year (e.g. `CMP-2026-0001`), extracts the counter, increments it by 1, and formats it with leading zeros.
4. **How are passwords stored securely?**
   - *Answer:* Passwords are never stored in plaintext. They are hashed using PHP's `password_hash($pass, PASSWORD_BCRYPT)`, which includes an automatic cryptographic salt and a cost factor of 10.
5. **How does CSRF protection work without JavaScript?**
   - *Answer:* A cryptographically secure 32-byte token is stored in the PHP session and injected as a hidden `<input type="hidden" name="csrf_token">` inside every POST form. Upon submission, `hash_equals()` validates the token before processing.
6. **Can a student view someone else's complaint by altering the URL query parameter?**
   - *Answer:* No. The SQL query explicitly includes `AND c.student_id = :session_student_id`. If an unauthorized student ID is detected, the script redirects with an error flash message.
7. **What is the advantage of Docker containerization for this project?**
   - *Answer:* Docker eliminates the "it works on my machine" problem. The exact PHP 8.3 environment, Apache configuration, and MySQL 8.0 database run identically across Windows, macOS, or Linux with a single command (`docker compose up -d`).
8. **Why is the frontend built with pure CSS and zero JavaScript?**
   - *Answer:* As specified in the PRD, the system relies on native browser form submissions, fast page reloads, and server-side state. This minimizes client-side vulnerabilities, works reliably without heavy JS dependencies, and guarantees accessibility.
9. **How is persistent data protected during container recreation?**
   - *Answer:* `docker-compose.yml` mounts two named host volumes: `db_data` for MySQL database files and `uploads_data` for uploaded complaint photos.
10. **What HTTP status codes are used for access control?**
   - *Answer:* Protected pages check session existence; if unauthenticated, they send an HTTP 302 redirect header to the respective login page.
