<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\ApproveExpenseRequest;
use App\Models\Expense;
use App\Models\Approval;

class ExpenseController extends Controller
{
    public function store(StoreExpenseRequest $request)
    {
        $expense = Expense::create([
            'amount' => $request->amount,
            'status_id' => 1, // Status 'Menunggu Persetujuan'
        ]);
        return response()->json($expense, 201);
    }

    public function show($id)
    {
        $expense = Expense::with('approvals.approver', 'status')->findOrFail($id);
        return response()->json($expense);
    }

    public function approve(ApproveExpenseRequest $request, $id)
    {
        $expense = Expense::findOrFail($id);
        $currentApprovalStage = Approval::where('expense_id', $expense->id)
                                         ->whereNull('status_id')
                                         ->orderBy('id', 'asc')
                                         ->firstOrFail();

        if ($currentApprovalStage->approver_id != $request->approver_id) {
            return response()->json(['message' => 'Approver is not authorized for this stage'], 403);
        }

        $currentApprovalStage->update(['status_id' => 2]); // Status 'Disetujui'

        if (Approval::where('expense_id', $expense->id)->whereNull('status_id')->doesntExist()) {
            $expense->update(['status_id' => 2]); // Status 'Disetujui'
        }

        return response()->json($expense);
    }
}
