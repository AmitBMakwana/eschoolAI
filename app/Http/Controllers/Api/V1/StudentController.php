<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ParentProfile;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    /**
     * List students with filtering, search, and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Student::with(['user', 'schoolClass', 'section', 'parent.user']);

        if ($request->has('class_id')) {
            $query->where('class_id', $request->input('class_id'));
        }

        if ($request->has('section_id')) {
            $query->where('section_id', $request->input('section_id'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('admission_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $students = $query->orderBy('admission_number')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $students->items(),
            'meta' => [
                'current_page' => $students->currentPage(),
                'total' => $students->total(),
                'last_page' => $students->lastPage(),
            ],
        ]);
    }

    /**
     * Enroll a new student (creates user login and student record).
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'password' => 'nullable|string|min:6',
            'class_id' => 'required|exists:school_classes,id',
            'section_id' => 'required|exists:sections,id',
            'admission_number' => 'nullable|string|unique:students,admission_number',
            'roll_number' => 'nullable|string',
            'dob' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'parent_name' => 'nullable|string',
            'parent_email' => 'nullable|email',
            'parent_phone' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $studentRole = Role::where('slug', Role::STUDENT)->firstOrFail();
        $parentRole = Role::where('slug', Role::PARENT)->firstOrFail();

        $student = DB::transaction(function () use ($request, $studentRole, $parentRole) {
            // 1. Create Student User
            $user = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password', 'student123')),
                'role_id' => $studentRole->id,
                'status' => 'active',
            ]);

            // 2. Create or Link Parent if provided
            $parentId = null;
            if ($request->filled('parent_email')) {
                $parentUser = User::firstOrCreate(
                    ['email' => $request->input('parent_email')],
                    [
                        'name' => $request->input('parent_name', 'Guardian'),
                        'password' => Hash::make('parent123'),
                        'role_id' => $parentRole->id,
                        'phone' => $request->input('parent_phone'),
                        'status' => 'active',
                    ]
                );

                $parentProfile = ParentProfile::firstOrCreate(
                    ['user_id' => $parentUser->id],
                    ['relationship' => 'Parent']
                );
                $parentId = $parentProfile->id;
            }

            // 3. Create Student Record
            $admNumber = $request->input('admission_number') ?: 'ADM-' . date('Y') . '-' . rand(1000, 9999);

            return Student::create([
                'user_id' => $user->id,
                'class_id' => $request->input('class_id'),
                'section_id' => $request->input('section_id'),
                'parent_id' => $parentId,
                'admission_number' => $admNumber,
                'roll_number' => $request->input('roll_number'),
                'dob' => $request->input('dob'),
                'gender' => $request->input('gender'),
                'enrollment_date' => now(),
                'status' => 'active',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Student enrolled successfully.',
            'data' => $student->load(['user', 'schoolClass', 'section', 'parent.user']),
        ], 201);
    }

    /**
     * Show single student profile and attendance stats.
     */
    public function show(int $id): JsonResponse
    {
        $student = Student::with(['user', 'schoolClass', 'section', 'parent.user'])->findOrFail($id);

        // Attendance stats
        $totalDays = Attendance::where('student_id', $student->id)->count();
        $presentDays = Attendance::where('student_id', $student->id)->where('status', 'present')->count();
        $attendanceRate = $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 1) : 100;

        return response()->json([
            'success' => true,
            'data' => [
                'student' => $student,
                'stats' => [
                    'total_attendance_days' => $totalDays,
                    'present_days' => $presentDays,
                    'attendance_percentage' => $attendanceRate,
                ],
            ],
        ]);
    }

    /**
     * Update an existing student record.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $student = Student::with('user')->findOrFail($id);

        if ($request->filled('name') && $student->user) {
            $student->user->update(['name' => $request->input('name')]);
        }
        if ($request->filled('class_id')) {
            $student->class_id = $request->input('class_id');
        }
        if ($request->filled('section_id')) {
            $student->section_id = $request->input('section_id');
        }
        if ($request->filled('roll_number')) {
            $student->roll_number = $request->input('roll_number');
        }
        if ($request->filled('status')) {
            $student->status = $request->input('status');
        }
        $student->save();

        return response()->json([
            'success' => true,
            'message' => 'Student record updated successfully.',
            'data' => $student->load(['user', 'schoolClass', 'section']),
        ]);
    }

    /**
     * Delete / Archive a student record.
     */
    public function destroy(int $id): JsonResponse
    {
        $student = Student::findOrFail($id);
        $student->delete();

        return response()->json([
            'success' => true,
            'message' => 'Student record deleted successfully.',
        ]);
    }
}
