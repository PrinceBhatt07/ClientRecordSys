<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_title',
        'project_description'
    ];

    public function clients()
    {
        return $this->belongsToMany(Client::class, 'client_projects');
    }

    public function technologies()
    {
        return $this->belongsToMany(Technology::class, 'project_technologies');
    }
}
