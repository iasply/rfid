<?php

namespace Database\Seeders;

use App\Models\Cattle;
use App\Models\User;
use App\Models\Vaccine;
use App\Models\Workstation;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LargeTestDatasetSeeder extends Seeder
{
    /**
     * Monthly probability weights per vaccine type (index 0 = January, 11 = December).
     *
     * Based on the Brazilian MAPA/Embrapa bovine vaccination calendar:
     *   - Febre Aftosa:          mandatory government campaigns in May and November
     *   - Brucelose:             1st-semester deadline (May 31), permitted until Nov 30
     *   - Raiva:                 endemic regions; peaks during rainy season (Nov–Apr)
     *   - Clostridiose:          polyvalent, mild 1st-semester bias
     *   - Carbúnculo Sintomático:paired with Clostridiose, young animals
     *   - Leptospirose:          every 6 months → two peaks (May, Oct/Nov)
     *   - IBR/BVD:               pre-breeding season (Mar/Sep)
     *   - Verminose:             "5-7-9" scheme (May, July, September) + Nov reinforcement
     *   - Tristeza Parasitária:  tick-driven, dry season bias (Jun–Sep)
     *   - Botulismo:             dry season (Jun–Oct)
     */
    private array $vaccineSeasons = [
        'Febre Aftosa'           => [ 1,  1,  1,  2, 10,  2,  1,  1,  1,  2, 10,  2],
        'Brucelose'              => [ 3,  3,  4,  5,  8,  4,  2,  2,  2,  2,  5,  3],
        'Raiva'                  => [ 6,  6,  5,  3,  2,  1,  1,  2,  2,  3,  5,  7],
        'Clostridiose'           => [ 2,  2,  3,  4,  6,  4,  3,  3,  4,  3,  4,  2],
        'Carbúnculo Sintomático' => [ 2,  2,  3,  4,  6,  4,  3,  3,  4,  3,  3,  2],
        'Leptospirose'           => [ 3,  3,  4,  4,  6,  3,  3,  3,  5,  6,  4,  3],
        'IBR/BVD'                => [ 3,  4,  7,  4,  3,  2,  2,  3,  7,  4,  3,  2],
        'Verminose'              => [ 1,  1,  1,  1, 10,  1,  8,  1,  8,  1,  6,  1],
        'Tristeza Parasitária'   => [ 2,  2,  3,  3,  4,  5,  6,  6,  5,  4,  3,  2],
        'Botulismo'              => [ 2,  2,  2,  3,  4,  6,  6,  5,  4,  4,  3,  2],
    ];

    public function run(): void
    {
        Vaccine::query()->delete();
        Cattle::query()->delete();
        Workstation::query()->delete();
        User::where('is_veterinarian', true)->delete();

        $workstations = Workstation::factory()->count(5)->create();
        $vets         = User::factory()->count(10)->veterinarian()->create();

        // 300 cattle distributed evenly across vets
        $allCattle  = collect();
        $totalCattle = 300;
        $perVet      = intdiv($totalCattle, $vets->count());
        $extra       = $totalCattle % $vets->count();

        foreach ($vets as $i => $vet) {
            $count     = $perVet + ($i < $extra ? 1 : 0);
            $batch     = Cattle::factory()->count($count)->create(['user_id' => $vet->id]);
            $allCattle = $allCattle->concat($batch);
        }

        $vaccineTypes = array_keys($this->vaccineSeasons);
        $yearStart    = Carbon::now()->subYear()->startOfDay();
        $today        = Carbon::now()->startOfDay();

        // Pre-build workstation id list and vet id list for fast random access
        $wsIds  = $workstations->pluck('id')->all();
        $vetIds = $vets->pluck('id')->all();

        $rows = [];

        foreach ($allCattle as $animal) {
            $vaccineCount = rand(3, 8);

            // Shuffle types so each animal gets a varied, non-repetitive schedule
            $shuffled  = $vaccineTypes;
            shuffle($shuffled);
            $schedule  = array_slice($shuffled, 0, $vaccineCount);

            foreach ($schedule as $type) {
                $date = $this->seasonalDate($yearStart, $today, $type);

                // Weight at vaccination time: animals are lighter further in the past.
                // Assume 10–30% total growth over the year (realistic for beef cattle).
                $daysAgo     = $today->diffInDays($date);
                $growthShare = rand(10, 30) / 100;
                $factor      = 1 - ($growthShare * ($daysAgo / 365));
                $pastWeight  = $animal->weight * max(0.70, $factor);
                // Add a small noise of ±2% around the estimated past weight
                $noise       = $pastWeight * (rand(-20, 20) / 1000);
                $weight      = round(max(80.0, $pastWeight + $noise), 2);

                $rows[] = [
                    'rfid_tag'         => $animal->rfid_tag,
                    'vaccine_type'     => $type,
                    'current_weight'   => $weight,
                    'vaccination_date' => $date->toDateString(),
                    'user_id'          => $vetIds[array_rand($vetIds)],
                    'workstation_id'   => $wsIds[array_rand($wsIds)],
                    'created_at'       => $date->toDateTimeString(),
                    'updated_at'       => $date->toDateTimeString(),
                ];
            }
        }

        // Bulk insert in chunks (SQLite performs well up to ~500 rows per statement)
        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('vaccines')->insert($chunk);
        }

        $this->command->info(sprintf(
            'Seeded: %d workstations | %d vets | %d cattle | %d vaccine records.',
            $workstations->count(),
            $vets->count(),
            $allCattle->count(),
            count($rows),
        ));
    }

    /**
     * Pick a random date within [$from, $to] with monthly probability
     * weighted by the given vaccine type's seasonal profile.
     */
    private function seasonalDate(Carbon $from, Carbon $to, string $vaccineType): Carbon
    {
        $weights = $this->vaccineSeasons[$vaccineType] ?? array_fill(0, 12, 1);

        // Build weighted pool: each calendar month in range appears $weight times
        $pool   = [];
        $cursor = $from->copy()->startOfMonth();

        while ($cursor->lte($to)) {
            $w = $weights[$cursor->month - 1];
            for ($i = 0; $i < $w; $i++) {
                $pool[] = $cursor->copy();
            }
            $cursor->addMonth();
        }

        /** @var Carbon $chosenMonth */
        $chosenMonth = $pool[array_rand($pool)];

        // Clamp day range to [from, to] within the chosen month
        $dayStart  = $chosenMonth->copy()->startOfMonth()->max($from);
        $dayEnd    = $chosenMonth->copy()->endOfMonth()->min($to);
        $dayOffset = rand(0, max(0, (int) $dayEnd->diffInDays($dayStart)));

        return $dayStart->copy()->addDays($dayOffset);
    }
}
