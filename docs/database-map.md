# Database Schema Map

## 1. Multi-Tenant Core Tables
- `tenants`: `id`, `name`, `subdomain`, `code`, `status`, `plan_id`, `created_at`, `updated_at`.
- `users`: `id`, `tenant_id` (nullable for Super Admin), `name`, `email`, `phone`, `password`, `role_id`, `status`, `avatar_url`, `created_at`, `updated_at`.
- `roles`: `id`, `name`, `slug`, `description`, `is_system`.
- `permissions`: `id`, `name`, `slug`, `module`.
- `role_permissions`: `role_id`, `permission_id`.
- `audit_logs`: `id`, `tenant_id`, `user_id`, `event`, `auditable_type`, `auditable_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`.

## 2. Subscription & SaaS Billing Tables
- `plans`: `id`, `name`, `slug`, `price_monthly`, `price_annual`, `student_limit`, `storage_limit_gb`, `ai_credit_quota`, `features_json`, `is_active`.
- `subscriptions`: `id`, `tenant_id`, `plan_id`, `status`, `trial_ends_at`, `starts_at`, `renews_at`, `ends_at`.
- `invoices`: `id`, `tenant_id`, `subscription_id`, `invoice_number`, `amount`, `tax`, `status`, `paid_at`, `invoice_url`.
- `ai_usage_logs`: `id`, `tenant_id`, `user_id`, `module`, `provider`, `model`, `input_tokens`, `output_tokens`, `computed_cost`, `created_at`.

## 3. Academic Structure Tables
- `academic_years`: `id`, `tenant_id`, `name`, `start_date`, `end_date`, `is_current`.
- `school_classes`: `id`, `tenant_id`, `name`, `code`, `order_index`.
- `sections`: `id`, `tenant_id`, `class_id`, `name`, `capacity`, `class_teacher_id`.
- `subjects`: `id`, `tenant_id`, `class_id`, `name`, `code`, `type` (Theory/Practical).
- `students`: `id`, `tenant_id`, `user_id`, `admission_number`, `class_id`, `section_id`, `roll_number`, `dob`, `gender`, `address`, `parent_id`, `enrollment_date`, `status`.
- `teachers`: `id`, `tenant_id`, `user_id`, `employee_code`, `qualification`, `joining_date`, `designation`.
- `teacher_subjects`: `id`, `tenant_id`, `teacher_id`, `subject_id`, `section_id`.
- `parents`: `id`, `tenant_id`, `user_id`, `occupation`, `relationship`, `alternate_phone`.

## 4. Academic Operations Tables
- `attendances`: `id`, `tenant_id`, `student_id`, `class_id`, `section_id`, `date`, `status` (Present/Absent/Late/HalfDay), `remarks`, `marked_by`.
- `timetables`: `id`, `tenant_id`, `class_id`, `section_id`, `subject_id`, `teacher_id`, `day_of_week`, `start_time`, `end_time`, `room_number`.
- `homework`: `id`, `tenant_id`, `class_id`, `section_id`, `subject_id`, `teacher_id`, `title`, `description`, `due_date`, `attachment_url`.
- `homework_submissions`: `id`, `tenant_id`, `homework_id`, `student_id`, `submission_text`, `attachment_url`, `submitted_at`, `status`, `feedback`, `marks`.
- `notices`: `id`, `tenant_id`, `title`, `content`, `audience_type`, `target_role`, `target_class_id`, `is_published`, `publish_date`, `created_by`.
- `messages`: `id`, `tenant_id`, `sender_id`, `receiver_id`, `group_id`, `message_text`, `is_read`, `created_at`.

## 5. Finance & Fee Tables
- `fee_heads`: `id`, `tenant_id`, `name`, `description`, `is_mandatory`.
- `fee_structures`: `id`, `tenant_id`, `academic_year_id`, `class_id`, `fee_head_id`, `amount`, `due_date`, `frequency`.
- `student_fee_invoices`: `id`, `tenant_id`, `student_id`, `academic_year_id`, `invoice_number`, `total_amount`, `concession_amount`, `paid_amount`, `due_amount`, `due_date`, `status`.
- `fee_payments`: `id`, `tenant_id`, `invoice_id`, `student_id`, `amount`, `payment_mode` (Cash/Card/Online/Cheque), `transaction_ref`, `receipt_number`, `received_by`, `paid_at`.
- `fee_concessions`: `id`, `tenant_id`, `student_id`, `fee_head_id`, `discount_type` (Percentage/Fixed), `discount_value`, `reason`, `approved_by`.

## 6. Examination Tables
- `exams`: `id`, `tenant_id`, `academic_year_id`, `name`, `term`, `start_date`, `end_date`, `status`.
- `exam_schedules`: `id`, `tenant_id`, `exam_id`, `class_id`, `subject_id`, `exam_date`, `start_time`, `end_time`, `max_marks`, `pass_marks`.
- `question_banks`: `id`, `tenant_id`, `class_id`, `subject_id`, `chapter`, `question_text`, `question_type`, `marks`, `bloom_level`, `difficulty`, `options_json`, `correct_answer`, `explanation`.
- `exam_results`: `id`, `tenant_id`, `exam_schedule_id`, `student_id`, `marks_obtained`, `grade`, `remarks`, `is_published`.

## 7. RAG & AI Knowledge Tables
- `documents`: `id`, `tenant_id`, `class_id`, `subject_id`, `chapter`, `title`, `file_path`, `file_type`, `file_size`, `total_pages`, `status` (Uploaded/Processing/OCR/Embedding/Indexed/Failed), `error_message`, `uploaded_by`.
- `document_chunks`: `id`, `tenant_id`, `document_id`, `chunk_index`, `page_number`, `chunk_text`, `token_count`, `vector_point_id`.
- `ai_generated_contents`: `id`, `tenant_id`, `user_id`, `module_type` (lesson_plan/question_paper/worksheet/evaluation/circular/report_analysis), `input_params_json`, `output_payload_json`, `prompt_version`, `status`.
- `answer_evaluations`: `id`, `tenant_id`, `exam_schedule_id`, `student_id`, `scanned_file_url`, `ocr_extracted_text`, `ai_suggested_marks`, `ai_confidence`, `ai_feedback_json`, `final_marks`, `reviewed_by`, `status`.
