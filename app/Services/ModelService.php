<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ModelService
{
    public function performImageOCR($report)
    {
        $ocrBaseUrl = env('OCR_MODEL_URL');

        $ocrResponse = Http::asMultipart()->attach(
            name: 'file',
            contents: fopen(filename: $report->getRealPath(), mode: 'r'),
            filename: $report->getClientOriginalName()
        )->post(
                url: "$ocrBaseUrl/ocr",
            );

        return $ocrResponse->json()['lines'];
    }

    private function promptTuner($ocr)
    {
        $prompt = "
            You are Dr. MedStruct, a clinically trained AI assistant.
            Your ONLY task is to extract structured medical report data from OCR-TEXT below.
            You MUST obey these rules with zero deviation:

            1. Output must be a single, valid JSON object that matches the schema provided.
            2. You are not allowed to guess or fabricate any values.
            3. If a value is missing or unclear:
            - Use null for unknown numbers
            - Use '' for strings
            - Use {} for objects
            4. You must include all top-level keys, even if they are empty or contain only null values.
            5. DO NOT invent new field names, keys, or sections.
            6. If any text cannot be mapped to the schema, put it in the 'extra' field.
            7. DO NOT add explanations, comments, or extra messages.
            8. The output MUST start with `{` and end with `}`.
            9. Do not output the schema. Just use it as your reference.
            10.Return One and Only Single Json
            ```json
            ```
        ";

        $schema = <<<JSON
        {
            "patient": {
                "name": "",
                "age": null,
                "sex": "",
                "patient_id": "",
                "contact": {
                    "phone": "",
                    "email": "",
                    "address": ""
                }
            },
            "hospital": {
                "name": "",
                "department": "",
                "doctor": "",
                "report_date": "YYYY-MM-DD"
                "hospital_id": ""
            },
            "report": {
                "blood_test": {
                    "wbc_count": {
                        "value": null,
                        "unit": "",
                        "reference_range": ""
                    },
                    "rbc_count": {
                        "value": null,
                        "unit": "",
                        "reference_range": ""
                    },
                    "hemoglobin": {
                        "value": null,
                        "unit": "",
                        "reference_range": ""
                    },
                    "hematocrit": {
                        "value": null,
                        "unit": "",
                        "reference_range": ""
                    },
                    "mcv": {
                        "value": null,
                        "unit": "",
                        "reference_range": ""
                    },
                    "mch": {
                        "value": null,
                        "unit": "",
                        "reference_range": ""
                    },
                    "mchc": {
                        "value": null,
                        "unit": "",
                        "reference_range": ""
                    },
                    "rdw": {
                        "value": null,
                        "unit": "",
                        "reference_range": ""
                    },
                    "platelet_count": {
                        "value": null,
                        "unit": "",
                        "reference_range": ""
                    },
                    "mpv": {
                        "value": null,
                        "unit": "",
                        "reference_range": ""
                    },
                    "differential": {
                        "neutrophils": {
                            "value": null,
                            "unit": "",
                            "reference_range": ""
                        },
                        "lymphocytes": {
                            "value": null,
                            "unit": "",
                            "reference_range": ""
                        },
                        "monocytes": {
                            "value": null,
                            "unit": "",
                            "reference_range": ""
                        },
                        "eosinophils": {
                            "value": null,
                            "unit": "",
                            "reference_range": ""
                        },
                        "basophils": {
                            "value": null,
                            "unit": "",
                            "reference_range": ""
                        }
                    }
                },
                "urine_test": {
                    "color": "",
                    "appearance": "",
                    "ph": null,
                    "specific_gravity": null,
                    "protein": "",
                    "glucose": "",
                    "ketones": "",
                    "bilirubin": "",
                    "urobilinogen": "",
                    "nitrite": "",
                    "leukocyte_esterase": "",
                    "microscopic_examination": {
                        "rbc": "",
                        "wbc": "",
                        "epithelial_cells": "",
                        "casts": "",
                        "crystals": "",
                        "bacteria": ""
                    }
                },
                "ecg_summary": {
                    "rhythm": "",
                    "heart_rate": null,
                    "pr_interval": null,
                    "qrs_duration": null,
                    "qt_interval": null,
                    "interpretation": ""
                },
                "xray_summary": {
                    "body_part": "",
                    "view": "",
                    "findings": "",
                    "impression": ""
                },
                "ct_scan_summary": {
                    "region": "",
                    "contrast_used": false,
                    "findings": "",
                    "impression": ""
                },
                "mri_scan_summary": {
                    "region": "",
                    "contrast_used": false,
                    "sequences": [""],
                    "findings": "",
                    "impression": ""
                },
                "discharge_summary": {
                    "diagnosis": "",
                    "treatment_given": "",
                    "condition_at_discharge": "",
                    "medications": [""],
                    "follow_up_instructions": ""
                }
            },
            "extra": {}
        }
        JSON;

        $ocr_data = implode('\n', $ocr);

        $fineTunedPrompt = "
            {$prompt}
            schema: {$schema}
            ocr_text: {$ocr_data}
       ";

        return $fineTunedPrompt;
    }

    private function cleanModelContext($generatedText)
    {
        $cleanText = preg_replace('/^```json|```$/m', '', trim($generatedText));

        // Decode JSON
        return json_decode($cleanText, true);
    }

    public function ocrToJson($ocr): array
    {
        set_time_limit(0);

        $ocr_data = $this->promptTuner($ocr);
        $payload = [
            [
                'parts' => [
                    ['text' => $ocr_data]
                ]
            ]
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-goog-api-key' => env('GEMINI_API_KEY'),
        ])->post(
                env('GEMINI_MODEL_URL'),
                [
                    'contents' => $payload
                ]
            );

        $data = $response->json();
        $generatedText = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'No output';
        $reportJson = $this->cleanModelContext($generatedText);

        return $reportJson;

    }

}