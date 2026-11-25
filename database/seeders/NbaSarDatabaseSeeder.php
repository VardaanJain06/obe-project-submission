<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\Academic\Models\Institution;
use App\Domains\Academic\Models\Department;
use App\Domains\Academic\Models\AcademicYear;
use App\Domains\Academic\Models\Program;
use App\Domains\Academic\Models\AcademicIntake;
use App\Domains\Faculty\Models\FacultyProfile;
use App\Domains\Faculty\Models\FacultyAcademicYear;
use App\Domains\Faculty\Models\FacultyFdp;
use App\Domains\Faculty\Models\FacultyPublication;
use App\Domains\Curriculum\Models\Course;
use App\Domains\Curriculum\Models\ProgramOutcome;
use App\Domains\Curriculum\Models\ProgramSpecificOutcome;
use App\Domains\Curriculum\Models\CourseOutcome;
use App\Domains\Curriculum\Models\CoPoMapping;
use App\Domains\Attainment\Models\StudentAssessment;
use App\Domains\Attainment\Models\IndirectAssessment;
use App\Domains\Attainment\Models\AttainmentResult;
use App\Domains\Student\Models\StudentCohort;
use App\Domains\Student\Models\StudentPlacement;
use App\Domains\Finance\Models\FinancialBudget;
use App\Domains\Finance\Models\BudgetItem;
use App\Domains\SAR\Models\SarReport;
use App\Domains\SAR\Models\SarCriterion;
use App\Domains\SAR\Models\SarAuditLog;

class NbaSarDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Institution & Department
        $inst = Institution::firstOrCreate(
            ['code' => 'NIET-098'],
            [
                'name' => 'National Institute of Engineering & Technology',
                'head_of_institution' => 'Dr. R. K. Sharma (Principal / Director)',
                'address' => 'Knowledge Park Tech Zone, Phase II',
                'website' => 'https://niet-tech.edu.in',
            ]
        );

        $dept = Department::firstOrCreate(
            ['code' => 'CSE', 'institution_id' => $inst->id],
            [
                'name' => 'Department of Computer Science and Engineering',
                'hod_name' => 'Dr. Arvind Mehra, Ph.D.',
                'hod_email' => 'hod.cse@niet-tech.edu.in',
            ]
        );

        // Default System Users for Roles
        \App\Models\User::firstOrCreate(
            ['email' => 'admin@nba-obe.edu'],
            [
                'name' => 'System Super Admin',
                'password' => \Illuminate\Support\Facades\Hash::make('Admin@123'),
                'role' => \App\Models\User::ROLE_SUPER_ADMIN,
                'designation' => 'Chief Technology Officer & Admin',
                'phone' => '+91 9900112233',
                'is_active' => true,
            ]
        );

        \App\Models\User::firstOrCreate(
            ['email' => 'hod.cse@niet-tech.edu.in'],
            [
                'name' => 'Dr. Arvind Mehra',
                'password' => \Illuminate\Support\Facades\Hash::make('Password@123'),
                'role' => \App\Models\User::ROLE_HOD,
                'department_id' => $dept->id,
                'designation' => 'Professor & Head of Department',
                'faculty_code' => 'FAC-CSE-001',
                'phone' => '+91 9811223344',
                'is_active' => true,
            ]
        );

        \App\Models\User::firstOrCreate(
            ['email' => 'faculty@niet-tech.edu.in'],
            [
                'name' => 'Prof. Rohit Kulkarni',
                'password' => \Illuminate\Support\Facades\Hash::make('Password@123'),
                'role' => \App\Models\User::ROLE_FACULTY,
                'department_id' => $dept->id,
                'designation' => 'Associate Professor & Course Coordinator',
                'faculty_code' => 'FAC-CSE-008',
                'phone' => '+91 9822334455',
                'is_active' => true,
            ]
        );

        \App\Models\User::firstOrCreate(
            ['email' => 'iqac@niet-tech.edu.in'],
            [
                'name' => 'Dr. R. K. Sharma',
                'password' => \Illuminate\Support\Facades\Hash::make('Password@123'),
                'role' => \App\Models\User::ROLE_IQAC,
                'designation' => 'Principal & IQAC Chairman',
                'phone' => '+91 9833445566',
                'is_active' => true,
            ]
        );

        \App\Models\User::firstOrCreate(
            ['email' => 'evaluator@nba-obe.edu'],
            [
                'name' => 'Prof. NBA Expert Committee',
                'password' => \Illuminate\Support\Facades\Hash::make('Password@123'),
                'role' => \App\Models\User::ROLE_EVALUATOR,
                'designation' => 'External Accreditation Peer Reviewer',
                'phone' => '+91 9844556677',
                'is_active' => true,
            ]
        );

        // 2. Academic Years (CAY, CAYm1, CAYm2, CAYm3)
        $cay = AcademicYear::firstOrCreate(['name' => '2024-25'], ['type' => 'CAY', 'start_year' => 2024, 'end_year' => 2025, 'is_current' => true]);
        $caym1 = AcademicYear::firstOrCreate(['name' => '2023-24'], ['type' => 'CAYm1', 'start_year' => 2023, 'end_year' => 2024, 'is_current' => false]);
        $caym2 = AcademicYear::firstOrCreate(['name' => '2022-23'], ['type' => 'CAYm2', 'start_year' => 2022, 'end_year' => 2023, 'is_current' => false]);
        $caym3 = AcademicYear::firstOrCreate(['name' => '2021-22'], ['type' => 'CAYm3', 'start_year' => 2021, 'end_year' => 2022, 'is_current' => false]);

        // 3. Program
        $prog = Program::firstOrCreate(
            ['code' => 'BTECH-CSE'],
            [
                'institution_id' => $inst->id,
                'department_id' => $dept->id,
                'name' => 'B.Tech in Computer Science and Engineering',
                'degree_level' => 'UG',
                'sanctioned_intake' => 120,
                'year_of_start' => 2008,
                'nba_tier' => 'Tier-II',
                'coordinator_name' => 'Dr. S. Narayan (NBA Coordinator)',
            ]
        );

        // 4. Academic Intake data (UGT1 + UGT2 + UGT3 + UGT4)
        if (AcademicIntake::where('program_id', $prog->id)->count() === 0) {
            AcademicIntake::create(['program_id' => $prog->id, 'academic_year_id' => $cay->id, 'ug_1st_year' => 120, 'ug_2nd_year' => 128, 'ug_3rd_year' => 125, 'ug_4th_year' => 122, 'lateral_entry' => 12]);
            AcademicIntake::create(['program_id' => $prog->id, 'academic_year_id' => $caym1->id, 'ug_1st_year' => 120, 'ug_2nd_year' => 126, 'ug_3rd_year' => 124, 'ug_4th_year' => 120, 'lateral_entry' => 12]);
            AcademicIntake::create(['program_id' => $prog->id, 'academic_year_id' => $caym2->id, 'ug_1st_year' => 120, 'ug_2nd_year' => 124, 'ug_3rd_year' => 120, 'ug_4th_year' => 118, 'lateral_entry' => 10]);
        }

        // 5. Faculty Profiles (Professors, Assoc Profs, Asst Profs)
        $facultyData = [
            ['name' => 'Dr. Arvind Mehra', 'faculty_id' => 'FAC-CSE-001', 'degree' => 'Ph.D. Computer Science', 'cadre' => 'Professor', 'exp' => 18, 'joining' => '2012-07-01'],
            ['name' => 'Dr. Sunita Varma', 'faculty_id' => 'FAC-CSE-002', 'degree' => 'Ph.D. AI & Deep Learning', 'cadre' => 'Professor', 'exp' => 16, 'joining' => '2014-06-15'],
            ['name' => 'Dr. Rajesh Deshmukh', 'faculty_id' => 'FAC-CSE-003', 'degree' => 'Ph.D. Cloud Computing', 'cadre' => 'Professor', 'exp' => 15, 'joining' => '2015-08-01'],
            ['name' => 'Dr. Priya Nambiar', 'faculty_id' => 'FAC-CSE-004', 'degree' => 'Ph.D. Cybersecurity', 'cadre' => 'Associate Professor', 'exp' => 12, 'joining' => '2017-07-10'],
            ['name' => 'Dr. Ankit Gupta', 'faculty_id' => 'FAC-CSE-005', 'degree' => 'Ph.D. Data Science', 'cadre' => 'Associate Professor', 'exp' => 11, 'joining' => '2018-01-05'],
            ['name' => 'Dr. Sneha Roy', 'faculty_id' => 'FAC-CSE-006', 'degree' => 'Ph.D. Computer Vision', 'cadre' => 'Associate Professor', 'exp' => 10, 'joining' => '2018-07-15'],
            ['name' => 'Dr. Vikas Patel', 'faculty_id' => 'FAC-CSE-007', 'degree' => 'Ph.D. IoT Systems', 'cadre' => 'Associate Professor', 'exp' => 9, 'joining' => '2019-06-01'],
            ['name' => 'Prof. Rohit Kulkarni', 'faculty_id' => 'FAC-CSE-008', 'degree' => 'M.Tech Software Engineering', 'cadre' => 'Associate Professor', 'exp' => 12, 'joining' => '2016-08-10'],
            ['name' => 'Prof. Neha Sen', 'faculty_id' => 'FAC-CSE-009', 'degree' => 'M.Tech Computer Networks', 'cadre' => 'Assistant Professor', 'exp' => 7, 'joining' => '2020-01-10'],
            ['name' => 'Prof. Amitava Ghosh', 'faculty_id' => 'FAC-CSE-010', 'degree' => 'M.Tech Machine Learning', 'cadre' => 'Assistant Professor', 'exp' => 6, 'joining' => '2020-07-01'],
            ['name' => 'Prof. Pooja Hegde', 'faculty_id' => 'FAC-CSE-011', 'degree' => 'M.Tech Distributed Systems', 'cadre' => 'Assistant Professor', 'exp' => 6, 'joining' => '2020-08-15'],
            ['name' => 'Prof. Manoj Iyer', 'faculty_id' => 'FAC-CSE-012', 'degree' => 'M.Tech Information Security', 'cadre' => 'Assistant Professor', 'exp' => 5, 'joining' => '2021-06-01'],
            ['name' => 'Prof. Shweta Joshi', 'faculty_id' => 'FAC-CSE-013', 'degree' => 'M.Tech Web Technologies', 'cadre' => 'Assistant Professor', 'exp' => 5, 'joining' => '2021-07-10'],
            ['name' => 'Prof. Deepali Rathi', 'faculty_id' => 'FAC-CSE-014', 'degree' => 'M.Tech Embedded Systems', 'cadre' => 'Assistant Professor', 'exp' => 4, 'joining' => '2022-01-15'],
            ['name' => 'Prof. Saurabh Mishra', 'faculty_id' => 'FAC-CSE-015', 'degree' => 'M.Tech Cloud Architecture', 'cadre' => 'Assistant Professor', 'exp' => 4, 'joining' => '2022-06-20'],
            ['name' => 'Prof. Kavita Rao', 'faculty_id' => 'FAC-CSE-016', 'degree' => 'M.Tech Database Systems', 'cadre' => 'Assistant Professor', 'exp' => 4, 'joining' => '2022-07-01'],
            ['name' => 'Prof. Tanmay Bhatia', 'faculty_id' => 'FAC-CSE-017', 'degree' => 'M.Tech Algorithm Design', 'cadre' => 'Assistant Professor', 'exp' => 3, 'joining' => '2023-01-05'],
            ['name' => 'Prof. Divya Nair', 'faculty_id' => 'FAC-CSE-018', 'degree' => 'M.Tech Big Data Analytics', 'cadre' => 'Assistant Professor', 'exp' => 3, 'joining' => '2023-06-15'],
            ['name' => 'Prof. Rahul Saxena', 'faculty_id' => 'FAC-CSE-019', 'degree' => 'M.Tech Computer Engineering', 'cadre' => 'Assistant Professor', 'exp' => 2, 'joining' => '2023-07-01'],
            ['name' => 'Prof. Meenakshi S.', 'faculty_id' => 'FAC-CSE-020', 'degree' => 'M.Tech AI & Robotics', 'cadre' => 'Assistant Professor', 'exp' => 2, 'joining' => '2024-01-10'],
            ['name' => 'Prof. Nitin Chawla', 'faculty_id' => 'FAC-CSE-021', 'degree' => 'M.Tech Blockchain Technologies', 'cadre' => 'Assistant Professor', 'exp' => 2, 'joining' => '2024-06-01'],
            ['name' => 'Prof. Ananya Mukherjee', 'faculty_id' => 'FAC-CSE-022', 'degree' => 'M.Tech Human Computer Interaction', 'cadre' => 'Assistant Professor', 'exp' => 1, 'joining' => '2024-07-01'],
            ['name' => 'Dr. Karan Malhotra', 'faculty_id' => 'FAC-CSE-023', 'degree' => 'Ph.D. Computer Science', 'cadre' => 'Associate Professor', 'exp' => 10, 'joining' => '2019-08-01'],
            ['name' => 'Prof. Riya Kapoor', 'faculty_id' => 'FAC-CSE-024', 'degree' => 'M.Tech Software Systems', 'cadre' => 'Assistant Professor', 'exp' => 5, 'joining' => '2021-08-02'],
            ['name' => 'Prof. Aditya Menon', 'faculty_id' => 'FAC-CSE-025', 'degree' => 'M.Tech Artificial Intelligence', 'cadre' => 'Assistant Professor', 'exp' => 4, 'joining' => '2022-07-04'],
            ['name' => 'Prof. Simran Kaur', 'faculty_id' => 'FAC-CSE-026', 'degree' => 'M.Tech Data Engineering', 'cadre' => 'Assistant Professor', 'exp' => 3, 'joining' => '2023-07-03'],
            ['name' => 'Prof. Arjun Verma', 'faculty_id' => 'FAC-CSE-027', 'degree' => 'M.Tech Computer Science', 'cadre' => 'Assistant Professor', 'exp' => 2, 'joining' => '2024-07-08'],
        ];

        foreach ($facultyData as $f) {
            $prof = FacultyProfile::firstOrCreate(
                ['faculty_id' => $f['faculty_id']],
                [
                    'department_id' => $dept->id,
                    'name' => $f['name'],
                    'apaar_id' => 'APAAR-' . rand(10000000, 99999999),
                    'email' => strtolower(str_replace(['Dr. ', 'Prof. ', ' '], ['', '', '.'], $f['name'])) . '@niet-tech.edu.in',
                    'mobile' => '+91 98' . rand(10000000, 99999999),
                    'joining_date' => $f['joining'],
                    'highest_degree' => $f['degree'],
                    'degree_specialization' => $f['degree'],
                    'teaching_experience_years' => $f['exp'],
                    'is_active' => true,
                ]
            );

            // Assign to CAY, CAYm1, CAYm2
            foreach ([$cay, $caym1, $caym2] as $y) {
                FacultyAcademicYear::firstOrCreate(
                    ['faculty_profile_id' => $prof->id, 'academic_year_id' => $y->id],
                    [
                        'program_id' => $prog->id,
                        'designation' => $f['cadre'],
                        'cadre' => $f['cadre'],
                        'pay_scale' => str_contains($f['cadre'], 'Professor') ? '7th CPC Level 14' : (str_contains($f['cadre'], 'Associate') ? '7th CPC Level 13A' : '7th CPC Level 10'),
                        'association_type' => 'Regular',
                        'is_exclusive_to_program' => true,
                    ]
                );
            }

            // Faculty FDP
            if (FacultyFdp::where('faculty_profile_id', $prof->id)->count() === 0) {
                FacultyFdp::create([
                    'faculty_profile_id' => $prof->id,
                    'academic_year_id' => $cay->id,
                    'title' => 'Advanced Pedagogical Training & Outcome Based Education Implementation',
                    'duration_days' => 5,
                    'organizer' => 'AICTE - ATAL Academy',
                    'start_date' => '2024-09-10',
                    'end_date' => '2024-09-15',
                ]);
            }

            // Publication
            if (str_contains($f['degree'], 'Ph.D') && FacultyPublication::where('faculty_profile_id', $prof->id)->count() === 0) {
                FacultyPublication::create([
                    'faculty_profile_id' => $prof->id,
                    'academic_year_id' => $cay->id,
                    'title' => 'Scalable Distributed Architectures for High-Throughput Stream Processing',
                    'journal_conference_name' => 'IEEE Transactions on Computers',
                    'publication_type' => 'Journal',
                    'indexing' => 'SCI',
                    'impact_factor' => 3.85,
                    'published_date' => '2024-11-20',
                ]);
            }
        }

        $this->call(Criterion5FacultySeeder::class);

        // 6. Program Outcomes (PO1 to PO12)
        $nbaPos = [
            ['code' => 'PO1', 'title' => 'Engineering Knowledge', 'desc' => 'Apply knowledge of mathematics, science, engineering fundamentals, and computer engineering specialization.'],
            ['code' => 'PO2', 'title' => 'Problem Analysis', 'desc' => 'Identify, formulate, review research literature, and analyze complex engineering problems reaching substantiated conclusions.'],
            ['code' => 'PO3', 'title' => 'Design/Development of Solutions', 'desc' => 'Design solutions for complex engineering problems and design system components or processes that meet specified needs.'],
            ['code' => 'PO4', 'title' => 'Conduct Investigations of Complex Problems', 'desc' => 'Use research-based knowledge and research methods including design of experiments, analysis and interpretation of data.'],
            ['code' => 'PO5', 'title' => 'Modern Tool Usage', 'desc' => 'Create, select, and apply appropriate techniques, resources, and modern engineering and IT tools.'],
            ['code' => 'PO6', 'title' => 'The Engineer and Society', 'desc' => 'Apply reasoning informed by contextual knowledge to assess societal, health, safety, legal and cultural issues.'],
            ['code' => 'PO7', 'title' => 'Environment and Sustainability', 'desc' => 'Understand the impact of professional engineering solutions in societal and environmental contexts.'],
            ['code' => 'PO8', 'title' => 'Ethics', 'desc' => 'Apply ethical principles and commit to professional ethics and responsibilities and norms of engineering practice.'],
            ['code' => 'PO9', 'title' => 'Individual and Team Work', 'desc' => 'Function effectively as an individual, and as a member or leader in diverse teams, and in multidisciplinary settings.'],
            ['code' => 'PO10', 'title' => 'Communication', 'desc' => 'Communicate effectively on complex engineering activities with the engineering community and with society at large.'],
            ['code' => 'PO11', 'title' => 'Project Management and Finance', 'desc' => 'Demonstrate knowledge and understanding of engineering and management principles and apply these to one’s own work.'],
            ['code' => 'PO12', 'title' => 'Life-long Learning', 'desc' => 'Recognize the need for, and have the preparation and ability to engage in independent and life-long learning.'],
        ];

        $createdPos = [];
        foreach ($nbaPos as $p) {
            $createdPos[$p['code']] = ProgramOutcome::firstOrCreate(
                ['program_id' => $prog->id, 'po_code' => $p['code']],
                ['title' => $p['title'], 'description' => $p['desc']]
            );
        }

        // Program Specific Outcomes (PSOs)
        $nbaPsos = [
            ['code' => 'PSO1', 'title' => 'Software Systems Architecture', 'desc' => 'Design, build, and deploy robust, secure, and full-stack software applications using contemporary frameworks.'],
            ['code' => 'PSO2', 'title' => 'Intelligent Computing & Data Science', 'desc' => 'Develop predictive and generative AI models, analytics pipelines, and cloud computing solutions.'],
        ];

        $createdPsos = [];
        foreach ($nbaPsos as $p) {
            $createdPsos[$p['code']] = ProgramSpecificOutcome::firstOrCreate(
                ['program_id' => $prog->id, 'pso_code' => $p['code']],
                ['title' => $p['title'], 'description' => $p['desc']]
            );
        }
        // 7. Courses & Articulation Matrix
        // Realistic B.Tech CSE course catalog covering Sem 1 to Sem 8
        if (Course::where('program_id', $prog->id)->count() === 0) {

            $coursesData = [

                // ==================== SEMESTER 1 ====================
                [
                    'code' => 'MA101',
                    'name' => 'Engineering Mathematics-I',
                    'sem' => 1,
                    'credits' => 4.0,
                    'cos' => [
                        'Apply differential calculus techniques to solve engineering problems.',
                        'Use matrices and determinants to model and solve systems of linear equations.',
                        'Apply integral calculus to compute engineering quantities and evaluate functions.',
                        'Analyze sequences, series and multivariable functions using appropriate mathematical techniques.',
                        'Solve basic engineering problems using differential equations and mathematical models.',
                    ],
                ],
                [
                    'code' => 'PH101',
                    'name' => 'Engineering Physics',
                    'sem' => 1,
                    'credits' => 4.0,
                    'cos' => [
                        'Explain fundamental principles of mechanics, waves and oscillations used in engineering.',
                        'Apply electromagnetic theory to solve basic engineering problems.',
                        'Analyze optical phenomena using principles of interference, diffraction and polarization.',
                        'Explain the physical principles of lasers, fibre optics and semiconductor materials.',
                        'Apply concepts of modern physics to engineering and computing applications.',
                    ],
                ],
                [
                    'code' => 'CS101',
                    'name' => 'Programming for Problem Solving',
                    'sem' => 1,
                    'credits' => 4.0,
                    'cos' => [
                        'Explain fundamental programming concepts including variables, data types, operators and control structures.',
                        'Develop programs using functions, arrays, strings and structured programming techniques.',
                        'Apply pointers, structures and dynamic memory concepts to solve computational problems.',
                        'Analyze programming problems and design appropriate algorithms before implementation.',
                        'Develop, test and debug C programs using systematic problem-solving techniques.',
                    ],
                ],

                // ==================== SEMESTER 2 ====================
                [
                    'code' => 'MA102',
                    'name' => 'Engineering Mathematics-II',
                    'sem' => 2,
                    'credits' => 4.0,
                    'cos' => [
                        'Apply differential equations to formulate engineering and computing models.',
                        'Use Laplace transforms to solve linear differential equations and system problems.',
                        'Apply Fourier series and transforms to analyze periodic and continuous signals.',
                        'Use vector calculus concepts to solve engineering field problems.',
                        'Analyze mathematical models using appropriate numerical and analytical techniques.',
                    ],
                ],
                [
                    'code' => 'CS102',
                    'name' => 'Data Structures',
                    'sem' => 2,
                    'credits' => 4.0,
                    'cos' => [
                        'Explain abstract data types and analyze the complexity of basic data structure operations.',
                        'Implement linear data structures including arrays, linked lists, stacks and queues.',
                        'Implement and analyze trees, binary search trees and priority queues.',
                        'Apply hashing and graph data structures to solve computational problems.',
                        'Select appropriate data structures based on problem requirements and efficiency constraints.',
                    ],
                ],
                [
                    'code' => 'CS103',
                    'name' => 'Digital Logic and Computer Organization',
                    'sem' => 2,
                    'credits' => 4.0,
                    'cos' => [
                        'Explain number systems, Boolean algebra and fundamental digital logic concepts.',
                        'Design combinational and sequential digital circuits using logic gates and flip-flops.',
                        'Explain the organization and functional components of a digital computer.',
                        'Analyze CPU, memory hierarchy and input-output organization.',
                        'Apply computer organization principles to understand the execution of machine-level instructions.',
                    ],
                ],

                // ==================== SEMESTER 3 ====================
                [
                    'code' => 'CS201',
                    'name' => 'Discrete Mathematics',
                    'sem' => 4.0,
                    'sem' => 3,
                    'credits' => 4.0,
                    'cos' => [
                        'Apply propositional and predicate logic to formulate and verify computational statements.',
                        'Use set theory, relations and functions to model computer science problems.',
                        'Apply combinatorics and recurrence relations to solve counting and algorithmic problems.',
                        'Analyze graphs and trees for modelling networks and computational structures.',
                        'Apply discrete mathematical techniques to support algorithm and software design.',
                    ],
                ],
                [
                    'code' => 'CS202',
                    'name' => 'Object Oriented Programming',
                    'sem' => 3,
                    'credits' => 4.0,
                    'cos' => [
                        'Explain object-oriented programming concepts including classes, objects and encapsulation.',
                        'Implement inheritance, polymorphism and abstraction to build reusable software components.',
                        'Apply exception handling, generic programming and collection concepts in application development.',
                        'Analyze software problems and design object-oriented solutions using appropriate class relationships.',
                        'Develop and test modular applications using object-oriented programming principles.',
                    ],
                ],
                [
                    'code' => 'CS203',
                    'name' => 'Computer Organization and Architecture',
                    'sem' => 3,
                    'credits' => 4.0,
                    'cos' => [
                        'Explain instruction set architecture and the organization of computer systems.',
                        'Analyze CPU datapath, control unit and instruction execution mechanisms.',
                        'Evaluate different memory technologies and memory hierarchy organizations.',
                        'Explain pipelining, cache organization and performance improvement techniques.',
                        'Analyze processor and I/O architectures for performance-oriented computing systems.',
                    ],
                ],

                // ==================== SEMESTER 4 ====================
                [
                    'code' => 'CS301',
                    'name' => 'Data Structures and Algorithms',
                    'sem' => 4,
                    'credits' => 4.0,
                    'cos' => [
                        'Analyze the time and space complexity of fundamental algorithms.',
                        'Implement sorting and searching algorithms and compare their performance.',
                        'Apply divide-and-conquer, greedy and dynamic programming techniques to computational problems.',
                        'Design graph algorithms for traversal, shortest path and connectivity problems.',
                        'Select and justify efficient algorithmic solutions based on problem constraints.',
                    ],
                ],
                [
                    'code' => 'CS302',
                    'name' => 'Database Management Systems',
                    'sem' => 4,
                    'credits' => 4.0,
                    'cos' => [
                        'Explain database architecture, relational models and database management concepts.',
                        'Design normalized relational schemas using keys, constraints and functional dependencies.',
                        'Develop SQL queries for data definition, manipulation, aggregation and transaction processing.',
                        'Analyze transaction management, concurrency control and database recovery mechanisms.',
                        'Design and implement a database solution for a real-world application.',
                    ],
                ],
                [
                    'code' => 'CS303',
                    'name' => 'Probability and Statistics',
                    'sem' => 4,
                    'credits' => 3.0,
                    'cos' => [
                        'Apply probability concepts to model uncertain events and random processes.',
                        'Use probability distributions to analyze engineering and computing datasets.',
                        'Apply statistical estimation and hypothesis testing techniques to real-world problems.',
                        'Analyze correlation and regression models for identifying relationships among variables.',
                        'Interpret statistical results and use them to support data-driven decisions.',
                    ],
                ],

                // ==================== SEMESTER 5 ====================
                [
                    'code' => 'CS401',
                    'name' => 'Operating Systems',
                    'sem' => 5,
                    'credits' => 4.0,
                    'cos' => [
                        'Explain operating system architecture, services and system call mechanisms.',
                        'Analyze process scheduling, synchronization and inter-process communication techniques.',
                        'Apply memory management and virtual memory concepts to operating system problems.',
                        'Analyze file systems, storage management and I/O organization.',
                        'Evaluate operating system mechanisms for performance, reliability and resource management.',
                    ],
                ],
                [
                    'code' => 'CS402',
                    'name' => 'Computer Networks',
                    'sem' => 5,
                    'credits' => 4.0,
                    'cos' => [
                        'Explain layered network architectures and fundamental communication protocols.',
                        'Analyze data-link, network and transport layer mechanisms.',
                        'Apply IP addressing, routing and subnetting techniques to network design problems.',
                        'Analyze TCP, UDP, DNS, HTTP and other application-layer protocols.',
                        'Design and evaluate reliable computer network solutions for practical applications.',
                    ],
                ],
                [
                    'code' => 'CS403',
                    'name' => 'Software Engineering',
                    'sem' => 5,
                    'credits' => 3.0,
                    'cos' => [
                        'Explain software development life-cycle models and engineering principles.',
                        'Apply requirements engineering techniques to specify software systems.',
                        'Design software architectures using appropriate modelling and design principles.',
                        'Apply software testing techniques to verify and validate software products.',
                        'Manage software projects using version control, Agile practices and quality assurance methods.',
                    ],
                ],

                // ==================== SEMESTER 6 ====================
                [
                    'code' => 'CS501',
                    'name' => 'Artificial Intelligence and Machine Learning',
                    'sem' => 6,
                    'credits' => 4.0,
                    'cos' => [
                        'Explain fundamental concepts and paradigms of artificial intelligence.',
                        'Apply supervised and unsupervised learning algorithms to structured datasets.',
                        'Evaluate machine learning models using suitable performance metrics.',
                        'Design machine learning pipelines including preprocessing, training and validation.',
                        'Develop an AI/ML solution for a real-world computational problem.',
                    ],
                ],
                [
                    'code' => 'CS502',
                    'name' => 'Web Technologies',
                    'sem' => 6,
                    'credits' => 3.0,
                    'cos' => [
                        'Explain client-server architecture and fundamental web technologies.',
                        'Develop responsive web interfaces using HTML, CSS and JavaScript.',
                        'Implement interactive web applications using modern JavaScript concepts.',
                        'Integrate web applications with RESTful APIs and databases.',
                        'Develop and test a secure and responsive full-stack web application.',
                    ],
                ],
                [
                    'code' => 'CS503',
                    'name' => 'Theory of Computation',
                    'sem' => 6,
                    'credits' => 4.0,
                    'cos' => [
                        'Explain formal languages, grammars and finite automata.',
                        'Design deterministic and nondeterministic finite automata for regular languages.',
                        'Apply context-free grammars and pushdown automata to language recognition problems.',
                        'Analyze Turing machines and computational models.',
                        'Determine the decidability and computational complexity of selected problems.',
                    ],
                ],

                // ==================== SEMESTER 7 ====================
                [
                    'code' => 'CS601',
                    'name' => 'Cloud Computing and Distributed Systems',
                    'sem' => 7,
                    'credits' => 4.0,
                    'cos' => [
                        'Explain cloud computing service models, deployment models and virtualization concepts.',
                        'Analyze distributed system architectures and communication mechanisms.',
                        'Apply cloud storage, compute and networking concepts to application deployment.',
                        'Evaluate scalability, availability and fault-tolerance mechanisms in distributed systems.',
                        'Design a cloud-based distributed solution for a real-world application.',
                    ],
                ],
                [
                    'code' => 'CS602',
                    'name' => 'Cybersecurity and Cryptographic Protocols',
                    'sem' => 7,
                    'credits' => 3.0,
                    'cos' => [
                        'Explain fundamental cybersecurity threats, vulnerabilities and security principles.',
                        'Apply symmetric and asymmetric cryptographic techniques to secure information.',
                        'Analyze authentication, authorization and access-control mechanisms.',
                        'Evaluate common network and application security vulnerabilities.',
                        'Design basic security controls for protecting information systems and applications.',
                    ],
                ],
                [
                    'code' => 'CS603',
                    'name' => 'Big Data Analytics',
                    'sem' => 7,
                    'credits' => 3.0,
                    'cos' => [
                        'Explain big data characteristics, architectures and distributed data processing models.',
                        'Apply data preprocessing and storage techniques to large datasets.',
                        'Use distributed computing frameworks for processing large-scale datasets.',
                        'Analyze datasets using appropriate statistical and machine learning techniques.',
                        'Develop a scalable analytics solution for a real-world big data problem.',
                    ],
                ],

                // ==================== SEMESTER 8 ====================
                [
                    'code' => 'CS701',
                    'name' => 'Software Project',
                    'sem' => 8,
                    'credits' => 6.0,
                    'cos' => [
                        'Identify and formulate a real-world computing problem using appropriate engineering methods.',
                        'Analyze requirements and design a technically feasible software solution.',
                        'Implement and integrate software components using appropriate development tools.',
                        'Validate the developed solution using systematic testing and evaluation techniques.',
                        'Document and present the complete project demonstrating professional engineering practice.',
                    ],
                ],
                [
                    'code' => 'CS702',
                    'name' => 'Blockchain Technology',
                    'sem' => 8,
                    'credits' => 3.0,
                    'cos' => [
                        'Explain blockchain architecture, distributed ledgers and consensus mechanisms.',
                        'Analyze cryptographic primitives used for blockchain security.',
                        'Develop basic smart contracts using blockchain programming frameworks.',
                        'Evaluate blockchain applications in different industry domains.',
                        'Design a blockchain-based solution for a suitable real-world problem.',
                    ],
                ],
                [
                    'code' => 'CS703',
                    'name' => 'Generative AI and Natural Language Processing',
                    'sem' => 8,
                    'credits' => 3.0,
                    'cos' => [
                        'Explain fundamental concepts of natural language processing and generative AI.',
                        'Apply text preprocessing and representation techniques to language datasets.',
                        'Analyze transformer-based architectures and language model capabilities.',
                        'Evaluate generative AI systems using appropriate quality, safety and performance measures.',
                        'Develop a prototype NLP or generative AI application for a real-world use case.',
                    ],
                ],
            ];

            foreach ($coursesData as $c) {

                $course = Course::create([
                    'program_id' => $prog->id,
                    'academic_year_id' => $cay->id,
                    'course_code' => $c['code'],
                    'course_name' => $c['name'],
                    'semester' => $c['sem'],
                    'credits' => $c['credits'],
                    'lecture_hours' => 3,
                    'tutorial_hours' => 0,
                    'practical_hours' => 2,
                    'course_type' => 'Core',
                ]);

                // Create 5 meaningful Course Outcomes per course
                foreach ($c['cos'] as $index => $description) {

                    $coNumber = $index + 1;

                    $bloomLevel = match ($coNumber) {
                        1 => 'Understand',
                        2 => 'Apply',
                        3 => 'Apply',
                        4 => 'Analyze',
                        5 => 'Create',
                    };

                    $co = CourseOutcome::create([
                        'course_id' => $course->id,
                        'co_code' => "CO{$coNumber}",
                        'description' => $description,
                        'target_percentage' => 65.0,
                        'target_level' => 2.5,
                        'bloom_level' => $bloomLevel,
                    ]);

                    // CO-PO articulation matrix
                    $mapPos = [
                        'PO1' => 3,
                        'PO2' => 3,
                        'PO3' => 3,
                        'PO5' => 2,
                        'PO9' => 2,
                        'PO12' => 2,
                    ];

                    foreach ($mapPos as $poKey => $lvl) {

                        if (isset($createdPos[$poKey])) {

                            $correlationLevel = max(
                                1,
                                min(
                                    3,
                                    $lvl + (($coNumber % 3) - 1)
                                )
                            );

                            CoPoMapping::create([
                                'course_outcome_id' => $co->id,
                                'target_type' => 'PO',
                                'target_id' => $createdPos[$poKey]->id,
                                'correlation_level' => $correlationLevel,
                                'justification' => "The {$co['co_code']} outcome of {$c['name']} contributes to {$poKey} through course-specific knowledge, analysis and practical application.",
                            ]);
                        }
                    }

                    // CO-PSO articulation
                    foreach ($createdPsos as $psoKey => $psoModel) {

                        CoPoMapping::create([
                            'course_outcome_id' => $co->id,
                            'target_type' => 'PSO',
                            'target_id' => $psoModel->id,
                            'correlation_level' => 2,
                            'justification' => "The {$co['co_code']} outcome supports {$psoKey} through application of computing knowledge and industry-oriented skills.",
                        ]);
                    }

                    // CIE Assessment
                    StudentAssessment::create([
                        'course_id' => $course->id,
                        'course_outcome_id' => $co->id,
                        'academic_year_id' => $cay->id,
                        'assessment_type' => 'Internal_Test_1',
                        'total_students_attempted' => 125,
                        'students_meeting_target' => 98,
                        'percentage_achieved' => 78.4,
                        'attainment_level' => 2.8,
                    ]);

                    // SEE Assessment
                    StudentAssessment::create([
                        'course_id' => $course->id,
                        'course_outcome_id' => $co->id,
                        'academic_year_id' => $cay->id,
                        'assessment_type' => 'End_Sem',
                        'total_students_attempted' => 125,
                        'students_meeting_target' => 92,
                        'percentage_achieved' => 73.6,
                        'attainment_level' => 2.6,
                    ]);

                    // CO Attainment Result
                    AttainmentResult::create([
                        'course_outcome_id' => $co->id,
                        'academic_year_id' => $cay->id,
                        'direct_cie_score' => 2.8,
                        'direct_see_score' => 2.6,
                        'direct_attainment' => 2.64,
                        'indirect_attainment' => 2.55,
                        'final_score' => 2.62,
                    ]);
                }
            }
        }
        // 8. Indirect Assessments (Surveys)
        if (IndirectAssessment::where('program_id', $prog->id)->count() === 0) {
            foreach ($createdPos as $poCode => $poModel) {
                IndirectAssessment::create(['program_id' => $prog->id, 'academic_year_id' => $cay->id, 'survey_type' => 'Graduate_Exit_Survey', 'target_type' => 'PO', 'target_id' => $poModel->id, 'average_rating' => 2.65, 'total_respondents' => 110]);
                IndirectAssessment::create(['program_id' => $prog->id, 'academic_year_id' => $cay->id, 'survey_type' => 'Alumni_Survey', 'target_type' => 'PO', 'target_id' => $poModel->id, 'average_rating' => 2.50, 'total_respondents' => 75]);
                IndirectAssessment::create(['program_id' => $prog->id, 'academic_year_id' => $cay->id, 'survey_type' => 'Employer_Survey', 'target_type' => 'PO', 'target_id' => $poModel->id, 'average_rating' => 2.70, 'total_respondents' => 30]);
            }

            foreach ($createdPsos as $psoCode => $psoModel) {
                IndirectAssessment::create(['program_id' => $prog->id, 'academic_year_id' => $cay->id, 'survey_type' => 'Graduate_Exit_Survey', 'target_type' => 'PSO', 'target_id' => $psoModel->id, 'average_rating' => 2.70, 'total_respondents' => 110]);
                IndirectAssessment::create(['program_id' => $prog->id, 'academic_year_id' => $cay->id, 'survey_type' => 'Alumni_Survey', 'target_type' => 'PSO', 'target_id' => $psoModel->id, 'average_rating' => 2.55, 'total_respondents' => 75]);
                IndirectAssessment::create(['program_id' => $prog->id, 'academic_year_id' => $cay->id, 'survey_type' => 'Employer_Survey', 'target_type' => 'PSO', 'target_id' => $psoModel->id, 'average_rating' => 2.75, 'total_respondents' => 30]);
            }
        }

        // 9. Student Cohorts (Criterion 4)
        if (StudentCohort::where('program_id', $prog->id)->count() === 0) {
            StudentCohort::create(['program_id' => $prog->id, 'batch_name' => '2020-2024 (CAYm1)', 'entry_year' => 2020, 'graduation_year' => 2024, 'cohort_type' => 'CAYm1', 'sanctioned_intake' => 120, 'admitted_first_year' => 120, 'lateral_admitted_second_year' => 12, 'graduated_without_backlog' => 102, 'graduated_in_stipulated_time' => 122]);
            StudentCohort::create(['program_id' => $prog->id, 'batch_name' => '2019-2023 (CAYm2)', 'entry_year' => 2019, 'graduation_year' => 2023, 'cohort_type' => 'CAYm2', 'sanctioned_intake' => 120, 'admitted_first_year' => 118, 'lateral_admitted_second_year' => 10, 'graduated_without_backlog' => 96, 'graduated_in_stipulated_time' => 116]);
            StudentCohort::create(['program_id' => $prog->id, 'batch_name' => '2018-2022 (CAYm3)', 'entry_year' => 2018, 'graduation_year' => 2022, 'cohort_type' => 'CAYm3', 'sanctioned_intake' => 120, 'admitted_first_year' => 120, 'lateral_admitted_second_year' => 12, 'graduated_without_backlog' => 94, 'graduated_in_stipulated_time' => 114]);
        }

        // Placements (Criterion 4.5)
        if (StudentPlacement::where('program_id', $prog->id)->count() === 0) {
            StudentPlacement::create(['program_id' => $prog->id, 'academic_year_id' => $cay->id, 'final_year_students' => 122, 'placed_students' => 96, 'higher_studies_students' => 14, 'entrepreneurs_count' => 3, 'median_salary_lpa' => 7.8]);
            StudentPlacement::create(['program_id' => $prog->id, 'academic_year_id' => $caym1->id, 'final_year_students' => 120, 'placed_students' => 92, 'higher_studies_students' => 12, 'entrepreneurs_count' => 2, 'median_salary_lpa' => 7.2]);
            StudentPlacement::create(['program_id' => $prog->id, 'academic_year_id' => $caym2->id, 'final_year_students' => 118, 'placed_students' => 88, 'higher_studies_students' => 15, 'entrepreneurs_count' => 2, 'median_salary_lpa' => 6.5]);
        }

             // 10. Financial Budgets (Criterion 10)
        // Three-year recurring and non-recurring budget ledger
        if (FinancialBudget::where('program_id', $prog->id)->count() === 0) {

            // ==================== 2024-25 (CFY) ====================
            $cfyBudget = FinancialBudget::create([
                'institution_id' => $inst->id,
                'program_id' => $prog->id,
                'academic_year_id' => $cay->id,
                'financial_year_name' => '2024-25 (CFY)',
                'cfy_type' => 'CFY',
                'total_budget_allocated' => 8500000.00,
                'total_actual_expenditure' => 7950000.00,
                'utilization_percentage' => 93.53,
            ]);

            BudgetItem::create([
                'financial_budget_id' => $cfyBudget->id,
                'category' => 'Recurring',
                'sub_category' => 'Faculty & Staff Salaries',
                'budget_allocated' => 5200000.00,
                'actual_expenditure' => 5180000.00,
            ]);

            BudgetItem::create([
                'financial_budget_id' => $cfyBudget->id,
                'category' => 'Recurring',
                'sub_category' => 'Software Licenses, Cloud & Subscriptions',
                'budget_allocated' => 800000.00,
                'actual_expenditure' => 780000.00,
            ]);

            BudgetItem::create([
                'financial_budget_id' => $cfyBudget->id,
                'category' => 'Recurring',
                'sub_category' => 'R&D, Conferences & Faculty Development',
                'budget_allocated' => 500000.00,
                'actual_expenditure' => 490000.00,
            ]);

            BudgetItem::create([
                'financial_budget_id' => $cfyBudget->id,
                'category' => 'Non-Recurring',
                'sub_category' => 'AI & GPU Lab Infrastructure Upgrade',
                'budget_allocated' => 2000000.00,
                'actual_expenditure' => 1500000.00,
            ]);


            // ==================== 2023-24 (CFYm1) ====================
            $cfym1Budget = FinancialBudget::create([
                'institution_id' => $inst->id,
                'program_id' => $prog->id,
                'academic_year_id' => $caym1->id,
                'financial_year_name' => '2023-24 (CFYm1)',
                'cfy_type' => 'CFYm1',
                'total_budget_allocated' => 7800000.00,
                'total_actual_expenditure' => 7420000.00,
                'utilization_percentage' => 95.13,
            ]);

            BudgetItem::create([
                'financial_budget_id' => $cfym1Budget->id,
                'category' => 'Recurring',
                'sub_category' => 'Faculty & Staff Salaries',
                'budget_allocated' => 4700000.00,
                'actual_expenditure' => 4650000.00,
            ]);

            BudgetItem::create([
                'financial_budget_id' => $cfym1Budget->id,
                'category' => 'Recurring',
                'sub_category' => 'Software Licenses & Cloud Services',
                'budget_allocated' => 700000.00,
                'actual_expenditure' => 660000.00,
            ]);

            BudgetItem::create([
                'financial_budget_id' => $cfym1Budget->id,
                'category' => 'Recurring',
                'sub_category' => 'R&D, Conferences & Faculty Travel',
                'budget_allocated' => 500000.00,
                'actual_expenditure' => 460000.00,
            ]);

            BudgetItem::create([
                'financial_budget_id' => $cfym1Budget->id,
                'category' => 'Non-Recurring',
                'sub_category' => 'IoT & Embedded Systems Lab Setup',
                'budget_allocated' => 1900000.00,
                'actual_expenditure' => 1650000.00,
            ]);


            // ==================== 2022-23 (CFYm2) ====================
            $cfym2Budget = FinancialBudget::create([
                'institution_id' => $inst->id,
                'program_id' => $prog->id,
                'academic_year_id' => $caym2->id,
                'financial_year_name' => '2022-23 (CFYm2)',
                'cfy_type' => 'CFYm2',
                'total_budget_allocated' => 7100000.00,
                'total_actual_expenditure' => 6680000.00,
                'utilization_percentage' => 94.08,
            ]);

            BudgetItem::create([
                'financial_budget_id' => $cfym2Budget->id,
                'category' => 'Recurring',
                'sub_category' => 'Faculty & Staff Salaries',
                'budget_allocated' => 4300000.00,
                'actual_expenditure' => 4220000.00,
            ]);

            BudgetItem::create([
                'financial_budget_id' => $cfym2Budget->id,
                'category' => 'Recurring',
                'sub_category' => 'Software Licenses & IT Services',
                'budget_allocated' => 600000.00,
                'actual_expenditure' => 560000.00,
            ]);

            BudgetItem::create([
                'financial_budget_id' => $cfym2Budget->id,
                'category' => 'Recurring',
                'sub_category' => 'Research, Training & Faculty Travel',
                'budget_allocated' => 400000.00,
                'actual_expenditure' => 350000.00,
            ]);

            BudgetItem::create([
                'financial_budget_id' => $cfym2Budget->id,
                'category' => 'Non-Recurring',
                'sub_category' => 'Computer Laboratory Equipment',
                'budget_allocated' => 1400000.00,
                'actual_expenditure' => 1200000.00,
            ]);

            BudgetItem::create([
                'financial_budget_id' => $cfym2Budget->id,
                'category' => 'Non-Recurring',
                'sub_category' => 'Network & Infrastructure Upgrade',
                'budget_allocated' => 400000.00,
                'actual_expenditure' => 350000.00,
            ]);
        }

        // 11. Master SAR Report
        $sar = SarReport::firstOrCreate(
            ['program_id' => $prog->id, 'academic_year_id' => $cay->id],
            [
                'title' => 'Self-Assessment Report (SAR) for UG Engineering - B.Tech CSE (Tier-II)',
                'current_state' => 'DEPARTMENT_CONSOLIDATION',
                'submitted_by' => 'Dr. S. Narayan (NBA Coordinator)',
                'hod_verified_by' => 'Dr. Arvind Mehra (HOD CSE)',
                'submitted_at' => now()->subDays(5),
                'is_locked' => false,
                'total_claimed_marks' => 842.50,
                'workflow_notes' => 'Faculty mark entries and articulation matrix consolidated. Awaiting institutional review.',
            ]
        );

        $sar->update([
            'current_state' => 'DEPARTMENT_CONSOLIDATION',
            'total_claimed_marks' => 842.50,
        ]);

        // SAR Criteria 1 to 10 records
        $criteriaData = [
            ['num' => 1, 'title' => 'Vision, Mission and Program Educational Objectives', 'max' => 60, 'claimed' => 54.0],
            ['num' => 2, 'title' => 'Program Curriculum and Teaching-Learning Processes', 'max' => 120, 'claimed' => 108.0],
            ['num' => 3, 'title' => 'Course Outcomes and Program Outcomes', 'max' => 120, 'claimed' => 104.8],
            ['num' => 4, 'title' => "Students' Performance and Placement", 'max' => 150, 'claimed' => 130.0],
            ['num' => 5, 'title' => 'Faculty Information and Contributions', 'max' => 200, 'claimed' => 176.5],
            ['num' => 6, 'title' => 'Facilities and Technical Support', 'max' => 80, 'claimed' => 72.0],
            ['num' => 7, 'title' => 'Continuous Improvement', 'max' => 50, 'claimed' => 43.0],
            ['num' => 8, 'title' => 'First Year Academics', 'max' => 50, 'claimed' => 44.0],
            ['num' => 9, 'title' => 'Student Support Systems', 'max' => 50, 'claimed' => 45.0],
            ['num' => 10, 'title' => 'Governance, Institutional Support & Financial Resources', 'max' => 120, 'claimed' => 108.0],
        ];

        foreach ($criteriaData as $c) {
            $summaryObs = null;
            if ($c['num'] === 1) {
                $summaryObs = 'Vision: At TIET, our vision rests on two pillars: (1) A deeply meaningful experience for all stakeholders that shapes lives with wisdom, purpose, and compassion; and (2) Socially impactful research that advances knowledge while solving real challenges of industry, society, and humanity. Together, they embody our commitment to seeking excellence for all. | Mission: TIET exists to educate and empower students while advancing knowledge through research, so that we serve our society with wisdom, compassion, and a commitment to seek excellence.';
            }

            SarCriterion::updateOrCreate(
                ['sar_report_id' => $sar->id, 'criterion_number' => $c['num']],
                [
                    'title' => "Criterion {$c['num']}: {$c['title']}",
                    'max_marks' => $c['max'],
                    'claimed_marks' => $c['claimed'],
                    'status' => 'VERIFIED',
                    'is_verified' => true,
                    'summary_observations' => $summaryObs,
                ]
            );
        }

        // Audit Logs
        SarAuditLog::create([
            'sar_report_id' => $sar->id,
            'user_name' => 'Dr. S. Narayan',
            'user_role' => 'NBA Coordinator',
            'action' => 'STATE_TRANSITION',
            'domain_module' => 'SAR',
            'payload' => ['from' => 'DRAFT', 'to' => 'DEPARTMENT_CONSOLIDATION'],
            'description' => 'Submitted SAR report for Department Consolidation review.',
            'ip_address' => '192.168.1.45',
        ]);
    }
}
