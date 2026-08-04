<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $india = Country::create(['name' => 'India', 'iso2' => 'IN', 'phone_code' => '+91']);

        $maharashtra = State::create(['country_id' => $india->id, 'name' => 'Maharashtra', 'code' => 'MH']);
        $karnataka = State::create(['country_id' => $india->id, 'name' => 'Karnataka', 'code' => 'KA']);
        $delhi = State::create(['country_id' => $india->id, 'name' => 'Delhi', 'code' => 'DL']);
        $telangana = State::create(['country_id' => $india->id, 'name' => 'Telangana', 'code' => 'TG']);

        $cities = [
            ['state' => $maharashtra, 'name' => 'Mumbai', 'lat' => 19.0760, 'lng' => 72.8777],
            ['state' => $maharashtra, 'name' => 'Pune', 'lat' => 18.5204, 'lng' => 73.8567],
            ['state' => $karnataka, 'name' => 'Bengaluru', 'lat' => 12.9716, 'lng' => 77.5946],
            ['state' => $delhi, 'name' => 'New Delhi', 'lat' => 28.6139, 'lng' => 77.2090],
            ['state' => $telangana, 'name' => 'Hyderabad', 'lat' => 17.3850, 'lng' => 78.4867],
        ];

        foreach ($cities as $c) {
            City::create([
                'state_id' => $c['state']->id,
                'name' => $c['name'],
                'slug' => Str::slug($c['name']),
                'latitude' => $c['lat'],
                'longitude' => $c['lng'],
                'is_active' => true,
            ]);
        }
    }
}
