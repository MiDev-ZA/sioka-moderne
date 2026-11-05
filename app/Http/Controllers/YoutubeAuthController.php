<?php

namespace App\Http\Controllers;

use Google\Service\YouTube;
use App\Models\YoutubeToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\GoogleProvider;

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
        } catch (\Throwable $e) {
            Log::error('YouTube OAuth callback error', ['message' => $e->getMessage()]);

            return redirect()
                ->route('webtv')
                ->with('error', 'Erreur de connexion YouTube : ' . $e->getMessage());
        }

        YoutubeToken::query()->delete();
        YoutubeToken::create([
            'access_token'  => $google->token,
            'refresh_token' => $google->refreshToken,
            'expires_at'    => now()->addSeconds($google->expiresIn),
        ]);

        return redirect()->route('webtv')->with('success', 'Connexion YouTube réussie !');
    }
}