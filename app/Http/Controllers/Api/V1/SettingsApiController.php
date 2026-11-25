<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\SystemSetting;
use App\Models\User;
use App\Domains\Academic\Models\Institution;
use App\Domains\Curriculum\Models\Course;
use App\Domains\Curriculum\Models\CourseOutcome;
use App\Domains\Curriculum\Models\CoPoMapping;
use App\Domains\Attainment\Models\StudentAssessment;
use App\Domains\Attainment\Models\IndirectAssessment;
use App\Domains\Attainment\Models\AttainmentResult;
use App\Domains\Student\Models\StudentCohort;
use App\Domains\Student\Models\StudentPlacement;
use App\Domains\Faculty\Models\FacultyAcademicYear;
use App\Domains\Faculty\Models\FacultyFdp;
use App\Domains\Faculty\Models\FacultyPublication;
use App\Domains\Finance\Models\FinancialBudget;
use App\Domains\Finance\Models\BudgetItem;
use App\Domains\SAR\Models\SarReport;
use App\Domains\SAR\Models\SarCriterion;
use App\Domains\SAR\Models\SarAuditLog;

class SettingsApiController extends Controller
{
    /**
     * Get all system settings and branding
     */
    public function index(): JsonResponse
    {
        $settings = SystemSetting::getAllSettings();

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    /**
     * Update system settings and branding
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'app_name' => 'nullable|string|max:150',
            'app_short_name' => 'nullable|string|max:50',
            'app_tagline' => 'nullable|string|max:250',
            'institution_name' => 'nullable|string|max:200',
            'institution_code' => 'nullable|string|max:50',
            'head_of_institution' => 'nullable|string|max:150',
            'institution_address' => 'nullable|string|max:300',
            'institution_website' => 'nullable|string|max:200',
            'contact_email' => 'nullable|email|max:100',
            'contact_phone' => 'nullable|string|max:50',
            'nba_tier' => 'nullable|string|in:Tier-I,Tier-II',
            'current_academic_year' => 'nullable|string|max:50',
            'theme_color' => 'nullable|string|max:20',
            'login_logo' => 'nullable|string',
            'sidebar_logo' => 'nullable|string',
            'favicon' => 'nullable|string',
            'report_header_logo' => 'nullable|string',
        ]);

        foreach ($validated as $key => $val) {
            $group = in_array($key, ['login_logo', 'sidebar_logo', 'favicon', 'report_header_logo', 'theme_color'])
                ? 'branding'
                : (in_array($key, ['institution_name', 'institution_code', 'head_of_institution', 'institution_address', 'institution_website']) ? 'institution' : 'general');

            SystemSetting::set($key, (string)($val ?? ''), $group);
        }

        // Also update Institution model if name/address provided
        if (!empty($validated['institution_name'])) {
            $inst = Institution::first();
            if ($inst) {
                $inst->update([
                    'name' => $validated['institution_name'],
                    'code' => $validated['institution_code'] ?? $inst->code,
                    'head_of_institution' => $validated['head_of_institution'] ?? $inst->head_of_institution,
                    'address' => $validated['institution_address'] ?? $inst->address,
                    'website' => $validated['institution_website'] ?? $inst->website,
                ]);
            }
        }

        SarAuditLog::create([
            'user_name' => 'Super Admin',
            'user_role' => 'Super Admin',
            'action' => 'SETTINGS_UPDATED',
            'domain_module' => 'Settings',
            'description' => 'Updated system settings, institution details, and branding logos.',
            'ip_address' => $request->ip() ?? '127.0.0.1',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'System settings and branding updated successfully.',
            'data' => SystemSetting::getAllSettings(),
        ]);
    }

    /**
     * Upload an image (Logo / Favicon) and return URL
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg,ico,webp|max:3072',
            'type' => 'required|string|in:login_logo,sidebar_logo,favicon,report_header_logo',
        ]);

        $file = $request->file('image');
        $type = $request->input('type');

        // Store into public uploads directory
        $fileName = $type . '_' . time() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('uploads/branding'), $fileName);
        $url = asset('uploads/branding/' . $fileName);

        // Update setting immediately
        SystemSetting::set($type, $url, 'branding', 'image');

        return response()->json([
            'success' => true,
            'message' => 'Image uploaded successfully.',
            'url' => $url,
            'type' => $type,
        ]);
    }

    /**
     * Truncate/Reset Sample Academic & Score Data (Requires Super Admin Password)
     * Preserves Users & Login Accounts!
     */
    public function resetAcademicData(Request $request): JsonResponse
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        // Find super admin account to verify password
        $admin = Auth::user() ?? User::where('role', 'Super Admin')->first() ?? User::first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Super Admin password. System data reset authorization denied.',
            ], 403);
        }

        DB::beginTransaction();
        try {
            // Disable foreign key constraints during purge
            \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();

            // 1. Delete Curriculum & Matrix
            CoPoMapping::query()->delete();
            CourseOutcome::query()->delete();
            Course::query()->delete();

            // 2. Delete Attainment & Assessments
            StudentAssessment::query()->delete();
            IndirectAssessment::query()->delete();
            AttainmentResult::query()->delete();

            // 3. Delete Student Cohorts & Placements
            StudentCohort::query()->delete();
            StudentPlacement::query()->delete();

            // 4. Delete Faculty FDPs & Publications
            FacultyFdp::query()->delete();
            FacultyPublication::query()->delete();
            FacultyAcademicYear::query()->delete();

            // 5. Delete Financial Budgets
            BudgetItem::query()->delete();
            FinancialBudget::query()->delete();

            // 6. Reset SAR Reports to 0 / Draft
            SarReport::query()->update([
                'current_state' => 'DRAFT',
                'total_claimed_marks' => 0.0,
            ]);

            SarCriterion::query()->update([
                'claimed_marks' => 0.0,
            ]);

            \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

            // Audit log the purge
            SarAuditLog::create([
                'user_name' => $admin->name,
                'user_role' => 'Super Admin',
                'action' => 'SYSTEM_DATA_PURGED',
                'domain_module' => 'System',
                'description' => "Super Admin {$admin->name} truncated sample academic and evaluation data for a clean slate. User accounts were preserved.",
                'ip_address' => $request->ip() ?? '127.0.0.1',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'All sample courses, assessment marks, student batches, and evaluation scores have been successfully cleared. Staff and user accounts remain fully intact for starting from scratch.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

            return response()->json([
                'success' => false,
                'message' => 'System reset failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Re-seed/Restore Sample Evaluation & Demonstration Data
     */
    public function restoreSampleData(Request $request): JsonResponse
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $admin = Auth::user() ?? User::where('role', 'Super Admin')->first() ?? User::first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Super Admin password. Action denied.',
            ], 403);
        }

        try {
            \Illuminate\Support\Facades\Artisan::call('db:seed', [
                '--class' => 'NbaSarDatabaseSeeder',
                '--force' => true,
            ]);

            SarAuditLog::create([
                'user_name' => $admin->name,
                'user_role' => 'Super Admin',
                'action' => 'SAMPLE_DATA_RESTORED',
                'domain_module' => 'System',
                'description' => "Super Admin {$admin->name} re-seeded and restored demo academic courses, outcomes, and assessment records.",
                'ip_address' => $request->ip() ?? '127.0.0.1',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sample NBA courses, CO-PO articulation matrices, mark entries, and evaluation data restored successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore sample data: ' . $e->getMessage(),
            ], 500);
        }
    }
}
