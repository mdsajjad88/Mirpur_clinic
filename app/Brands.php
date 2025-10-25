<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brands extends Model
{
    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Return list of brands for a business
     *
     * @param  int  $business_id
     * @param  bool  $show_none = false
     * @return array
     */
    public static function forDropdown($business_id, $show_none = false, $filter_use_for_repair = false, $type = false)
    {
        $query = Brands::where('business_id', $business_id);

        if ($filter_use_for_repair) {
            $query->where('use_for_repair', 1);
        }

        if ($type) {
            $query->where('type', $type);
        }

        $brands = $query->orderBy('name', 'asc')
                    ->pluck('name', 'id');

        if ($show_none) {
            $brands->prepend(__('lang_v1.none'), '');
        }

        return $brands;
    }
    public static function forDropdownWithSubType($business_id, $show_none = false, $filter_use_for_repair = false, $type = false, $sub_type, $multipleType = false)
    {
        $query = Brands::where('business_id', $business_id);

        if ($filter_use_for_repair) {
            $query->where('use_for_repair', 1);
        }

        if ($type) {
            $query->where('type', $type);
        }
        if ($sub_type) {
            $query->where('sub_type', $sub_type);
        }
        if ($multipleType) {
            $query->whereIn('sub_type', $multipleType);
        }

        $brands = $query->orderBy('name', 'asc')
                    ->pluck('name', 'id');

        if ($show_none) {
            $brands->prepend(__('lang_v1.none'), '');
        }

        return $brands;
    }
}
