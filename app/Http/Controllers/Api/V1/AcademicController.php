<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Timetable;
use App\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
     * Create a new class (Admin / Principal only).
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
            'sections' => 'nullable|array', // e.g. ["A", "B"]
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $schoolClass = SchoolClass::create([
            'name' => $request->input('name'),
            'code' => $request->input('code'),
            'order_index' => $request->input('order_index', 0),
        ]);

        // Auto-create sections if provided
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
}
