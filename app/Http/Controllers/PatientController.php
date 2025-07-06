<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Repositories\PatientRepository;
use Illuminate\Http\Request;
use App\DTOs\PatientDTO;
use App\Http\Resources\PatientResource;

class PatientController extends Controller
{
    public function __construct(private readonly PatientRepository $patientRepo)
    {
    }

    public function validationRules(): array
    {
        return [
            "create" => [
                'patient_id' => [
                    'required',
                    'string',
                    'between:3,15',
                    'unique:patients'
                ],
                'patient_name' => [
                    'required',
                    'string',
                    'between:3,15'
                ],
                'dob' => [
                    'required',
                    'date'
                ],
            ],
            "update" => [
                'patient_id' => 'required|string',
                'field' => 'required|string',
                'new_value' => 'required|string'
            ]
        ];
    }

    // ✅ Create Patient
    public function create(Request $request)
    {
        $validated = $request->validate($this->validationRules()["create"]);
        $patient = $this->patientRepo->create($validated);

        return new PatientDTO(
            data: new PatientResource($patient),
            status: 'success',
            message: 'Patient created successfully.',
            code: 201
        );
    }

    // ✅ Update Patient
    public function update(Request $request)
    {
        $validated = $request->validate($this->validationRules()["update"]);

        $updated = $this->patientRepo->update(
            $validated["patient_id"],
            $validated["field"],
            $validated["new_value"]
        );

        if (!$updated) {
            return new PatientDTO(
                data: [],
                status: 'error',
                message: 'Update failed. No matching patient found.',
                code: 404
            );
        }

        $patient = $this->patientRepo->get($validated["patient_id"]);

        return new PatientDTO(
            data: new PatientResource($patient),
            status: 'success',
            message: 'Patient updated successfully.',
            code: 200
        );
    }

    // ✅ Delete Patient
    public function remove($patient_id)
    {
        $patient = $this->patientRepo->get($patient_id);

        if (!$patient) {
            return new PatientDTO(
                data: [],
                status: 'error',
                message: 'Patient not found.',
                code: 404
            );
        }

        $this->patientRepo->delete($patient_id);

        return new PatientDTO(
            data: new PatientResource($patient),
            status: 'success',
            message: 'Patient deleted successfully.',
            code: 200
        );
    }

    // ✅ Get a Single Patient
    public function retrieve($patient_id)
    {
        $patient = $this->patientRepo->get($patient_id);

        if (!$patient) {
            return new PatientDTO(
                data: [],
                status: 'error',
                message: 'No patient exists with this ID.',
                code: 404
            );
        }

        return new PatientDTO(
            data: new PatientResource($patient),
            status: 'success',
            message: 'Patient found.',
            code: 200
        );
    }

    // ✅ Get All Patients
    public function all()
    {
        $patients = $this->patientRepo->all();

        return new PatientDTO(
            data: PatientResource::collection($patients)->resolve(),
            status: 'success',
            message: 'All patients retrieved successfully.',
            code: 200
        );
    }
}