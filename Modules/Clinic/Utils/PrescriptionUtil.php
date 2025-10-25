<?php

namespace Modules\Clinic\Utils;

use App\Utils\Util;
use Modules\Clinic\Entities\DoctorProfile;
use Modules\Clinic\Entities\PatientAppointmentRequ;
use App\{User, Transaction};
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Clinic\Entities\DoctorAdvice;
use Modules\Clinic\Entities\PrescribeAdvice;
use Modules\Clinic\Entities\PrescribedCC;
use Modules\Clinic\Entities\Prescription;
use Modules\Clinic\Entities\PrescribeTherapie;
use Exception;
use Modules\Clinic\Entities\ChiefComplain;
use Modules\Clinic\Entities\DiseaseHistory;
use Modules\Clinic\Entities\InvestigationHistory;
use Modules\Clinic\Entities\PrescribeMedicine;
use Modules\Clinic\Entities\PrescribeTest;
use Modules\Clinic\Entities\PrescriptionTemplate;
use Modules\Clinic\Entities\MissingMedicine;
use Modules\Clinic\Entities\PatientSessionDetails;
use Modules\Clinic\Entities\PatientSessionInfo;
use Modules\Clinic\Entities\MissingTest;
use Modules\Clinic\Entities\MissingTherapy;
use Carbon\Carbon;
use App\Contact;
use Illuminate\Support\Facades\Http;
use Modules\Clinic\Entities\PrescribeIpdAdmission;

class PrescriptionUtil extends Util
{
    public function generatePrescription($data)
    {
        DB::beginTransaction();

        try {
            $prescriptionNumber = $this->generatePrescriptionNumber();
            $appointment_id = $data['appointment_id'];
            $appointment = PatientAppointmentRequ::with('patient')->find($appointment_id);
            $sessionId = $appointment->patient_session_info_id;
            // 
            if (!$appointment) {
                throw new Exception('Appointment not found');
            }

            $prescription = $this->StorePrescription($prescriptionNumber, $appointment, $data);
            $medicine = $this->storeMedicine($prescription->id, $appointment, $data);
            $test = $this->storeTest($prescription->id, $data);
            $therapy = $this->storeTherapy($prescription->id, $data);
            $advices = $this->StoreAdvice($prescription->id, $data);
            $ipdAdmission = $this->storeIpdAdmission($prescription->id, $data);
            $complains = $this->StoreComplain($prescription->id, $data);
            $investigationHistories = $this->StoreInvestigationHistories($prescription->id, $data);
            $diseaseHistories = $this->StoreDiseaseHistories($prescription->id, $data);

            if ($data['action'] == 'save_as_template') {
                $prescription->template = 1;
                $prescription->save();
                $template = $this->storeTemplate($prescription, $data);
            }
            $appointment->remarks = 'prescribed';
            $appointment->can_visit = now();
            $appointment->is_visited = 1;
            $appointment->visited_date = now();
            $appointment->save();
            if ($prescription->modified_by == null) {
                $session = $this->storeVisite($sessionId, $appointment->patient_contact_id);
            }
            DB::commit();
            $output = ['success' => true, 'msg' => 'Prescription generated successfully', 'prescription' => $prescription];
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error generating prescription: ' . $e->getMessage() .
                ' in file ' . $e->getFile() . ' on line ' . $e->getLine());

            $output = [
                'success' => false,
                'msg' => 'Failed to generate prescription: ' . $e->getMessage()
            ];
        }

