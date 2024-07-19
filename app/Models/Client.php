<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'updated_by',
        'name',
        'contact',
        'email',
        'skype_id',
        'address',
        'country',
        'website_url',
        'linkedin_url',
        'facebook_url',
        'is_archived',
        'archived_at'
    ];

    protected $casts = [
        'is_archived' => 'boolean',
        'archived_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'client_projects')->with('technologies');
    }

    public function technologies()
    {
        return $this->belongsToMany(Technology::class, 'client_technologies');
    }

    public function scopeSearch($query, $value)
    {
        $query->where(function ($query) use ($value) {
            $query->where('name', 'like', '%' . $value . '%')
                ->orWhere('email', 'like', '%' . $value . '%')
                ->orWhere('address', 'like', '%' . $value . '%')
                ->orWhere('country', 'like', '%' . $value . '%')
                ->orWhere('website_url', 'like', '%' . $value . '%');
        })->orWhereHas('technologies', function ($query) use ($value) {
            $query->where('technology', 'like', '%' . $value . '%');
        })->get();
    }
}
