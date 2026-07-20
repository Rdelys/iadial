<?php
// app/Models/IarecepAppointment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IarecepAppointment extends Model
{
    protected $fillable = [
        'user_id', 'iarecep_test_id', 'token', 'date', 'time',
        'full_name', 'phone', 'email', 'notes', 'source', 'status',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
    ];

    public function test()
    {
        return $this->belongsTo(IarecepTest::class, 'iarecep_test_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}