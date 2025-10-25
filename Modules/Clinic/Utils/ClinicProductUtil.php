<?php

namespace Modules\Clinic\Utils;

use App\Utils\Util;
use Modules\Clinic\Entities\DoctorProfile;
use Modules\Clinic\Entities\PatientAppointmentRequ;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Transaction;
use App\Product;
use App\Variation;
use App\VariationLocationDetails;
class ClinicProductUtil extends Util
{
    public function filterProduct($sub_type, $business_id, $search_term, $location_id = null, $not_for_selling = null, $price_group_id = null, $product_types = [], $search_fields = [], $check_qty = false, $search_type = 'like')
    {
        $query = Product::with('brand', 'category')->join('variations', 'products.id', '=', 'variations.product_id')
                ->active()
                ->whereNull('variations.deleted_at')
                ->leftjoin('units as U', 'products.unit_id', '=', 'U.id')
                ->leftjoin(
                    'variation_location_details AS VLD',
                    function ($join) use ($location_id) {
                        $join->on('variations.id', '=', 'VLD.variation_id');

                        //Include Location
                        if (! empty($location_id)) {
                            $join->where(function ($query) use ($location_id) {
                                $query->where('VLD.location_id', '=', $location_id);
                                $query->orWhereNull('VLD.location_id');
                            });
                        }
                    }
                )->leftjoin('purchase_lines as pl', 'variations.id', '=', 'pl.variation_id')
                ->leftjoin('business_locations as l', 'VLD.location_id', '=', 'l.id')
                ->where('products.product_type', $sub_type);
                
                if (! is_null($not_for_selling)) {
            $query->where('products.not_for_selling', $not_for_selling);
        }

        if (! empty($price_group_id)) {
            $query->leftjoin(
                'variation_group_prices AS VGP',
                function ($join) use ($price_group_id) {
                    $join->on('variations.id', '=', 'VGP.variation_id')
                        ->where('VGP.price_group_id', '=', $price_group_id);
                }
            );
        }

        $query->where('products.business_id', $business_id)
                ->where('products.type', '!=', 'modifier');

        if (! empty($product_types)) {
            $query->whereIn('products.type', $product_types);
        }

        // if (in_array('lot', $search_fields)) {
        //     $query->leftjoin('purchase_lines as pl', 'variations.id', '=', 'pl.variation_id');
        // }

        //Include search
        if (! empty($search_term)) {

            //Search with like condition
            if ($search_type == 'like') {
                $query->where(function ($query) use ($search_term, $search_fields) {
                    if (in_array('name', $search_fields)) {
                        $query->where('products.name', 'like', '%'.$search_term.'%');
                    }

                    if (in_array('sku', $search_fields)) {
                        $query->orWhere('sku', 'like', '%'.$search_term.'%');
                    }

                    if (in_array('sub_sku', $search_fields)) {
                        $query->orWhere('sub_sku', 'like', '%'.$search_term.'%');
                    }

                    if (in_array('lot', $search_fields)) {
                        $query->orWhere('pl.lot_number', 'like', '%'.$search_term.'%');
                    }

                    if (in_array('product_custom_field1', $search_fields)) {
                        $query->orWhere('product_custom_field1', 'like', '%'.$search_term.'%');
                    }
                    if (in_array('product_custom_field2', $search_fields)) {
                        $query->orWhere('product_custom_field2', 'like', '%'.$search_term.'%');
                    }
                    if (in_array('product_custom_field3', $search_fields)) {
                        $query->orWhere('product_custom_field3', 'like', '%'.$search_term.'%');
                    }
                    if (in_array('product_custom_field4', $search_fields)) {
                        $query->orWhere('product_custom_field4', 'like', '%'.$search_term.'%');
                    }
                });
            }

            //Search with exact condition
            if ($search_type == 'exact') {
                $query->where(function ($query) use ($search_term, $search_fields) {
                    if (in_array('name', $search_fields)) {
                        $query->where('products.name', $search_term);
                    }

                    if (in_array('sku', $search_fields)) {
                        $query->orWhere('sku', $search_term);
                    }

                    if (in_array('sub_sku', $search_fields)) {
                        $query->orWhere('sub_sku', $search_term);
                    }

                    if (in_array('lot', $search_fields)) {
                        $query->orWhere('pl.lot_number', $search_term);
                    }
                });
            }
        }

        //Include check for quantity
        if ($check_qty) {
            $query->where('VLD.qty_available', '>', 0);
        }

        if (! empty($location_id)) {
            $query->ForLocation($location_id);
        }
        if (!empty($location_id)) {
            $query->where(function ($query) use ($location_id) {
                $query->where('VLD.location_id', $location_id)
                    ->orWhere(function ($query) use ($location_id) {
                        $query->where('VLD.qty_available', '>', 0)
                            ->where('VLD.location_id', '!=', $location_id)->addSelect('l.name as location_name','VLD.qty_available as stock',);
                    });
            });
        }
        $query->select(
                'products.id as product_id',
                'products.name',
                'products.sku',
                'products.type',
                'products.brand_id',
                'products.category_id',
                'l.id as location_id',
                'products.enable_stock',
                'variations.id as variation_id',
                'variations.name as variation',
                'VLD.qty_available',
                'variations.sell_price_inc_tax as selling_price',
                'variations.sub_sku',
                'U.short_name as unit',
                DB::raw('MIN(pl.exp_date) as nearest_exp_date'),
                'pl.id as purchase_line_id',
                'pl.transaction_id',
                'pl.lot_number',
                'pl.exp_date'
            );
        $query->addSelect(DB::raw('SUM(VLD.qty_available) as total_quantity'));
        if (! empty($price_group_id)) {
            $query->addSelect(DB::raw('IF (VGP.price_type = "fixed", VGP.price_inc_tax, VGP.price_inc_tax * variations.sell_price_inc_tax / 100) as variation_group_price'));
        }

        if (in_array('lot', $search_fields)) {
            $query->addSelect('pl.id as purchase_line_id', 'pl.lot_number');
        }

        $query->groupBy('variations.id');

        return $query->orderBy('VLD.qty_available', 'desc')
                        ->get();
    }
    public function barcode_types()
    {
        $types = ['C128' => 'Code 128 (C128)', 'C39' => 'Code 39 (C39)', 'EAN13' => 'EAN-13', 'EAN8' => 'EAN-8', 'UPCA' => 'UPC-A', 'UPCE' => 'UPC-E'];

        return $types;
    }
    public function getVariationStockDetails($business_id, $variation_id, $location_id)
    {
        $purchase_details = Variation::join('products as p', 'p.id', '=', 'variations.product_id')
                    ->join('units', 'p.unit_id', '=', 'units.id')
                    ->leftjoin('units as u', 'p.secondary_unit_id', '=', 'u.id')
                    ->leftjoin('product_variations as pv', 'variations.product_variation_id', '=', 'pv.id')
                    ->leftjoin('purchase_lines as pl', 'pl.variation_id', '=', 'variations.id')
                    ->leftjoin('transactions as t', 'pl.transaction_id', '=', 't.id')
                    ->where('t.location_id', $location_id)
                    //->where('t.status', 'received')
                    ->where('p.business_id', $business_id)
                    ->where('variations.id', $variation_id)

                    ->select(
                        DB::raw("SUM(IF(t.type='purchase' AND t.status='received', pl.quantity, 0)) as total_purchase"),
                        DB::raw("SUM(IF(t.type='purchase' OR t.type='purchase_return', pl.quantity_returned, 0)) as total_purchase_return"),
                        DB::raw('SUM(pl.quantity_adjusted_damage) as total_adjusted'),
                        DB::raw('SUM(pl.quantity_adjusted_surplus) as total_adjusted_surplus'),
                        DB::raw("SUM(IF(t.type='opening_stock', pl.quantity, 0)) as total_opening_stock"),
                        DB::raw("SUM(IF(t.type='purchase_transfer', pl.quantity, 0)) as total_purchase_transfer"),
                        'variations.sub_sku as sub_sku',
                        'p.name as product',
                        'p.type',
                        'p.sku',
                        'p.id as product_id',
                        'units.short_name as unit',
                        'u.short_name as second_unit',
                        'pv.name as product_variation',
                        'variations.name as variation_name',
                        'variations.id as variation_id'
                    )
                  ->get()->first();

        $sell_details = Variation::join('products as p', 'p.id', '=', 'variations.product_id')
                    ->leftjoin('transaction_sell_lines as sl', 'sl.variation_id', '=', 'variations.id')
                    ->join('transactions as t', 'sl.transaction_id', '=', 't.id')
                    ->where('t.location_id', $location_id)
                    ->where('t.status', 'final')
                    ->where('p.business_id', $business_id)
                    ->where('variations.id', $variation_id)
                    ->select(
                        DB::raw("SUM(IF(t.type='sell', sl.quantity, 0)) as total_sold"),
                        DB::raw("SUM(IF(t.type='sell', sl.quantity_returned, 0)) as total_sell_return"),
                        DB::raw("SUM(IF(t.type='sell_transfer', sl.quantity, 0)) as total_sell_transfer")
                    )
                  ->get()->first();

        $current_stock = VariationLocationDetails::where('variation_id',
                                            $variation_id)
                                        ->where('location_id', $location_id)
                                        ->first();

        if ($purchase_details->type == 'variable') {
            $product_name = $purchase_details->product.' - '.$purchase_details->product_variation.' - '.$purchase_details->variation_name.' ('.$purchase_details->sub_sku.')';
        } else {
            $product_name = $purchase_details->product.' ('.$purchase_details->sku.')';
        }

        $output = [
            'variation' => $product_name,
            'product_id' => 'p.id',
            'unit' => $purchase_details->unit,
            'second_unit' => $purchase_details->second_unit,
            'total_purchase' => $purchase_details->total_purchase,
            'total_purchase_return' => $purchase_details->total_purchase_return,
            'total_adjusted' => $purchase_details->total_adjusted,
            'total_adjusted_surplus' => $purchase_details->total_adjusted_surplus,
            'total_opening_stock' => $purchase_details->total_opening_stock,
            'total_purchase_transfer' => $purchase_details->total_purchase_transfer,
            'total_sold' => $sell_details->total_sold,
            'total_sell_return' => $sell_details->total_sell_return,
            'total_sell_transfer' => $sell_details->total_sell_transfer,
            'current_stock' => $current_stock->qty_available ?? 0,
        ];

        return $output;
    }


