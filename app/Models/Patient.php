<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    // Table Name
    protected $table = 'patients';

    // Primary Key
    protected $primaryKey = 'patient_id';
    public $incrementing = false;
    protected $keyType = 'string';

    // Field Access Control
    protected $fillable = ['patient_id', 'patient_name', 'dob'];

}
