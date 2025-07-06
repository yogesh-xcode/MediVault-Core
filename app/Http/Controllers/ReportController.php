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
use Illuminate\Support\Str;

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
            "retrieve" =>
                [
                    'pathParams' =>
                        [
                            'patient_id' => [
                                'required',
                                'string',
                            ],
                            'report_type' => [
                                'nullable',
                                'string',
                                Rule::in($report_types)
                            ]

                        ],
                    'queryParams' => [
                        'from_date' => 'date',
                        'to_date' => 'date',
                    ]

                ],

        ];
    }

    public function uploadReport(Request $request, $patient_id): ReportDTO
    {
        if (!$request->hasFile(key: 'medical-report')) {
            return new ReportDTO(
                data: [],
                status: "error",
                message: "No report file uploaded.",
                code: 422
            );
        }

        $request->validate($this->validationRules()["upload"]);

        $report = $request->file('medical-report');

        try {
            $url = $this->storageRepo->storeReport(report: $report, patient_id: $patient_id);

            $ocr_data = $this->modelService->performImageOCR(report: $report);
            $structuredReport = $this->modelService->ocrToJson(ocr: $ocr_data);

            $reportData = [
                'report_id' => $this->reportService->generateReportID(),
                'report' => $structuredReport,
                'url' => $url
            ];

            if (!$this->patientRepo->exists($patient_id)) {
                return new ReportDTO(
                    data: [],
                    status: "error",
                    message: "Patient does not exist. Please create a new record.",
                    code: 404
                );
            }

            $this->medicalReportRepository->add(
                report: $reportData,
                patient_id: $patient_id
            );

            return new ReportDTO(
                data: $reportData,
                status: "success",
                message: "Report was uploaded and stored successfully.",
                code: 201
            );
        } catch (\Throwable $e) {
            return new ReportDTO(
                data: [],
                status: "error",
                message: "Something went wrong during upload or processing. " . $e->getMessage(),
                code: 500
            );
        }
    }

    public function retrieveReport(Request $request, string $patient_id, ?string $report_type = null): ReportDTO
    {
        $retrievePayload = $report_type ? [
            'patient_id' => $patient_id,
            'report_type' => $report_type
        ] : compact('patient_id');

        $pathValidated = validator(
            data: $retrievePayload,
            rules: $this->validationRules()["retrieve"]["pathParams"]
        )->validate();
        $queryValidated = $request->validate(rules: $this->validationRules()["retrieve"]["queryParams"]);

        if (!$this->patientRepo->exists(patient_id: $patient_id)) {
            return new ReportDTO(
                data: [],
                status: "error",
                message: "Patient does not exist. Please create a new record.",
                code: 404
            );
        }

        $reports = $this->medicalReportRepository->retrieve(
            patient_id: $pathValidated["patient_id"],
            report_type: $pathValidated["report_type"] ?? null,
            timeline: $queryValidated ?? null
        );

        if ($reports->isEmpty()) {
            return new ReportDTO(
                data: [],
                status: "error",
                message: "No reports found for this patient.",
                code: 404
            );
        }

        $returnReportKey = $report_type ?? 'reports';

        return new ReportDTO(
            data: [
                $returnReportKey => $reports
            ],
            status: "success",
            message: "Fetched medical reports for patient ID: $patient_id",
            code: 200
        );
    }
}