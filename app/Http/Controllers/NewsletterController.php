<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NewsletterController extends Controller
{
    // Inscription publique (AJAX)
    public function subscribe(Request $request): JsonResponse
    {
        // Honeypot : un bot remplit ce champ caché, on feint le succès sans rien enregistrer
        if ($request->filled('website')) {
            return $this->ok();
        }

        // Normalisation : texte uniquement, sans espaces autour, en minuscules
        $email = $request->input('email');
        $request->merge([
            'email' => is_string($email) ? Str::lower(trim($email)) : null,
        ]);

        $data = $request->validate([
            'email' => [
                'required',
                'string',
                'max:254',
                'email:rfc',
                // Liste blanche : seuls ces caractères sont acceptés (aucun < > " ' ` \ ni espace)
                'regex:/^[a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,}$/',
            ],
        ], [
            'email.required' => 'Veuillez saisir votre adresse e-mail.',
            'email.string'   => 'Adresse e-mail invalide.',
            'email.email'    => 'Adresse e-mail invalide.',
            'email.regex'    => 'Adresse e-mail invalide.',
            'email.max'      => 'Adresse e-mail trop longue.',
        ]);

        try {
            // Requête paramétrée par Eloquent : aucune concaténation SQL
            NewsletterSubscriber::firstOrCreate(
                ['email' => $data['email']],
                ['token' => Str::random(64)]
            );
        } catch (QueryException $e) {
            // 23000 (MySQL/SQLite) et 23505 (PostgreSQL) = doublon lors de deux envois simultanés : sans gravité
            if (! in_array((string) $e->getCode(), ['23000', '23505'], true)) {
                report($e); // détail dans storage/logs, jamais envoyé au visiteur

                return $this->json(['message' => 'Une erreur est survenue. Réessayez plus tard.'], 500);
            }
        }

        // Même réponse que l'adresse existe déjà ou non (ne révèle pas qui est inscrit)
        return $this->ok();
    }

    // Suppression d'un abonné (dashboard, protégée par auth + role:editor dans web.php)
    public function destroy(NewsletterSubscriber $subscriber): JsonResponse
    {
        $subscriber->delete();

        return $this->json(['message' => 'Abonné supprimé.']);
    }

    private function ok(): JsonResponse
    {
        return $this->json(['message' => 'Merci ! Vous êtes bien inscrit(e).']);
    }

    // Réponses JSON non mises en cache et non interprétables comme autre chose que du JSON
    private function json(array $payload, int $status = 200): JsonResponse
    {
        return response()
            ->json($payload, $status)
            ->header('Cache-Control', 'no-store')
            ->header('X-Content-Type-Options', 'nosniff');
    }
}