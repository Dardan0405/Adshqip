<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdvertiserTeamInvitation extends Model
{
    protected $table = 'aq_advertiser_team_invitations';

    protected $fillable = [
        'owner_advertiser_id',
        'member_id',
        'email',
        'name',
        'team_role',
        'permissions',
        'token',
        'status',
        'expires_at',
        'accepted_at',
        'invited_by',
    ];

    protected $casts = [
        'permissions' => 'array',
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_advertiser_id');
    }

    public function member()
    {
        return $this->belongsTo(AdvertiserTeamMember::class, 'member_id');
    }

    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function isAcceptable(): bool
    {
        return $this->status === 'pending'
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }
}
