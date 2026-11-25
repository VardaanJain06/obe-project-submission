<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\NbaSarDatabaseSeeder;
use App\Domains\Academic\Models\Program;
use App\Domains\Academic\Models\AcademicYear;
use App\Domains\Faculty\Services\SfrCalculatorService;
use App\Domains\Faculty\Services\FqiCalculatorService;
use App\Domains\Faculty\Services\CadreRatioCalculatorService;
use App\Domains\Attainment\Services\AttainmentEngine;
use App\Domains\SAR\Models\SarReport;
use App\Domains\SAR\StateMachine\SarStateMachine;

class NbaSarCalculationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(NbaSarDatabaseSeeder::class);
    }

    public function test_sfr_calculator_engine_computes_accurate_ratio_and_marks(): void
    {
        $program = Program::first();
        $service = new SfrCalculatorService();
        $result = $service->calculate($program);

        $this->assertNotNull($result);
        $this->assertGreaterThan(0, $result['avg_students_N']);
        $this->assertGreaterThan(0, $result['avg_faculty_F']);
        $this->assertGreaterThan(0, $result['avg_sfr']);
        $this->assertLessThanOrEqual(20.0, $result['claimed_marks']);
    }

    public function test_fqi_calculator_engine_computes_qualification_index(): void
    {
        $program = Program::first();
        $service = new FqiCalculatorService();
        $result = $service->calculate($program);

        $this->assertNotNull($result);
        $this->assertGreaterThan(0, $result['avg_fqi']);
        $this->assertLessThanOrEqual(25.0, $result['claimed_marks']);
    }

    public function test_cadre_ratio_calculator_engine_evaluates_cadre_proportions(): void
    {
        $program = Program::first();
        $service = new CadreRatioCalculatorService();
        $result = $service->calculate($program);

        $this->assertNotNull($result);
        $this->assertGreaterThan(0, $result['claimed_marks']);
        $this->assertLessThanOrEqual(25.0, $result['claimed_marks']);
    }

    public function test_attainment_engine_combines_direct_and_indirect_scores(): void
    {
        $program = Program::first();
        $engine = new AttainmentEngine();
        $result = $engine->computeFinalProgramAttainment($program);

        $this->assertNotEmpty($result['po_attainments']);
        $this->assertGreaterThan(0, $result['overall_po_average']);
        $this->assertLessThanOrEqual(3.0, $result['overall_po_average']);

        // Check PO1 combined formula: 0.8 * Direct + 0.2 * Indirect
        $po1 = $result['po_attainments']['PO1'];
        $expected = round((0.80 * $po1['direct_attainment']) + (0.20 * $po1['indirect_attainment']), 2);
        $this->assertEquals($expected, $po1['final_attainment']);
    }

    public function test_sar_state_machine_transition_and_audit_logging(): void
    {
        $report = SarReport::first();
        $report->current_state = SarStateMachine::STATE_DRAFT;
        $report->save();

        $stateMachine = new SarStateMachine();
        $updated = $stateMachine->transition(
            $report,
            SarStateMachine::STATE_DEPARTMENT,
            'Dr. Arvind Mehra',
            'HOD',
            'Department verification complete'
        );

        $this->assertEquals(SarStateMachine::STATE_DEPARTMENT, $updated->current_state);
        $this->assertDatabaseHas('sar_audit_logs', [
            'sar_report_id' => $report->id,
            'action' => 'STATE_TRANSITION',
            'user_role' => 'HOD',
        ]);
    }

    public function test_dashboard_api_returns_success_json_with_all_criteria(): void
    {
        $response = $this->getJson('/api/v1/nba/dashboard');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'report_id',
                'report_title',
                'total_claimed_marks',
                'total_max_marks',
                'total_percentage',
                'criteria',
                'live_engines' => [
                    'sfr',
                    'fqi',
                    'cadre',
                    'attainment',
                    'success_rate',
                    'placement',
                    'budget',
                ],
            ],
        ]);
    }

    public function test_sfr_simulator_api_endpoint(): void
    {
        $response = $this->postJson('/api/v1/nba/sfr-simulate', [
            'ugt1' => 120,
            'ugt2' => 120,
            'ugt3' => 120,
            'ugt4' => 120,
            'faculty_count' => 32,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'total_students' => 480,
                'faculty_count' => 32,
                'simulated_sfr' => 15.0,
                'claimed_marks' => 20.0,
            ],
        ]);
    }

    public function test_fqi_simulator_api_endpoint(): void
    {
        $response = $this->postJson('/api/v1/nba/fqi-simulate', [
            'phd_faculty_x' => 10,
            'mtech_faculty_y' => 15,
            'total_students' => 500,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'phd_faculty_x',
                'mtech_faculty_y',
                'total_students',
                'required_faculty_rf',
                'raw_fqi',
                'claimed_score',
            ],
        ]);
    }

    public function test_super_admin_can_login_and_fetch_current_profile(): void
    {
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@nba-obe.edu',
            'password' => 'Admin@123',
        ]);

        $loginResponse->assertStatus(200);
        $loginResponse->assertJson([
            'success' => true,
            'user' => [
                'email' => 'admin@nba-obe.edu',
                'role' => 'Super Admin',
            ],
        ]);

        $meResponse = $this->getJson('/api/v1/auth/me');
        $meResponse->assertStatus(200);
        $meResponse->assertJson([
            'success' => true,
            'authenticated' => true,
        ]);
    }

    public function test_super_admin_can_create_new_staff_member(): void
    {
        $response = $this->postJson('/api/v1/staff', [
            'name' => 'Dr. Vikramaditya Sen',
            'email' => 'vikram@niet-tech.edu.in',
            'password' => 'SecurePass@123',
            'role' => 'Faculty',
            'designation' => 'Assistant Professor',
            'highest_degree' => 'Ph.D. Computer Science',
            'teaching_experience_years' => 5,
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'user' => [
                'email' => 'vikram@niet-tech.edu.in',
                'role' => 'Faculty',
            ],
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'vikram@niet-tech.edu.in',
            'role' => 'Faculty',
        ]);
    }

    public function test_super_admin_can_toggle_staff_active_status(): void
    {
        $user = \App\Models\User::where('email', 'faculty@niet-tech.edu.in')->first();
        $this->assertTrue($user->is_active);

        $response = $this->postJson("/api/v1/staff/{$user->id}/toggle-status");
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_active' => false,
        ]);
    }

    public function test_super_admin_can_update_system_settings_and_branding(): void
    {
        $response = $this->postJson('/api/v1/settings', [
            'app_name' => 'Apex OBE Platform',
            'app_short_name' => 'APEX-NBA',
            'institution_name' => 'Apex Engineering College',
            'institution_code' => 'AEC-001',
            'login_logo' => 'https://example.com/logo.png',
            'favicon' => 'https://example.com/favicon.ico',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'app_name' => 'Apex OBE Platform',
                'app_short_name' => 'APEX-NBA',
                'institution_name' => 'Apex Engineering College',
                'login_logo' => 'https://example.com/logo.png',
                'favicon' => 'https://example.com/favicon.ico',
            ],
        ]);

        $getResponse = $this->getJson('/api/v1/settings');
        $getResponse->assertStatus(200);
        $getResponse->assertJson([
            'success' => true,
            'data' => [
                'app_name' => 'Apex OBE Platform',
            ],
        ]);
    }

    public function test_faculty_can_create_course_and_manage_outcomes(): void
    {
        $program = Program::first();

        $response = $this->postJson('/api/v1/courses', [
            'program_id' => $program->id,
            'course_code' => 'CS702',
            'course_name' => 'Deep Learning & Neural Networks',
            'semester' => 7,
            'credits' => 4.0,
            'lecture_hours' => 3,
            'practical_hours' => 2,
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'data' => [
                'course_code' => 'CS702',
                'course_name' => 'Deep Learning & Neural Networks',
            ],
        ]);

        $courseId = $response->json('data.id');

        // Add additional CO
        $coResponse = $this->postJson("/api/v1/courses/{$courseId}/co", [
            'co_code' => 'CO6',
            'description' => 'Deploy generative models on cloud clusters.',
            'target_percentage' => 70.0,
            'target_level' => 2.8,
            'bloom_level' => 'Create',
        ]);

        $coResponse->assertStatus(201);
        $coResponse->assertJson([
            'success' => true,
            'data' => [
                'co_code' => 'CO6',
                'bloom_level' => 'Create',
            ],
        ]);
    }

    public function test_faculty_can_record_assessment_marks_and_compute_attainment(): void
    {
        $course = \App\Domains\Curriculum\Models\Course::first();
        $co = $course->courseOutcomes()->first();

        $response = $this->postJson('/api/v1/attainment/record-marks', [
            'course_id' => $course->id,
            'course_outcome_id' => $co->id,
            'assessment_type' => 'Internal_Test_1',
            'total_students_attempted' => 100,
            'students_meeting_target' => 85,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'assessment' => [
                    'percentage_achieved' => 85.0,
                    'attainment_level' => 3.0,
                ],
            ],
        ]);
    }

    public function test_super_admin_cannot_reset_system_data_with_wrong_password(): void
    {
        $response = $this->postJson('/api/v1/settings/reset-academic-data', [
            'password' => 'WrongPassword@999',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_super_admin_can_reset_academic_and_score_data_with_valid_password(): void
    {
        // 1. Check initial data exists
        $this->assertGreaterThan(0, \App\Domains\Curriculum\Models\Course::count());
        $userCountBefore = \App\Models\User::count();
        $this->assertGreaterThan(0, $userCountBefore);

        // 2. Perform authorized reset
        $response = $this->postJson('/api/v1/settings/reset-academic-data', [
            'password' => 'Admin@123',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        // 3. Verify sample academic score data is cleared
        $this->assertEquals(0, \App\Domains\Curriculum\Models\Course::count());
        $this->assertEquals(0, \App\Domains\Curriculum\Models\CourseOutcome::count());
        $this->assertEquals(0, \App\Domains\Curriculum\Models\CoPoMapping::count());
        $this->assertEquals(0, \App\Domains\Attainment\Models\StudentAssessment::count());

        // 4. Verify users & login accounts remain 100% intact!
        $this->assertEquals($userCountBefore, \App\Models\User::count());
        $this->assertDatabaseHas('users', ['email' => 'admin@nba-obe.edu']);
        $this->assertDatabaseHas('users', ['email' => 'hod.cse@niet-tech.edu.in']);
        $this->assertDatabaseHas('users', ['email' => 'faculty@niet-tech.edu.in']);
    }

    public function test_super_admin_can_restore_sample_demo_data_with_valid_password(): void
    {
        // 1. Reset first
        $this->postJson('/api/v1/settings/reset-academic-data', [
            'password' => 'Admin@123',
        ]);
        $this->assertEquals(0, \App\Domains\Curriculum\Models\Course::count());

        // 2. Restore sample data
        $response = $this->postJson('/api/v1/settings/restore-sample-data', [
            'password' => 'Admin@123',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        // 3. Verify demo courses, COs, and assessments are repopulated
        $this->assertGreaterThan(0, \App\Domains\Curriculum\Models\Course::count());
        $this->assertGreaterThan(0, \App\Domains\Curriculum\Models\CourseOutcome::count());
        $this->assertGreaterThan(0, \App\Domains\Curriculum\Models\CoPoMapping::count());
        $this->assertGreaterThan(0, \App\Domains\Attainment\Models\StudentAssessment::count());
        $this->assertGreaterThan(0, \App\Domains\SAR\Models\SarCriterion::count());
    }
}
