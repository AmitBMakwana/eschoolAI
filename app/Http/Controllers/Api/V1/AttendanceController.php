<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Student;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Bulk mark attendance for a class and section.
     */
    public function mark(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'class_id' => 'required|exists:school_classes,id',
            'section_id' => 'required|exists:sections,id',
            'date' => 'required|date',
            'records' => 'required|array|min:1',
            'records.*.student_id' => 'required|exists:students,id',
            'records.*.status' => 'required|in:present,absent,late,half_day,holiday',
            'records.*.remarks' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $classId = $request->input('class_id');
        $sectionId = $request->input('section_id');
        $date = $request->input('date');
        $records = $request->input('records');

        DB::transaction(function () use ($records, $classId, $sectionId, $date, $user) {
            foreach ($records as $rec) {
                Attendance::updateOrCreate(
                    [
                        'student_id' => $rec['student_id'],
                        'date' => $date,
                    ],
                    [
                        'class_id' => $classId,
                        'section_id' => $sectionId,
                        'status' => $rec['status'],
                        'remarks' => $rec['remarks'] ?? null,
                        'marked_by_user_id' => $user->id,
                    ]
                );
            }
        });

        $this->auditLogService->log(
            event: 'attendance.marked',
            newValues: ['class_id' => $classId, 'section_id' => $sectionId, 'date' => $date, 'count' => count($records)],
            request: $request
        );

        return response()->json([
            'success' => true,
            'message' => "Attendance saved for " . count($records) . " students on {$date}.",
        ]);
    }

    /**
     * Get attendance summary report by class and section.
     */
    public function summary(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'class_id' => 'required|exists:school_classes,id',
            'section_id' => 'required|exists:sections,id',
            'date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $date = $request->input('date', date('Y-m-d'));
        $classId = $request->input('class_id');
        $sectionId = $request->input('section_id');

        $students = Student::with('user')
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->get();

        $attendanceRecords = Attendance::where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->whereDate('date', $date)
            ->get();

        $attendanceMap = $attendanceRecords->keyBy('student_id');

        $presentCount = 0;
        $absentCount = 0;
        $lateCount = 0;

        $roster = $students->map(function ($student) use ($attendanceMap, &$presentCount, &$absentCount, &$lateCount) {
            $record = $attendanceMap->get($student->id);
            $status = $record ? $record->status : 'unmarked';

            if ($status === 'present') $presentCount++;
            elseif ($status === 'absent') $absentCount++;
            elseif ($status === 'late') $lateCount++;

            return [
                'student_id' => $student->id,
                'admission_number' => $student->admission_number,
                'name' => $student->user?->name,
                'roll_number' => $student->roll_number,
                'status' => $status,
                'remarks' => $record?->remarks,
            ];
        });

        $totalStudents = $students->count();
        $rate = $totalStudents > 0 ? round(($presentCount / $totalStudents) * 100, 1) : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'date' => $date,
                'total_students' => $totalStudents,
                'present' => $presentCount,
                'absent' => $absentCount,
                'late' => $lateCount,
                'attendance_rate' => $rate,
                'roster' => $roster,
            ],
        ]);
    }
}
