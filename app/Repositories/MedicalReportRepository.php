<?php

namespace App\Repositories;

use App\Models\MedicalReport;
use App\Models\MedicalReportTrack;

use Illuminate\Support\Collection;

class MedicalReportRepository
{
    public function add(array $report, string $patient_id): void
    {
        MedicalReport::create(attributes: $report);

        $track = MedicalReportTrack::firstOrCreate(
            attributes: ['patient_id' => $patient_id],
            values: ['report_ids' => []]
        );

        $report_id = $report["report_id"];
        $track->push('report_ids', $report_id);
    }

    private function retrieveAll(array $med_report_ids): Collection
    {
        return MedicalReport::whereIn(
            column: 'report_id',
            values: $med_report_ids
        )->get();
    }

    private function retrieveByType(array $med_report_ids, string $report_type): Collection
    {
        return MedicalReport::whereIn(
            column: 'report_id',
            values: $med_report_ids
        )->pluck(
                column: "report.report.$report_type",
                key: 'report_id'
            )->filter();
    }

    private function retrieveByTypeDate(array $med_report_ids, string $report_type, array $timeline): Collection
    {
        return MedicalReport::whereIn(
            column: 'report_id',
            values: $med_report_ids
        )->whereBetween(
                column: 'report.hospital.report_date',
                values: [
                    $timeline["from_date"],
                    $timeline["to_date"]
                ]
            )->pluck(
                column: "report.report.$report_type",
                key: 'report_id'
            )->filter();
    }



    public function retrieve(
        string $patient_id,
        string $report_type,
        ?array $timeline
    ): Collection {

        $med_report_ids = MedicalReportTrack::where(
            column: 'patient_id',
            operator: $patient_id
        )->value(column: 'report_ids');

        if (!$med_report_ids)
            return collect();

        if (!$report_type)
            return $this->retrieveAll(med_report_ids: $med_report_ids);

        return $timeline
            ? $this->retrieveByTypeDate(
                med_report_ids:
                $med_report_ids,
                report_type: $report_type,
                timeline: $timeline
            )
            : $this->retrieveByType(
                med_report_ids:
                $med_report_ids,
                report_type: $report_type
            );
    }
}