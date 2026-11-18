<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdController extends Controller
{
    public function index()
    {
        $ads = Ad::latest()->get();
        return view('dashboard.ads', compact('ads'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, true);
        $data['image'] = $request->file('image')->store('ads', 'public');
        $data['is_active'] = $request->boolean('is_active');

        Ad::create($data);
        return back()->with('success', 'Publicité ajoutée.');
    }

    public function update(Request $request, Ad $ad)
    {
        $data = $this->validated($request, false);

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($ad->image);
            $data['image'] = $request->file('image')->store('ads', 'public');
        }
        $data['is_active'] = $request->boolean('is_active');

        $ad->update($data);
        return back()->with('success', 'Publicité mise à jour.');
    }

    public function destroy(Ad $ad)
    {
        Storage::disk('public')->delete($ad->image);
        $ad->delete();
        return back()->with('success', 'Publicité supprimée.');
    }

    // Route publique : compte le clic puis redirige vers l'annonceur
    public function click(Ad $ad)
    {
        $ad->increment('clicks');
        return redirect()->away($ad->url);
    }

    private function validated(Request $request, bool $imageRequired): array
    {
        return $request->validate([
            'name'       => 'required|string|max:120',
            'advertiser' => 'nullable|string|max:120',
            'slot'       => 'required|string|in:top-ticker,home-after-news,home-after-podcasts',
            'image'      => ($imageRequired ? 'required' : 'nullable') . '|image|max:2048',
            'image_alt'  => 'nullable|string|max:160',
            'url'        => 'required|url:http,https|max:500',
            'starts_at'  => 'nullable|date',
            'ends_at'    => 'nullable|date|after_or_equal:starts_at',
        ]);
    }
}