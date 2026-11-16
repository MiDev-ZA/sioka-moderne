<?php

namespace App\Http\Controllers;

use App\Models\Boite;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BoiteController extends Controller
{
    /** Objets proposés dans le formulaire public (doivent rester identiques à la vue). */
    public const SUBJECTS = ['Suggestion', 'Signalement', 'Question', 'Témoignage', 'Autre'];

    /** Anti-spam : limites et seuils. */
    private const DAILY_LIMIT_PER_IP = 20;      // messages par jour et par IP
    private const MIN_FILL_MS        = 3000;    // temps minimum passé sur le formulaire (ms)
    private const MAX_LINKS          = 2;       // liens autorisés dans le message

    /**
     * Liste des messages reçus (dashboard).
     */
    public function index(Request $request): View
    {
        $statut = in_array($request->query('statut'), ['non-lus', 'lus'], true)
            ? $request->query('statut')
            : null;

        $query = Boite::query()->latest();

        if ($statut === 'non-lus') {
            $query->where('is_read', false);
        } elseif ($statut === 'lus') {
            $query->where('is_read', true);
        }

        $messages = $query->paginate(15)->withQueryString();
        $total    = Boite::count();
        $nonLus   = Boite::where('is_read', false)->count();

        return view('dashboard.messages-citoyens', compact('messages', 'total', 'nonLus', 'statut'));
    }

    /**
     * Réception d'un message depuis le formulaire public (AJAX).
     */
    public function send(Request $request): JsonResponse
    {
        $okMessage = 'Il a bien été transmis à la rédaction de SIOKA.';

        // 1) Honeypot : un humain ne remplit jamais ce champ caché.
        //    Faux "OK" pour ne rien révéler au bot, rien n'est enregistré.
        if ($request->filled('website')) {
            return response()->json(['message' => $okMessage]);
        }

        // 2) Preuve que le JavaScript du formulaire a tourné (temps passé sur la page, en ms).
        //    Un envoi direct sans ce champ n'est pas fait depuis le vrai formulaire.
        $elapsed = $request->input('elapsed');
        if (! is_numeric($elapsed)) {
            return response()->json(['message' => $okMessage]);
        }

        // 3) Limite quotidienne par IP (en plus du throttle de la route : 5/minute).
        $dailyKey = 'boite-day:' . $request->ip();
        if (RateLimiter::tooManyAttempts($dailyKey, self::DAILY_LIMIT_PER_IP)) {
            return response()->json([
                'message' => 'Vous avez atteint la limite de messages pour aujourd\'hui. Réessayez demain.',
                'daily'   => true,
            ], 429);
        }

        // 4) Validation
        $noLink   = 'not_regex:/(https?:\/\/|www\.)/i';
        $noCtrl   = 'not_regex:/[\x00-\x08\x0B\x0C\x0E-\x1F]/';

        $data = $request->validate([
            'subject' => ['required', 'string', Rule::in(self::SUBJECTS)],
            'message' => ['required', 'string', 'min:10', 'max:2000', $noCtrl],
            'name'    => ['nullable', 'string', 'max:120', $noLink, $noCtrl],
            'contact' => ['nullable', 'string', 'max:120', $noLink, $noCtrl],
        ], [
            'subject.required'  => 'Veuillez choisir un objet.',
            'subject.in'        => 'Objet invalide.',
            'message.required'  => 'Veuillez écrire votre message.',
            'message.min'       => 'Votre message est trop court.',
            'message.max'       => 'Votre message est trop long.',
            'message.not_regex' => 'Votre message contient des caractères non autorisés.',
            'name.max'          => 'Le nom est trop long.',
            'name.not_regex'    => 'Le nom ne peut pas contenir de lien.',
            'contact.max'       => 'Le contact est trop long.',
            'contact.not_regex' => 'Le contact ne peut pas contenir de lien.',
        ]);

        // 5) Envoi trop rapide pour un humain
        if ((int) $elapsed < self::MIN_FILL_MS) {
            throw ValidationException::withMessages([
                'message' => 'Veuillez patienter quelques secondes avant d\'envoyer.',
            ]);
        }

        // 6) Trop de liens dans le message (spam typique)
        if (preg_match_all('~(?:https?://|www\.)~i', $data['message']) > self::MAX_LINKS) {
            throw ValidationException::withMessages([
                'message' => 'Votre message contient trop de liens.',
            ]);
        }

        // 7) Doublon : même message reçu dans les dernières 24 h → faux "OK", pas de nouvel enregistrement
        $doublon = Boite::where('message', $data['message'])
            ->where('created_at', '>=', now()->subDay())
            ->exists();

        if ($doublon) {
            return response()->json(['message' => $okMessage]);
        }

        Boite::create($data);
        RateLimiter::hit($dailyKey, 86400);

        return response()->json(['message' => $okMessage], 201);
    }

    /**
     * Marque un message comme lu / non lu (dashboard).
     */
    public function toggle(Request $request, Boite $boite): JsonResponse|RedirectResponse
    {
        $boite->update(['is_read' => ! $boite->is_read]);

        if ($request->expectsJson()) {
            return response()->json(['is_read' => $boite->is_read]);
        }

        return back();
    }

    /**
     * Supprime un message (dashboard).
     */
    public function destroy(Request $request, Boite $boite): JsonResponse|RedirectResponse
    {
        $boite->delete();

        if ($request->expectsJson()) {
            return response()->json(['deleted' => true]);
        }

        return back()->with('success', 'Message supprimé.');
    }
}