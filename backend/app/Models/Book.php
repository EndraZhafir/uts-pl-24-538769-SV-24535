<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'author',
        'type',
        'published_year',
        'status',
    ];

    protected $casts = [
        'published_year' => 'integer',
    ];
}