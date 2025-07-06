<?php

namespace App\Repositories;

use App\Models\Patient;

class PatientRepository
{

    public function create($patient)
    {
        return Patient::create(attributes: $patient);
    }

    public function update($patient_id, $field, $newValue)
    {
        return Patient::where('patient_id', $patient_id)
            ->update([$field => $newValue]);
    }

    public function delete($patient_id)
    {
        return Patient::find($patient_id)->delete();
    }

    public function get($patient_id): Patient|null
    {
        return Patient::where('patient_id', $patient_id)->first();
    }

    public function all()
    {
        return Patient::all();
    }

    public function exists($patient_id): bool
    {
        return $this->get(patient_id: $patient_id) ? true : false;
    }
}