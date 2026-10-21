<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\WikiPageVersion;
use App\Support\WikiDiff;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WikiPageVersionController extends Controller
{
    /**
     * Every edit of every wiki page (or of one page when tag_id is given),
     * newest first.
     */
    public function index(Request $request): View
    {
        $query = WikiPageVersion::query()->with(['tag', 'updater'])->latest('id');

        $filterTag = null;

        if ($request->filled('tag_id')) {
            $tagId = $request->integer('tag_id');
            $filterTag = Tag::find($tagId);
            $query->where('tag_id', $tagId);
        }

        $updaterName = $request->string('updater')->toString();

        if ($updaterName !== '') {
            $query->whereHas('updater', fn ($q) => $q->where('name', $updaterName));
        }

        return view('wiki.versions', [
            'versions' => $query->paginate(25)->withQueryString(),
            'filterTag' => $filterTag,
            'updaterName' => $updaterName,
        ]);
    }

    /**
     * What a version changed compared with the version before it. A new page
     * has nothing to compare with, so it has no diff.
     */
    public function diff(WikiPageVersion $version): View
    {
        abort_if($version->is_new, 404);

        $previous = $version->previous();

        abort_if($previous === null, 404);

        $version->load(['tag', 'updater']);
        $previous->load('updater');

        return view('wiki.diff', [
            'version' => $version,
            'previous' => $previous,
            'otherNamesSegments' => (string) $previous->other_names !== (string) $version->other_names
                ? WikiDiff::compute((string) $previous->other_names, (string) $version->other_names)
                : null,
            'bodySegments' => WikiDiff::compute($previous->body, $version->body),
        ]);
    }
}