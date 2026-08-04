<?php

namespace Database\Seeders;

use App\Models\Amenity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AmenitySeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['Parking', 'facility', 'car'],
            ['AC', 'facility', 'wind'],
            ['Steam', 'facility', 'droplets'],
            ['Sauna', 'facility', 'flame'],
            ['Yoga', 'services', 'flower'],
            ['CrossFit', 'services', 'dumbbell'],
            ['Swimming', 'facility', 'waves'],
            ['Locker', 'facility', 'lock'],
            ['Personal Trainer', 'services', 'user-check'],
            ['Female Trainer', 'services', 'user'],
            ['Cardio', 'equipment', 'activity'],
            ['Strength Zone', 'equipment', 'dumbbell'],
            ['Weightlifting', 'equipment', 'weight'],
            ['Functional Training', 'services', 'zap'],
            ['Zumba', 'services', 'music'],
            ['24x7 Access', 'facility', 'clock'],
            ['Ladies Only', 'facility', 'heart'],
            ['Shower', 'facility', 'shower-head'],
            ['Wi-Fi', 'facility', 'wifi'],
            ['Nutrition Bar', 'services', 'apple'],
        ];

        foreach ($items as [$name, $cat, $icon]) {
            Amenity::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'category' => $cat,
                'icon' => $icon,
                'is_active' => true,
            ]);
        }
    }
}
