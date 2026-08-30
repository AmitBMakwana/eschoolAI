<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Subject;
use App\Models\TeacherAllocation;
use App\Models\Timetable;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AcademicController extends Controller
{
    /**
     * List all classes and sections for current school.
     */
    public function classes(Request $request): JsonResponse
    {
        $classes = SchoolClass::with(['sections.classTeacher', 'subjects'])
            ->orderBy('order_index')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $classes,
        ]);
    }

    /**
     * Create a new class.
     */
    public function storeClass(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->isSchoolAdmin() && !$user->isPrincipal() && !$user->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:20',
            'order_index' => 'nullable|integer',
            'sections' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $schoolClass = SchoolClass::create([
            'name' => $request->input('name'),
            'code' => $request->input('code'),
            'order_index' => $request->input('order_index', 0),
        ]);

        $sectionNames = $request->input('sections', ['A']);
        foreach ($sectionNames as $secName) {
            Section::create([
                'class_id' => $schoolClass->id,
                'name' => is_array($secName) ? $secName['name'] : $secName,
                'capacity' => 40,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Class created successfully.',
            'data' => $schoolClass->load('sections'),
        ], 201);
    }

    /**
     * List sections.
     */
    public function sections(Request $request): JsonResponse
    {
        $query = Section::with(['schoolClass', 'classTeacher']);
        if ($request->has('class_id')) {
            $query->where('class_id', $request->input('class_id'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->get(),
        ]);
    }

    /**
     * Create section.
     */
    public function storeSection(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'class_id' => 'required|exists:school_classes,id',
            'name' => 'required|string|max:50',
            'capacity' => 'nullable|integer|min:1',
            'class_teacher_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $section = Section::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Section created successfully.',
            'data' => $section->load(['schoolClass', 'classTeacher']),
        ], 201);
    }

    /**
     * List teachers in tenant.
     */
    public function teachers(Request $request): JsonResponse
    {
        $teacherRole = Role::where('slug', Role::TEACHER)->first();
        $teachers = User::with('role')
            ->where('tenant_id', TenantContext::get()?->id)
            ->where(function ($q) use ($teacherRole) {
                if ($teacherRole) {
                    $q->where('role_id', $teacherRole->id);
                }
            })
            ->get();

        return response()->json([
            'success' => true,
            'data' => $teachers,
        ]);
    }

    /**
     * Create / Invite a Teacher.
     */
    public function storeTeacher(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150',
            'phone' => 'nullable|string|max:30',
            'password' => 'nullable|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $teacherRole = Role::where('slug', Role::TEACHER)->first();
        $tenantId = TenantContext::get()?->id;

        $user = User::create([
            'tenant_id' => $tenantId,
            'role_id' => $teacherRole?->id,
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'password' => Hash::make($request->input('password', 'password')),
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Teacher profile created successfully.',
            'data' => $user->load('role'),
        ], 201);
    }

    /**
     * List subjects.
     */
    public function subjects(Request $request): JsonResponse
    {
        $query = Subject::with('schoolClass');

        if ($request->has('class_id')) {
            $query->where('class_id', $request->input('class_id'));
        }

        $subjects = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $subjects,
        ]);
    }

    /**
     * Create a subject.
     */
    public function storeSubject(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:20',
            'class_id' => 'nullable|exists:school_classes,id',
            'type' => 'nullable|in:theory,practical,both',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $subject = Subject::create([
            'name' => $request->input('name'),
            'code' => $request->input('code'),
            'class_id' => $request->input('class_id'),
            'type' => $request->input('type', 'theory'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subject created successfully.',
            'data' => $subject,
        ], 201);
    }

    /**
     * List Teacher Subject Allocations.
     */
    public function teacherAllocations(Request $request): JsonResponse
    {
        $allocations = TeacherAllocation::with(['teacher', 'schoolClass', 'section', 'subject'])->get();

        return response()->json([
            'success' => true,
            'data' => $allocations,
        ]);
    }

    /**
     * Store Teacher Subject Allocation.
     */
    public function storeTeacherAllocation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'teacher_user_id' => 'required|exists:users,id',
            'class_id' => 'required|exists:school_classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $allocation = TeacherAllocation::updateOrCreate(
            [
                'teacher_user_id' => $request->input('teacher_user_id'),
                'class_id' => $request->input('class_id'),
                'section_id' => $request->input('section_id'),
                'subject_id' => $request->input('subject_id'),
            ],
            $request->all()
        );

        return response()->json([
            'success' => true,
            'message' => 'Subject assigned to teacher successfully.',
            'data' => $allocation->load(['teacher', 'schoolClass', 'section', 'subject']),
        ], 201);
    }

    /**
     * Query timetable matrix for a class and section.
     */
    public function timetables(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'class_id' => 'required|exists:school_classes,id',
            'section_id' => 'required|exists:sections,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $timetables = Timetable::with(['subject', 'teacher'])
            ->where('class_id', $request->input('class_id'))
            ->where('section_id', $request->input('section_id'))
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $timetables,
        ]);
    }

    /**
     * Schedule a timetable period.
     */
    public function storeTimetable(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'class_id' => 'required|exists:school_classes,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_user_id' => 'nullable|exists:users,id',
            'day_of_week' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'room_number' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $slot = Timetable::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Timetable period scheduled successfully.',
            'data' => $slot->load(['subject', 'teacher']),
        ], 201);
    }

    /**
     * Delete a timetable period.
     */
    public function destroyTimetable(int $id): JsonResponse
    {
        $slot = Timetable::findOrFail($id);
        $slot->delete();

        return response()->json([
            'success' => true,
            'message' => 'Timetable period deleted successfully.',
        ]);
    }

    /**
     * Delete a teacher allocation.
     */
    public function destroyTeacherAllocation(int $id): JsonResponse
    {
        $allocation = TeacherAllocation::findOrFail($id);
        $allocation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Teacher allocation deleted successfully.',
        ]);
    }

    /**
     * Delete a school class.
     */
    public function destroyClass(int $id): JsonResponse
    {
        $class = SchoolClass::findOrFail($id);
        $class->delete();

        return response()->json([
            'success' => true,
            'message' => 'Class deleted successfully.',
        ]);
    }

    /**
     * Delete a teacher.
     */
    public function destroyTeacher(int $id): JsonResponse
    {
        $teacher = User::findOrFail($id);
        $teacher->delete();

        return response()->json([
            'success' => true,
            'message' => 'Teacher removed successfully.',
        ]);
    }

    /**
     * Delete a subject.
     */
    public function destroySubject(int $id): JsonResponse
    {
        $subject = Subject::findOrFail($id);
        $subject->delete();

        return response()->json([
            'success' => true,
            'message' => 'Subject removed successfully.',
        ]);
    }

    /**
     * List Roles and Permissions Matrix.
     */
    public function rolesAndPermissions(Request $request): JsonResponse
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();

        return response()->json([
            'success' => true,
            'data' => [
                'roles' => $roles,
                'permissions' => $permissions,
            ],
        ]);
    }
}
