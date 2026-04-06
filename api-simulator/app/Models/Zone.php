<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    use HasFactory;

    protected $table = 'aq_zones';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'site_id',
        'name',
        'format_id',
        'format_key',
        'size_id',
        'size_key',
        'placement',
        'floor_price',
        'status',
        'ad_code',
        'is_deleted',
        'target_age_min',
        'target_age_max',
        'target_gender',
        'target_color',
        'target_height_min',
        'target_height_max',
        'target_weight_min',
        'target_weight_max',
        'frequency_views',
        'auto_reload',
        'reload_time',
        'target_countries',
        'target_devices',
    ];

    protected $casts = [
        'is_deleted' => 'boolean',
        'auto_reload' => 'boolean',
        'floor_price' => 'decimal:4',
        'target_countries' => 'array',
        'target_devices' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class, 'site_id');
    }

    public function format()
    {
        return $this->belongsTo(AdFormat::class, 'format_id');
    }

    public function size()
    {
        return $this->belongsTo(AdSize::class, 'size_id');
    }

    public function directCampaignLinks()
    {
        return $this->hasMany(DirectCampaignZone::class, 'zone_id');
    }
}
