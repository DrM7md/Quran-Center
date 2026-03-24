<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Memorization extends Model
{
    protected $fillable = [
        'student_id',
        'halaqa_id',
        'muhafidh_id',
        'type',
        'surah_id',
        'from_ayah',
        'to_ayah',
        'rating',
        'notes',
        'heard_at',
    ];

    protected $casts = [
        'heard_at' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function halaqa(): BelongsTo
    {
        return $this->belongsTo(Halaqa::class);
    }

    public function muhafidh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'muhafidh_id');
    }

    public function surah(): BelongsTo
    {
        return $this->belongsTo(Surah::class);
    }
}
