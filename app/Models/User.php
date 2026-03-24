<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Center;
use App\Models\Halaqa;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'center_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


public function center()
{
    return $this->belongsTo(Center::class);
}

public function isSuperAdmin(): bool
{
    return $this->hasRole('super-admin');
}

public function isCenterAdmin(): bool
{
    return $this->hasRole('admin') && !$this->hasRole('super-admin');
}

public function halaqas()
{
    return $this->belongsToMany(Halaqa::class)
        ->withPivot(['is_primary','starts_at','ends_at'])
        ->withTimestamps();
}

public function primaryHalaqa()
{
    return $this->belongsToMany(Halaqa::class)
        ->withPivot(['is_primary','starts_at','ends_at'])
        ->wherePivot('is_primary', 1);
}

public function primary_halaqa_id(): ?int
{
    return $this->primaryHalaqa()->value('halaqas.id');
}

public function getPrimaryHalaqaIdAttribute(): ?int
{
    // لو عندك عمود halaqa_id وتستخدمه لبعض الحالات
    if (!is_null($this->halaqa_id)) {
        return (int) $this->halaqa_id;
    }

    // غير كذا: خذ الحلقة الأساسية من pivot (halaqa_user)
    return $this->halaqas()
        ->wherePivot('is_primary', 1)
        ->value('halaqas.id');
}



public function activeHalaqas()
{
    $today = Carbon::today()->toDateString();

    return $this->halaqas()
        ->where(function ($q) use ($today) {
            $q->whereNull('halaqa_user.starts_at')
              ->orWhereDate('halaqa_user.starts_at', '<=', $today);
        })
        ->where(function ($q) use ($today) {
            $q->whereNull('halaqa_user.ends_at')
              ->orWhereDate('halaqa_user.ends_at', '>=', $today);
        });
}

public function accessibleHalaqaIds(): array
{
    return $this->activeHalaqas()
        ->pluck('halaqas.id')
        ->map(fn ($v) => (int) $v)
        ->toArray();
}




    public function isAdmin(): bool
{
    return $this->hasRole(['super-admin', 'admin']);
}

public function isGuardian(): bool
{
    return $this->hasRole('guardian');
}

public function guardianStudents(): BelongsToMany
{
    return $this->belongsToMany(Student::class, 'guardian_student', 'guardian_id', 'student_id')
        ->withPivot(['approved_at', 'approved_by'])
        ->withTimestamps();
}

public function guardianNotifications(): HasMany
{
    return $this->hasMany(GuardianNotification::class, 'user_id');
}

public function unreadNotificationsCount(): int
{
    return $this->guardianNotifications()->where('is_read', false)->count();
}

}


