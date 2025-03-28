<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'category',
        'image',
        'price',
        'stock',
        'store_id',
        'status',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
