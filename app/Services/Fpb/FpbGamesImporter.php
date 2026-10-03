<?php

namespace App\Services\Fpb;

use App\Enums\GameStatus;
use App\Enums\HomeAway;
use App\Models\Game;
use App\Models\Player;
use App\Models\Team;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class FpbGamesImporter
{
    protected const MESES = [
        'JAN' => 1, 'FEV' => 2, 'MAR' => 3, 'ABR' => 4, 'MAI' => 5, 'JUN' => 6,
        'JUL' => 7, 'AGO' => 8, 'SET' => 9, 'OUT' => 10, 'NOV' => 11, 'DEZ' => 12,
    ];

    /**
     * @return array{created:int, updated:int, deleted:int, total:int}
     */
    public function import(Team $team, bool $prune = true): array
    {
        if (! $team->fpb_url) {
            throw new RuntimeException(__('Team ":name" has no FPB link.', ['name' => $team->name]));
        }

        $html = $this->fetch($team->fpb_url);
        $fixtures = $this->parse($html);

        if ($fixtures === []) {
            throw new RuntimeException(__('No games were found on the page (unexpected structure or empty page).'));
        }

        $clubName = $this->detectClub($fixtures);

        $created = 0;
        $updated = 0;
        $seenIds = [];

        foreach ($fixtures as $fixture) {
            $isHome = $fixture['home'] === $clubName;
            $isAway = $fixture['away'] === $clubName;

            if (! $isHome && ! $isAway) {
                continue;
            }

            $seenIds[] = $fixture['id'];

            $hasScore = $fixture['pc'] !== null && $fixture['pf'] !== null;

            $attributes = [
                'opponent' => $isHome ? $fixture['away'] : $fixture['home'],
                'date_time' => $fixture['date_time'],
                'place' => $fixture['loc'],
                'home_away' => $isHome ? HomeAway::Home : HomeAway::Away,
                'competition' => $fixture['comp'],
                'home_points' => $hasScore ? $fixture['pc'] : null,
                'away_points' => $hasScore ? $fixture['pf'] : null,
                'status' => $hasScore ? GameStatus::Played : GameStatus::Scheduled,
            ];

            $game = Game::firstOrNew([
                'team_id' => $team->id,
                'fpb_id' => $fixture['id'],
            ]);

            $exists = $game->exists;

            if (! $exists) {
                $game->fill(Game::defaultAttributes());
            }

            $game->fill($attributes);
            $dirty = $game->isDirty();
            $game->save();

            if (! $exists) {
                $created++;
            } elseif ($dirty) {
                $updated++;
            }
        }

        $deleted = 0;

        if ($prune) {
            $deleted = Game::query()
                ->where('team_id', $team->id)
                ->whereNotNull('fpb_id')
                ->whereNotIn('fpb_id', $seenIds)
                ->delete();
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'deleted' => $deleted,
            'total' => count($seenIds),
        ];
    }

    /**
     * @return array{created:int, updated:int, deleted:int, total:int}
     */
    public function importPlayers(Team $team, bool $prune = true): array
    {
        if (! $team->fpb_url) {
            throw new RuntimeException(__('Team ":name" has no FPB link.', ['name' => $team->name]));
        }

        $html = $this->fetch($team->fpb_url);
        $athletes = $this->parsePlayers($html);

        if ($athletes === []) {
            throw new RuntimeException(__('No roster was found on the page (unexpected structure or no athletes).'));
        }

        $created = 0;
        $updated = 0;
        $seenIds = [];

        foreach ($athletes as $athlete) {
            $seenIds[] = $athlete['fpb_id'];

            $player = Player::firstOrNew([
                'team_id' => $team->id,
                'fpb_id' => $athlete['fpb_id'],
            ]);

            if (! $player->exists) {
                $player->fill([
                    'name' => $athlete['name'],
                    'slug' => $this->uniquePlayerSlug($team, $athlete['name']),
                    'number' => $athlete['number'] !== '' ? (int) $athlete['number'] : null,
                    'position' => $athlete['position'] !== '' ? $athlete['position'] : null,
                    'order' => 0,
                    'enabled' => true,
                    'photo' => $athlete['photo_url'] !== '' ? $this->downloadImage($athlete['photo_url'], 'players') : null,
                ]);
                $player->save();
                $created++;

                continue;
            }

            $player->name = $athlete['name'];
            $player->number = $athlete['number'] !== '' ? (int) $athlete['number'] : null;

            if ($athlete['position'] !== '') {
                $player->position = $athlete['position'];
            }

            if ($player->isDirty()) {
                $player->save();
                $updated++;
            }
        }

        $deleted = 0;

        if ($prune) {
            $stale = Player::query()
                ->where('team_id', $team->id)
                ->whereNotNull('fpb_id')
                ->whereNotIn('fpb_id', $seenIds)
                ->get();

            foreach ($stale as $player) {
                $this->deleteLocalImage($player->photo);
                $player->delete();
                $deleted++;
            }
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'deleted' => $deleted,
            'total' => count($seenIds),
        ];
    }

    /**
     * @return array<int, array{fpb_id: string, name: string, position: string, number: string, photo_url: string}>
     */
    public function parsePlayers(string $html): array
    {
        $segments = explode('<div class="roster__player">', $html);
        array_shift($segments);

        $athletes = [];

        foreach ($segments as $segment) {
            if (! preg_match('#/atletas/(\d+)#', $segment, $idMatch)) {
                continue;
            }

            preg_match('/header__heading">\s*(.*?)\s*<\/h2>/su', $segment, $name);
            preg_match('/header_position">\s*(.*?)\s*<\/div>/su', $segment, $position);
            preg_match('/header_jnumber">\s*(\d*)/su', $segment, $number);
            preg_match('/roster__player__bust"\s+src="([^"]*)"/su', $segment, $photo);

            if (empty($name[1])) {
                continue;
            }

            $athletes[$idMatch[1]] = [
                'fpb_id' => $idMatch[1],
                'name' => $this->clean($name[1]),
                'position' => isset($position[1]) ? $this->clean($position[1]) : '',
                'number' => $number[1] ?? '',
                'photo_url' => $photo[1] ?? '',
            ];
        }

        return array_values($athletes);
    }

    /**
     * @param  array<int, array<string, mixed>>  $fixtures
     */
    public function detectClub(array $fixtures): string
    {
        $counts = [];

        foreach ($fixtures as $fixture) {
            $counts[$fixture['home']] = ($counts[$fixture['home']] ?? 0) + 1;
            $counts[$fixture['away']] = ($counts[$fixture['away']] ?? 0) + 1;
        }

        arsort($counts);

        return (string) array_key_first($counts);
    }

    public function fetch(string $url): string
    {
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36',
            'Accept-Language' => 'pt-PT,pt;q=0.9',
        ])->timeout(30)->retry(2, 500)->get($url);

        if (! $response->successful()) {
            throw new RuntimeException(__('Failed to fetch the FPB page (HTTP :status).', ['status' => $response->status()]));
        }

        return $response->body();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function parse(string $html): array
    {
        preg_match_all('/<h3 class="date">\s*(.*?)\s*<\/h3>/su', $html, $dateMatches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

        $games = [];
        $count = count($dateMatches);

        for ($i = 0; $i < $count; $i++) {
            $offset = $dateMatches[$i][0][1];
            $end = $i + 1 < $count ? $dateMatches[$i + 1][0][1] : strlen($html);
            $block = substr($html, $offset, $end - $offset);

            if (! preg_match('/(\d{1,2})\s+([A-Z]{3})\s+(20\d\d)/u', $dateMatches[$i][1][0], $dateParts)) {
                continue;
            }

            $day = (int) $dateParts[1];
            $month = self::MESES[$dateParts[2]] ?? null;
            $year = (int) $dateParts[3];

            if (! $month) {
                continue;
            }

            $segments = explode('href="/ficha-de-jogo?internalID=', $block);
            array_shift($segments);

            foreach ($segments as $segment) {
                if (! preg_match('/^(\d+)/', $segment, $idMatch)) {
                    continue;
                }

                $id = $idMatch[1];

                preg_match_all('/<span class="fullName">\s*(.*?)\s*<\/span>/su', $segment, $names);

                if (count($names[1]) < 2) {
                    continue;
                }

                $home = $this->clean($names[1][0]);
                $away = $this->clean($names[1][1]);

                preg_match_all('/results_text[^>]*>\s*(\d+)/su', $segment, $scores);
                preg_match('/class="hour[^"]*">\s*<h3>\s*(\d{1,2})H(\d{2})/su', $segment, $hour);
                preg_match('/location-wrapper.*?<b>\s*(.*?)\s*<\/b>/su', $segment, $location);
                preg_match('/class="competition">\s*<span>\s*(.*?)\s*<\/span>/su', $segment, $competition);

                $game = $games[$id] ?? [
                    'id' => $id,
                    'year' => $year,
                    'month' => $month,
                    'day' => $day,
                    'home' => $home,
                    'away' => $away,
                    'loc' => null,
                    'comp' => null,
                    'pc' => null,
                    'pf' => null,
                    'time' => null,
                ];

                if (isset($location[1])) {
                    $game['loc'] = str_replace(' ,', ', ', $this->clean($location[1]));
                }

                if (isset($competition[1])) {
                    $parts = explode('|', $this->clean($competition[1]));
                    $game['comp'] = trim((string) end($parts));
                }

                if (count($scores[1]) >= 2 && $game['pc'] === null) {
                    $game['pc'] = (int) $scores[1][0];
                    $game['pf'] = (int) $scores[1][1];
                }

                if ($hour && ! $game['time']) {
                    $game['time'] = sprintf('%02d:%s', (int) $hour[1], $hour[2]);
                }

                $games[$id] = $game;
            }
        }

        return array_map(function (array $game): array {
            $time = $game['time'] ?? '00:00';
            $game['date_time'] = CarbonImmutable::create(
                $game['year'],
                $game['month'],
                $game['day'],
                (int) substr($time, 0, 2),
                (int) substr($time, 3, 2),
            );

            return $game;
        }, array_values($games));
    }

    protected function uniquePlayerSlug(Team $team, string $name, ?int $ignorePlayerId = null): string
    {
        $base = Player::slugFromName($name);
        $slug = $base;
        $suffix = 2;

        while (Player::query()
            ->where('team_id', $team->id)
            ->where('slug', $slug)
            ->when($ignorePlayerId !== null, fn ($query) => $query->whereKeyNot($ignorePlayerId))
            ->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    protected function downloadImage(string $url, string $directory): ?string
    {
        try {
            $response = Http::timeout(20)->get($url);

            if (! $response->successful() || $response->body() === '') {
                return null;
            }

            $extension = pathinfo((string) parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
            $path = $directory.'/'.Str::random(40).'.'.$extension;

            Storage::disk('public')->put($path, $response->body());

            return $path;
        } catch (Throwable) {
            return null;
        }
    }

    protected function deleteLocalImage(?string $path): void
    {
        if ($path && ! Str::startsWith($path, ['http://', 'https://', '/']) && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    protected function clean(string $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', $value) ?? '');
    }
}
