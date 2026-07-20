<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DevisRequest extends Model
{
    protected $fillable = ['name', 'email', 'phone', 'company_name', 'options', 'message', 'status'];

    protected $casts = [
        'options' => 'array',
    ];

    protected $attributes = [
        'status' => 'nouveau',
    ];
}