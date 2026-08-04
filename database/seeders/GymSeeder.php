<?php

namespace Database\Seeders;

use App\Enums\GymStatus;
use App\Enums\UserRole;
use App\Models\Amenity;
use App\Models\City;
use App\Models\Gym;
use App\Models\GymImage;
use App\Models\GymPlan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GymSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::where('role', UserRole::GYM_OWNER->value)->first();
        $cities = City::all();
        $amenityIds = Amenity::pluck('id')->all();

        $samples = [
            ['Iron Paradise', 'Mumbai', 19.0760, 72.8777, 1500],
            ['CrossFit Warriors', 'Bengaluru', 12.9716, 77.5946, 1800],
            ['Ladies Studio 21', 'Pune', 18.5204, 73.8567, 1200],
            ['24x7 Fitness Hub', 'New Delhi', 28.6139, 77.2090, 2200],
            ['Aqua Athletic Club', 'Hyderabad', 17.3850, 78.4867, 2500],
            ['Yoga & Zen Center', 'Mumbai', 19.1000, 72.9000, 900],
        ];

        Gym::withoutSyncingToSearch(function () use ($samples, $cities, $owner, $amenityIds): void {
            foreach ($samples as $i => [$name, $cityName, $lat, $lng, $price]) {
                $city = $cities->firstWhere('name', $cityName) ?? $cities->random();
                $gym = Gym::create([
                    'owner_id' => $owner->id,
                    'city_id' => $city->id,
                    'name' => $name,
                    'slug' => Str::slug($name).'-'.($i + 1),
                    'description' => "$name is a modern gym offering world-class equipment, expert trainers and a motivating atmosphere.",
                    'address' => "Sector $i, Main Road, $cityName",
                    'area' => "Zone ".chr(65 + $i),
                    'latitude' => $lat + ($i * 0.005),
                    'longitude' => $lng + ($i * 0.005),
                    'phone' => '+9190000000'.(10 + $i),
                    'email' => 'contact+'.($i + 1).'@'.Str::slug($name).'.com',
                    'cover_image' => 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?w=1600',
                    'opening_time' => '05:00',
                    'closing_time' => '23:00',
                    'is_24x7' => $i === 3,
                    'ladies_only' => $i === 2,
                    'gender_preference' => $i === 2 ? 'female' : 'unisex',
                    'trainer_count' => rand(4, 15),
                    'crowd_level' => ['low', 'medium', 'high'][rand(0, 2)],
                    'starting_price' => $price,
                    'status' => GymStatus::APPROVED->value,
                    'is_verified' => true,
                    'is_featured' => $i < 3,
                    'approved_at' => now(),
                    'rating_avg' => rand(38, 49) / 10,
                    'rating_count' => rand(20, 200),
                ]);

                $gym->amenities()->sync(array_rand(array_flip($amenityIds), 8));

                foreach ([
                    'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?w=1600',
                    'https://images.unsplash.com/photo-1571902943202-507ec2618e8f?w=1600',
                    'https://images.unsplash.com/photo-1540497077202-7c8a3999166f?w=1600',
                    'https://images.unsplash.com/photo-1518611012118-696072aa579a?w=1600',
                ] as $idx => $url) {
                    GymImage::create(['gym_id' => $gym->id, 'url' => $url, 'sort_order' => $idx]);
                }

                $planTemplates = [
                    ['name' => 'Trial Day', 'duration_type' => 'daily', 'days' => 1, 'price' => 300],
                    ['name' => 'Monthly Access', 'duration_type' => 'monthly', 'days' => 30, 'price' => $price],
                    ['name' => 'Quarterly', 'duration_type' => 'quarterly', 'days' => 90, 'price' => $price * 2.7, 'popular' => true],
                    ['name' => 'Annual VIP', 'duration_type' => 'yearly', 'days' => 365, 'price' => $price * 9.5],
                    ['name' => 'Personal Training (10 sessions)', 'duration_type' => 'personal_training', 'days' => 30, 'price' => $price * 3],
                ];

                foreach ($planTemplates as $tpl) {
                    GymPlan::create([
                        'gym_id' => $gym->id,
                        'name' => $tpl['name'],
                        'duration_type' => $tpl['duration_type'],
                        'duration_days' => $tpl['days'],
                        'price' => $tpl['price'],
                        'is_active' => true,
                        'is_popular' => $tpl['popular'] ?? false,
                        'features' => ['Full gym access', 'Steam & sauna', 'Locker included'],
                    ]);
                }
            }
        });
    }
}
