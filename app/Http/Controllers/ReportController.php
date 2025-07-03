<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Repositories\StorageRepository;

class ReportController extends Controller
{
    public function __construct(
        private StorageRepository $storageRepo
    ) {}

    public function uploadReport(Request $request,$patient_id)
    {
        if (!$request->hasFile('medical-report')) {
            return response()->json(['error' => 'No report file uploaded.'], 422);
        }

        $report = $request->file('medical-report');

        $url = $this->storageRepo->store_report($report,$patient_id);

        return response()->json([
            'url' => $url,
        ],201);
    }
}