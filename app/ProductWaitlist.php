<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductWaitlist extends Model
{
    use HasFactory, SoftDeletes;

     // Define the fillable fields for mass assignment
     protected $fillable = [
        'waitlist_no',
        'transaction_id',
        'contact_id',
        'product_id',
        'location_id',
        'quantity_requested',
        'status',
        'estimated_restock_date',
        'restock_date',
        'notification_sent_date',
        'fulfilled_date',
        'reference',
        'notes',
        'added_by',
    ];

    // Define the relationships with other models

    // Contact relationship
    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    // Product relationship
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    // User relationship (added by)
    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
