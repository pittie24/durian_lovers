<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'description',
        'composition',
        'weight',
        'price',
        'stock',
        'sold_count',
        'image_url',
        'rating_avg',
        'rating_count',
    ];

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }
}
