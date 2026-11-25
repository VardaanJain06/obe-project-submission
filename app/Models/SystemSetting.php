<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $guarded = [];

    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function set(string $key, $value, string $group = 'general', string $type = 'string'): self
    {
        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'group' => $group,
                'type' => $type,
            ]
        );
    }

    public static function getAllSettings(): array
    {
        $defaults = [
            'app_name' => 'NBA SAR & OBE Platform',
            'app_short_name' => 'NBA',
            'app_tagline' => 'Outcome-Based Education & Self-Assessment Compliance Engine',
            'institution_name' => 'National Institute of Engineering & Technology',
            'institution_code' => 'NIET-098',
            'head_of_institution' => 'Dr. R. K. Sharma (Principal / Director)',
            'institution_address' => 'Knowledge Park Tech Zone, Phase II, Greater Noida, UP',
            'institution_website' => 'https://niet-tech.edu.in',
            'contact_email' => 'admin@niet-tech.edu.in',
            'contact_phone' => '+91 9811223344',
            'nba_tier' => 'Tier-II',
            'current_academic_year' => '2024-25 (CAY)',
            'login_logo' => '',
            'sidebar_logo' => '',
            'favicon' => '',
            'report_header_logo' => '',
            'theme_color' => '#6366f1',
        ];

        $stored = static::all()->pluck('value', 'key')->toArray();
        return array_merge($defaults, $stored);
    }
}
