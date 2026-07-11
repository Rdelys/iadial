<?php
// app/Models/IarecepMessage.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IarecepMessage extends Model
{
    protected $fillable = ['iarecep_test_id', 'role', 'content'];
}