<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = [
        'title', 'route_name', 'icon', 'permission',
        'parent_id', 'sort_order', 'group_label', 'section',
        'is_active', 'visibility_config',
    ];

    protected $casts = [
        'visibility_config' => 'array',
        'is_active'         => 'boolean',
    ];

    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('sort_order');
    }

    public function parent()
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    /**
     * Cek visibility berdasarkan config (feature flags seperti rfid, school_mode, dll)
     */
    public function checkVisibility(): bool
    {
        if (empty($this->visibility_config)) {
            return true;
        }

        foreach ($this->visibility_config as $key => $value) {
            if (config("app.{$key}") != $value) {
                return false;
            }
        }

        return true;
    }
}
