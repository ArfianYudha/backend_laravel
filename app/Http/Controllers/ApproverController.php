<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreApproverRequest;
use App\Models\Approver;


class ApproverController extends Controller
{
    public function store(StoreApproverRequest $request)
    {
        $approver = Approver::create($request->validated());
        return response()->json($approver, 201);
    }
}
