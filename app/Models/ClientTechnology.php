<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInfoTechnology extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'technology_id',
    ];
}
