<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ExamPaper;
use App\Models\QuestionBank;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuestionBankController extends Controller
{
    /**
     * List questions in question bank with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = QuestionBank::with(['subject', 'schoolClass', 'author:id,name']);

        if ($request->has('subject_id')) {
            $query->where('subject_id', $request->input('subject_id'));
        }

        if ($request->has('class_id')) {
            $query->where('class_id', $request->input('class_id'));
        }

        if ($request->has('difficulty')) {
            $query->where('difficulty', $request->input('difficulty'));
        }

        if ($request->has('question_type')) {
            $query->where('question_type', $request->input('question_type'));
        }

        if ($request->filled('topic')) {
            $query->where('topic', 'like', '%' . $request->input('topic') . '%');
        }

        $questions = $query->orderBy('id', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $questions->items(),
            'meta' => [
                'current_page' => $questions->currentPage(),
                'total' => $questions->total(),
            ],
        ]);
    }

    /**
     * Add question to Question Bank.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'nullable|exists:school_classes,id',
            'topic' => 'nullable|string|max:150',
            'difficulty' => 'required|in:easy,medium,hard',
            'question_type' => 'required|in:mcq,short_answer,essay,numerical,true_false',
            'question_text' => 'required|string',
            'options' => 'nullable|array',
            'correct_answer' => 'required|string',
            'explanation' => 'nullable|string',
            'marks' => 'required|numeric|min:0.5',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $question = QuestionBank::create([
            'subject_id' => $request->input('subject_id'),
            'class_id' => $request->input('class_id'),
            'topic' => $request->input('topic'),
            'difficulty' => $request->input('difficulty'),
            'question_type' => $request->input('question_type'),
            'question_text' => $request->input('question_text'),
            'options' => $request->input('options'),
            'correct_answer' => $request->input('correct_answer'),
            'explanation' => $request->input('explanation'),
            'marks' => $request->input('marks'),
            'created_by_user_id' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Question added to Question Bank.',
            'data' => $question->load(['subject', 'schoolClass']),
        ], 201);
    }

    /**
     * Construct and compose an Exam Paper from Question Bank.
     */
    public function generatePaper(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'required|exists:school_classes,id',
            'title' => 'required|string|max:250',
            'instructions' => 'nullable|string',
            'total_marks' => 'required|numeric|min:10',
            'duration_minutes' => 'required|integer|min:15',
            'easy_count' => 'nullable|integer|min:0',
            'medium_count' => 'nullable|integer|min:0',
            'hard_count' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $subjectId = $request->input('subject_id');
        $easyCount = (int) $request->input('easy_count', 4);
        $mediumCount = (int) $request->input('medium_count', 4);
        $hardCount = (int) $request->input('hard_count', 2);

        $easyQuestions = QuestionBank::where('subject_id', $subjectId)->where('difficulty', 'easy')->limit($easyCount)->get();
        $medQuestions = QuestionBank::where('subject_id', $subjectId)->where('difficulty', 'medium')->limit($mediumCount)->get();
        $hardQuestions = QuestionBank::where('subject_id', $subjectId)->where('difficulty', 'hard')->limit($hardCount)->get();

        $sections = [
            [
                'section_name' => 'Section A: Objective & Foundation (Easy)',
                'questions' => $easyQuestions,
                'weightage' => $easyQuestions->sum('marks'),
            ],
            [
                'section_name' => 'Section B: Conceptual & Analytical (Medium)',
                'questions' => $medQuestions,
                'weightage' => $medQuestions->sum('marks'),
            ],
            [
                'section_name' => 'Section C: Advanced Synthesis & Problems (Hard)',
                'questions' => $hardQuestions,
                'weightage' => $hardQuestions->sum('marks'),
            ],
        ];

        $paper = ExamPaper::create([
            'subject_id' => $subjectId,
            'class_id' => $request->input('class_id'),
            'title' => $request->input('title'),
            'instructions' => $request->input('instructions', 'Read all questions carefully. All questions are compulsory.'),
            'total_marks' => $request->input('total_marks'),
            'duration_minutes' => $request->input('duration_minutes'),
            'sections' => $sections,
            'created_by_user_id' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Exam question paper generated successfully.',
            'data' => $paper->load(['subject', 'schoolClass']),
        ], 201);
    }
}
