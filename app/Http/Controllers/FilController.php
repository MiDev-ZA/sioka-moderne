<?php

namespace App\Http\Controllers;

use App\Models\Fil;
use Illuminate\Http\Request;

class FilController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => ['nullable', 'exists:categories,id'],
            'content' => ['required', 'string', 'max:280'],
        ]);

        Fil::create([
            'user_id' => auth()->id(),
            'category_id' => $validated['category_id'] ?? null,
            'content' => $validated['content'],
            'is_active' => true,
        ]);

        return back()->with('success', 'Fil en continu ajouté.');
    }

    public function toggle(Fil $fil)
    {
        $fil->update(['is_active' => !$fil->is_active]);

        return back()->with('success', 'Statut mis à jour.');
    }

    public function update(Request $request, Fil $fil)
    {
        $validated = $request->validate([
            'category_id' => ['nullable', 'exists:categories,id'],
            'content' => ['required', 'string', 'max:280'],
        ]);

        $fil->update($validated);

        return back()->with('success', 'Fil en continu modifié.');
    }

    public function destroy(Fil $fil)
    {
        $fil->delete();

        return back()->with('success', 'Fil en continu supprimé.');
    }
}