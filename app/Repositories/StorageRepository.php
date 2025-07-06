<?php

namespace App\Repositories;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StorageRepository
{
    public function disk()
    {
        return Storage::disk('report_storage');
    }
    public function storeReport(UploadedFile $report, string $patient_id): string
    {
        $filename = 'medical-report' . "-{$patient_id}-" . Str::random(8) . '.' . $report->getClientOriginalExtension();

        $relativePath = $report->storeAs(
            '',
            $filename,
            'report_storage'
        );

        return $this->disk()->url($relativePath);
    }
}