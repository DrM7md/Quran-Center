<?php

namespace App\Models;

use App\Models\Center;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Halaqa extends Model
{
    protected $fillable = ['name','teacher_id','is_active','center_id'];

    public function center()
    {
        return $this->belongsTo(Center::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function muhafidh()
{
    return $this->belongsTo(\App\Models\User::class, 'muhafidh_id');
}


public function muhafidhs()
{
    return $this->belongsToMany(User::class)
        ->withPivot(['is_primary','starts_at','ends_at'])
        ->withTimestamps();
}

public function primaryMuhafidh()
{
    return $this->muhafidhs()->wherePivot('is_primary', true);
}
}
