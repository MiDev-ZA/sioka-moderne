<?php

namespace App\Http\Controllers;

use App\Models\Programme;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProgrammeController extends Controller
{
    private const JOURS = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];

    /**
     * Page du planning : liste tous les programmes (la vue les groupe par jour).
     */
    public function index(): View
    {
        $programmes = Programme::orderBy('start')->get();

        return view('dashboard.emissions', compact('programmes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules(), $this->messages());

        Programme::create($data);

        return redirect()->route('emissions')->with('success', 'Programme ajouté au planning.');
    }

    public function update(Request $request, Programme $programme): RedirectResponse
    {
        $data = $request->validate($this->rules(), $this->messages());

        $programme->update($data);

        return redirect()->route('emissions')->with('success', 'Programme mis à jour.');
    }

    public function destroy(Programme $programme): RedirectResponse
    {
        $programme->delete();

        return redirect()->route('emissions')->with('success', 'Programme supprimé.');
    }

    private function rules(): array
    {
        return [
            'day'         => ['required', Rule::in(self::JOURS)],
            'start'       => ['required', 'date_format:H:i'],
            'end'         => ['required', 'date_format:H:i', 'after:start'],
            'title'       => ['required', 'string', 'max:180'],
            'description' => ['required', 'string', 'max:200'],
        ];
    }

    private function messages(): array
    {
        return [
            'day.required'         => 'Le jour de diffusion est obligatoire.',
            'day.in'               => 'Le jour sélectionné est invalide.',
            'start.required'       => "L'heure de début est obligatoire.",
            'start.date_format'    => "L'heure de début est invalide.",
            'end.required'         => "L'heure de fin est obligatoire.",
            'end.date_format'      => "L'heure de fin est invalide.",
            'end.after'            => "L'heure de fin doit être après l'heure de début.",
            'title.required'       => "Le titre de l'émission est obligatoire.",
            'title.max'            => 'Le titre ne doit pas dépasser 180 caractères.',
            'description.required' => 'La description est obligatoire.',
            'description.max'      => 'La description ne doit pas dépasser 200 caractères.',
        ];
    }
}