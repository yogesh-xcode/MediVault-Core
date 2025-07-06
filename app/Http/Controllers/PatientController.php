<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Repositories\PatientRepository;
use Illuminate\Http\Request;
use App\DTOs\PatientDTO;
use App\Http\Resources\PatientResource;

class PatientController extends Controller
{
<<<<<<< HEAD
    public function __construct(private readonly PatientRepository $patientRepo)
    {
    }
=======
    public function __construct(private readonly PatientRepository $patientRepo) {}
>>>>>>> dev

    public function validationRules(): array
    {
        return [
<<<<<<< HEAD
            "create" => [
=======
            "patient" => [
>>>>>>> dev
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
<<<<<<< HEAD
            ],
            "update" => [
                'patient_id' => 'required|string',
                'field' => 'required|string',
                'new_value' => 'required|string'
=======
>>>>>>> dev
            ]
        ];
    }

    // ✅ Create Patient
    public function create(Request $request)
    {
<<<<<<< HEAD
        $validated = $request->validate($this->validationRules()["create"]);
=======
        $validated = $request->validate($this->validationRules()["patient"]);
>>>>>>> dev
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
<<<<<<< HEAD
        $validated = $request->validate($this->validationRules()["update"]);

        $updated = $this->patientRepo->update(
            $validated["patient_id"],
            $validated["field"],
            $validated["new_value"]
=======
        $validated = $request->validate([
            'patient_id' => 'required|string',
            'column'     => 'required|string',
            'value'      => 'required|string'
        ]);

        $updated = $this->patientRepo->update(
            $validated["patient_id"],
            $validated["column"],
            $validated["value"]
>>>>>>> dev
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
<<<<<<< HEAD
    public function remove($patient_id)
=======
    public function delete($patient_id)
>>>>>>> dev
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
<<<<<<< HEAD
    public function retrieve($patient_id)
=======
    public function get($patient_id)
>>>>>>> dev
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
<<<<<<< HEAD
    public function all()
    {
        $patients = $this->patientRepo->all();
=======
    public function getAll()
    {
        $patients = Patient::all();
>>>>>>> dev

        return new PatientDTO(
            data: PatientResource::collection($patients)->resolve(),
            status: 'success',
            message: 'All patients retrieved successfully.',
            code: 200
        );
    }
<<<<<<< HEAD
}
=======
}
>>>>>>> dev
