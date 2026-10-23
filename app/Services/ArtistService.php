<?php

namespace App\Services;

use App\Models\Artist;
use App\Models\ArtistVersion;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Creates and edits artists and keeps their history.
 */
class ArtistService
{
    private const MAX_URLS = 100;

    private const MAX_URL_LENGTH = 2000;

    /**
     * The artist of a tag that is in the artist category. An artist that does
     * not exist yet is created, with "New" as its first version.
     */
    public function ensureForTag(Tag $tag): Artist
    {
        $artist = Artist::query()->where('tag_id', $tag->id)->first();

        if ($artist !== null) {
            return $artist;
        }

        return DB::transaction(function () use ($tag): Artist {
            $artist = Artist::create([
                'tag_id' => $tag->id,
                'other_names' => $tag->other_names,
                'is_deleted' => false,
                'version' => 0,
            ]);

            $artist->setRelation('tag', $tag);
            $this->recordVersion($artist, ['New'], true);

            return $artist;
        });
    }

    /**
     * @param  array{other_names?: ?string, group_name?: ?string, urls?: array<string, bool>}  $data
     */
    public function create(string $name, array $data): Artist
    {
        $existing = Tag::query()->where('name', $name)->first();

        if ($existing !== null) {
            if ($existing->category === 'artist' && Artist::query()->where('tag_id', $existing->id)->exists()) {
                throw ValidationException::withMessages([
                    'name' => 'An artist with this name already exists.',
                ]);
            }

            if (! in_array($existing->category, ['general', 'artist'], true)) {
                throw ValidationException::withMessages([
                    'name' => 'This name is already used by a '.$existing->category.' tag, so it cannot become an artist.',
                ]);
            }
        }

        return DB::transaction(function () use ($name, $data, $existing): Artist {
            // The artist is made below, with its data, as one "New" version.
            $tag = Tag::withoutArtistSync(function () use ($name, $existing): Tag {
                if ($existing === null) {
                    return Tag::create(['name' => $name, 'category' => 'artist']);
                }

                if ($existing->category !== 'artist') {
                    $existing->update(['category' => 'artist']);
                }

                return $existing;
            });

            $artist = Artist::create([
                'tag_id' => $tag->id,
                'other_names' => $data['other_names'] ?? null,
                'group_name' => $data['group_name'] ?? null,
                'is_deleted' => false,
                'version' => 0,
            ]);

            $artist->setRelation('tag', $tag);
            $this->syncUrls($artist, $data['urls'] ?? []);
            $this->recordVersion($artist, ['New'], true);

            return $artist;
        });
    }

    /**
     * Applies the edit and records a version when something changed.
     *
     * @param  array{name: string, other_names: ?string, group_name: ?string, is_deleted: bool, urls: array<string, bool>}  $data
     * @return list<string> What changed; empty when the edit changed nothing.
     */
    public function update(Artist $artist, array $data): array
    {
        $artist->loadMissing('tag');
        $artist->load('urls');

        return DB::transaction(function () use ($artist, $data): array {
            $changes = [];

            if ($data['name'] !== $artist->tag->name) {
                $artist->tag->update(['name' => $data['name']]);
                $changes[] = 'Renamed';
            }

            if ((string) $data['other_names'] !== (string) $artist->other_names) {
                $changes[] = 'OtherNames';
            }

            if ((string) $data['group_name'] !== (string) $artist->group_name) {
                $changes[] = 'GroupName';
            }

            if ($data['is_deleted'] !== $artist->is_deleted) {
                $changes[] = $data['is_deleted'] ? 'Deleted' : 'Undeleted';
            }

            $currentUrls = $artist->urls->mapWithKeys(fn ($url) => [$url->url => $url->is_active])->all();
            $newUrls = $data['urls'];
            ksort($currentUrls);
            ksort($newUrls);

            if ($currentUrls !== $newUrls) {
                $changes[] = 'URLs';
                $this->syncUrls($artist, $data['urls']);
            }

            if ($changes === []) {
                return [];
            }

            $artist->fill([
                'other_names' => $data['other_names'],
                'group_name' => $data['group_name'],
                'is_deleted' => $data['is_deleted'],
            ]);

            $this->recordVersion($artist, $changes);

            return $changes;
        });
    }

    /**
     * Reads the URLs of the form. One per line, "-" in front of a URL marks it
     * as no longer active.
     *
     * @return array<string, bool> {url: is_active}
     *
     * @throws ValidationException
     */
    public function parseUrls(?string $text): array
    {
        $urls = [];

        foreach (preg_split('/\R/', (string) $text) ?: [] as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            $active = true;

            if (str_starts_with($line, '-')) {
                $active = false;
                $line = trim(substr($line, 1));
            }

            if (! preg_match('#^https?://[^\s/$.?\#][^\s]*$#iu', $line) || mb_strlen($line) > self::MAX_URL_LENGTH) {
                throw ValidationException::withMessages([
                    'urls' => 'Invalid URL: "'.mb_strimwidth($line, 0, 80, '...').'". Each URL must start with http:// or https:// and have no spaces.',
                ]);
            }

            $urls[$line] = $active;
        }

        if (count($urls) > self::MAX_URLS) {
            throw ValidationException::withMessages([
                'urls' => 'An artist can have at most '.self::MAX_URLS.' URLs.',
            ]);
        }

        return $urls;
    }

    /**
     * Space separated names, with the spaces tidied. Null when empty.
     */
    public function cleanOtherNames(?string $value): ?string
    {
        $names = trim((string) preg_replace('/\s+/', ' ', (string) $value));

        return $names === '' ? null : $names;
    }

    /**
     * The group name is written like a tag name.
     */
    public function cleanGroupName(?string $value): ?string
    {
        $name = Tag::normalizeName((string) $value);

        return $name === '' ? null : $name;
    }

    /**
     * @param  array<string, bool>  $urls
     */
    private function syncUrls(Artist $artist, array $urls): void
    {
        $existing = $artist->urls()->get()->keyBy('url');

        foreach ($existing as $url => $row) {
            if (! array_key_exists($url, $urls)) {
                $row->delete();
            }
        }

        foreach ($urls as $url => $active) {
            $row = $existing->get($url);

            if ($row === null) {
                $artist->urls()->create(['url' => $url, 'is_active' => $active]);
            } elseif ($row->is_active !== $active) {
                $row->update(['is_active' => $active]);
            }
        }

        $artist->load('urls');
    }

    /**
     * @param  list<string>  $changes
     */
    private function recordVersion(Artist $artist, array $changes, bool $isNew = false): ArtistVersion
    {
        $artist->loadMissing('tag');
        $artist->load('urls');

        $version = (int) $artist->version + 1;
        $updaterId = auth()->check() ? (int) auth()->id() : null;

        $record = $artist->versions()->create([
            'updater_id' => $updaterId,
            'version' => $version,
            'name' => $artist->tag->name,
            'other_names' => $artist->other_names,
            'group_name' => $artist->group_name,
            'urls' => $artist->urlsText() ?: null,
            'is_deleted' => $artist->is_deleted,
            'is_new' => $isNew,
            'change_summary' => implode(',', $changes),
        ]);

        $artist->forceFill(['version' => $version, 'updater_id' => $updaterId])->save();

        return $record;
    }
}
