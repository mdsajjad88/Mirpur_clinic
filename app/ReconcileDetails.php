<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
class ReconcileDetails extends Model
{
    use HasFactory, SoftDeletes;

    protected $table ='reconcile_details';
    protected $fillable = [
        'reconcile_id', 'name', 'sku', 'physical_qty', 'software_qty', 'difference', 'difference_percentage', 'created_by', 'updated_by',
    ];
    protected $dates = ['deleted_at'];
    
    public function creator(){
        return $this->belongsTo(User::class, 'created_by');
    }
    public function updater(){
        return $this->belongsTo(User::class, 'updated_by');
    }
    
    public function reconcile()
    {
        return $this->belongsTo(Reconcile::class, 'reconcile_id');
    }
    
    
}
