<?php

namespace App\Services;

use Illuminate\Support\Str;
class ReportService
{
    public function generateReportID()
    {
        return 'PRID' . str_replace('-', '', (string) Str::uuid());
    }
}