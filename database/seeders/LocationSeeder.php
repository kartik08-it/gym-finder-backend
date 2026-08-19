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
        $india = Country::updateOrCreate(['iso2' => 'IN'], ['name' => 'India', 'phone_code' => '+91']);

        $maharashtra = State::updateOrCreate(['country_id' => $india->id, 'code' => 'MH'], ['name' => 'Maharashtra']);
        $karnataka = State::updateOrCreate(['country_id' => $india->id, 'code' => 'KA'], ['name' => 'Karnataka']);
        $delhi = State::updateOrCreate(['country_id' => $india->id, 'code' => 'DL'], ['name' => 'Delhi']);
        $telangana = State::updateOrCreate(['country_id' => $india->id, 'code' => 'TG'], ['name' => 'Telangana']);
        $uttarPradesh = State::updateOrCreate(['country_id' => $india->id, 'code' => 'UP'], ['name' => 'Uttar Pradesh']);

        $cities = [
            ['state' => $maharashtra, 'name' => 'Mumbai', 'lat' => 19.0760, 'lng' => 72.8777],
            ['state' => $maharashtra, 'name' => 'Pune', 'lat' => 18.5204, 'lng' => 73.8567],
            ['state' => $karnataka, 'name' => 'Bengaluru', 'lat' => 12.9716, 'lng' => 77.5946],
            ['state' => $delhi, 'name' => 'New Delhi', 'lat' => 28.6139, 'lng' => 77.2090],
            ['state' => $telangana, 'name' => 'Hyderabad', 'lat' => 17.3850, 'lng' => 78.4867],
            ['state' => $uttarPradesh, 'name' => 'Noida', 'lat' => 28.5355, 'lng' => 77.3910],
            ['state' => $uttarPradesh, 'name' => 'Gurugram', 'lat' => 28.4595, 'lng' => 77.0266],
            ['state' => $uttarPradesh, 'name' => 'Greater Noida', 'lat' => 28.4744, 'lng' => 77.5030],
            ['state' => $uttarPradesh, 'name' => 'Meerut', 'lat' => 28.9845, 'lng' => 77.7064],
            ['state' => $uttarPradesh, 'name' => 'Muzaffarnagar', 'lat' => 29.4727, 'lng' => 77.7085],
        ];

        foreach ($cities as $c) {
            City::updateOrCreate(['slug' => Str::slug($c['name'])], [
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
