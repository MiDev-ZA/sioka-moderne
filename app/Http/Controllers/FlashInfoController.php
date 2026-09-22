<?php

namespace App\Http\Controllers;

use App\Models\FlashInfo;
use Illuminate\Http\Request;

class FlashInfoController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'content' => ['required', 'string', 'max:280'],
        ]);

        FlashInfo::create([
            'user_id' => auth()->id(),
            'category_id' => $validated['category_id'],
            'content' => $validated['content'],
            'is_active' => true,
        ]);

        return back()->with('success', 'Flash info ajoutée.');
    }

    public function toggle(FlashInfo $flashInfo)
    {
        $flashInfo->update(['is_active' => !$flashInfo->is_active]);

        return back()->with('success', 'Statut mis à jour.');
    }

    public function update(Request $request, FlashInfo $flashInfo)
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'content' => ['required', 'string', 'max:280'],
        ]);

        $flashInfo->update($validated);

        return back()->with('success', 'Flash info modifiée.');
    }

    public function destroy(FlashInfo $flashInfo)
    {
        $flashInfo->delete();

        return back()->with('success', 'Flash info supprimée.');
    }
}