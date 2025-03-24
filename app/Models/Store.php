<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

     protected $fillable = [
        'vendor_id',
        'name',
        'location',
        'status'
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
