<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class MedicalReport extends Model
{
    protected $connection = "mongodb";
    protected $collection = "medical_reports";
    protected $fillable = [
        'report_id',
        'report',
        'url'
    ];

}