        return $output;
    }
    private function calculateTemplateSimilarity($currentData, $templateId)
    {
        $template = PrescriptionTemplate::where('id', $templateId)->first();

        if (!$template) {
            return 0;
        }

        // $templatePrescription = Prescription::findOrFail($template->prescription_id);

        // Calculate percentage
        $similarityPercentage = 98;

        return round($similarityPercentage, 2);
    }

    public function StorePrescription($prescriptionNumber, $appointment, $data)
    {
        $prescription = Prescription::where('appointment_id', $appointment->id)->first();
        $followUpDate = $prescription?->follow_up_date; // Default to existing value
        if (!$prescription || isset($data['follow_up_number']) || isset($data['follow_up_type'])) {
            if (!empty($data['follow_up_number']) && !empty($data['follow_up_type'])) {
                $baseDate = Carbon::parse($appointment->request_date ?? now()); // Base date: request_date or now
                $followUpDate = match (strtolower($data['follow_up_type'])) {
                    'days' => $baseDate->addDays($data['follow_up_number']),
                    'months' => $baseDate->addMonths($data['follow_up_number']),
                    'years' => $baseDate->addYears($data['follow_up_number']),
                    default => null
                };
            }
        }

        // Get the template ID from the prescription_templates table
        $template = PrescriptionTemplate::where('prescription_id', $data['template_id'])->first();
        $templateId = $template->id ?? null;

        $prescription = Prescription::updateOrCreate(
            ['appointment_id' => $appointment->id],
            [
                'prescription_number' => $prescriptionNumber ?? null,
                'visit_date' => $appointment->request_date ?? null,
                'doctor_user_id' => $appointment->doctor_user_id ?? null,
                'prescription_date' => now() ?? null,
                'patient_contact_id' => $appointment->patient_contact_id ?? null,
                'patient_profile_id' => $appointment->patient_profile_id ?? null,
                'transaction_id' => $appointment->bill_no ?? null,
                'name' => $appointment->patient->first_name . ' ' . $appointment->patient->last_name ?? null,
                'age' => $appointment->patient->age ?? null,
                'type' => $appointment->appointment_type ?? 'doctor',
                'gender' => $appointment->patient->gender ?? null,
                'current_weight' => $data['current_weight'] ?? null,
                'current_height' => $data['current_height'] ?? null,
                'current_height_feet' => $data['current_height_feet'] ?? null,
                'current_height_inches' => $data['current_height_inches'] ?? null,
                'body_temp' => $data['body_temp'] ?? null,
                'blood_pressure' => $data['blood_pressure'] ?? null,
                'diastolic_pressure' => $data['diastolic_pressure'] ?? null,
                'systolic_pressure' => $data['systolic_pressure'] ?? null,
                'pulse_rate' => $data['pulse_rate'] ?? null,
                'respiratory' => $data['respiratory'] ?? null,
                'bmi' => $data['bmi'] ?? null,
                'body_fat_percent' => $data['body_fat_percent'] ?? null,
                'fat_mass_percent' => $data['fat_mass_percent'] ?? null,
                'lean_mass_percent' => $data['lean_mass_percent'] ?? null,
                'note' => $data['note'] ?? null,
                'differential_diagonosis_ddx' => $data['differential_diagonosis_ddx'] ?? null,
                'next_visit_date' => $followUpDate ? $followUpDate->format('Y-m-d H:i:s') : null,
                'comments' => $data['comments'] ?? null,
                'follow_up' => $data['follow_up'] ?? null,
                'follow_up_type' => $data['follow_up_type'] ?? null,
                'follow_up_number' => $data['follow_up_number'] ?? null,
                'created_by' => auth()->user()->id ?? null,
                'template_id' => $templateId
            ]
        );

        $log_properties = [
            'id' => $prescription->id,
        ];

        // Calculate similarity if template is used
        if (!empty($templateId)) {
            $prescription->template_similarity_percentage = $this->calculateTemplateSimilarity($data, $templateId);
        }

        if ($prescription->wasRecentlyCreated) {
            $prescription->start_time = $data['start_time'] ?? null;
            $prescription->end_time = now();
            $prescription->save();

            $this->activityLog($prescription, 'Prescription Created', null, $log_properties);
        }
        if (!$prescription->wasRecentlyCreated) {
            $prescription->modified_by = auth()->user()->id;
            $prescription->save();

            $this->activityLog($prescription, 'Updated', null, $log_properties);
        }

        return $prescription;
    }


    private function StoreAdvice($prescription, $data, $type = 'doctor')
    {
        if (empty($data['advice_id'])) {
            PrescribeAdvice::where('prescription_id', $prescription)->delete();
            return [];
        }
        $existingAdvices = PrescribeAdvice::where('prescription_id', $prescription)
            ->where('type', $type)
            ->pluck('advice_id')
            ->toArray();

        $adviceIdsToDelete = array_diff($existingAdvices, $data['advice_id']);
        PrescribeAdvice::where('prescription_id', $prescription)
            ->where('type', $type)
            ->whereIn('advice_id', $adviceIdsToDelete)
            ->delete();

        $adviceList = DoctorAdvice::whereIn('id', $data['advice_id'])->pluck('value', 'id')->toArray();

        $storedAdvices = [];
        foreach ($data['advice_id'] as $index => $adviceId) {
            $adviseName = $adviceList[$adviceId] ?? null;

            $advice = PrescribeAdvice::updateOrCreate(
                [
                    'prescription_id' => $prescription,
                    'advice_id' => $adviceId,
                    'type' => $type
                ],
                [
                    'advise_name' => $adviseName,
                    'is_natural' => 1,
                    'created_by' => auth()->user()->id,
                ]
            );

            $storedAdvices[] = $advice;
        }

        Log::info('Advices successfully stored/updated for prescription ID: ' . $prescription, [
            'advice_ids' => $data['advice_id'],
            'advice_names' => array_values($adviceList)
        ]);

        return $storedAdvices;
    }

    private function storeIpdAdmission($prescription_id, $data)
    {
        try {
            // Check if ipd_admission is set, default to false if not provided
            $isIpdAdmission = isset($data['ipd_admission']) ? $data['ipd_admission'] : false;
            
            // Set admission_days only if ipd_admission is true and ipd_days is provided
            $admissionDays = ($isIpdAdmission && isset($data['ipd_days'])) ? $data['ipd_days'] : null;

            $ipdAdmission = PrescribeIpdAdmission::updateOrCreate(
                ['prescription_id' => $prescription_id],
                [
                    'is_ipd_admission' => $isIpdAdmission,
                    'admission_days' => $admissionDays,
                ]
            );

            // Log the activity
            $log_properties = [
                'id' => $ipdAdmission->id,
                'is_ipd_admission' => $ipdAdmission->is_ipd_admission,
                'admission_days' => $ipdAdmission->admission_days,
            ];

            if ($ipdAdmission->wasRecentlyCreated) {
                $this->activityLog($ipdAdmission, 'IPD Admission Created', null, $log_properties);
            } else {
                $this->activityLog($ipdAdmission, 'IPD Admission Updated', null, $log_properties);
            }

            return $ipdAdmission;

        } catch (Exception $e) {
            Log::error('Error storing IPD admission: ' . $e->getMessage());
            throw new Exception('Failed to store IPD admission: ' . $e->getMessage());
        }
    }


    private function generatePrescriptionNumber()
    {
        $latestPrescription = DB::table('prescriptions')
            ->latest('prescription_number')
            ->first();

        $nextPrescriptionNumber = $latestPrescription
            ? (int) $latestPrescription->prescription_number + 1
            : 1;
        $prescriptionNumber = str_pad($nextPrescriptionNumber, 8, '0', STR_PAD_LEFT);
        while (DB::table('prescriptions')->where('prescription_number', $prescriptionNumber)->exists()) {
            $nextPrescriptionNumber++;
            $prescriptionNumber = str_pad($nextPrescriptionNumber, 8, '0', STR_PAD_LEFT);
        }

        return $prescriptionNumber;
    }
    private function storeMedicine($prescriptionNumber, $appointment, $data)
    {
        $count = min(
            count($data['medicine_name'] ?? []),
            count($data['generic_name'] ?? []),
            count($data['taken_instruction'] ?? []),
            count($data['dosage_form'] ?? []),
            count($data['medication_duration'] ?? []),
            count($data['product_id'] ?? []),
            count($data['generic_id'] ?? []),
            count($data['medicine_comment'] ?? [])
        );
        $data['medicine_name'] = array_slice($data['medicine_name'] ?? [], 0, $count);
        $data['generic_name'] = array_slice($data['generic_name'] ?? [], 0, $count);
        $data['taken_instruction'] = array_slice($data['taken_instruction'] ?? [], 0, $count);
        $data['dosage_form'] = array_slice($data['dosage_form'] ?? [], 0, $count);
        $data['medication_duration'] = array_slice($data['medication_duration'] ?? [], 0, $count);
        $data['product_id'] = array_slice($data['product_id'] ?? [], 0, $count);
        $data['generic_id'] = array_slice($data['generic_id'] ?? [], 0, $count);
        $data['medicine_comment'] = array_slice($data['medicine_comment'] ?? [], 0, $count);

        $existingMedicines = PrescribeMedicine::where('prescription_id', $prescriptionNumber)
            ->pluck('x_medicine_id')
            ->toArray();

        $oldMedicineCount = count($existingMedicines);
        $newMedicineCount = count($data['product_id']);

        $newMedicineIds = $data['product_id'];

        $medicinesToDelete = array_diff($existingMedicines, $newMedicineIds);

        if (!empty($medicinesToDelete)) {
            PrescribeMedicine::where('prescription_id', $prescriptionNumber)
                ->whereIn('x_medicine_id', $medicinesToDelete)
                ->delete();
        }
        MissingMedicine::where('prescription_id', $prescriptionNumber)
            ->whereNotIn('name', $data['medicine_name'])
            ->delete();

        $storedMedicines = [];
        $updatedMedicines = [];
        $createdMedicines = [];

        foreach ($data['medicine_name'] as $index => $medicineName) {
            if (
                !isset(
                    $data['product_id'][$index],
                    $data['taken_instruction'][$index],
                    $data['dosage_form'][$index],
                    $data['medication_duration'][$index]
                )
            ) {
                Log::warning("Skipping medicine entry at index $index due to missing data.");
                continue;
            }

            $productId = $data['product_id'][$index];
            $comment = $data['medicine_comment'][$index];
            $wasCreated = false;
            if ($productId == 0) {
                $missingMedicine = MissingMedicine::updateOrCreate(
                    [
                        'prescription_id' => $prescriptionNumber,
                        'name' => $medicineName,
                    ],
                    [
                        'created_by' => auth()->user()->id ?? null,
                    ]
                );

                Log::info('Created or updated missing medicine entry: ' . $medicineName);

                $medicine = PrescribeMedicine::updateOrCreate(
                    [
                        'prescription_id' => $prescriptionNumber,
                        'x_medicine_name' => $medicineName,
                    ],
                    [
                        'x_medicine_id' => $productId,
                        'patient_profile_id' => $appointment['patient_profile_id'] ?? null,
                        'doctor_profile_id' => $appointment['doctor_profile_id'] ?? null,
                        'comment' => $comment,
                        'taken_instruction' => $data['taken_instruction'][$index],
                        'dosage_form' => $data['dosage_form'][$index],
                        'medication_duration' => $data['medication_duration'][$index],
                        'created_by' => auth()->user()->id ?? null,
                    ]
                );
                $wasCreated = $medicine->wasRecentlyCreated;
            } else {
                $medicine = PrescribeMedicine::updateOrCreate(
                    [
                        'prescription_id' => $prescriptionNumber,
                        'x_medicine_id' => $productId,
                    ],
                    [
                        'patient_profile_id' => $appointment['patient_profile_id'] ?? null,
                        'doctor_profile_id' => $appointment['doctor_profile_id'] ?? null,
                        'x_medicine_name' => $medicineName,
                        'generic_id' => $data['generic_id'][$index],
                        'generic_name' => $data['generic_name'][$index],
                        'comment' => $comment,
                        'taken_instruction' => $data['taken_instruction'][$index],
                        'dosage_form' => $data['dosage_form'][$index],
                        'medication_duration' => $data['medication_duration'][$index],
                        'created_by' => auth()->user()->id ?? null,
                    ]
                );
                $wasCreated = $medicine->wasRecentlyCreated;
            }

            $storedMedicines[] = $medicine;
            if ($wasCreated) {
                $createdMedicines[] = $medicine;
            } else {
                $updatedMedicines[] = $medicine;
            }
        }

        $log_properties = [
            'id' => $prescriptionNumber,
            'medicine_ids' => collect($storedMedicines)->pluck('x_medicine_id')->join(', '),
            'medicine_generic_ids' => collect($storedMedicines)->pluck('generic_id')->join(', '),
            'medicine_names' => collect($storedMedicines)->pluck('x_medicine_name')->join(', '),
            'medicine_generic_names' => collect($storedMedicines)->pluck('generic_name')->join(', '),
            'old_medicine_count' => $oldMedicineCount,
            'new_medicine_count' => $newMedicineCount,
            'created_medicines' => collect($createdMedicines)->pluck('x_medicine_name')->join(', '),
            'updated_medicines' => collect($updatedMedicines)->pluck('x_medicine_name')->join(', ')
        ];

        if (!empty($storedMedicines)) {
            $action = !empty($createdMedicines) && empty($updatedMedicines) ? 'Medicines Created' : (empty($createdMedicines) && !empty($updatedMedicines) ? 'Medicines Updated' :
                'Medicines Created and Updated');
            $this->activityLog($storedMedicines[0], $action, null, $log_properties);
        }

        return $storedMedicines;
    }

    private function storeTest($prescriptionNumber, $data)
    {
        $oldTestCount = PrescribeTest::where('prescription_id', $prescriptionNumber)->count();

        if (empty($data['test_product_id'])) {
            $deletedTests = PrescribeTest::where('prescription_id', $prescriptionNumber)->delete();
            MissingTest::where('prescription_id', $prescriptionNumber)->delete(); // Delete missing tests as well
            return [];
        }

        // Existing test IDs in the main table
        $existingTests = PrescribeTest::where('prescription_id', $prescriptionNumber)
            ->pluck('product_id')
            ->toArray();

        // New test product IDs from the request
        $newTestProductIds = $data['test_product_id'];

        // Find tests to delete
        $testsToDelete = array_diff($existingTests, $newTestProductIds);

        // Delete tests that are no longer needed
        PrescribeTest::where('prescription_id', $prescriptionNumber)
            ->whereIn('product_id', $testsToDelete)
            ->delete();

        // Delete missing tests that are no longer needed
        MissingTest::where('prescription_id', $prescriptionNumber)
            ->whereNotIn('name', $data['test_name'])
            ->delete();

        $storedTests = [];
        $createdTests = [];
        $updatedTests = [];

        foreach ($data['test_name'] as $index => $testName) {
            $productId = $data['test_product_id'][$index] ?? null;
            $comment = $data['test_comment'][$index] ?? null;

            if ($productId == 0) {
                // Handle missing tests (product_id = 0)
                $missingTest = MissingTest::updateOrCreate(
                    [
                        'prescription_id' => $prescriptionNumber,
                        'name' => $testName, // Use test_name as a unique identifier
                    ],
                    [
                        'created_by' => auth()->user()->id,
                    ]
                );

                Log::info('Created or updated missing test entry: ' . $testName);

                // Also store in the main table
                $test = PrescribeTest::updateOrCreate(
                    [
                        'prescription_id' => $prescriptionNumber,
                        'test_name' => $testName, // Use test_name as a unique identifier
                    ],
                    [
                        'product_id' => $productId,
                        'comment' => $comment,
                        'created_by' => auth()->user()->id,
                    ]
                );

                $wasCreated = $test->wasRecentlyCreated;
            } else {
                // Handle regular tests (product_id != 0)
                $test = PrescribeTest::updateOrCreate(
                    [
                        'prescription_id' => $prescriptionNumber,
                        'product_id' => $productId,
                    ],
                    [
                        'test_name' => $testName,
                        'comment' => $comment,
                        'created_by' => auth()->user()->id,
                    ]
                );

                $wasCreated = $test->wasRecentlyCreated;
            }

            $storedTests[] = $test;
            if ($wasCreated) {
                $createdTests[] = $test;
            } else {
                $updatedTests[] = $test;
            }
        }

        $newTestCount = count($storedTests);

        $log_properties = [
            'id' => $prescriptionNumber,
            'test_ids' => collect($storedTests)->pluck('product_id')->join(', '),
            'test_names' => collect($storedTests)->pluck('test_name')->join(', '),
            'old_test_count' => $oldTestCount,
            'new_test_count' => $newTestCount,
            'created_tests' => collect($createdTests)->pluck('test_name')->join(', '),
            'updated_tests' => collect($updatedTests)->pluck('test_name')->join(', ')
        ];

        if (!empty($storedTests)) {
            $action = !empty($createdTests) && empty($updatedTests) ? 'Tests Created' : (empty($createdTests) && !empty($updatedTests) ? 'Tests Updated' :
                'Tests Created and Updated');
            $this->activityLog($storedTests[0], $action, null, $log_properties);
        }

        return $storedTests;
    }

    private function StoreComplain($prescriptionNumber, $data)
    {
        if (empty($data['chief_complain_id'])) {
            PrescribedCC::where('prescription_id', $prescriptionNumber)->delete();
            return [];
        }
        $existingComplain = PrescribedCC::where('prescription_id', $prescriptionNumber)
            ->pluck('complain_id')
            ->toArray();
        $newComplainId = $data['chief_complain_id'];
        $complainToDelete = array_diff($existingComplain, $newComplainId);
        PrescribedCC::where('prescription_id', $prescriptionNumber)
            ->whereIn('complain_id', $complainToDelete)
            ->delete();
        $storedComplain = [];
        foreach ($data['complain_name'] as $index => $complain_name) {
            $complainId = $data['chief_complain_id'][$index] ?? null;
            $comment = $data['complain_comment'][$index] ?? null;
            $complain = PrescribedCC::updateOrCreate(
                [
                    'prescription_id' => $prescriptionNumber,
                    'complain_id' => $complainId
                ],
                [
                    'complain_name' => $complain_name,
                    'comment' => $comment,
                    'created_by' => auth()->user()->id,
                ]
            );

            $storedComplain[] = $complain;
        }

        return $storedComplain;
    }
    private function StoreInvestigationHistories($prescriptionId, $data)
    {
        // If no investigation history data is provided, delete existing records related to the prescription
        if (empty($data['investigation_history'])) {
            InvestigationHistory::where('prescription_id', $prescriptionId)->delete();
            return [];
        }

        // Existing investigation histories in the main table
        $existingHistories = InvestigationHistory::where('prescription_id', $prescriptionId)
            ->pluck('test_name')
            ->toArray();

        // New investigation history test names from the request
        $newTestNames = array_column($data['investigation_history'], 'test_name');

        // Find histories to delete
        $historiesToDelete = array_diff($existingHistories, $newTestNames);

        // Delete histories that are no longer needed
        InvestigationHistory::where('prescription_id', $prescriptionId)
            ->whereIn('test_name', $historiesToDelete)
            ->delete();

        $storedHistories = [];

        // Loop through the investigation history data and store or update entries
        foreach ($data['investigation_history'] as $history) {
            if (!empty($history['test_name'])) {
                // Store or update investigation history entries
                $investigationHistory = InvestigationHistory::updateOrCreate(
                    [
                        'prescription_id' => $prescriptionId,
                        'test_name' => $history['test_name'],
                    ],
                    [
                        'date' => $history['date'],
                        'result_value' => $history['result_value'],
                        'created_by' => auth()->user()->id,
                    ]
                );

                // Log information
                Log::info('Created or updated investigation history entry for test: ' . $history['test_name']);

                // Store the investigation history record in an array for return
                $storedHistories[] = $investigationHistory;
            }
        }

        return $storedHistories;
    }

    private function StoreDiseaseHistories($prescriptionNumber, $data)
    {
        if (empty($data['disease_history'])) {
            DiseaseHistory::where('prescription_id', $prescriptionNumber)->delete();
            return [];
        }
        $existingHistories = DiseaseHistory::where('prescription_id', $prescriptionNumber)
            ->pluck('chief_complaint_id')
            ->toArray();
        $newChiefComplaintIds = $data['disease_history'];
        $historiesToDelete = array_diff($existingHistories, $newChiefComplaintIds);
        DiseaseHistory::where('prescription_id', $prescriptionNumber)
            ->whereIn('chief_complaint_id', $historiesToDelete)
            ->delete();

        $storedHistories = [];

        foreach ($data['disease_history'] as $chiefComplaintId) {
            $diseaseHistory = DiseaseHistory::updateOrCreate(
                [
                    'prescription_id' => $prescriptionNumber,
                    'chief_complaint_id' => $chiefComplaintId,
                ],
                [
                    'created_by' => auth()->user()->id,
                ]
            );
            $storedHistories[] = $diseaseHistory;
        }

        return $storedHistories;
    }


    private function storeTherapy($prescriptionNumber, $data)
    {
        $oldTherapyCount = PrescribeTherapie::where('prescription_id', $prescriptionNumber)->count();

        if (empty($data['therapy_product_id'])) {
            PrescribeTherapie::where('prescription_id', $prescriptionNumber)->delete();
            MissingTherapy::where('prescription_id', $prescriptionNumber)->delete();
            return [];
        }
        $existingTherapy = PrescribeTherapie::where('prescription_id', $prescriptionNumber)
            ->pluck('product_id')
            ->toArray();
        $newTherapyProductIds = $data['therapy_product_id'];
        $therapyToDelete = array_diff($existingTherapy, $newTherapyProductIds);
        PrescribeTherapie::where('prescription_id', $prescriptionNumber)
            ->whereIn('product_id', $therapyToDelete)
            ->delete();
        MissingTherapy::where('prescription_id', $prescriptionNumber)
            ->whereNotIn('name', $data['therapy_name'])
            ->delete();

        $storedTherapy = [];
        $createdTherapies = [];
        $updatedTherapies = [];

        foreach ($data['therapy_name'] as $index => $therapyName) {
            $productId = $data['therapy_product_id'][$index] ?? null;
            $frequency = $data['therapy_frequency'][$index] ?? null;
            $sessionCount = $data['session_count'][$index] ?? null;

            if ($productId == 0) {
                $alternativeTherapy = MissingTherapy::updateOrCreate(
                    [
                        'prescription_id' => $prescriptionNumber,
                        'name' => $therapyName,
                    ],
                    [
                        'frequency' => $frequency,
                        'created_by' => auth()->user()->id,
                    ]
                );
                $therapy = PrescribeTherapie::updateOrCreate(
                    [
                        'prescription_id' => $prescriptionNumber,
                        'therapy_name' => $therapyName,
                    ],
                    [
                        'product_id' => $productId,
                        'frequency' => $frequency,
                        'session_count' => $sessionCount,
                        'created_by' => auth()->user()->id,
                    ]
                );
                $wasCreated = $therapy->wasRecentlyCreated;
            } else {
                $therapy = PrescribeTherapie::updateOrCreate(
                    [
                        'prescription_id' => $prescriptionNumber,
                        'product_id' => $productId,
                    ],
                    [
                        'therapy_name' => $therapyName,
                        'frequency' => $frequency,
                        'session_count' => $sessionCount,
                        'created_by' => auth()->user()->id,
                    ]
                );
                $wasCreated = $therapy->wasRecentlyCreated;
            }

            $storedTherapy[] = $therapy;
            if ($wasCreated) {
                $createdTherapies[] = $therapy;
            } else {
                $updatedTherapies[] = $therapy;
            }
        }

        $log_properties = [
            'id' => $prescriptionNumber,
            'therapy_ids' => collect($storedTherapy)->pluck('product_id')->join(', '),
            'therapy_names' => collect($storedTherapy)->pluck('therapy_name')->join(', '),
            'old_therapy_count' => $oldTherapyCount,
            'new_therapy_count' => count($data['therapy_product_id']),
            'created_therapies' => collect($createdTherapies)->pluck('therapy_name')->join(', '),
            'updated_therapies' => collect($updatedTherapies)->pluck('therapy_name')->join(', ')
        ];

        if (!empty($storedTherapy)) {
            $action = !empty($createdTherapies) && empty($updatedTherapies) ? 'Therapies Created' : (empty($createdTherapies) && !empty($updatedTherapies) ? 'Therapies Updated' :
                'Therapies Created and Updated');
            $this->activityLog($storedTherapy[0], $action, null, $log_properties);
        }

        return $storedTherapy;
    }
    private function storeTemplate($prescription, $data)
    {
        $template = PrescriptionTemplate::where('appointment_id', $data['appointment_id'])->first();

        if (!$template) {
            $template = new PrescriptionTemplate(); // Create new template if not found
            $template->appointment_id = $data['appointment_id'];
        }

        $template->prescription_id = $prescription->id;
        $template->status = 1;
        $template->created_by = auth()->user()->id;
        $template->save();

        return $template;
    }


    public function getPrescriptionQuery()
    {
        return DB::table('prescriptions')
            ->join('patient_profiles', 'patient_profiles.id', '=', 'prescriptions.patient_profile_id')
            ->join('users as created_by_user', 'created_by_user.id', '=', 'prescriptions.created_by')
            ->leftJoin('patient_appointment_requests', 'patient_appointment_requests.patient_profile_id', '=', 'prescriptions.patient_profile_id')
            ->leftJoin('users as assigned_doctor', 'assigned_doctor.id', '=', 'prescriptions.doctor_user_id')
            ->select(
                'prescriptions.id',
                'patient_profiles.first_name as patient_first_name',
                'patient_profiles.last_name as patient_last_name',
                'patient_profiles.mobile as patient_mobile',
                'created_by_user.first_name as creator_first_name',
                'created_by_user.last_name as creator_last_name',
                'assigned_doctor.first_name as assigned_doctor_first_name',
                'assigned_doctor.last_name as assigned_doctor_last_name',
                'prescriptions.created_at',
            )
            ->groupBy('prescriptions.id', 'patient_profiles.first_name', 'patient_profiles.last_name', 'patient_profiles.mobile', 'created_by_user.first_name', 'created_by_user.last_name', 'assigned_doctor.first_name', 'assigned_doctor.last_name');
    }
    private function storeVisite($sessionId, $patient_id)
    {
        $sessionInfo = PatientSessionInfo::where('id', $sessionId)->latest()->first();
        $transaction = Transaction::find($sessionInfo->transaction_id);
        if ($sessionInfo) {
            $sessionDetails = new PatientSessionDetails();
            $sessionDetails->patient_session_id = $sessionId;
            $sessionDetails->patient_contact_id = $patient_id;
            $sessionDetails->visit_date = now();
            $sessionDetails->transaction_amount = $sessionInfo->session_amount;
            $sessionDetails->created_by = auth()->user()->id;
            $sessionDetails->save();
            $sessionInfo->visited_count += 1;
            $sessionInfo->remaining_visit -= 1;
            if ($sessionInfo->remaining_visit == 0 && $transaction->payment_status == 'paid') {
                $sessionInfo->is_closed = 1;
            }
            $sessionInfo->updated_by = auth()->user()->id;
            $sessionInfo->save();
        }
    }
    // Api medicine store
    private function storePharmacy($contactId, $doctorName, $appointment_id, $data, $prescriptionId)
    {
        $product_ids = isset($data['product_id']) ? array_filter($data['product_id'], function ($id) {
            return $id > 0;
        }) : [];

        $generic_ids = isset($data['generic_id']) ? array_filter($data['generic_id'], function ($id) {
            return $id > 0;
        }) : [];

        Log::info('Prescription data product_id: ' . json_encode($product_ids));
        Log::info('Prescription data generic_id: ' . json_encode($generic_ids));

        // সব প্রোডাক্ট API থেকে আনা
        $products_info = Http::get('https://awc.careneterp.com:82/api/products');
        $products = collect($products_info->json());

        $finalProducts = collect();
        $addedGenericIds = []; // কোন generic_id এর product যোগ হয়েছে তা ট্র্যাক করার জন্য

        // যদি generic_ids থাকে
        if (!empty($generic_ids)) {
            foreach ($generic_ids as $gid) {
                $genericProducts = $products->where('generic_id', $gid)->where('qty_available', '>', 0);

                if ($genericProducts->isNotEmpty()) {
                    // lowest brand priority বের করা
                    $minPriority = $genericProducts->min(function ($p) {
                        return $p['brand']['priority'] ?? PHP_INT_MAX;
                    });

                    // ওই priority-র products ফিল্টার করা
                    $samePriorityProducts = $genericProducts->filter(function ($p) use ($minPriority) {
                        return isset($p['brand']['priority']) && $p['brand']['priority'] == $minPriority;
                    });

                    // একই priority-র মধ্যে qty_available সবচেয়ে বেশি যেটা সেটি নেওয়া
                    $selectedProduct = $samePriorityProducts->sortByDesc(function ($p) {
                        return (float)$p['qty_available'];
                    })->first();

                    if ($selectedProduct) {
                        $qty = $this->calculateQty(
                            $appointment_id,
                            $selectedProduct['product_id'],
                            $prescriptionId,
                            $selectedProduct['size'],
                            $selectedProduct['pack_unit']
                        );

                        $finalProducts->push([
                            'product_id' => $selectedProduct['product_id'],
                            'quantity' => $qty,
                        ]);

                        // এই generic_id add হয়ে গেছে
                        $addedGenericIds[] = $gid;

                        Log::info("Selected product for generic_id {$gid}: " . $selectedProduct['product_id'] . " (priority={$minPriority}, qty_available={$selectedProduct['qty_available']})");
                    }
                }
            }
        }

        // product_id দিয়ে আসা গুলো যোগ করা (যদি generic_id না থাকে বা উপরের লিস্টে না থাকে)
        foreach ($product_ids as $id) {
            $product = $products->firstWhere('product_id', $id);

            if ($product && $product['qty_available'] > 0) {
                $productGenericId = $product['generic_id'] ?? 0;

                if (empty($productGenericId) || !in_array($productGenericId, $addedGenericIds)) {
                    $qty = $this->calculateQty(
                        $appointment_id,
                        $product['product_id'],
                        $prescriptionId,
                        $product['size'],
                        $product['pack_unit']
                    );

                    $finalProducts->push([
                        'product_id' => $id,
                        'quantity' => $qty,
                    ]);

                    Log::info("Added product_id {$id} (no/unused generic_id={$productGenericId})");
                }
            }
        }

        $apiData = [
            'customer_id' => $contactId,
            'appointment_id' => $appointment_id,
            'reference' => $doctorName,
            'products' => $finalProducts->values()->toArray(),
        ];

        $url = 'https://awc.careneterp.com:82/api/storeClinicOrder';
        $response = Http::post($url, $apiData);

        return $response;
    }

    public function calculateQty($appointment_id, $productId, $prescriptionId, $size, $pack_unit)
    {
        $medicine = PrescribeMedicine::join('dosage', 'dosage.id', '=', 'prescribed_medicines.taken_instruction')
            ->where('prescription_id', $prescriptionId)
            ->where('x_medicine_id', $productId)
            ->select('dosage.value')
            ->first();

        $duration = PrescribeMedicine::join('durations', 'durations.id', '=', 'prescribed_medicines.medication_duration')
            ->where('prescription_id', $prescriptionId)
            ->where('x_medicine_id', $productId)
            ->select('durations.value')
            ->first();
        // Extract numeric value from size
        preg_match('/(\d+)/', $size, $sizeMatches);
        $sizeValue = $sizeMatches ? intval($sizeMatches[1]) : null;

        Log::info('Extracted size value: ' . $sizeValue);

        if (!$medicine || !$duration) {
            Log::info('Medicine or duration not found.');
            return 0; // Return 0 to avoid undefined variable issues
        }

        // Extract dosage values and sum them
        $dosageValues = explode('+', $medicine->value);
        $dosageSum = array_sum(array_map('intval', $dosageValues));

        Log::info('Separated dosage values: ' . json_encode($dosageValues));
        Log::info('Total dosage sum: ' . $dosageSum);

        // Extract number and unit from duration
        preg_match('/(\d+)\s*(\w+)/i', $duration->value, $matches);

        if (!$matches) {
            Log::info('Invalid duration format.');
            return 0; // Return 0 if duration format is invalid
        }

        $durationValue = intval($matches[1]); // Extract number (e.g., 7)
        $durationUnit = strtolower($matches[2]); // Convert to lowercase

        // Convert duration to days
        if (str_contains($durationUnit, 'month')) {
            $totalDays = $durationValue * 30; // Convert months to days
        } elseif (str_contains($durationUnit, 'year')) {
            $totalDays = $durationValue * 365; // Convert years to days
        } else {
            $totalDays = $durationValue; // Treat as days
        }

        $totalQty = $totalDays * $dosageSum;
        if ($pack_unit == 'cap' || $pack_unit == 'tab') {
            $orgQty = round($totalQty / $sizeValue);
        } else {
            $orgQty = 1;
        }

        Log::info("Duration: {$durationValue} {$durationUnit}");
        Log::info("Total quantity: " . $totalQty);
        Log::info("Organized quantity: " . $orgQty);

        return $orgQty;
    }

    public function activityLog($on, $action = null, $before = null, $properties = [], $log_changes = true, $business_id = null)
    {
        if ($log_changes) {
            $log_properties = $on->log_properties ?? [];
            foreach ($log_properties as $property) {
                if (isset($on->$property)) {
                    $properties['attributes'][$property] = $on->$property;
                }

                if (! empty($before) && isset($before->$property)) {
                    $properties['old'][$property] = $before->$property;
                }
            }
        }

        //Check if session has business id
        $business_id = session()->has('business') ? session('business.id') : $business_id;

        //Check if subject has business id
        if (empty($business_id) && ! empty($on->business_id)) {
            $business_id = $on->business_id;
        }
        // Check for IP address from headers, fallback to getClientIp()
        $properties['ip_address'] = $this->getUserIP();

        Log::info($properties['ip_address']);

        // dd($properties['ip_address']);
        $business = session()->has('business') ? session('business') : Business::find($business_id);

        date_default_timezone_set($business->time_zone);

        $activity = activity()
            ->performedOn($on)
            ->withProperties($properties)
            ->log($action);

        $activity->business_id = $business_id;
        $activity->save();
    }
    public function doctorPrescriptionReport()
    {
        $report = DB::table('prescriptions as p')
            ->join('patient_appointment_requests as pa', 'pa.id', '=', 'p.appointment_id')
            ->join('patient_profiles as pp', 'pp.id', '=', 'p.patient_profile_id')
            ->join('users as d', 'd.id', '=', 'p.doctor_user_id')
            ->join('doctor_profiles as dp', 'dp.user_id', '=', 'd.id')
            ->select(
                'dp.first_name as doctor_first_name',
                'dp.last_name as doctor_last_name',
                'p.doctor_user_id',
                DB::raw("SUM(
                    CASE 
                        WHEN (
                            SELECT COUNT(*) 
                            FROM patient_appointment_requests prev 
                            WHERE prev.patient_profile_id = pp.id 
                            AND prev.id < pa.id
                        ) = 0 THEN 1 ELSE 0 
                    END
                ) as new_patients"),
                DB::raw("SUM(
                    CASE 
                        WHEN (
                            SELECT MAX(request_date)
                            FROM patient_appointment_requests prev
                            WHERE prev.patient_profile_id = pp.id
                            AND prev.id < pa.id
                        ) >= DATE_SUB(pa.request_date, INTERVAL 4 MONTH) THEN 1 ELSE 0 
                    END
                ) as follow_up_patients"),
                DB::raw("SUM(
                    CASE 
                        WHEN (
                            SELECT COUNT(*) 
                            FROM patient_appointment_requests prev 
                            WHERE prev.patient_profile_id = pp.id 
                            AND prev.id < pa.id
                        ) > 0 AND (
                            SELECT MAX(request_date)
                            FROM patient_appointment_requests prev
                            WHERE prev.patient_profile_id = pp.id
                            AND prev.id < pa.id
                        ) < DATE_SUB(pa.request_date, INTERVAL 4 MONTH) THEN 1 ELSE 0 
                    END
                ) as old_patients"),
                DB::raw("SUM(CASE WHEN pa.appointment_media = 1 THEN 1 ELSE 0 END) as chamber_visits"),
                DB::raw("SUM(CASE WHEN pa.appointment_media = 2 THEN 1 ELSE 0 END) as online_visits"),
                DB::raw("SUM(CASE WHEN pa.appointment_media = 3 THEN 1 ELSE 0 END) as report_follow_ups"),
                DB::raw("COUNT(p.id) as total_patients"),
                DB::raw("AVG(TIMESTAMPDIFF(MINUTE, pa.confirm_time, p.start_time)) as avg_waiting_time"),
                DB::raw("AVG(
                    GREATEST(
                        TIMESTAMPDIFF(MINUTE, 
                            STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(pa.request_slot, '$.slot_details.start')), '%H:%i'), 
                            p.end_time
                        ), 0
                    )
                ) as avg_patient_waiting_time"),
                // DB::raw("GREATEST(
                //     TIMESTAMPDIFF(MINUTE, 
                //         STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(pa.request_slot, '$.slot_details.start')), '%H:%i'), 
                //         p.end_time
                //     ), 0
                // ) as patient_waiting_time"),
            )->groupBy('p.doctor_user_id');
        return $report;
    }
    public function getAbandonReportQuery($add_date = 0)
    {
        // Ensure $add_date is a valid integer
        $add_date = is_numeric($add_date) ? (int)$add_date : 0;

        $abandonPatients = DB::table('prescriptions as p1')
            ->join('patient_profiles as pp', 'pp.id', '=', 'p1.patient_profile_id')
            ->join('contacts as contact', 'contact.id', '=', 'pp.patient_contact_id')
            ->whereRaw("EXISTS (
                SELECT 1 FROM prescriptions as p_check 
                WHERE p_check.patient_profile_id = p1.patient_profile_id
            )")
            ->whereRaw("NOT EXISTS (
                SELECT 1 FROM prescriptions as p2 
                WHERE p2.patient_profile_id = p1.patient_profile_id 
                AND p2.created_at >= 
                    CASE 
                        WHEN p1.follow_up_type = 'days' THEN DATE_ADD(p1.created_at, INTERVAL (p1.follow_up_number + {$add_date}) DAY)
                        WHEN p1.follow_up_type = 'months' THEN DATE_ADD(p1.created_at, INTERVAL ((p1.follow_up_number * 30) + {$add_date}) DAY)
                        WHEN p1.follow_up_type = 'years' THEN DATE_ADD(p1.created_at, INTERVAL (p1.follow_up_number + {$add_date}) YEAR)
                        ELSE NULL
                    END
            )")
            ->whereRaw("NOW() >= 
                CASE 
                    WHEN p1.follow_up_type = 'days' THEN DATE_ADD(p1.created_at, INTERVAL (p1.follow_up_number + {$add_date}) DAY)
                    WHEN p1.follow_up_type = 'months' THEN DATE_ADD(p1.created_at, INTERVAL ((p1.follow_up_number * 30) + {$add_date}) DAY)
                    WHEN p1.follow_up_type = 'years' THEN DATE_ADD(p1.created_at, INTERVAL (p1.follow_up_number + {$add_date}) YEAR)
                    ELSE NULL
                END
            ")
            ->select(
                'pp.id',
                'pp.first_name',
                'pp.last_name',
                'pp.mobile',
                'p1.follow_up_number',
                'contact.contact_id as patient_id',
                'p1.follow_up_type',
                'p1.created_at as last_prescription_date',
                DB::raw("CASE 
                    WHEN p1.follow_up_type = 'days' THEN DATE_ADD(p1.created_at, INTERVAL (p1.follow_up_number + {$add_date}) DAY)
                    WHEN p1.follow_up_type = 'months' THEN DATE_ADD(p1.created_at, INTERVAL ((p1.follow_up_number * 30) + {$add_date}) DAY)
                    WHEN p1.follow_up_type = 'years' THEN DATE_ADD(p1.created_at, INTERVAL (p1.follow_up_number + {$add_date}) YEAR)
                    ELSE NULL
                END as expected_follow_up_date")
            )
            ->groupBy('p1.patient_profile_id')
            ->get();

        return $abandonPatients;
    }



    public function generateTherapyPrescription($data)
    {
        $prescriptionNumber = $this->generatePrescriptionNumber();
        $appointment_id = $data['appointment_id'];
        $appointment = PatientAppointmentRequ::with('patient')->find($appointment_id);
        $sessionId = $appointment->patient_session_info_id;
        if (!$appointment) {
            $output = [
                'success' => false,
                'msg' => 'Patient Appointment not found.'
            ];
            return $output;
        }

        try {
            DB::beginTransaction();
            $prescription = $this->StorePrescription($prescriptionNumber, $appointment, $data);
            $medicine = $this->storeMedicine($prescription->id, $appointment, $data);
            $test = $this->storeTest($prescription->id, $data);
            $diseaseHistories = $this->StoreDiseaseHistories($prescription->id, $data);
            $advices = $this->StoreOtherAdvice($prescription->id, $data, [
                'treatment_plan' => ['treatment_plan_id', 'treatment_plan'],
                'home_advice'    => ['home_advice_id', 'home_advice'],
                'on_examination' => ['on_examination_id', 'on_examination'],
            ]);
            $appointment->remarks = 'prescribed';
            $appointment->can_visit = now();
            $appointment->is_visited = 1;
            $appointment->visited_date = now();
            $appointment->save();

            $complains = $this->StoreComplain($prescription->id, $data);
            DB::commit();
            $output = ['success' => true, 'msg' => 'Prescription generated successfully', 'prescription' => $prescription];
            return $output;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('Error generating prescription: ' . $e->getMessage());
            $output = ['success' => false, 'msg' => 'Error generating prescription: ' . $e->getMessage()];
            return $output;
        }
    }



    private function StoreOtherAdvice($prescription, $data, array $keys)
{
    $normalized = [];

    foreach ($keys as $type => $pair) {
        [$idKey, $nameKey] = $pair; // destructure value array

        $mapped = [
            'advice_id'   => $data[$idKey]   ?? [],
            'advice_name' => $data[$nameKey] ?? [],
        ];

        // Store each type separately
        $normalized[$type] = $this->StoreAdvice($prescription, $mapped, $type);
    }

    return $normalized;
}


}
