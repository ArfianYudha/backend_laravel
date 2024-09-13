<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreApprovalStageRequest;
use App\Http\Requests\UpdateApprovalStageRequest;
use App\Models\ApprovalStage;


class ApprovalStageController extends Controller
{
    public function store(StoreApprovalStageRequest $request)
    {
        $stage = ApprovalStage::create($request->validated());
        return response()->json($stage, 201);
    }

    public function update(UpdateApprovalStageRequest $request, $id)
    {
        $stage = ApprovalStage::findOrFail($id);
        $stage->update($request->validated());
        return response()->json($stage, 200);
    }
}
