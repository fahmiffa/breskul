<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

class AppContext
{
    protected array $settings = [];

    public function load(): void
    {
        $user = Auth::user();

        if (!$user) {
            return;
        }

        $app = $user->app()
            ->with('features')
            ->first();

        if (!$app) {
            return;
        }

        $features = $app->features->pluck('name')->toArray();

        $this->settings = [
            'id'          => $app->id,
            'name'        => $app->name,
            'school_mode' => in_array('junior', $features),
            'halaqoh'     => in_array('halaqoh', $features),
            'rfid'        => in_array('rfid', $features),
            'jurnal'      => in_array('jurnal', $features),
            'features'    => $features,
        ];

        // dd($this->settings);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->settings[$key] ?? $default;
    }

    public function hasFeature(string $feature): bool
    {
        return in_array(
            $feature,
            $this->settings['features'] ?? [],
            true
        );
    }

    public function all(): array
    {
        return $this->settings;
    }
}