<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_name',
        'location',
        'contact_number',
        'email',
        'business_type',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
