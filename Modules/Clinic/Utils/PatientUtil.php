<?php

namespace Modules\Clinic\Utils;

use App\Contact;
use App\Transaction;
use Modules\Clinic\Entities\PatientUser;
use Modules\Clinic\Entities\PatientProfile;
use Modules\Clinic\Entities\PatientDisease;
use Modules\Clinic\Entities\PatientSessionInfo;
use App\Utils\Util;
use App\Utils\TransactionUtil;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Modules\Clinic\Entities\ReportAndProblem;
use Illuminate\Support\Facades\Http;

class PatientUtil extends Util
{
    public function createNewContactApi($input)
    {
        DB::beginTransaction();
        try {
            $count = 0;
            if (!empty($input['contact_id'])) {
                $count = Contact::where('business_id', $input['business_id'])
                    ->where('contact_id', $input['contact_id'])
                    ->count();
            }
            $ref_count = $this->setAndGetReferenceCount('contacts', $input['business_id']);
            if (empty($input['contact_id'])) {
                $input['contact_id'] = $this->generateReferenceNumber('contacts', $ref_count, $input['business_id']);
            }

            if ($count == 0) {
                $contact = Contact::create($input);
                $patient = new PatientProfile();
                $patient->patient_contact_id = $contact->id;
                $patient->mobile = $contact->mobile;
                $patient->first_name = $contact->first_name;
                $patient->last_name = $contact->last_name;
                $patient->date_of_birth = $contact->dob;
                $patient->age = $input['age'] ?? '';
                $patient->gender = $input['gender'] ?? '';
                $patient->email = $input['email'] ?? '';
                $patient->profession = $input['profession'] ?? '';
                $patient->blood_group = $input['blood_group'] ?? '';
                $patient->marital_status = $input['marital_status'] ?? '';
                $patient->address = $input['address'] ?? '';
                $patient->city = $input['city'] ?? '';
                $patient->state = $input['state'] ?? '';
                $patient->post_code = $input['zip_code'] ?? '';
                $patient->address2 = $input['address_line_2'] ?? '';
                $patient->district_id = isset($input['district_id']) ? $input['district_id'] : null;
                $patient->save();
                // Handle diseases (if any)
                if (!empty($input['disease'])) {
                    foreach ($input['disease'] as $diseaseId) {
                        $patient_disease = new PatientDisease();
                        $patient_disease->patient_profile_id = $patient->id;
                        $patient_disease->disease_id = $diseaseId;
                        $patient_disease->created_by = auth()->user()->id;
                        $patient_disease->save();
                    }
                }

                DB::commit();

                $output = [
                    'success' => true,
                    'data' => $contact,
                    'msg' => __('contact.added_success'),
                ];

                return $output;
            } else {
                throw new \Exception('Error Processing Request: API call failed.', 1);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error creating contact or patient profile', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $input,
            ]);

            return [
                'success' => false,
                'msg' => 'Error Processing Request: ' . $e->getMessage(),
            ];
        }
    }
    public function createNewContact($input)
    {
        DB::beginTransaction();

        try {
            $count = 0;
            if (!empty($input['contact_id'])) {
                $count = Contact::where('business_id', $input['business_id'])
                    ->where('contact_id', $input['contact_id'])
                    ->count();
            }

            if ($count == 0) {
                $ref_count = $this->setAndGetReferenceCount('contacts', $input['business_id']);

                if (empty($input['contact_id'])) {
                    $input['contact_id'] = $this->generateReferenceNumber('contacts', $ref_count, $input['business_id']);
                }

                $contact = Contact::create($input);
                $patient = new PatientProfile();
                $patient->patient_contact_id = $contact->id;
                $patient->mobile = $contact->mobile;
                $patient->first_name = $contact->first_name;
                $patient->last_name = $contact->last_name;
                $patient->date_of_birth = $contact->dob;
                $patient->age = $input['age'] ?? '';
                $patient->gender = $input['gender'] ?? '';
                $patient->email = $input['email'] ?? '';
                $patient->profession = $input['profession'] ?? '';
                $patient->blood_group = $input['blood_group'] ?? '';
                $patient->marital_status = $input['marital_status'] ?? '';
                $patient->address = $input['address'] ?? '';
                $patient->city = $input['city'] ?? '';
                $patient->state = $input['state'] ?? '';
                $patient->post_code = $input['zip_code'] ?? '';
                $patient->address2 = $input['address_line_2'] ?? '';
                $patient->district_id = isset($input['district_id']) ? $input['district_id'] : null;
                $patient->save();

                $data['contact'] = $contact;
                $data['patient'] = $patient;

                // Handle diseases (if any)
                if (!empty($input['disease'])) {
                    foreach ($input['disease'] as $diseaseId) {
                        $patient_disease = new PatientDisease();
                        $patient_disease->patient_profile_id = $patient->id;
                        $patient_disease->disease_id = $diseaseId;
                        $patient_disease->created_by = auth()->user()->id;
                        $patient_disease->save();
                    }
                }

                DB::commit();

                $output =  [
                    'success' => true,
                    'data' => $data,
                    'msg' => __('contact.added_success'),
                ];
            } else {
                $output = [
                    'success' => false,
                    'msg' => 'Error Processing Request: Contact ID already exists',
                ];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $output = [
                'success' => false,
                'msg' => 'Error Processing Request: ' . $e->getMessage(),
            ];
        }
        return $output;
    }

    private function storeContactViaApi($input)
    {
        $apiUrl = 'https://awc.careneterp.com:82/api/contacts';

        try {
            $data = [
                'type' => $input['type'],
                'prefix' => $input['prefix'] ?? '',
                'contact_type_radio' => 'individual',
                'first_name' => $input['first_name'],
                'mobile' => $input['mobile'],
                'email' => $input['email'],
                'dob' => $input['dob'] ?? '',
                'age' => $input['age'],
                'gender' => $input['gender'],
                'customer_group_id' => $input['customer_group_id'] ?? '',
                'send_sms' => $input['send_sms'] ?? 1,
                'alternate_number' => $input['alternate_number'] ?? '',
                'address_line_1' => $input['address_line_1'] ?? '',
                'address_line_2' => $input['address_line_2'] ?? '',
                'city' => $input['city'] ?? '',
                'state' => $input['state'] ?? '',
                'country' => $input['country'] ?? '',
                'zip_code' => $input['zip_code'] ?? '',
            ];
            return Http::post($apiUrl, $data);
        } catch (\Exception $e) {
            Log::error('API call failed in storeContactViaApi method', [
                'error' => $e->getMessage(),
                'input' => $input,
            ]);
            throw new \Exception('Error Processing Request: Unable to reach API.', 1);
        }
    }

    public function getSubcribePatientInfo()
    {
        $data = PatientSessionInfo::join('session_information', 'patient_session_info.session_id', '=', 'session_information.id')
            ->join('patient_profiles', 'patient_session_info.patient_contact_id', '=', 'patient_profiles.patient_contact_id')
            ->join('contacts', 'patient_profiles.patient_contact_id', '=', 'contacts.id')
            ->select(
                'patient_session_info.id as patient_session_info_id',
                'patient_session_info.*',
                'session_information.*',
                'patient_profiles.*',
                'contacts.contact_id as contact_id',
                'session_information.id as subscription_id',
            );
        return $data;
    }
}
