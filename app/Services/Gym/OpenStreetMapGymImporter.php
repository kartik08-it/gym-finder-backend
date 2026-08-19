<?php

namespace App\Services\Gym;

use App\Models\City;
use App\Models\Gym;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class OpenStreetMapGymImporter
{
    public function import(City $city, User $owner, bool $dryRun = false): array
    {
        $response = Http::asForm()->withHeaders([
            'User-Agent' => config('services.nominatim.user_agent'),
        ])->timeout(90)->post(
            env('OVERPASS_URL', 'https://overpass-api.de/api/interpreter'),
            ['data' => $this->query($city)]
        )->throw()->json();

        $created = 0;
        $updated = 0;

        foreach ($response['elements'] ?? [] as $element) {
            $tags = $element['tags'] ?? [];
            $name = trim($tags['name'] ?? '');

            if ($name === '') {
                continue;
            }

            $sourceId = $element['type'].'/'.$element['id'];
            $coordinates = $this->coordinates($element);
            $attributes = [
                'owner_id' => $owner->id,
                'city_id' => $city->id,
                'name' => $name,
                'slug' => Str::slug($name).'-osm-'.$element['id'],
                'address' => $this->address($tags, $city),
                'area' => $tags['addr:suburb'] ?? $tags['addr:district'] ?? null,
                'postal_code' => $tags['addr:postcode'] ?? null,
                'latitude' => $coordinates['lat'],
                'longitude' => $coordinates['lon'],
                'phone' => $this->phone($tags),
                'website' => $tags['website'] ?? $tags['contact:website'] ?? null,
                'source' => 'openstreetmap',
                'source_id' => $sourceId,
                'source_url' => 'https://www.openstreetmap.org/'.$element['type'].'/'.$element['id'],
                'last_synced_at' => now(),
            ];

            if ($dryRun) {
                $created++;
                continue;
            }

            $gym = Gym::withTrashed()->where('source', 'openstreetmap')
                ->where('source_id', $sourceId)->first();

            Gym::withoutSyncingToSearch(function () use ($gym, $attributes, &$created, &$updated): void {
                if ($gym) {
                    $gym->restore();
                    $gym->update($attributes);
                    $updated++;
                } else {
                    Gym::create($attributes);
                    $created++;
                }
            });
        }

        return compact('created', 'updated');
    }

    private function query(City $city): string
    {
        $latitude = (float) $city->latitude;
        $longitude = (float) $city->longitude;

        return '[out:json][timeout:80];(nwr["leisure"="fitness_centre"](around:20000,'
            .$latitude.','.$longitude.');nwr["sport"~"fitness|gym",i](around:20000,'
            .$latitude.','.$longitude.'););out center tags;';
    }

    private function coordinates(array $element): array
    {
        return [
            'lat' => $element['lat'] ?? $element['center']['lat'],
            'lon' => $element['lon'] ?? $element['center']['lon'],
        ];
    }

    private function address(array $tags, City $city): string
    {
        $parts = array_filter([
            $tags['addr:housenumber'] ?? null,
            $tags['addr:street'] ?? null,
            $tags['addr:place'] ?? null,
            $tags['addr:suburb'] ?? null,
            $tags['addr:city'] ?? $city->name,
        ]);

        return implode(', ', $parts) ?: $city->name;
    }

    private function phone(array $tags): ?string
    {
        $phone = $tags['phone'] ?? $tags['contact:phone'] ?? null;

        return $phone ? substr(trim(explode(';', $phone)[0]), 0, 20) : null;
    }
}