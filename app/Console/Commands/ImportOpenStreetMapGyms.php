<?php

namespace App\Console\Commands;

use App\Models\City;
use App\Models\User;
use App\Services\Gym\OpenStreetMapGymImporter;
use Illuminate\Console\Command;

class ImportOpenStreetMapGyms extends Command
{
    protected $signature = 'gyms:import-osm
        {city? : City name, or omit to import all configured cities}
        {--dry-run : Fetch and count listings without writing to the database}';

    protected $description = 'Import pending gym listings from OpenStreetMap';

    public function handle(OpenStreetMapGymImporter $importer): int
    {
        $owner = User::where('email', config('services.gym_import.owner_email'))->first();

        if (! $owner) {
            $this->error('Import owner was not found. Set GYM_IMPORT_OWNER_EMAIL to an existing admin email.');
            return self::FAILURE;
        }

        $cities = City::query()->where('is_active', true)
            ->when($this->argument('city'), fn ($query, $city) => $query->where('name', 'like', $city))
            ->get();

        if ($cities->isEmpty()) {
            $this->error('No active city matched the requested name.');
            return self::FAILURE;
        }

        foreach ($cities as $city) {
            $this->info('Importing '.$city->name.'...');
            $result = $importer->import($city, $owner, (bool) $this->option('dry-run'));
            $this->line("  created: {$result['created']}, updated: {$result['updated']}");
        }

        return self::SUCCESS;
    }
}