    public function getVariationStockHistory($business_id, $variation_id, $location_id)
    {
        $stock_history = Transaction::leftjoin('transaction_sell_lines as sl',
            'sl.transaction_id', '=', 'transactions.id')
                                ->leftjoin('purchase_lines as pl',
                                    'pl.transaction_id', '=', 'transactions.id')
                                ->leftjoin('stock_adjustment_lines as al',
                                    'al.transaction_id', '=', 'transactions.id')
                                ->leftjoin('transactions as return', 'transactions.return_parent_id', '=', 'return.id')
                                ->leftjoin('purchase_lines as rpl',
                                    'rpl.transaction_id', '=', 'return.id')
                                ->leftjoin('transaction_sell_lines as rsl',
                                        'rsl.transaction_id', '=', 'return.id')
                                ->leftjoin('contacts as c', 'transactions.contact_id', '=', 'c.id')
                                ->leftjoin('users as us', 'transactions.created_by', '=', 'us.id')
                                ->where('transactions.location_id', $location_id)
                                ->where(function ($q) use ($variation_id) {
                                    $q->where('sl.variation_id', $variation_id)
                                        ->orWhere('pl.variation_id', $variation_id)
                                        ->orWhere('al.variation_id', $variation_id)
                                        ->orWhere('rpl.variation_id', $variation_id)
                                        ->orWhere('rsl.variation_id', $variation_id);
                                })
                                ->whereIn('transactions.type', ['sell', 'purchase', 'stock_adjustment', 'opening_stock', 'sell_transfer', 'purchase_transfer', 'production_purchase', 'purchase_return', 'sell_return', 'production_sell'])
                                ->select(
                                    'transactions.id as transaction_id',
                                    'transactions.return_parent_id as return_id',
                                    'transactions.type as transaction_type',
                                    'sl.quantity as sell_line_quantity',
                                    'pl.quantity as purchase_line_quantity',
                                    'rsl.quantity_returned as sell_return',
                                    'rpl.quantity_returned as purchase_return',
                                    'al.quantity as stock_adjusted',
                                    'pl.quantity_returned as combined_purchase_return',
                                    'transactions.return_parent_id',
                                    'transactions.transaction_date',
                                    'transactions.status',
                                    'transactions.adjustment_sign',
                                    'transactions.mfg_parent_production_purchase_id as mfg_parent_id',
                                    'transactions.invoice_no',
                                    'transactions.ref_no',
                                    'transactions.additional_notes',
                                    'c.name as contact_name',
                                    'us.first_name as first_name',
                                    'us.last_name as last_name',
                                    'c.supplier_business_name',
                                    'pl.secondary_unit_quantity as purchase_secondary_unit_quantity',
                                    'sl.secondary_unit_quantity as sell_secondary_unit_quantity'
                                )
                                ->orderBy('transactions.transaction_date', 'asc')
                                ->get();

        $stock_history_array = [];
        $stock = 0;
        $stock_in_second_unit = 0;
        foreach ($stock_history as $stock_line) {
            $temp_array = [
                'date' => $stock_line->transaction_date,
                'transaction_id' => $stock_line->transaction_id,
                'contact_name' => $stock_line->contact_name,
                'supplier_business_name' => $stock_line->supplier_business_name,
                'created_by' =>  $stock_line->first_name.' '.$stock_line->last_name,
            ];
            if ($stock_line->transaction_type == 'sell') {
                if ($stock_line->status != 'final') {
                    continue;
                }
                $quantity_change = -1 * $stock_line->sell_line_quantity;
                $stock += $quantity_change;

                $stock_in_second_unit -= $stock_line->sell_secondary_unit_quantity;
                $stock_history_array[] = array_merge($temp_array, [
                    'quantity_change' => $quantity_change,
                    'stock' => $this->roundQuantity($stock),
                    'type' => 'sell',
                    'type_label' => __('sale.sale'),
                    'ref_no' => $stock_line->invoice_no,
                    'sele_id' => $stock_line->transaction_id,
                    'sell_secondary_unit_quantity' => ! empty($stock_line->sell_secondary_unit_quantity) ? $this->roundQuantity($stock_line->sell_secondary_unit_quantity) : 0,
                    'stock_in_second_unit' => $this->roundQuantity($stock_in_second_unit),
                ]);
            } elseif ($stock_line->transaction_type == 'purchase') {
                if ($stock_line->status != 'received') {
                    continue;
                }
                if($stock_line->adjustment_sign=='Plus'){
                    
                    $label='Surplus';
                }else{
                    $label=__('lang_v1.purchase');
                    
                }
                $quantity_change = $stock_line->purchase_line_quantity;
                $stock += $quantity_change;
                $stock_in_second_unit += $stock_line->purchase_secondary_unit_quantity;
                $stock_history_array[] = array_merge($temp_array, [
                    'quantity_change' => $quantity_change,
                    'stock' => $this->roundQuantity($stock),
                    'type' => 'purchase',
                    'type_label' => $label,
                    'ref_no' => $stock_line->ref_no,
                    'sele_id' => $stock_line->transaction_id,
                    'purchase_secondary_unit_quantity' => ! empty($stock_line->purchase_secondary_unit_quantity) ? $this->roundQuantity($stock_line->purchase_secondary_unit_quantity) : 0,
                    'stock_in_second_unit' => $this->roundQuantity($stock_in_second_unit),
                ]);
            } elseif ($stock_line->transaction_type == 'stock_adjustment') {
                $quantity_change = -1 * $stock_line->stock_adjusted;
                $stock += $quantity_change;
                $stock_history_array[] = array_merge($temp_array, [
                    'quantity_change' => $quantity_change,
                    'stock' => $this->roundQuantity($stock),
                    'type' => 'stock_adjustment',
                    'type_label' => __('stock_adjustment.stock_adjustment'),
                    'ref_no' => $stock_line->ref_no,
                    'sele_id' => $stock_line->transaction_id,
                    'stock_in_second_unit' => $this->roundQuantity($stock_in_second_unit),
                ]);
            } elseif ($stock_line->transaction_type == 'opening_stock') {
                $quantity_change = $stock_line->purchase_line_quantity;
                $stock += $quantity_change;
                $stock_in_second_unit += $stock_line->purchase_secondary_unit_quantity;
                $stock_history_array[] = array_merge($temp_array, [
                    'quantity_change' => $quantity_change,
                    'stock' => $this->roundQuantity($stock),
                    'type' => 'opening_stock',
                    'type_label' => __('report.opening_stock'),
                    'ref_no' => $stock_line->ref_no ?? '',
                    'sele_id' => $stock_line->transaction_id,
                    'additional_notes' => $stock_line->additional_notes,
                    'purchase_secondary_unit_quantity' => ! empty($stock_line->purchase_secondary_unit_quantity) ? $this->roundQuantity($stock_line->purchase_secondary_unit_quantity) : 0,
                    'stock_in_second_unit' => $this->roundQuantity($stock_in_second_unit),
                ]);
            } elseif ($stock_line->transaction_type == 'sell_transfer') {
                if ($stock_line->status != 'final') {
                    continue;
                }
                $quantity_change = -1 * $stock_line->sell_line_quantity;
                $stock += $quantity_change;
                $stock_history_array[] = array_merge($temp_array, [
                    'quantity_change' => $quantity_change,
                    'stock' => $this->roundQuantity($stock),
                    'type' => 'sell_transfer',
                    'type_label' => __('lang_v1.stock_transfers').' ('.__('lang_v1.out').')',
                    'ref_no' => $stock_line->ref_no,
                    'sele_id' => $stock_line->transaction_id,
                    'stock_in_second_unit' => $this->roundQuantity($stock_in_second_unit),
                ]);
            } elseif ($stock_line->transaction_type == 'purchase_transfer') {
                if ($stock_line->status != 'received') {
                    continue;
                }

                $quantity_change = $stock_line->purchase_line_quantity;
                $stock += $quantity_change;
                $stock_history_array[] = array_merge($temp_array, [
                    'quantity_change' => $quantity_change,
                    'stock' => $this->roundQuantity($stock),
                    'type' => 'purchase_transfer',
                    'type_label' => __('lang_v1.stock_transfers').' ('.__('lang_v1.in').')',
                    'ref_no' => $stock_line->ref_no,
                    'sele_id' => $stock_line->transaction_id,
                    'stock_in_second_unit' => $this->roundQuantity($stock_in_second_unit),
                ]);
            } elseif ($stock_line->transaction_type == 'production_sell') {
                if ($stock_line->status != 'final') {
                    continue;
                }
                $quantity_change = -1 * $stock_line->sell_line_quantity;
                $stock += $quantity_change;
                $stock_history_array[] = array_merge($temp_array, [
                    'quantity_change' => $quantity_change,
                    'stock' => $this->roundQuantity($stock),
                    'type' => 'sell',
                    'type_label' => __('manufacturing::lang.ingredient'),
                    'ref_no' => $stock_line->ref_no,
                    'sele_id' => $stock_line->mfg_parent_id,
                    'stock_in_second_unit' => $this->roundQuantity($stock_in_second_unit),
                ]);
            } elseif ($stock_line->transaction_type == 'production_purchase') {
                $quantity_change = $stock_line->purchase_line_quantity;
                $stock += $quantity_change;
                $stock_history_array[] = array_merge($temp_array, [
                    'quantity_change' => $quantity_change,
                    'stock' => $this->roundQuantity($stock),
                    'type' => 'production_purchase',
                    'type_label' => __('manufacturing::lang.manufactured'),
                    'ref_no' => $stock_line->ref_no,
                    'sele_id' => $stock_line->transaction_id,
                    'stock_in_second_unit' => $this->roundQuantity($stock_in_second_unit),
                ]);
            } elseif ($stock_line->transaction_type == 'purchase_return') {
                $quantity_change = -1 * ($stock_line->combined_purchase_return + $stock_line->purchase_return);
                $stock += $quantity_change;
                $stock_history_array[] = array_merge($temp_array, [
                    'quantity_change' => $quantity_change,
                    'stock' => $this->roundQuantity($stock),
                    'type' => 'purchase_return',
                    'type_label' => __('lang_v1.purchase_return'),
                    'ref_no' => $stock_line->ref_no,
                    'sele_id' => $stock_line->transaction_id,
                    'stock_in_second_unit' => $this->roundQuantity($stock_in_second_unit),
                ]);
            } elseif ($stock_line->transaction_type == 'sell_return') {
                $quantity_change = $stock_line->sell_return;
                $stock += $quantity_change;
                $stock_history_array[] = array_merge($temp_array, [
                    'quantity_change' => $quantity_change,
                    'stock' => $this->roundQuantity($stock),
                    'type' => 'purchase_transfer',
                    'type_label' => __('lang_v1.sell_return'),
                    'ref_no' => $stock_line->invoice_no,
                    'sele_id' => $stock_line->return_id,
                    'stock_in_second_unit' => $this->roundQuantity($stock_in_second_unit),
                ]);
            }
        }

        return array_reverse($stock_history_array);
    }

}