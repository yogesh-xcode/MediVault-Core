<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class MedicalReportTrack extends Model
{

    protected $connection = "mongodb";
    protected $collection = "patient_reports";
    protected $fillable = [
        'patient_id',
        'report_ids'
    ];

}