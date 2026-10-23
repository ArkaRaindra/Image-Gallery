<?php

namespace App\Http\Controllers;

use App\Models\ArtistVersion;
use App\Support\ArtistDiff;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ArtistVersionController extends Controller
{
    private const ORDERS = ['id', 'name'];

    /**
     * Every edit of every artist, newest first. Each edit is compared with the
     * version before it ("previous") or with the artist as it is now
     * ("current").
     */
    public function index(Request $request): View
    {
        $order = $request->query('order');

        $filters = [
            'updater' => trim($request->string('updater')->toString()),
            'artist' => trim($request->string('artist')->toString()),
            'order' => in_array($order, self::ORDERS, true) ? $order : 'id',
        ];

        $compare = $request->query('compare') === 'current' ? 'current' : 'previous';

        $query = ArtistVersion::query()->with(['artist.tag', 'updater']);

        if ($filters['updater'] !== '') {
            $query->whereHas('updater', fn ($q) => $q->where('name', $filters['updater']));
        }

        if ($filters['artist'] !== '') {
            $query->where('name', 'like', '%'.str_replace(' ', '_', strtolower($filters['artist'])).'%');
        }

        if ($filters['order'] === 'name') {
            $query->orderBy('name')->orderByDesc('id');
        } else {
            $query->orderByDesc('id');
        }

        $versions = $query->paginate(25)->withQueryString();

        $bases = $compare === 'current'
            ? $this->currentVersions($versions->getCollection())
            : $this->previousVersions($versions->getCollection());

        $diffs = [];

        foreach ($versions as $version) {
            $key = $compare === 'current' ? $version->artist_id : $version->artist_id.':'.($version->version - 1);
            $diffs[$version->id] = ArtistDiff::compare($bases->get($key), $version);
        }

        return view('artists.versions', [
            'versions' => $versions,
            'diffs' => $diffs,
            'filters' => $filters,
            'compare' => $compare,
        ]);
    }

    /**
     * The version before each given version, keyed "artist_id:version".
     *
     * @param  Collection<int, ArtistVersion>  $versions
     * @return Collection<string, ArtistVersion>
     */
    private function previousVersions($versions)
    {
        $wanted = $versions->filter(fn (ArtistVersion $v) => $v->version > 1);

        if ($wanted->isEmpty()) {
            return collect();
        }

        return ArtistVersion::query()
            ->where(function ($query) use ($wanted): void {
                foreach ($wanted as $version) {
                    $query->orWhere(fn ($q) => $q
                        ->where('artist_id', $version->artist_id)
                        ->where('version', $version->version - 1));
                }
            })
            ->get()
            ->keyBy(fn (ArtistVersion $v) => $v->artist_id.':'.$v->version);
    }

    /**
     * The newest version of each artist of the given versions, keyed by artist id.
     *
     * @param  Collection<int, ArtistVersion>  $versions
     * @return Collection<int, ArtistVersion>
     */
    private function currentVersions($versions)
    {
        $artistIds = $versions->pluck('artist_id')->unique()->values();

        if ($artistIds->isEmpty()) {
            return collect();
        }

        return ArtistVersion::query()
            ->whereIn('id', ArtistVersion::query()
                ->selectRaw('max(id)')
                ->whereIn('artist_id', $artistIds)
                ->groupBy('artist_id'))
            ->get()
            ->keyBy('artist_id');
    }
}
