<?php

declare(strict_types=1);

namespace Workbench\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Workbench\App\Models\Horse;
use Workbench\App\Models\Ranch;
use Workbench\App\Models\Rider;
use Workbench\App\Models\User;
use Workbench\App\Notifications\HorseEscaped;

/**
 * Fixed, hand-written demo data for browsing the workbench panel and for the
 * README screenshots. Nothing here is random, so every capture looks the same.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->create([
            'name' => 'Jordan Reyes',
            'email' => 'jordan@ranch.test',
            'password' => bcrypt('password'),
            // Not an admin, so the admin-only `notes` field and column stay
            // hidden, which also shows `canSee` at work.
            'is_admin' => false,
        ]);

        $ranches = collect(['Lazy J Ranch', 'Circle K Outfitters', 'Red Mesa Stables'])
            ->map(fn (string $name) => Ranch::query()->create(['name' => $name]));

        $user->ranches()->attach($ranches->pluck('id'));

        $riders = collect([
            ['Wes Calloway', 0], ['Dolores Vance', 0], ['Hank Mercer', 1],
            ['June Whitaker', 1], ['Eli Navarro', 2], ['Rosa Delgado', 2],
        ])->map(fn (array $row) => Rider::query()->create([
            'name' => $row[0],
            'ranch_id' => $ranches[$row[1]]->id,
        ]));

        $horses = [
            // name, breed, age, saddled, rider index, ranch index, days ago
            ['Cisco', 'quarter', 7, true, 0, 0, 1],
            ['Dakota', 'appaloosa', 5, true, 1, 0, 3],
            ['Comanche', 'mustang', 11, false, null, 1, 4],
            ['Juniper', 'quarter', 4, true, 2, 1, 6],
            ['Ranger', 'mustang', 9, false, 3, 1, 9],
            ['Willow', 'appaloosa', 6, true, 4, 2, 12],
            ['Blaze', 'quarter', 8, true, 5, 2, 15],
            ['Sundance', 'mustang', 12, false, null, 0, 18],
            ['Pepper', 'appaloosa', 3, false, 0, 0, 22],
            ['Maverick', 'quarter', 10, true, 2, 1, 27],
            ['Sage', 'mustang', 6, true, 4, 2, 31],
            ['Bandit', 'quarter', 5, false, 1, 0, 36],
        ];

        foreach ($horses as [$name, $breed, $age, $saddled, $rider, $ranch, $daysAgo]) {
            $created = Carbon::now()->subDays($daysAgo)->setTime(9, 30);

            $horse = Horse::query()->create([
                'name' => $name,
                'breed' => $breed,
                'age' => $age,
                'is_saddled' => $saddled,
                'rider_id' => $rider === null ? null : $riders[$rider]->id,
                'ranch_id' => $ranches[$ranch]->id,
                'foaled_on' => Carbon::now()->subYears($age)->startOfYear()->addDays(40),
                'last_vet_visit' => $created->copy()->addDay(),
                'notes' => "**Temperament:** steady on the trail.\n\n- Shod every six weeks\n- Prefers the north pasture",
            ]);

            $horse->forceFill(['created_at' => $created, 'updated_at' => $created])->saveQuietly();
        }

        foreach (['Comanche', 'Sundance'] as $horse) {
            $user->notify(new HorseEscaped($horse));
        }
    }
}
