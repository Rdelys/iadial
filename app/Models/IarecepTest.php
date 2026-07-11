<?php
// app/Models/IarecepTest.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IarecepTest extends Model
{
    protected $fillable = [
        'token', 'company_name', 'full_name', 'email',
        'sector', 'about', 'mode', 'status',
    ];

    public function messages()
    {
        return $this->hasMany(IarecepMessage::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'in_progress';
    }

    // app/Models/IarecepTest.php — à ajouter dans la classe

    public function appointments()
    {
        return $this->hasMany(IarecepAppointment::class);
    }
}