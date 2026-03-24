<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;


class Student extends Model
{
    protected $fillable = [
        'name',
        'age',
        'phone',
        'halaqa_id',
        'is_active',
        'monthly_target_ayahs',
    ];

    protected $casts = [
        'is_active'            => 'boolean',
        'monthly_target_ayahs' => 'integer',
    ];

    public function halaqa()
    {
        return $this->belongsTo(Halaqa::class);
    }

    // ✅ فلترة موحّدة حسب المستخدم
public function scopeVisibleTo($q, $user)
{
    // super-admin يرى الكل بدون قيود
    if ($user->isSuperAdmin()) {
        return $q;
    }

    // admin عادي → طلاب مركزه فقط
    if ($user->hasRole('admin')) {
        return $user->center_id
            ? $q->whereHas('halaqa', fn($hq) => $hq->where('center_id', $user->center_id))
            : $q;
    }

    // muhafidh → طلاب حلقاته فقط
    $halaqaIds = $user->halaqas()->pluck('halaqas.id');

    if ($halaqaIds->isEmpty() && !empty($user->halaqa_id)) {
        $halaqaIds = collect([$user->halaqa_id]);
    }

    return $q->whereIn('halaqa_id', $halaqaIds);
}


public function absences()
{
    return $this->hasMany(\App\Models\Absence::class);
}

public function memorizations()
{
    return $this->hasMany(\App\Models\Memorization::class);
}

public function guardians()
{
    return $this->belongsToMany(User::class, 'guardian_student', 'student_id', 'guardian_id')
        ->withPivot(['approved_at', 'approved_by'])
        ->withTimestamps();
}

}
