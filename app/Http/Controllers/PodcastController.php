<?php

namespace App\Http\Controllers;

use App\Models\Podcast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PodcastController extends Controller
{
    /**
     * Enregistre un nouveau podcast.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'          => ['required', 'string', 'max:180'],
            'slug'           => ['nullable', 'string', 'max:200'],
            'description'    => ['required', 'string', 'max:200'],
            'soundcloud_url' => ['required', 'url', 'max:500', 'regex:/soundcloud\.com/i'],
            'image'          => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'image_caption'  => ['nullable', 'string', 'max:255'],
            'image_alt'      => ['nullable', 'string', 'max:255'],
            'is_published'   => ['nullable', 'boolean'],
        ], [
            'soundcloud_url.regex' => "Le lien doit être une URL SoundCloud valide.",
        ]);

        $validated['slug'] = Podcast::generateUniqueSlug($validated['title'], $validated['slug'] ?? null);
        $validated['is_published'] = $request->boolean('is_published');
        $validated['user_id'] = auth()->id();

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('podcasts', 'public');
        }

        Podcast::create($validated);

        return back()->with('success', $validated['is_published']
            ? 'Podcast publié avec succès.'
            : 'Podcast enregistré comme brouillon.');
    }

    /**
     * Met à jour un podcast existant.
     */
    public function update(Request $request, Podcast $podcast)
    {
        $validated = $request->validate([
            'title'          => ['required', 'string', 'max:180'],
            'slug'           => ['nullable', 'string', 'max:200'],
            'description'    => ['required', 'string', 'max:200'],
            'soundcloud_url' => ['required', 'url', 'max:500', 'regex:/soundcloud\.com/i'],
            'image'          => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'image_caption'  => ['nullable', 'string', 'max:255'],
            'image_alt'      => ['nullable', 'string', 'max:255'],
            'is_published'   => ['nullable', 'boolean'],
        ], [
            'soundcloud_url.regex' => "Le lien doit être une URL SoundCloud valide.",
        ]);

        $validated['slug'] = Podcast::generateUniqueSlug($validated['title'], $validated['slug'] ?? null, $podcast->id);
        $validated['is_published'] = $request->boolean('is_published');

        if ($request->hasFile('image')) {
            if (!empty($podcast->image)) {
                Storage::disk('public')->delete($podcast->image);
            }
            $validated['image'] = $request->file('image')->store('podcasts', 'public');
        }

        $podcast->update($validated);

        return back()->with('success', $validated['is_published']
            ? 'Podcast mis à jour et publié.'
            : 'Podcast mis à jour (brouillon).');
    }

    /**
     * Supprime un podcast.
     */
    public function destroy(Podcast $podcast)
    {
        if (!empty($podcast->image)) {
            Storage::disk('public')->delete($podcast->image);
        }

        $podcast->delete();

        return back()->with('success', 'Podcast supprimé.');
    }
}