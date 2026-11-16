<?php

namespace App\Http\Controllers;

use Google\Service\YouTube;
use App\Models\YoutubeToken;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\GoogleProvider;
use Laravel\Socialite\Two\InvalidStateException;

class YoutubeAuthController extends Controller
{
    protected function provider(): AbstractProvider
    {
        return Socialite::buildProvider(GoogleProvider::class, [
            'client_id'     => config('services.youtube.client_id'),
            'client_secret' => config('services.youtube.client_secret'),
            'redirect'      => config('services.youtube.redirect_uri'),
        ])->enablePKCE();
    }

    public function redirect()
    {
        return $this->provider()
            ->scopes([YouTube::YOUTUBE])
            ->with([
                'access_type'            => 'offline',
                'prompt'                 => 'consent',
                'include_granted_scopes' => 'true',
            ])
            ->redirect();
    }

    public function callback(Request $request)
    {
        if ($request->has('error')) {
            return redirect()
                ->route('webtv')
                ->with('error', 'Connexion YouTube annulée ou refusée.');
        }

        try {
            $google = $this->provider()->user();
        } catch (InvalidStateException $e) {
            // État (state) ou vérificateur PKCE introuvable en session : le callback a été
            // rejoué (rechargement de page, double clic) ou la session a été perdue.
            Log::warning('YouTube OAuth callback : état invalide', ['message' => $e->getMessage()]);

            if (YoutubeToken::query()->exists()) {
                return redirect()
                    ->route('webtv')
                    ->with('success', 'YouTube est déjà connecté.');
            }

            return redirect()
                ->route('webtv')
                ->with('error', 'La session de connexion a expiré. Relancez la connexion YouTube.');
        } catch (\Throwable $e) {
            // Le détail complet (y compris la réponse de Google) va dans storage/logs/laravel.log.
            Log::error('YouTube OAuth callback error', [
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
                'response'  => ($e instanceof RequestException && $e->hasResponse())
                    ? (string) $e->getResponse()->getBody()
                    : null,
            ]);

            // Le message technique n'est montré à l'écran qu'en mode debug.
            $message = config('app.debug')
                ? 'Erreur de connexion YouTube : ' . $e->getMessage()
                : 'Erreur de connexion YouTube. Consultez les journaux du serveur.';

            return redirect()->route('webtv')->with('error', $message);
        }

        // Google ne renvoie pas toujours de refresh_token : on garde alors l'ancien,
        // sinon la connexion cesserait de fonctionner à l'expiration du jeton d'accès.
        $refreshToken = $google->refreshToken ?: YoutubeToken::latest()->first()?->refresh_token;

        YoutubeToken::query()->delete();
        YoutubeToken::create([
            'access_token'  => $google->token,
            'refresh_token' => $refreshToken,
            'expires_at'    => now()->addSeconds((int) ($google->expiresIn ?? 3600)),
        ]);

        return redirect()->route('webtv')->with('success', 'Connexion YouTube réussie !');
    }
}