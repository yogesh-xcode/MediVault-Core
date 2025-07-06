<?php

namespace App\Http\Controllers;

use App\DTOs\ReportDTO;
use App\Services\ModelService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Repositories\MedicalReportRepository;
use App\Repositories\PatientRepository;
use App\Repositories\StorageRepository;
use App\Services\ReportService;

class ReportController extends Controller
{
    public function __construct(
        private StorageRepository $storageRepo,
        private ReportService $reportService,
        private MedicalReportRepository $medicalReportRepository,
        private ModelService $modelService,
        private PatientRepository $patientRepo
    ) {
    }

    public function validationRules(): array
    {
        $report_types = [
            'blood_test',
            'urine_test',
            'ecg_summary',
            'xray_summary',
            'ct_scan_summary',
            'mri_scan_summary',
            'discharge_summary'
        ];

        return [
            "upload" => [
                'medical-report' => 'required|mimes:jpg,jpeg,png,pdf|max:10240'
            ],
            "retrieve" => [
                'pathParams' => [
                    'patient_id' => ['required', 'string'],
                    'report_type' => ['nullable', 'string', Rule::in($report_types)],
                ],
                'queryParams' => [
                    'from_date' => 'date',
                    'to_date' => 'date',
                ]
            ],
        ];
    }

    public function uploadReport(Request $request, string $patient_id): ReportDTO
    {
        if (!$request->hasFile('medical-report')) {
            return new ReportDTO([], "error", "No report file uploaded.", 422);
        }

        $request->validate($this->validationRules()["upload"]);

        try {
            $reportFile = $request->file('medical-report');
            $url = $this->storageRepo->storeReport($reportFile, $patient_id);

            $ocr_data = $this->modelService->performImageOCR($reportFile);
            $structuredReport = $this->modelService->ocrToJson($ocr_data);

            $reportData = [
                'report_id' => $this->reportService->generateReportID(),
                'report' => $structuredReport,
                'url' => $url
            ];

            if (!$this->patientRepo->exists($patient_id)) {
                return new ReportDTO([], "error", "Patient does not exist. Please create a new record.", 404);
            }

            $this->medicalReportRepository->add($reportData, $patient_id);

            return new ReportDTO($reportData, "success", "Report was uploaded and stored successfully.", 201);
        } catch (\Throwable $e) {
            return new ReportDTO([], "error", "Something went wrong during upload or processing. " . $e->getMessage(), 500);
        }
    }

    public function retrieveReport(Request $request, string $patient_id, ?string $report_type = null): ReportDTO
    {
        $retrievePayload = [
            'patient_id' => $patient_id,
            'report_type' => $report_type
        ];

        $validatedPath = validator($retrievePayload, $this->validationRules()["retrieve"]["pathParams"])->validate();
        $validatedQuery = $request->validate($this->validationRules()["retrieve"]["queryParams"]);

        if (!$this->patientRepo->exists($patient_id)) {
            return new ReportDTO([], "error", "Patient does not exist. Please create a new record.", 404);
        }

        $reports = $this->medicalReportRepository->retrieve(
            $validatedPath["patient_id"],
            $validatedPath["report_type"] ?? null,
            $validatedQuery ?? null
        );

        if ($reports->isEmpty()) {
            return new ReportDTO([], "error", "No reports found for this patient.", 404);
        }

        $returnReportKey = $report_type ?? 'reports';

        return new ReportDTO(
            [$returnReportKey => $reports],
            "success",
            "Fetched medical reports for patient ID: $patient_id",
            200
        );
    }
}