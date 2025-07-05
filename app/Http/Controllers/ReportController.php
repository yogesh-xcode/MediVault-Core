<?php

namespace App\Http\Controllers;

use App\DTOs\ReportDTO;
use App\Services\ModelService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Http\Controllers\Controller;
use App\Repositories\MedicalReportRepository;
use App\Repositories\StorageRepository;

class ReportController extends Controller
{

    public function __construct(
        private StorageRepository $storageRepo,
        private MedicalReportRepository $medicalReportRepository,
        private ModelService $modelService
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
            "retrive" =>
                [
                    'pathParams' =>
                        [
                            'patient_id' => [
                                'required',
                                'string',
                            ],
                            'report_type' => [
                                'string',
                                Rule::in(values: $report_types)
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
            $url = $this->storageRepo->store_report(report: $report, patient_id: $patient_id);

            $ocr_data = $this->modelService->performImageOCR(report: $report);
            $structuredReport = $this->modelService->ocrToJson(ocr: $ocr_data);

            $report = [
                'report_id' => uniqid(prefix: 'PRID'),
                'report' => $structuredReport,
                'url' => $url
            ];

            $this->medicalReportRepository->add(
                report: $report,
                patient_id: $patient_id
            );

            return new ReportDTO(
                data: $report,
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

    public function retriveReport(Request $request, $patient_id, $report_type): ReportDTO
    {
        $retrive_payload = [
            'patient_id' => $patient_id,
            'report_type' => $report_type ?? null
        ];

        $pathValidated = validator(
            data: $retrive_payload,
            rules: $this->validationRules()["retrive"]["pathParams"]
        )->validate();
        $queryValidated = $request->validate(rules: $this->validationRules()["retrive"]["queryParams"]);

        $reports = $this->medicalReportRepository->retrieve(
            patient_id: $pathValidated["patient_id"],
            report_type: $pathValidated["report_type"] ?? null,
            timeline: $queryValidated ?? null
        );

        return new ReportDTO(
            data: [
                $report_type => $reports
            ],
            status: "success",
            message: "medical reports of $patient_id is here",
            code: 200
        );
    }
}