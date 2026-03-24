<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessRequest extends Model
{
    protected $fillable = [
        'email',
        'requester_name',
        'message',
        'center_id',
        'is_read',
        'is_resolved',
    ];

    protected $casts = [
        'is_read'     => 'boolean',
        'is_resolved' => 'boolean',
    ];

    public function scopeUnread($q)
    {
        return $q->where('is_read', false);
    }

    public function scopePending($q)
    {
        return $q->where('is_resolved', false);
    }
}
