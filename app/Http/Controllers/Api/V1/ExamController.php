<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamMark;
use App\Models\ExamTerm;
use App\Models\GradingScale;
use App\Models\Student;
use App\Services\AuditLogService;
use App\Services\GradingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExamController extends Controller
{
    public function __construct(
        protected GradingService $gradingService,
        protected AuditLogService $auditLogService
    ) {}

    /**
     * List exam terms.
     */
    public function terms(Request $request): JsonResponse
    {
        $terms = ExamTerm::withCount('exams')->orderBy('start_date', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $terms,
        ]);
    }

    /**
     * Create exam term (Admin / Principal).
     */
    public function storeTerm(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:150',
            'academic_year' => 'nullable|string|max:20',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $term = ExamTerm::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Exam term created successfully.',
            'data' => $term,
        ], 201);
    }

    /**
     * List exams with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Exam::with(['term', 'schoolClass', 'subject']);

        if ($request->has('exam_term_id')) {
            $query->where('exam_term_id', $request->input('exam_term_id'));
        }

        if ($request->has('class_id')) {
            $query->where('class_id', $request->input('class_id'));
        }

        $exams = $query->orderBy('exam_date')->get();

        return response()->json([
            'success' => true,
            'data' => $exams,
        ]);
    }

    /**
     * Schedule an exam.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'exam_term_id' => 'required|exists:exam_terms,id',
            'class_id' => 'required|exists:school_classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:200',
            'exam_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'total_marks' => 'required|numeric|min:1',
            'passing_marks' => 'required|numeric|min:0|lte:total_marks',
            'room_number' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $exam = Exam::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Exam scheduled successfully.',
            'data' => $exam->load(['term', 'schoolClass', 'subject']),
        ], 201);
    }

    /**
     * List marks ledger for an exam.
     */
    public function marks(int $examId): JsonResponse
    {
        $exam = Exam::with(['schoolClass.sections', 'subject'])->findOrFail($examId);
        $students = Student::with('user')
            ->where('class_id', $exam->class_id)
            ->get();

        $marksMap = ExamMark::where('exam_id', $exam->id)->get()->keyBy('student_id');

        $ledger = $students->map(function ($student) use ($marksMap) {
            $rec = $marksMap->get($student->id);
            return [
                'student_id' => $student->id,
                'admission_number' => $student->admission_number,
                'name' => $student->user?->name,
                'roll_number' => $student->roll_number,
                'marks_obtained' => $rec ? $rec->marks_obtained : null,
                'is_absent' => $rec ? (bool) $rec->is_absent : false,
                'grade' => $rec?->grade,
                'grade_point' => $rec?->grade_point,
                'remarks' => $rec?->remarks,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'exam' => $exam,
                'ledger' => $ledger,
            ],
        ]);
    }

    /**
     * Bulk save marks ledger for an exam.
     */
    public function storeBulkMarks(Request $request, int $examId): JsonResponse
    {
        $exam = Exam::findOrFail($examId);

        $validator = Validator::make($request->all(), [
            'records' => 'required|array|min:1',
            'records.*.student_id' => 'required|exists:students,id',
            'records.*.marks_obtained' => 'nullable|numeric|min:0|max:' . $exam->total_marks,
            'records.*.is_absent' => 'nullable|boolean',
            'records.*.remarks' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $updatedMarks = $this->gradingService->recordBulkMarks($exam, $request->input('records'), $user->id);

        $this->auditLogService->log(
            event: 'exam.marks_recorded',
            auditable: $exam,
            newValues: ['count' => count($request->input('records')), 'exam_title' => $exam->title],
            request: $request
        );

        return response()->json([
            'success' => true,
            'message' => 'Exam marks recorded and graded successfully.',
            'data' => $updatedMarks,
        ]);
    }

    /**
     * Get or generate student report card.
     */
    public function reportCard(Request $request, int $studentId): JsonResponse
    {
        $termId = $request->input('exam_term_id');
        if (!$termId) {
            $latestTerm = ExamTerm::orderBy('start_date', 'desc')->first();
            $termId = $latestTerm?->id;
        }

        if (!$termId) {
            return response()->json(['success' => false, 'message' => 'No exam terms found.'], 404);
        }

        $reportCard = $this->gradingService->generateReportCard($studentId, $termId);

        return response()->json([
            'success' => true,
            'data' => $reportCard->load(['student.user', 'student.schoolClass', 'term']),
        ]);
    }

    /**
     * List grading scales.
     */
    public function gradingScales(): JsonResponse
    {
        $scales = GradingScale::orderBy('min_percentage', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $scales,
        ]);
    }
}
