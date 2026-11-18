<?php

namespace App\Http\Controllers;

use App\Models\Equipe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class EquipeController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('equipe', 'public');
        }

        $equipe = Equipe::create($data);

        return response()->json($equipe->toMember(), 201);
    }

    public function update(Request $request, Equipe $equipe): JsonResponse
    {
        $data = $this->validated($request);

        if (! empty($data['parent_id'])) {
            $parentId = (int) $data['parent_id'];

            if ($parentId === $equipe->id || in_array($parentId, $this->descendantIds($equipe), true)) {
                throw ValidationException::withMessages([
                    'parent_id' => 'Ce supérieur hiérarchique est invalide (boucle dans l\'organigramme).',
                ]);
            }
        }

        if ($request->hasFile('photo')) {
            $this->deletePhoto($equipe);
            $data['photo'] = $request->file('photo')->store('equipe', 'public');
        } elseif ($request->boolean('remove_photo')) {
            $this->deletePhoto($equipe);
            $data['photo'] = null;
        }

        $equipe->update($data);

        return response()->json($equipe->fresh()->toMember());
    }

    public function destroy(Equipe $equipe): Response
    {
        $this->deletePhoto($equipe);
        $equipe->delete(); // les subordonnés passent à parent_id = null (nullOnDelete)

        return response()->noContent();
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'role'      => ['required', 'string', 'max:255'],
            'email'     => ['nullable', 'email', 'max:255'],
            'org'       => ['required', 'boolean'],
            'parent_id' => ['nullable', 'integer', 'exists:equipes,id'],
            'photo'     => ['nullable', 'image', 'max:2048'],
        ]);

        unset($data['photo']); // gérée séparément (fichier)

        return $data;
    }

    private function deletePhoto(Equipe $equipe): void
    {
        if ($equipe->photo) {
            Storage::disk('public')->delete($equipe->photo);
        }
    }

    /**
     * IDs de tous les subordonnés (directs et indirects) d'un membre.
     */
    private function descendantIds(Equipe $equipe): array
    {
        $byParent = Equipe::get(['id', 'parent_id'])->groupBy('parent_id');
        $out = [];
        $stack = [$equipe->id];

        while ($stack) {
            $current = array_pop($stack);
            foreach ($byParent->get($current, []) as $child) {
                $out[] = $child->id;
                $stack[] = $child->id;
            }
        }

        return $out;
    }
}