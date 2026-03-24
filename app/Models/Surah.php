<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Surah extends Model
{
    protected $fillable = ['number', 'name', 'ayahs_count'];

    public function ayahs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Ayah::class, 'surah_number', 'number');
    }
}
