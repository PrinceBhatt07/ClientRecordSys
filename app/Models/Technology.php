<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Technology extends Model
{
    use HasFactory;

    protected $fillable = [
        'technology'
    ];

    public function clients()
    {
        return $this->belongsToMany(Client::class, 'client_technologies');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_technologies');
    }
}
