<?php

namespace App\Http\Controllers;

use App\BusinessLocation;
use App\Contact;
use App\Product;
use App\TaxRate;
use App\Transaction;
use App\TypesOfService;
use App\Unit;
use App\Utils\BusinessUtil;
use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use App\Variation;
use App\TransactionPayment;
use DB;
use Excel;
use Illuminate\Http\Request;
use Modules\Clinic\Entities\PatientSessionInfo;
use Modules\Clinic\Entities\PatientProfile;
use Carbon\Carbon;

class ImportSalesController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $productUtil;

    protected $businessUtil;

    protected $transactionUtil;

    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param  ProductUtils  $product
     * @return void
     */
    public function __construct(
        ProductUtil $productUtil,
        BusinessUtil $businessUtil,
        TransactionUtil $transactionUtil,
        ModuleUtil $moduleUtil
    ) {
        $this->productUtil = $productUtil;
        $this->businessUtil = $businessUtil;
        $this->transactionUtil = $transactionUtil;
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! auth()->user()->can('sell.create')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $imported_sales = Transaction::where('business_id', $business_id)
            ->where('type', 'sell')
            ->whereNotNull('import_batch')
            ->with(['sales_person'])
            ->select('id', 'import_batch', 'import_time', 'invoice_no', 'created_by')
            ->orderBy('import_batch', 'desc')
            ->get();

        $imported_sales_array = [];
        foreach ($imported_sales as $sale) {
            $imported_sales_array[$sale->import_batch]['import_time'] = $sale->import_time;
            $imported_sales_array[$sale->import_batch]['created_by'] = $sale->sales_person->user_full_name;
            $imported_sales_array[$sale->import_batch]['invoices'][] = $sale->invoice_no;
        }

        $import_fields = $this->__importFields();

        return view('import_sales.index')->with(compact('imported_sales_array', 'import_fields'));
    }

    /**
     * Preview imported data and map columns with sale fields
     *
     * @return \Illuminate\Http\Response
     */
    public function preview(Request $request)
    {
        if (! auth()->user()->can('sell.create')) {
            abort(403, 'Unauthorized action.');
        }

        $notAllowed = $this->businessUtil->notAllowedInDemo();
        if (! empty($notAllowed)) {
            return $notAllowed;
        }

        $business_id = request()->session()->get('user.business_id');

        if ($request->hasFile('sales')) {
            $file_name = time() . '_' . $request->sales->getClientOriginalName();
            $request->sales->storeAs('temp', $file_name);

            $parsed_array = $this->__parseData($file_name);

            $import_fields = $this->__importFields();
            foreach ($import_fields as $key => $value) {
                $import_fields[$key] = $value['label'];
            }

            //Evaluate highest matching field with the header to pre select from dropdown
            $headers = $parsed_array[0];
            $match_array = [];
            foreach ($headers as $key => $value) {
                $match_percentage = [];
                foreach ($import_fields as $k => $v) {
                    similar_text($value, $v, $percentage);
                    $match_percentage[$k] = $percentage;
                }
                $max_key = array_keys($match_percentage, max($match_percentage))[0];

                //If match percentage is greater than 50% then pre select the value
                $match_array[$key] = $match_percentage[$max_key] >= 50 ? $max_key : null;
            }

            $business_locations = BusinessLocation::forDropdown($business_id);

            return view('import_sales.preview')->with(compact('parsed_array', 'import_fields', 'file_name', 'business_locations', 'match_array'));
        }
    }

    public function __parseData($file_name)
    {
        $array = Excel::toArray([], public_path('uploads/temp/' . $file_name))[0];

        //remove blank columns from headers
        $headers = array_filter($array[0]);

        //Remove header row
        unset($array[0]);
        $parsed_array[] = $headers;
        foreach ($array as $row) {
            $temp = [];
            foreach ($row as $k => $v) {
                if (array_key_exists($k, $headers)) {
                    $temp[] = $v;
                }
            }
            $parsed_array[] = $temp;
        }

        return $parsed_array;
    }

    /**
     * Import sales to database
     *
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request)
    {
        if (! auth()->user()->can('sell.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();

            $file_name = $request->input('file_name');
            $import_fields = $request->input('import_fields');
            $group_by = $request->input('group_by');
            $location_id = $request->input('location_id');
            $business_id = $request->session()->get('user.business_id');

            $file_path = public_path('uploads/temp/' . $file_name);
            $parsed_array = $this->__parseData($file_name);
            //Remove header row
            unset($parsed_array[0]);
            $formatted_sales_data = $this->__formatSaleData($parsed_array, $import_fields, $group_by);
            //Set maximum php execution time
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', -1);

            $this->__importSales($formatted_sales_data, $business_id, $location_id);

            DB::commit();

            $output = [
                'success' => 1,
                'msg' => __('lang_v1.sales_imported_successfully'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => $e->getMessage(),
            ];

            @unlink($file_path);

            return redirect('import-sales')->with('notification', $output);
        }

        @unlink($file_path);

        return redirect('import-sales')->with('status', $output);
    }

    private function __importSales($formated_data, $business_id, $location_id)
    {
        $import_batch = Transaction::where('business_id', $business_id)->max('import_batch');

        if (empty($import_batch)) {
            $import_batch = 1;
        } else {
            $import_batch = $import_batch + 1;
        }

        $now = \Carbon::now()->toDateTimeString();
        $row_index = 2;
        foreach ($formated_data as $data) {
            $order_total = 0;
            $sell_lines = [];
            foreach ($data as $line_data) {
                if (! empty($line_data['sku'])) {
                    $variation = Variation::where('sub_sku', $line_data['sku'])->with(['product'])->first();

                    $product = ! empty($variation) ? $variation->product : null;
                } else {
                    $product = Product::where('business_id', $business_id)
                        ->where('name', $line_data['product'])
                        ->with(['variations'])
                        ->first();
                    $variation = ! empty($product) ? $product->variations->first() : null;
                }

                if (empty($variation)) {
                    throw new \Exception(__('lang_v1.import_sale_product_not_found', ['row' => $row_index, 'product_name' => $line_data['product'], 'sku' => $line_data['sku']]));
                }

                $tax_id = null;
                $item_tax = 0;
                $line_discount = ! empty($line_data['item_discount']) ? $line_data['item_discount'] : 0;

                $unit_price = $line_data['unit_price'];

                $price_before_tax = $line_data['unit_price'] - $line_discount;
                $price_inc_tax = $price_before_tax;
                if (! empty($line_data['item_tax'])) {
                    $tax = TaxRate::where('business_id', $business_id)
                        ->where('name', $line_data['item_tax'])
                        ->first();

                    if (empty($tax)) {
                        throw new \Exception(__('lang_v1.import_sale_tax_not_found', ['row' => $row_index, 'tax_name' => $line_data['item_tax']]));
                    }
                    $tax_id = $tax->id;
                    $item_tax = $this->transactionUtil->calc_percentage($price_before_tax, $tax->amount);
                    $price_inc_tax = $price_before_tax + $item_tax;
                }

                //check if date is correct
                if (! empty($line_data['date'])) {
                    try {
                        \Carbon::parse($line_data['date']);
                    } catch (\Exception $e) {
                        throw new \Exception(__('lang_v1.invalid_date_format_at', ['row' => $row_index]));
                    }
                }

                $temp = [
                    'product_id' => $variation->product_id,
                    'variation_id' => $variation->id,
                    'quantity' => $line_data['quantity'],
                    'order_paid' => $line_data['order_paid'],
                    'visited_count' => $line_data['visited_count'],
                    'unit_price' => $unit_price,
                    'unit_price_inc_tax' => $price_inc_tax,
                    'line_discount_type' => 'fixed',
                    'line_discount_amount' => $line_discount,
                    'item_tax' => $item_tax,
                    'tax_id' => $tax_id,
                    'sell_line_note' => $line_data['item_description'],
                    'product_unit_id' => $product->unit_id,
                    'enable_stock' => $product->enable_stock,
                    'type' => $product->type,
                    'combo_variations' => $product->type == 'combo' ? $variation->combo_variations : [],
                ];

                $line_quantity = $line_data['quantity'];
                if (! empty($line_data['unit'])) {
                    $unit_name = trim($line_data['unit']);
                    $unit = Unit::where('actual_name', $unit_name)
                        ->orWhere('short_name', $unit_name)
                        ->first();

                    if (empty($unit)) {
                        throw new \Exception(__('lang_v1.import_sale_unit_not_found', ['row' => $row_index, 'unit_name' => $unit_name]));
                    }

                    //Check if sub unit
                    if ($unit->id != $product->unit_id) {
                        $temp['sub_unit_id'] = $unit->id;
                        $temp['base_unit_multiplier'] = $unit->base_unit_multiplier;
                        $line_quantity = ($line_quantity * $unit->base_unit_multiplier);
                    }
                }
                $order_total += ($temp['unit_price_inc_tax'] * $line_quantity);

                $sell_lines[] = $temp;

                $row_index++;
            }

            $first_sell_line = $data[0];
            //get contact
            $contactId = null;
            $contact = null;
            
            if (!empty($first_sell_line['customer_phone_number'])) {
                // First search for contacts with matching phone number
                $contacts = Contact::where('business_id', $business_id)
                    ->where('mobile', $first_sell_line['customer_phone_number'])
                    ->get();

                if ($contacts->count() > 0) {
                    // If contacts found with matching phone, check their patient profiles
                    foreach ($contacts as $potential_contact) {
                        $patient_profile = PatientProfile::where('patient_contact_id', $potential_contact->id)
                            ->where('mobile', $first_sell_line['customer_phone_number']);
                        
                        // If name is provided, also check name match
                        if (!empty($first_sell_line['customer_name'])) {
                            $patient_profile->where('first_name', $first_sell_line['customer_name']);
                        }

                        $patient_profile = $patient_profile->first();

                        if ($patient_profile) {
                            $contact = $potential_contact;
                            $contactId = $contact->id;
                            break;
                        }
                    }

                    // If no exact match found in patient profiles but contact exists with same phone
                    if (!$contact) {
                        $contact = $contacts->first(); // Use the first contact with matching phone
                        $contactId = $contact->id;
                        
                        // Create new patient profile for existing contact
                        $customer_name = !empty($first_sell_line['customer_name']) ? $first_sell_line['customer_name'] : $first_sell_line['customer_phone_number'];
                        $patient = PatientProfile::create([
                            'patient_contact_id' => $contactId,
                            'mobile' => $first_sell_line['customer_phone_number'],
                            'first_name' => $customer_name
                        ]);

                        \Log::info('Created new patient profile for existing contact', [
                            'business_id' => $business_id,
                            'contact_id' => $contactId,
                            'phone' => $first_sell_line['customer_phone_number'],
                            'name' => $customer_name
                        ]);
                    }
                } else {
                    // Only create new contact if no matching phone number found
                    $customer_name = !empty($first_sell_line['customer_name']) ? $first_sell_line['customer_name'] : $first_sell_line['customer_phone_number'];
                    $contact = Contact::create([
                        'business_id' => $business_id,
                        'type' => 'customer',
                        'name' => $customer_name,
                        'email' => $first_sell_line['customer_email'],
                        'mobile' => $first_sell_line['customer_phone_number'],
                        'created_by' => auth()->user()->id,
                    ]);
                    $contactId = $contact->id;

                    // Create patient profile for new contact
                    if ($contactId) {
                        $patient = PatientProfile::create([
                            'patient_contact_id' => $contactId,
                            'mobile' => $first_sell_line['customer_phone_number'],
                            'first_name' => $customer_name
                        ]);
                        
                        \Log::info('Created new contact and patient profile', [
                            'business_id' => $business_id,
                            'contact_id' => $contactId,
                            'phone' => $first_sell_line['customer_phone_number'],
                            'name' => $customer_name
                        ]);
                    }
                }
            } elseif (!empty($first_sell_line['customer_email'])) {
                $contact = Contact::where('business_id', $business_id)
                    ->where('email', $first_sell_line['customer_email'])
                    ->first();
                $contactId = $contact->id ?? null;
            }

            if (!$contactId) {
                throw new \Exception(__('lang_v1.contact_creation_failed'));
            }

            $sale_data = [
                'invoice_no' => $first_sell_line['invoice_no'],
                'location_id' => $location_id,
                'status' => 'final',
                'sub_type' => 'consultation',
                'contact_id' => $contact->id,
                'final_total' => ! empty($first_sell_line['order_total']) ? $first_sell_line['order_total'] : $order_total,
                'transaction_date' => ! empty($first_sell_line['date']) ? $first_sell_line['date'] : $now,
                'discount_amount' => 0,
                'import_batch' => $import_batch,
                'import_time' => $now,
                'commission_agent' => null,
            ];

            $is_types_service_enabled = $this->moduleUtil->isModuleEnabled('types_of_service');
            if ($is_types_service_enabled && ! empty($first_sell_line['types_of_service'])) {
                $types_of_service = TypesOfService::where('business_id', $business_id)
                    ->where('name', $first_sell_line['types_of_service'])
                    ->first();

                if (empty($types_of_service)) {
                    throw new \Exception(__('lang_v1.types_of_servicet_not_found', ['row' => $row_index, 'types_of_service_name' => $first_sell_line['types_of_service']]));
                }

                $sale_data['types_of_service_id'] = $types_of_service->id;
                $sale_data['service_custom_field_1'] = ! empty($first_sell_line['service_custom_field1']) ? $first_sell_line['service_custom_field1'] : null;
                $sale_data['service_custom_field_2'] = ! empty($first_sell_line['service_custom_field2']) ? $first_sell_line['service_custom_field2'] : null;
                $sale_data['service_custom_field_3'] = ! empty($first_sell_line['service_custom_field3']) ? $first_sell_line['service_custom_field3'] : null;
                $sale_data['service_custom_field_4'] = ! empty($first_sell_line['service_custom_field4']) ? $first_sell_line['service_custom_field4'] : null;
            }

            $invoice_total = [
                'total_before_tax' => ! empty($first_sell_line['order_total']) ? $first_sell_line['order_total'] : $order_total,
                'tax' => 0,
            ];

            $transaction = $this->transactionUtil->createSellTransaction($business_id, $sale_data, $invoice_total, auth()->user()->id, false);
            if (!$transaction || !$transaction->id) {
                return;
            }

            // Create single session info for the transaction
            $start_date = Carbon::today();
            $end_date = Carbon::today()->addMonths(6);
            $first_item = $data[0]; // Use the first item for visited count
            $remaining = 5 - $first_item['visited_count'];

            $sessionVisit = [
                'patient_contact_id' => $contactId,
                'transaction_id' => $transaction->id,
                'session_id' => 1,
                'start_date' => $start_date->toDateString(),
                'end_date' => $end_date->toDateString(),
                'total_visit' => 5,
                'visited_count' => $first_item['visited_count'],
                'remaining_visit' => $remaining,
                'created_at' => now(),
            ];
            PatientSessionInfo::create($sessionVisit);

            // Process payments - Sum up all payments first
            $total_paid = 0;
            foreach ($data as $item) {
                if (isset($item['order_paid'])) {
                    $total_paid += $item['order_paid'];
                } else {
                    \Log::emergency('order_paid is missing in item: ' . json_encode($item));
                }
            }

            // Create single payment record with total amount
            if ($total_paid > 0) {
                $payment = [
                    'transaction_id' => $transaction->id,
                    'amount' => $total_paid,
                    'method' => 'cash',
                    'paid_on' => now(),
                    'created_by' => auth()->user()->id,
                ];
                TransactionPayment::create($payment);
            }

            $this->transactionUtil->createOrUpdateSellLines($transaction, $sell_lines, $location_id, false, null, [], false);

            foreach ($sell_lines as $line) {
                if ($line['enable_stock']) {
                    $this->productUtil->decreaseProductQuantity(
                        $line['product_id'],
                        $line['variation_id'],
                        $location_id,
                        $line['quantity']
                    );
                }

                if ($line['type'] == 'combo') {
                    $line_total_quantity = $line['quantity'];
                    if (! empty($line['base_unit_multiplier'])) {
                        $line_total_quantity = $line_total_quantity * $line['base_unit_multiplier'];
                    }

                    //Decrease quantity of combo as well.
                    $combo_details = [];
                    foreach ($line['combo_variations'] as $combo_variation) {
                        $combo_variation_obj = Variation::find($combo_variation['variation_id']);

                        //Multiply both subunit multiplier of child product and parent product to the quantity
                        $combo_variation_quantity = $combo_variation['quantity'];
                        if (! empty($combo_variation['unit_id'])) {
                            $combo_variation_unit = Unit::find($combo_variation['unit_id']);
                            if (! empty($combo_variation_unit->base_unit_multiplier)) {
                                $combo_variation_quantity = $combo_variation_quantity * $combo_variation_unit->base_unit_multiplier;
                            }
                        }

                        $combo_details[] = [
                            'product_id' => $combo_variation_obj->product_id,
                            'variation_id' => $combo_variation['variation_id'],
                            'quantity' => $combo_variation_quantity * $line_total_quantity,
                        ];
                    }

                    $this->productUtil
                        ->decreaseProductQuantityCombo(
                            $combo_details,
                            $location_id
                        );
                }
            }

            //Update payment status
            $this->transactionUtil->updatePaymentStatus($transaction->id, $transaction->final_total);

            $business_details = $this->businessUtil->getDetails($business_id);
            $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

            $business = [
                'id' => $business_id,
                'accounting_method' => request()->session()->get('business.accounting_method'),
                'location_id' => $location_id,
                'pos_settings' => $pos_settings,
            ];
            $this->transactionUtil->mapPurchaseSell($business, $transaction->sell_lines, 'purchase');
        }
    }

    private function __formatSaleData($imported_data, $import_fields, $group_by)
    {
        $formatted_array = [];
        $invoice_number_key = array_search('invoice_no', $import_fields);
        $customer_name_key = array_search('customer_name', $import_fields);
        $customer_phone_key = array_search('customer_phone_number', $import_fields);
        $customer_email_key = array_search('customer_email', $import_fields);
        $date_key = array_search('date', $import_fields);
        $product_key = array_search('product', $import_fields);
        $sku_key = array_search('sku', $import_fields);
        $quantity_key = array_search('quantity', $import_fields);
        $unit_price_key = array_search('unit_price', $import_fields);
        $item_tax_key = array_search('item_tax', $import_fields);
        $item_discount_key = array_search('item_discount', $import_fields);
        $item_description_key = array_search('item_description', $import_fields);
        $order_total_key = array_search('order_total', $import_fields);
        $unit_key = array_search('unit', $import_fields);
        $tos_key = array_search('types_of_service', $import_fields);
        $order_paid = array_search('order_paid', $import_fields);
        $visited_count = array_search('visited_count', $import_fields);
        $service_custom_field1_key = array_search('service_custom_field1', $import_fields);
        $service_custom_field2_key = array_search('service_custom_field2', $import_fields);
        $service_custom_field3_key = array_search('service_custom_field3', $import_fields);
        $service_custom_field4_key = array_search('service_custom_field4', $import_fields);

        $row_index = 2;
        foreach ($imported_data as $key => $value) {
            $formatted_array[$key]['invoice_no'] = $invoice_number_key !== false ? $value[$invoice_number_key] : null;
            $formatted_array[$key]['customer_name'] = $customer_name_key !== false ? $value[$customer_name_key] : null;
            $formatted_array[$key]['customer_phone_number'] = $customer_phone_key !== false ? $value[$customer_phone_key] : null;
            $formatted_array[$key]['customer_email'] = $customer_email_key !== false ? $value[$customer_email_key] : null;
            $formatted_array[$key]['date'] = $date_key !== false ? $value[$date_key] : null;
            $formatted_array[$key]['product'] = $product_key !== false ? $value[$product_key] : null;
            $formatted_array[$key]['sku'] = $sku_key !== false ? $value[$sku_key] : null;
            $formatted_array[$key]['quantity'] = $quantity_key !== false ? $value[$quantity_key] : null;
            $formatted_array[$key]['unit_price'] = $unit_price_key !== false ? $value[$unit_price_key] : null;
            $formatted_array[$key]['item_tax'] = $item_tax_key !== false ? $value[$item_tax_key] : null;
            $formatted_array[$key]['item_discount'] = $item_discount_key !== false ? $value[$item_discount_key] : null;
            $formatted_array[$key]['item_description'] = $item_description_key !== false ? $value[$item_description_key] : null;
            $formatted_array[$key]['order_total'] = $order_total_key !== false ? $value[$order_total_key] : null;
            $formatted_array[$key]['unit'] = $unit_key !== false ? $value[$unit_key] : null;
            $formatted_array[$key]['types_of_service'] = $tos_key !== false ? $value[$tos_key] : null;
            $formatted_array[$key]['order_paid'] = $order_paid !== false ? $value[$order_paid] : null;
            $formatted_array[$key]['visited_count'] = $visited_count !== false ? $value[$visited_count] : null;
            $formatted_array[$key]['service_custom_field1'] = $service_custom_field1_key !== false ? $value[$service_custom_field1_key] : null;
            $formatted_array[$key]['service_custom_field2'] = $service_custom_field2_key !== false ? $value[$service_custom_field2_key] : null;
            $formatted_array[$key]['service_custom_field3'] = $service_custom_field3_key !== false ? $value[$service_custom_field3_key] : null;
            $formatted_array[$key]['service_custom_field4'] = $service_custom_field4_key !== false ? $value[$service_custom_field4_key] : null;
            $formatted_array[$key]['group_by'] = $value[$group_by];

            //check empty
            if (empty($formatted_array[$key]['customer_phone_number']) && empty($formatted_array[$key]['customer_email'])) {
                throw new \Exception(__('lang_v1.email_or_phone_cannot_be_empty_in_row', ['row' => $row_index]));
            }
            if (empty($formatted_array[$key]['product']) && empty($formatted_array[$key]['sku'])) {
                throw new \Exception(__('lang_v1.product_cannot_be_empty_in_row', ['row' => $row_index]));
            }
            if (empty($formatted_array[$key]['quantity'])) {
                throw new \Exception(__('lang_v1.quantity_cannot_be_empty_in_row', ['row' => $row_index]));
            }
            if (empty($formatted_array[$key]['unit_price'])) {
                throw new \Exception(__('lang_v1.unit_price_cannot_be_empty_in_row', ['row' => $row_index]));
            }

            $row_index++;
        }
        $group_by_key = $import_fields[$group_by];
        $formatted_data = [];
        foreach ($formatted_array as $array) {
            $formatted_data[$array['group_by']][] = $array;
        }

        return $formatted_data;
    }

    private function __importFields()
    {
        $fields = [
            'invoice_no' => ['label' => __('sale.invoice_no')],
            'customer_name' => ['label' => __('sale.customer_name')],
            'customer_phone_number' => ['label' => __('lang_v1.customer_phone_number'), 'instruction' => __('lang_v1.either_cust_email_or_phone_required')],
            'customer_email' => ['label' => __('lang_v1.customer_email'), 'instruction' => __('lang_v1.either_cust_email_or_phone_required')],
            'date' => ['label' => __('sale.sale_date'), 'instruction' => __('lang_v1.date_format_instruction')],
            'product' => ['label' => __('product.product_name'), 'instruction' => __('lang_v1.either_product_name_or_sku_required')],
            'sku' => ['label' => __('lang_v1.product_sku'), 'instruction' => __('lang_v1.either_product_name_or_sku_required')],
            'quantity' => ['label' => __('lang_v1.quantity'), 'instruction' => __('lang_v1.required')],
            'unit' => ['label' => __('lang_v1.product_unit')],
            'unit_price' => ['label' => __('sale.unit_price')],
            'item_tax' => ['label' => __('lang_v1.item_tax')],
            'item_discount' => ['label' => __('lang_v1.item_discount')],
            'item_description' => ['label' => __('lang_v1.item_description')],
            'order_total' => ['label' => __('lang_v1.order_total')],
            'order_paid' => ['label' => __('Paid Amount')],
            'start_date' => ['label' => __('Start Date')],
            'end_date' => ['label' => __('End Date')],
            'visited_count' => ['label' => __('Visited Count')],
        ];

        $is_types_service_enabled = $this->moduleUtil->isModuleEnabled('types_of_service');

        if ($is_types_service_enabled) {
            $fields['types_of_service'] = ['label' => __('lang_v1.types_of_service')];
            $fields['service_custom_field1'] = ['label' => __('lang_v1.service_custom_field_1')];
            $fields['service_custom_field2'] = ['label' => __('lang_v1.service_custom_field_2')];
            $fields['service_custom_field3'] = ['label' => __('lang_v1.service_custom_field_3')];
            $fields['service_custom_field4'] = ['label' => __('lang_v1.service_custom_field_4')];
        }

        return $fields;
    }

    /**
     * Deletes all sales from a batch
     *
     * @return \Illuminate\Http\Response
     */
    public function revertSaleImport($batch)
    {
        if (! auth()->user()->can('sell.delete')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = request()->session()->get('user.business_id');

            $sales = Transaction::where('business_id', $business_id)
                ->where('type', 'sell')
                ->where('import_batch', $batch)
                ->get();
            //Begin transaction
            DB::beginTransaction();
            foreach ($sales as $sale) {
                $this->transactionUtil->deleteSale($business_id, $sale->id);
            }

            DB::commit();

            $output = ['success' => 1, 'msg' => __('lang_v1.import_reverted_successfully')];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => trans('messages.something_went_wrong'),
            ];
        }

        return redirect('import-sales')->with('status', $output);
    }
}
