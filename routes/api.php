<?php

use App\Http\Controllers\Api\V1\AcademicController;
use App\Http\Controllers\Api\V1\AttendanceController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BillingController;
use App\Http\Controllers\Api\V1\CommunicationController;
use App\Http\Controllers\Api\V1\HomeworkController;
use App\Http\Controllers\Api\V1\StudentController;
use App\Http\Controllers\Api\V1\TenantController;
use App\Http\Middleware\SubscriptionFeatureMiddleware;
use App\Http\Middleware\TenantMiddleware;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - V1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // Public Auth Endpoints & Public Plans
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::get('/billing/plans', [BillingController::class, 'plans']);

    // Protected Routes (Sanctum + Tenant Context)
    Route::middleware(['auth:sanctum', TenantMiddleware::class])->group(function () {

        // Session & Identity
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Tenant Settings & Info
        Route::get('/tenant/current', [TenantController::class, 'current']);
        Route::put('/tenant/settings', [TenantController::class, 'updateSettings']);
        Route::get('/tenant/audit-logs', [TenantController::class, 'auditLogs']);

        // SaaS Billing & Subscriptions
        Route::get('/billing/subscription', [BillingController::class, 'subscription']);
        Route::post('/billing/subscribe', [BillingController::class, 'subscribe']);
        Route::get('/billing/invoices', [BillingController::class, 'invoices']);
        Route::post('/billing/coupons/validate', [BillingController::class, 'validateCoupon']);

        // Super Admin Platform Metrics
        Route::get('/platform/billing/metrics', [BillingController::class, 'platformMetrics']);

        // 1. Classes & Sections & Subjects
        Route::get('/classes', [AcademicController::class, 'classes']);
        Route::post('/classes', [AcademicController::class, 'storeClass']);
        Route::get('/subjects', [AcademicController::class, 'subjects']);
        Route::post('/subjects', [AcademicController::class, 'storeSubject']);
        Route::get('/timetables', [AcademicController::class, 'timetables']);
        Route::post('/timetables', [AcademicController::class, 'storeTimetable']);

        // 2. Students & Enrollment
        Route::get('/students', [StudentController::class, 'index']);
        Route::post('/students', [StudentController::class, 'store']);
        Route::get('/students/{id}', [StudentController::class, 'show']);

        // 3. Attendance
        Route::post('/attendance/mark', [AttendanceController::class, 'mark']);
        Route::get('/attendance/summary', [AttendanceController::class, 'summary']);

        // 4. Homework & Submissions
        Route::get('/homework', [HomeworkController::class, 'index']);
        Route::post('/homework', [HomeworkController::class, 'store']);
        Route::post('/homework/{id}/submit', [HomeworkController::class, 'submit']);
        Route::post('/homework/submissions/{id}/review', [HomeworkController::class, 'review']);

        // 5. Notices & Communication
        Route::get('/notices', [CommunicationController::class, 'notices']);
        Route::post('/notices', [CommunicationController::class, 'storeNotice']);
        Route::get('/messages', [CommunicationController::class, 'messages']);
        Route::post('/messages/send', [CommunicationController::class, 'sendMessage']);
    });
});
