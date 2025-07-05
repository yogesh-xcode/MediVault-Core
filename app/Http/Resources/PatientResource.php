<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'patient_id'   => $this->patient_id,
            'patient_name' => $this->patient_name,
            'dob'          => $this->dob,
        ];
    }
}
