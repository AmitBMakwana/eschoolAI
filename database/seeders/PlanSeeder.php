<?php

namespace Database\Seeders;

use App\Models\Coupon;
use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Core SaaS Subscription Plans
        $plans = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'Essential school management for small schools and academies.',
                'price_monthly' => 99.00,
                'price_annual' => 990.00, // 2 months free
                'student_limit' => 500,
                'storage_limit_gb' => 10,
                'ai_credit_quota' => 1000,
                'is_popular' => false,
                'features' => [
                    'student_management',
                    'teacher_management',
                    'attendance_tracking',
                    'fee_collection',
                    'basic_notices',
                    'ai_lesson_planner',
                    'ai_worksheet_generator',
                ],
            ],
            [
                'name' => 'Professional',
                'slug' => 'professional',
                'description' => 'Advanced AI tools, full academic suite, and examination management.',
                'price_monthly' => 249.00,
                'price_annual' => 2490.00,
                'student_limit' => 2000,
                'storage_limit_gb' => 50,
                'ai_credit_quota' => 10000,
                'is_popular' => true,
                'features' => [
                    'student_management',
                    'teacher_management',
                    'attendance_tracking',
                    'fee_collection',
                    'concessions_defaulters',
                    'timetable_conflict_check',
                    'live_chat_communication',
                    'examination_results',
                    'ai_lesson_planner',
                    'ai_question_paper_generator',
                    'ai_worksheet_generator',
                    'ai_answer_sheet_evaluation',
                    'ai_circular_generator',
                    'ai_student_report_analysis',
                    'knowledge_base_rag',
                ],
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'High capacity, custom AI models, multi-campus support, and priority SLA.',
                'price_monthly' => 599.00,
                'price_annual' => 5990.00,
                'student_limit' => 10000,
                'storage_limit_gb' => 250,
                'ai_credit_quota' => 50000,
                'is_popular' => false,
                'features' => [
                    'all_modules',
                    'multi_campus',
                    'unlimited_staff',
                    'custom_ai_models',
                    'priority_support',
                    'dedicated_database',
                    'custom_domain_ssl',
                ],
            ],
        ];

        foreach ($plans as $planData) {
            Plan::updateOrCreate(['slug' => $planData['slug']], $planData);
        }

        // 2. Promotional Discount Coupons
        Coupon::updateOrCreate(['code' => 'WELCOME20'], [
            'description' => '20% Welcome Discount for New Schools',
            'discount_type' => 'percentage',
            'discount_value' => 20.00,
            'max_uses' => 500,
            'is_active' => true,
        ]);

        Coupon::updateOrCreate(['code' => 'LAUNCH50'], [
            'description' => '50% Early Adopter Launch Special',
            'discount_type' => 'percentage',
            'discount_value' => 50.00,
            'max_uses' => 100,
            'is_active' => true,
        ]);
    }
}
