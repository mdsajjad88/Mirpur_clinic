<?php

namespace App\Utils;

use App\Contact;
use App\Transaction;
use Illuminate\Support\Facades\DB;
use Modules\Clinic\Entities\PatientProfile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ContactUtil extends Util
{
    /**
     * Returns Walk In Customer for a Business
     *
     * @param  int  $business_id
     * @return array/false
     */
    public function getWalkInCustomer($business_id, $array = true)
    {
        $contact = Contact::whereIn('type', ['customer', 'both'])
            ->where('contacts.business_id', $business_id)
            ->where('contacts.is_default', 1)
            ->leftjoin('customer_groups as cg', 'cg.id', '=', 'contacts.customer_group_id')
            ->select(
                'contacts.*',
                'cg.amount as discount_percent',
                'cg.price_calculation_type',
                'cg.selling_price_group_id'
            )
            ->first();

        if (! empty($contact)) {
            $contact->contact_address = $contact->contact_address;
            $output = $array ? $contact->toArray() : $contact;

            return $output;
        } else {
            return null;
        }
    }

    /**
     * Returns the customer group
     *
     * @param  int  $business_id
     * @param  int  $customer_id
     * @return array
     */
    public function getCustomerGroup($business_id, $customer_id)
    {
        $cg = [];

        if (empty($customer_id)) {
            return $cg;
        }

        $contact = Contact::leftjoin('customer_groups as CG', 'contacts.customer_group_id', 'CG.id')
            ->where('contacts.id', $customer_id)
            ->where('contacts.business_id', $business_id)
            ->select('CG.*')
            ->first();

        return $contact;
    }

    /**
     * Returns the contact info
     *
     * @param  int  $business_id
     * @param  int  $contact_id
     * @return array
     */
    public function getContactInfo($business_id, $contact_id)
    {
        $contact = Contact::where('contacts.id', $contact_id)
            ->where('contacts.business_id', $business_id)
            ->leftjoin('transactions AS t', 'contacts.id', '=', 't.contact_id')
            ->with(['business'])
            ->select(
                DB::raw("SUM(IF(t.type = 'purchase', final_total, 0)) as total_purchase"),
                DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', final_total, 0)) as total_invoice"),
                DB::raw("SUM(IF(t.type = 'purchase', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as purchase_paid"),
                DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as invoice_received"),
                DB::raw("SUM(IF(t.type = 'opening_balance', final_total, 0)) as opening_balance"),
                DB::raw("SUM(IF(t.type = 'opening_balance', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as opening_balance_paid"),
                'contacts.*'
            )->first();

        return $contact;
    }
    public function getPatientInfo($business_id, $contact_id)
    {
        $contact = Contact::where('contacts.id', $contact_id)
            ->where('contacts.business_id', $business_id)
            ->leftjoin('transactions AS t', 'contacts.id', '=', 't.contact_id')
            ->with(['business'])
            ->select(
                DB::raw("SUM(IF(t.type = 'purchase', final_total, 0)) as total_purchase"),
                DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', final_total, 0)) as total_invoice"),
                DB::raw("SUM(IF(t.type = 'purchase', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as purchase_paid"),
                DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as invoice_received"),
                DB::raw("SUM(IF(t.type = 'opening_balance', final_total, 0)) as opening_balance"),
                DB::raw("SUM(IF(t.type = 'opening_balance', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as opening_balance_paid"),
                'contacts.*'
            )->first();

        return $contact;
    }

    public function createNewContact($input)
    {
        //Check Contact id
        $count = 0;
        if (! empty($input['contact_id'])) {
            $count = Contact::where('business_id', $input['business_id'])
                ->where('contact_id', $input['contact_id'])
                ->count();
        }
        if ($count == 0) {
            $ref_count = $this->setAndGetReferenceCount('contacts', $input['business_id']);
            $opening_balance = isset($input['opening_balance']) ? $input['opening_balance'] : 0;
            if (isset($input['opening_balance'])) {
                unset($input['opening_balance']);
            }
            $assigned_to_users = [];
            if (! empty($input['assigned_to_users'])) {
                $assigned_to_users = $input['assigned_to_users'];
                unset($input['assigned_to_users']);
            }
            DB::beginTransaction();
             $input['contact_id'] = $this->generateReferenceNumber('contacts', $ref_count, $input['business_id']);
            $contact = Contact::create($input);
            if ($input['contact_type'] == 'individual') {
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
            }

            //Assigned the user
            if (! empty($assigned_to_users)) {
                $contact->userHavingAccess()->sync($assigned_to_users);
            }

            //Add opening balance
            if (! empty($opening_balance)) {
                $transactionUtil = new TransactionUtil();
                $transactionUtil->createOpeningBalanceTransaction($contact->business_id, $contact->id, $opening_balance, $contact->created_by, false);
            }
            DB::commit();
            $output = [
                'success' => true,
                'data' => $contact,
                'msg' => __('contact.added_success'),
            ];

            return $output;
        } else {
            throw new \Exception('Error Processing Request', 1);
        }
    }
    private function storeContactViaApiRes($input)
    {
        $apiUrl = 'https://awc.careneterp.com:86/api/contacts';

        try {
            $data = [
                'type' => $input['type'],
                'prefix' => $input['prefix'] ?? '',
                'contact_type_radio' => 'individual',
                'first_name' => $input['first_name'],
                'contact_id' => $input['contact_id'],
                'mobile' => $input['mobile'],
                'email' => $input['email'],
                'dob' => $input['dob'] ?? '',
                'age' => $input['age'] ?? '',
                'gender' => $input['gender'] ?? '',
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
            Log::error('API call failed', [
                'error' => $e->getMessage(),
                'input' => $input,
            ]);
            throw new \Exception('Error Processing Request: Unable to reach API.', 1);
        }
    }
    public function updateContact($input, $id, $business_id)
    {
        $count = 0;
        //Check Contact id
        if (! empty($input['contact_id'])) {
            $count = Contact::where('business_id', $business_id)
                ->where('contact_id', $input['contact_id'])
                ->where('id', '!=', $id)
                ->count();
        }

        if ($count == 0) {
            //Get opening balance if exists
            $ob_transaction = Transaction::where('contact_id', $id)
                ->where('type', 'opening_balance')
                ->first();

            $opening_balance = isset($input['opening_balance']) ? $input['opening_balance'] : 0;
            if (isset($input['opening_balance'])) {
                unset($input['opening_balance']);
            }

            //Assigned the user
            $assigned_to_users = [];
            if (! empty($input['assigned_to_users'])) {
                $assigned_to_users = $input['assigned_to_users'];
                unset($input['assigned_to_users']);
            }

            $contact = Contact::where('business_id', $business_id)->findOrFail($id);
            foreach ($input as $key => $value) {
                $contact->$key = $value;
            }
            $contact->save();

            //Assigned the user
            if (! empty($assigned_to_users)) {
                $contact->userHavingAccess()->sync($assigned_to_users);
            }

            //Opening balance update
            $transactionUtil = new TransactionUtil();
            if (! empty($ob_transaction)) {
                $opening_balance_paid = $transactionUtil->getTotalAmountPaid($ob_transaction->id);
                if (! empty($opening_balance_paid)) {
                    $opening_balance += $opening_balance_paid;
                }

                $ob_transaction->final_total = $opening_balance;
                $ob_transaction->save();
                //Update opening balance payment status
                $transactionUtil->updatePaymentStatus($ob_transaction->id, $ob_transaction->final_total);
            } else {
                //Add opening balance
                if (! empty($opening_balance)) {
                    $transactionUtil->createOpeningBalanceTransaction($business_id, $contact->id, $opening_balance, $contact->created_by, false);
                }
            }

            $output = [
                'success' => true,
                'msg' => __('contact.updated_success'),
                'data' => $contact,
            ];
        } else {
            throw new \Exception('Error Processing Request', 1);
        }

        return $output;
    }

    public function getContactQuery($business_id, $type, $contact_ids = [])
    {
        $query = Contact::leftjoin('transactions AS t', 'contacts.id', '=', 't.contact_id')
            ->leftjoin('customer_groups AS cg', 'contacts.customer_group_id', '=', 'cg.id')
            ->where('contacts.business_id', $business_id);

        if ($type == 'supplier') {
            $query->onlySuppliers();
        } elseif ($type == 'customer') {
            $query->onlyCustomers();
        } else {
            if (auth()->check() && ((! auth()->user()->can('customer.view') && auth()->user()->can('customer.view_own'))) || (! auth()->user()->can('supplier.view') && auth()->user()->can('supplier.view_own'))) {
                $query->onlyOwnContact();
            }
        }
        if (! empty($contact_ids)) {
            $query->whereIn('contacts.id', $contact_ids);
        }

        $query->select([
            'contacts.*',
            'cg.name as customer_group',
            DB::raw("SUM(IF(t.type = 'opening_balance', final_total, 0)) as opening_balance"),
            DB::raw("SUM(IF(t.type = 'opening_balance', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as opening_balance_paid"),
            DB::raw('MAX(DATE(transaction_date)) as max_transaction_date'),
            DB::raw("SUM(IF(t.type = 'ledger_discount', final_total, 0)) as total_ledger_discount"),
            't.transaction_date',
        ]);

        if (in_array($type, ['supplier', 'both'])) {
            $query->addSelect([
                DB::raw("SUM(IF(t.type = 'purchase', final_total, 0)) as total_purchase"),
                DB::raw("SUM(IF(t.type = 'purchase', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as purchase_paid"),
                DB::raw("SUM(IF(t.type = 'purchase_return', final_total, 0)) as total_purchase_return"),
                DB::raw("SUM(IF(t.type = 'purchase_return', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as purchase_return_paid"),
            ]);
        }

        if (in_array($type, ['customer', 'both'])) {
            $query->addSelect([
                DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', final_total, 0)) as total_invoice"),
                DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as invoice_received"),
                DB::raw("SUM(IF(t.type = 'sell_return', final_total, 0)) as total_sell_return"),
                DB::raw("SUM(IF(t.type = 'sell_return', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as sell_return_paid"),
            ]);
        }
        $query->groupBy('contacts.id');

        return $query;
    }
    public function getPatientQuery($business_id, $type, $contact_ids = [])
    {
        $query = Contact::leftjoin('transactions AS t', 'contacts.id', '=', 't.contact_id')
            ->leftjoin('customer_groups AS cg', 'contacts.customer_group_id', '=', 'cg.id')
            ->leftjoin('patient_profiles AS p_profile', 'contacts.id', '=', 'p_profile.patient_contact_id')
            ->leftjoin('patient_diseases AS disease', 'disease.patient_profile_id', '=', 'p_profile.id') 
            ->leftjoin('problems AS problems', 'problems.id', '=', 'disease.disease_id')
            ->where('contacts.business_id', $business_id);
      
        $query->onlyCustomers();
        
        if (! empty($contact_ids)) {
            $query->whereIn('contacts.id', $contact_ids);
        }

        $query->select([
            'contacts.*',
            'contacts.created_at as contact_created_at',
            'p_profile.age',
            'p_profile.gender',
            'cg.name as customer_group',
            DB::raw("IF(p_profile.id IS NOT NULL, 'Yes', 'No') as is_patient"),
            DB::raw('GROUP_CONCAT(DISTINCT problems.name) as disease_names')
        ]);
        $query->groupBy('contacts.id');
        if (!empty(request()->input('disease_names'))) {
            $query->havingRaw("GROUP_CONCAT(DISTINCT problems.name) LIKE ?", ['%' . request()->input('disease_names') . '%']);
        }
        return $query;
    }
}
