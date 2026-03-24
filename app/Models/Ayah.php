<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ayah extends Model
{
    public $timestamps  = false;
    public $incrementing = false;

    protected $primaryKey = ['surah_number', 'number_in_surah'];
    protected $keyType    = 'int';

    protected $fillable = ['surah_number', 'number_in_surah', 'text'];

    public function surah(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Surah::class, 'surah_number', 'number');
    }
}
