<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdvertiserTeamMember extends Model
{
    protected $table = 'aq_advertiser_team_members';

    protected $fillable = [
        'owner_advertiser_id',
        'user_id',
        'email',
        'name',
        'team_role',
        'permissions',
        'status',
        'invited_by',
        'accepted_at',
    ];

    protected $casts = [
        'permissions' => 'array',
        'accepted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_advertiser_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function invitations()
    {
        return $this->hasMany(AdvertiserTeamInvitation::class, 'member_id');
    }
}
