<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vendor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'company_name',
        'location',
        'contact_number',
        'email',
        'business_type',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

     public function stores()
    {
        return $this->hasMany(Store::class);
    }
}
