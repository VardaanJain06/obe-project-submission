# Outcome-Based Education (OBE) & NBA Accreditation Platform
### Criterion 1 (Vision & Mission) and Criterion 5 (Faculty Table 5A)

An autonomous, enterprise-grade Outcome-Based Education (OBE) and NBA Self-Assessment Report (SAR) platform engineered with **Domain-Driven Design (DDD)**, pure mathematical calculation services, reactive **Vue 3 + Vite** interface, and modular REST APIs.

---

## 🏛️ 1. Criterion 1: Institutional Vision & Mission Implementation

End-to-end implementation of **Criterion 1 (Vision, Mission, and Program Educational Objectives)** for Thapar Institute of Engineering & Technology (TIET).

### Vision Statement (Two Core Pillars)
> **"At TIET, our vision rests on two pillars:**
> 1. **Pillar 1 • Stakeholder Experience:** A deeply meaningful experience for all stakeholders that shapes lives with wisdom, purpose, and compassion.
> 2. **Pillar 2 • Socially Impactful Research:** Socially impactful research that advances knowledge while solving real challenges of industry, society, and humanity.
> 
> *Together, they embody our commitment to seeking excellence for all."*

### Mission Statement
> **"TIET exists to educate and empower students while advancing knowledge through research, so that we serve our society with wisdom, compassion, and a commitment to seek excellence."**

### Architectural Implementation:
- **Interactive UI Display (`resources/js/components/CriteriaExplorer.vue`):** Dedicated visual card rendered under Criterion 1.1 with verified badge, side-by-side pillar cards, and mission block.
- **Evaluation Engine (`app/Domains/SAR/Services/CriterionScoreService.php`):** Binds the two-pillar Vision and Mission into Criterion 1 marks evaluation (Claimed marks: 54.0 / 60.0).
- **SAR Report Compilation (`app/Domains/SAR/Services/SarReportCompilerService.php`):** Synthesizes Vision & Mission into compiled SAR documents.
- **Database Seeding (`database/seeders/NbaSarDatabaseSeeder.php`):** Seeds Vision and Mission text into `sar_criteria.summary_observations`.

---

## 👥 2. Criterion 5: Faculty Table 5A & Cadre Distribution

Complete implementation of **Criterion 5 (Faculty Information & Contributions)** featuring Table 5A with 60 faculty records extracted from official accreditation documentation.

### Exact Table 5A Structure (15 Columns)
1. **Sr. No.**
2. **Name of the Faculty** (Designation-based sequential convention)
3. **APAAR Faculty ID** (Unique synthetic identifier `APAAR-20260001` .. `APAAR-20260060`)
4. **Highest Degree** (100% verified doctoral degree)
5. **University** (Degree-granting institution)
6. **Area of Specialization** (Domain area)
7. **Date of Joining in TIET**
8. **Experience in years in TIET**
9. **Designation at the time of Joining TIET**
10. **Present Designation**
11. **Date (Designated as Prof/Assoc. Prof.), if any**
12. **Nature of Association** (Regular / Contractual / Ad hoc)
13. **Contractual Category** (Full time / Part time / Hourly)
14. **Currently Associated (Yes/No)**
15. **In case of NO, Date of Leaving**

### Designation-Based Sequential Naming (60 Records)
Preserves authentic academic attributes and cadre distribution without using real personal human names:
- **Professors (11):** `Professor1` through `Professor11`
- **Associate Professors (19):** `AssociateProfessor1` through `AssociateProfessor19`
- **Assistant Professors (30):** `AssistantProfessor1` through `AssistantProfessor30`
- **Cadre Ratio:** `11 : 19 : 30` (Fully compliant with Tier-II norms)

### Architectural Implementation:
- **Database Migrations:**
  - `database/migrations/2026_11_25_065639_update_faculty_profiles_for_nba_table_5a.php`
  - `database/migrations/2026_11_25_134000_add_table_5a_columns_to_faculty_profiles.php`
- **Database Seeder (`database/seeders/Criterion5FacultySeeder.php`):** Seeds all 60 faculty profiles and synchronized user accounts.
- **Backend API & Controllers (`app/Http/Controllers/Api/V1/SfrCalculatorApiController.php`):** `/api/v1/nba/faculty-table-5a` returns complete Table 5A roster, cadre counts, and doctoral index.
- **Frontend UI Grid (`resources/js/components/SfrSimulator.vue`):** Interactive data grid with sticky headers, horizontal scrolling, real-time search, cadre filtering, and status filters.
- **Mathematical Calculation Services:**
  - `SfrCalculatorService.php` (Student-Faculty Ratio calculation)
  - `FqiCalculatorService.php` (Faculty Qualification Index calculation)
  - `CadreRatioCalculatorService.php` (Cadre proportion evaluation)

---

## 🚀 Setup & Installation Instructions

### Prerequisites
- **PHP** >= 8.2 (with PDO, SQLite / MySQL, OpenSSL extensions enabled)
- **Composer** >= 2.0
- **Node.js** >= 18.0 & **npm** >= 9.0

### Step 1: Clone the Repository
```bash
git clone https://github.com/VardaanJain06/obe-project-submission.git
cd obe-project-submission
```

### Step 2: Install Dependencies
```bash
composer install
npm install
```

### Step 3: Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### Step 4: Run Migrations & Seeders
```bash
touch database/database.sqlite
php artisan migrate --seed
```

### Step 5: Build Frontend & Start Server
```bash
# Terminal 1: Build frontend assets
npm run build
# Or start Vite dev server:
npm run dev

# Terminal 2: Start backend server
php artisan serve
```

Access the application in your browser at `http://127.0.0.1:8000`.

---

## 🧪 Automated Test Verification

Run the full automated test suite covering SFR, FQI, Cadre ratio, Attainment, SAR workflow, and Table 5A APIs:
```bash
php vendor/phpunit/phpunit/phpunit tests/Feature/NbaSarCalculationTest.php
```
**Test Results:** `OK (17 tests, 92 assertions)` — 100% passing.

---

## 🔐 Default Access Accounts

The platform includes seed credentials for testing role-based access:

| Role | Email | Password | Responsibilities & Entry Points |
| :--- | :--- | :--- | :--- |
| **👑 Super Admin** | `admin@nba-obe.edu` | `Admin@123` | Full system access, staff accounts, system settings, SAR locking. |
| **🏛️ HOD (Dept Head)** | `hod.cse@niet-tech.edu.in` | `Password@123` | Department consolidation, faculty approval, matrix verification. |
| **🎓 Faculty / Coordinator** | `faculty@niet-tech.edu.in` | `Password@123` | Course marks entry, CO definition, CO-PO articulation. |
| **🔍 IQAC / Principal** | `iqac@niet-tech.edu.in` | `Password@123` | Institutional review, financial expenditure sign-off. |
| **📋 NBA Evaluator** | `evaluator@nba-obe.edu` | `Password@123` | External site visit evaluation, audit inspection, compliance verification. |

---

## 📄 License & Attribution
Developed and submitted by **Vardaan Jain** ([@VardaanJain06](https://github.com/VardaanJain06)).
