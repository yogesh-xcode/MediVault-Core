<?php

namespace App\Repositories;

use App\Models\Patient;

class PatientRepository{

    public function create($patient){
        return Patient::create(attributes:$patient);
    }

    public function update($patient_id, $column, $value)
    {
        return Patient::where('patient_id', $patient_id)
            ->update([$column => $value]);
    }
    
    public function delete($patient_id){
        return Patient::find($patient_id)->delete();
    }

    public function get($patient_id){
        return Patient::where('patient_id', $patient_id)->first();
    }

    public function getAll()
    {
        return Patient::all();
    }
}

