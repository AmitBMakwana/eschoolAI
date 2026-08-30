<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\Student;
use App\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HomeworkController extends Controller
{
    /**
     * List homework assignments.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Homework::with(['schoolClass', 'section', 'subject', 'teacher:id,name']);

        if ($request->has('class_id')) {
            $query->where('class_id', $request->input('class_id'));
        }

        if ($user->isStudent()) {
            $student = Student::where('user_id', $user->id)->first();
            if ($student) {
                $query->where('class_id', $student->class_id)
                      ->where('section_id', $student->section_id);
            }
        }

        $homeworkList = $query->orderBy('due_date', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $homeworkList->items(),
            'meta' => [
                'current_page' => $homeworkList->currentPage(),
                'total' => $homeworkList->total(),
            ],
        ]);
    }

    /**
     * Create homework assignment (Teacher / Admin).
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'class_id' => 'required|exists:school_classes,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'due_date' => 'required|date|after_or_equal:today',
            'attachment_url' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $homework = Homework::create([
            'class_id' => $request->input('class_id'),
            'section_id' => $request->input('section_id'),
            'subject_id' => $request->input('subject_id'),
            'assigned_by_user_id' => $request->user()->id,
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'due_date' => $request->input('due_date'),
            'attachment_url' => $request->input('attachment_url'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Homework assigned successfully.',
            'data' => $homework->load(['schoolClass', 'section', 'subject']),
        ], 201);
    }

    /**
     * Submit homework (Student only).
     */
    public function submit(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $student = Student::where('user_id', $user->id)->first();
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student record not found.',
            ], 404);
        }

        $homework = Homework::find($id);
        if (!$homework) {
            return response()->json([
                'success' => false,
                'message' => 'Homework not found.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'submission_text' => 'nullable|string',
            'attachment_url' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $submission = HomeworkSubmission::updateOrCreate(
            [
                'homework_id' => $homework->id,
                'student_id' => $student->id,
            ],
            [
                'submission_text' => $request->input('submission_text'),
                'attachment_url' => $request->input('attachment_url'),
                'submitted_at' => now(),
                'status' => 'submitted',
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Homework submitted successfully.',
            'data' => $submission,
        ]);
    }

    /**
     * Grade / review homework submission (Teacher).
     */
    public function review(Request $request, int $submissionId): JsonResponse
    {
        $submission = HomeworkSubmission::findOrFail($submissionId);

        $validator = Validator::make($request->all(), [
            'marks' => 'required|numeric|min:0|max:100',
            'teacher_feedback' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $submission->update([
            'marks' => $request->input('marks'),
            'teacher_feedback' => $request->input('teacher_feedback'),
            'status' => 'reviewed',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Submission reviewed and graded.',
            'data' => $submission,
        ]);
    }

    /**
     * Delete an assignment.
     */
    public function destroy(int $id): JsonResponse
    {
        $hw = Homework::findOrFail($id);
        $hw->delete();

        return response()->json([
            'success' => true,
            'message' => 'Assignment deleted successfully.',
        ]);
    }
}
