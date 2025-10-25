<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
class Reconcile extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'reconciles';
    protected $fillable = [
        'name', 'date', 'created_by', 'updated_by'
    ];
        protected $dates = ['deleted_at'];

    public function creator(){
        return $this->belongsTo(User::class, 'created_by');
    }
    public function updater(){
        return $this->belongsTo(User::class, 'updated_by');
    }
    public function details()
    {
        return $this->hasMany(ReconcileDetails::class, 'reconcile_id');
    }
   
}
