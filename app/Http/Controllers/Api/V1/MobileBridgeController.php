<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\DeviceToken;
use App\Models\Exam;
use App\Models\Homework;
use App\Models\MobileNotification;
use App\Models\Notice;
use App\Models\Student;
use App\Models\StudentFeeInvoice;
use App\Models\Timetable;
use App\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MobileBridgeController extends Controller
{
    /**
     * Mobile App Fast Bootstrap payload (single round-trip on cold start).
     */
    public function bootstrap(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenant = TenantContext::get();

        $unreadNotifications = MobileNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        $studentData = null;
        $feeBalance = 0.0;
        $pendingHomeworkCount = 0;

        if ($user->isStudent()) {
            $student = Student::with(['schoolClass', 'section'])->where('user_id', $user->id)->first();
            if ($student) {
                $studentData = [
                    'student_id' => $student->id,
                    'admission_number' => $student->admission_number,
                    'roll_number' => $student->roll_number,
                    'class_name' => $student->schoolClass?->name,
                    'section_name' => $student->section?->name,
                ];

                $pendingHomeworkCount = Homework::where('class_id', $student->class_id)
                    ->where('due_date', '>=', now())
                    ->count();

                $feeBalance = (float) StudentFeeInvoice::where('student_id', $student->id)
                    ->whereIn('status', ['unpaid', 'partially_paid', 'overdue'])
                    ->sum('balance_due');
            }
        }

        $upcomingExamsCount = Exam::where('exam_date', '>=', now()->toDateString())->count();

        $roleSpecificActions = match (true) {
            $user->isTeacher() => [
                ['title' => 'Mark Attendance', 'route' => '/attendance/mark', 'icon' => 'calendar-check'],
                ['title' => 'Assign Homework', 'route' => '/homework/create', 'icon' => 'book-open'],
                ['title' => 'AI Lesson Planner', 'route' => '/ai/lesson-plan', 'icon' => 'sparkles'],
                ['title' => 'Enter Exam Marks', 'route' => '/exams/marks', 'icon' => 'clipboard-document-list'],
            ],
            $user->isStudent(), $user->isParent() => [
                ['title' => 'My Timetable', 'route' => '/timetable', 'icon' => 'clock'],
                ['title' => 'Homework Dues', 'route' => '/homework', 'icon' => 'book-open'],
                ['title' => 'Report Card', 'route' => '/results', 'icon' => 'academic-cap'],
                ['title' => 'Fee Payment', 'route' => '/finance/pay', 'icon' => 'credit-card'],
            ],
            default => [
                ['title' => 'Dashboard Overview', 'route' => '/dashboard', 'icon' => 'chart-bar'],
                ['title' => 'Notice Board', 'route' => '/notices', 'icon' => 'bell'],
            ],
        };

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role?->slug,
                    'role_name' => $user->role?->name,
                    'permissions' => $user->role?->permissions ?? [],
                ],
                'tenant' => [
                    'id' => $tenant?->id,
                    'name' => $tenant?->name,
                    'subdomain' => $tenant?->subdomain,
                    'logo_url' => $tenant?->logo_url,
                    'academic_year' => '2026-2027',
                ],
                'student_profile' => $studentData,
                'metrics' => [
                    'unread_notifications' => $unreadNotifications,
                    'pending_homework_count' => $pendingHomeworkCount,
                    'upcoming_exams_count' => $upcomingExamsCount,
                    'fee_balance_due' => $feeBalance,
                ],
                'quick_actions' => $roleSpecificActions,
                'server_time' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Register or update mobile push device token (FCM / APNs).
     */
    public function registerDevice(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'platform' => 'required|in:android,ios,web',
            'device_name' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $device = DeviceToken::updateOrCreate(
            ['token' => $request->input('token')],
            [
                'user_id' => $request->user()->id,
                'platform' => $request->input('platform'),
                'device_name' => $request->input('device_name'),
                'last_active_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Device push token registered.',
            'data' => $device,
        ]);
    }

    /**
     * List user notifications.
     */
    public function notifications(Request $request): JsonResponse
    {
        $notifications = MobileNotification::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $notifications->items(),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'total' => $notifications->total(),
                'unread_count' => MobileNotification::where('user_id', $request->user()->id)->where('is_read', false)->count(),
            ],
        ]);
    }

    /**
     * Mark notification as read.
     */
    public function markNotificationRead(Request $request, int $id): JsonResponse
    {
        $notification = MobileNotification::where('user_id', $request->user()->id)->findOrFail($id);
        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read.',
            'data' => $notification,
        ]);
    }

    /**
     * Delta sync for offline mobile SQLite caching.
     */
    public function syncDelta(Request $request): JsonResponse
    {
        $lastSynced = $request->input('last_synced_at') 
            ? date('Y-m-d H:i:s', strtotime($request->input('last_synced_at'))) 
            : now()->subDays(7)->toDateTimeString();

        $notices = Notice::where('updated_at', '>=', $lastSynced)->get();
        $homework = Homework::with('subject')->where('updated_at', '>=', $lastSynced)->get();
        $timetables = Timetable::with(['schoolClass', 'section', 'subject'])->where('updated_at', '>=', $lastSynced)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'sync_timestamp' => now()->toIso8601String(),
                'delta' => [
                    'notices' => $notices,
                    'homework' => $homework,
                    'timetables' => $timetables,
                ],
            ],
        ]);
    }

    /**
     * Student / Parent consolidated chronological daily mobile feed.
     */
    public function studentFeed(Request $request): JsonResponse
    {
        $user = $request->user();
        $student = Student::where('user_id', $user->id)->first();
        $classId = $student?->class_id;

        $today = strtolower(date('l'));

        $schedule = Timetable::with('subject')
            ->where('class_id', $classId)
            ->where('day_of_week', $today)
            ->orderBy('start_time')
            ->get();

        $activeHomework = Homework::with('subject')
            ->where('class_id', $classId)
            ->where('due_date', '>=', date('Y-m-d'))
            ->orderBy('due_date')
            ->get();

        $recentNotices = Notice::where('is_published', true)
            ->whereIn('audience_type', ['all', 'students', 'parents'])
            ->orderBy('published_at', 'desc')
            ->limit(5)
            ->get();

        $upcomingExams = Exam::with('subject')
            ->where('class_id', $classId)
            ->where('exam_date', '>=', date('Y-m-d'))
            ->orderBy('exam_date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'today_schedule' => $schedule,
                'active_homework' => $activeHomework,
                'recent_notices' => $recentNotices,
                'upcoming_exams' => $upcomingExams,
            ],
        ]);
    }
}
