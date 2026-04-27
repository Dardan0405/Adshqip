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
        'choose_type',
        'mobile_app_id',
        'name',
        'format_id',
        'format_key',
        'size_id',
        'size_key',
        'zone_type',
        'placement',
        'floor_price',
        'passback',
        'image_width',
        'image_height',
        'html_template',
        'custom_css',
        'bg_color',
        'sponsored_prefix',
        'css_path',
        'inline_video',
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
        'content_width_px',
        'content_height_px',
        'top_offset_px',
        'right_offset_px',
        'z_index_value',
        'is_fixed',
        'hide_side',
        'target_countries',
        'target_devices',
    ];

    protected $casts = [
        'is_deleted' => 'boolean',
        'auto_reload' => 'boolean',
        'inline_video' => 'boolean',
        'is_fixed' => 'boolean',
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

    public function mobileApp()
    {
        return $this->belongsTo(TelegramMiniApp::class, 'mobile_app_id');
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
