<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\FeeConcession;
use App\Models\FeeHead;
use App\Models\FeePayment;
use App\Models\FeeStructure;
use App\Models\FinancialExpense;
use App\Models\Student;
use App\Models\StudentFeeInvoice;
use App\Services\AuditLogService;
use App\Services\FeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FeeController extends Controller
{
    public function __construct(
        protected FeeService $feeService,
        protected AuditLogService $auditLogService
    ) {}

    /**
     * List fee heads.
     */
    public function feeHeads(): JsonResponse
    {
        $heads = FeeHead::orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $heads,
        ]);
    }

    /**
     * Create fee head.
     */
    public function storeFeeHead(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'is_mandatory' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $head = FeeHead::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Fee category created.',
            'data' => $head,
        ], 201);
    }

    /**
     * List fee structures.
     */
    public function structures(Request $request): JsonResponse
    {
        $query = FeeStructure::with(['feeHead', 'schoolClass']);

        if ($request->has('class_id')) {
            $query->where('class_id', $request->input('class_id'));
        }

        $structures = $query->orderBy('class_id')->get();

        return response()->json([
            'success' => true,
            'data' => $structures,
        ]);
    }

    /**
     * Create fee structure for class.
     */
    public function storeStructure(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'fee_head_id' => 'required|exists:fee_heads,id',
            'class_id' => 'required|exists:school_classes,id',
            'amount' => 'required|numeric|min:0',
            'frequency' => 'required|in:monthly,quarterly,term_wise,annual,one_time',
            'due_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $structure = FeeStructure::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Fee structure allocated to class.',
            'data' => $structure->load(['feeHead', 'schoolClass']),
        ], 201);
    }

    /**
     * List or create fee concessions.
     */
    public function concessions(Request $request): JsonResponse
    {
        $concessions = FeeConcession::with(['student.user', 'student.schoolClass'])->where('is_active', true)->get();

        return response()->json([
            'success' => true,
            'data' => $concessions,
        ]);
    }

    public function storeConcession(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'title' => 'required|string|max:150',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $concession = FeeConcession::create(array_merge($request->all(), [
            'approved_by_user_id' => $request->user()->id,
            'is_active' => true,
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Fee concession / scholarship granted.',
            'data' => $concession->load(['student.user', 'student.schoolClass']),
        ], 201);
    }

    public function updateConcession(Request $request, int $id): JsonResponse
    {
        $concession = FeeConcession::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:150',
            'discount_type' => 'sometimes|in:percentage,fixed',
            'discount_value' => 'sometimes|numeric|min:0.01',
            'reason' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $concession->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Fee concession updated successfully.',
            'data' => $concession->load(['student.user', 'student.schoolClass']),
        ]);
    }

    public function destroyConcession(int $id): JsonResponse
    {
        $concession = FeeConcession::findOrFail($id);
        $concession->delete();

        return response()->json([
            'success' => true,
            'message' => 'Fee concession removed successfully.',
        ]);
    }

    /**
     * List student fee invoices with filters.
     */
    public function invoices(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = StudentFeeInvoice::with(['student.user', 'student.schoolClass']);

        if ($user->isStudent()) {
            $student = Student::where('user_id', $user->id)->first();
            if ($student) {
                $query->where('student_id', $student->id);
            }
        } elseif ($request->has('student_id')) {
            $query->where('student_id', $request->input('student_id'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        $invoices = $query->orderBy('due_date', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $invoices->items(),
            'meta' => [
                'current_page' => $invoices->currentPage(),
                'total' => $invoices->total(),
            ],
        ]);
    }

    /**
     * Generate batch invoices for entire class.
     */
    public function generateBatchInvoices(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'class_id' => 'required|exists:school_classes,id',
            'title' => 'required|string|max:200',
            'due_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $invoices = $this->feeService->generateClassInvoices(
                $request->input('class_id'),
                $request->input('title'),
                $request->input('due_date')
            );

            $this->auditLogService->log(
                event: 'fee.batch_invoiced',
                newValues: ['class_id' => $request->input('class_id'), 'count' => $invoices->count()],
                request: $request
            );

            return response()->json([
                'success' => true,
                'message' => "Generated {$invoices->count()} student fee invoices.",
                'data' => $invoices,
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Collect fee payment and issue receipt.
     */
    public function collectPayment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'student_fee_invoice_id' => 'required|exists:student_fee_invoices,id',
            'amount_paid' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,card,bank_transfer,cheque,online_gateway',
            'transaction_reference' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $invoice = StudentFeeInvoice::findOrFail($request->input('student_fee_invoice_id'));

        try {
            $payment = $this->feeService->recordPayment(
                $invoice,
                (float) $request->input('amount_paid'),
                $request->input('payment_method'),
                $request->input('transaction_reference'),
                $request->input('notes'),
                $request->user()->id
            );

            $this->auditLogService->log(
                event: 'fee.payment_collected',
                auditable: $payment,
                newValues: ['receipt_number' => $payment->receipt_number, 'amount' => $payment->amount_paid],
                request: $request
            );

            return response()->json([
                'success' => true,
                'message' => 'Payment collected and receipt generated.',
                'data' => $payment->load(['invoice', 'student.user']),
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * List fee defaulters.
     */
    public function defaulters(): JsonResponse
    {
        $defaulters = StudentFeeInvoice::with(['student.user', 'student.schoolClass', 'student.parent.user'])
            ->whereIn('status', ['unpaid', 'partially_paid', 'overdue'])
            ->where('due_date', '<', now())
            ->orderBy('balance_due', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $defaulters,
        ]);
    }

    /**
     * Financial analytics dashboard stats.
     */
    public function analytics(): JsonResponse
    {
        $analytics = $this->feeService->getAnalytics();

        return response()->json([
            'success' => true,
            'data' => $analytics,
        ]);
    }

    /**
     * Expenses management.
     */
    public function expenses(): JsonResponse
    {
        $expenses = FinancialExpense::orderBy('expense_date', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $expenses->items(),
            'meta' => [
                'current_page' => $expenses->currentPage(),
                'total' => $expenses->total(),
            ],
        ]);
    }

    public function storeExpense(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:200',
            'category' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'vendor' => 'nullable|string',
            'payment_method' => 'required|in:cash,bank_transfer,cheque,card',
            'receipt_url' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $expense = FinancialExpense::create(array_merge($request->all(), [
            'recorded_by_user_id' => $request->user()->id,
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Operational expense recorded.',
            'data' => $expense,
        ], 201);
    }
}
