# NBA Self-Assessment Report (SAR) Platform

An autonomous, enterprise-grade NBA Self-Assessment Report (SAR) & Outcome-Based Education (OBE) platform engineered with **Domain-Driven Design (DDD)**, pure mathematical calculation services, reactive **Vue 3 + Vite** interface, and modular REST APIs.

---

## 🔐 Super Admin & Role-Based Access Control

The platform features a secure authentication and authorization system with distinct entry/view points for each staff role:

| Role | Email | Password | Responsibilities & Entry Points |
| :--- | :--- | :--- | :--- |
| **👑 Super Admin** | `admin@nba-obe.edu` | `Admin@123` | Full system access, create & manage staff accounts, department & program assignments, system settings. |
| **🏛️ HOD (Dept Head)** | `hod.cse@niet-tech.edu.in` | `Password@123` | Department consolidation, faculty approval, matrix verification, SFR review. |
| **🎓 Faculty / Coordinator** | `faculty@niet-tech.edu.in` | `Password@123` | Course mark entry (CIE/SEE), CO definition, CO-PO articulation, direct attainment. |
| **🔍 IQAC / Principal** | `iqac@niet-tech.edu.in` | `Password@123` | Institutional review, financial expenditure sign-off, SAR locking & freezing. |
| **📋 NBA Evaluator** | `evaluator@nba-obe.edu` | `Password@123` | External site visit evaluation, audit inspection, compliance verification. |

---

## 🖥️ Enterprise Multi-View Dashboard Structure

Instead of cramming everything onto a single page, the dashboard is partitioned into dedicated modular workspace views:

```
Dashboard Navigation
├── 📊 Executive Dashboard & KPI Scores  # 1000-point gauge, compliance predictor, 5 quick pillars
├── 👥 Staff & Faculty Management       # Super Admin/HOD panel: Add staff, edit qualifications, toggle status
├── 🏛️ Programs & Intake (UGT)          # Sanctioned intake, UGT1-UGT4 enrolment, graduation cohorts
├── 🗺️ Articulation Matrix (CO-PO)      # Program master matrix & interactive course heatmap editor
├── 🎯 Attainment Evaluation Engine     # CIE/SEE mark evaluation, survey scores, final PO1-PO12 radar
├── 📐 Faculty SFR & FQI Simulators     # Real-time mathematical calculation simulators for Criterion 5
├── 💰 Financial Budgets (Crit 10)      # CFY/CFYm1 recurring & non-recurring capital expenditure
├── 📑 Criteria 1 - 10 Master Explorer  # Comprehensive self-assessment criteria breakdown
├── 🛡️ SAR Workflow & Audit Trail       # State machine progression (Draft -> Dept -> IQAC -> Locked)
└── 🖨️ Official Printable SAR Document  # Official Tier-II formatted NBA document with print stylesheets
```

---

## 🏛️ Domain-Driven Design (DDD) Layout (`app/Domains/`)

```
app/
├── Domains/
│   ├── Academic/       # Institution, Department, Program, AcademicYear (CAY, CAYm1, CAYm2), AcademicIntake
│   ├── Faculty/        # FacultyProfile, FacultyAcademicYear, FDPs, Publications
│   │   └── Services/   # SfrCalculatorService, FqiCalculatorService, CadreRatioCalculatorService
│   ├── Curriculum/     # Course, ProgramOutcome (PO1-12), PSO (PSO1-2), CourseOutcome (CO1-5), CoPoMapping
│   │   └── Services/   # ArticulationMatrixService
│   ├── Attainment/     # StudentAssessment (CIE/SEE), IndirectAssessment (Surveys), AttainmentResult
│   │   └── Services/   # DirectAttainmentService, IndirectAttainmentService, AttainmentEngine
│   ├── Student/        # StudentCohort (Graduation Success), StudentPlacement (Placement/Higher Studies)
│   │   └── Services/   # SuccessRateCalculatorService, PlacementIndexService
│   ├── Finance/        # FinancialBudget (CFY, CFYm1, CFYm2), BudgetItem (Recurring/Non-recurring)
│   │   └── Services/   # BudgetAnalysisService
│   └── SAR/            # SarReport, SarCriterion (1 to 10), SarAuditLog
│       ├── StateMachine/ # SarStateMachine (DRAFT -> DEPT -> INST -> LOCKED -> ARCHIVED)
│       └── Services/     # SarReportCompilerService (1000-point aggregation)
├── Http/
│   └── Controllers/Api/V1/ # RESTful JSON APIs for all domains
└── ...
```

---

## 🚀 Porting to Acadicron-ERP (`Acadicron-ERP-new`)

1. **Backend**: Copy `app/Domains/` and `app/Http/Controllers/Api/V1/` into `Acadicron-ERP-new/app/`.
2. **Migrations**: Copy `database/migrations/2025_01_01_000001_...` to Acadicron.
3. **Frontend**: Copy `resources/js/components/` into Acadicron's Vue component structure. All components communicate via `/api/v1/...` REST APIs.

---

## 🧪 Automated Test Suite

Run the full suite of automated tests verifying all calculation engines, state transitions, authentication, and staff management:
```bash
php artisan test
```
**13 Feature & Unit Tests passing with 62 assertions.**
