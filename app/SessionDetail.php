<?php

namespace App;

use App\Product;
use App\Transaction;
use App\Variation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'product_id',
        'variation_id',
        'quantity',
        'unit_price',
        'line_discount_type',
        'line_discount_amount',
        'line_tax_id',
        'line_tax_amount',
        'subtotal',
        'comment'
    ];

    /**
     * Get the transaction that owns the detail.
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Get the product associated with the detail.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the variation associated with the detail.
     */
    public function variation()
    {
        return $this->belongsTo(Variation::class);
    }
}
