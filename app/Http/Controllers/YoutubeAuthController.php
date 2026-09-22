<?php

namespace App\Http\Controllers;

use Google\Client;
use Google\Service\YouTube;
use App\Models\YoutubeToken;
use Illuminate\Http\Request;

class YoutubeAuthController extends Controller
{
    protected function client(): Client
    {
        $client = new Client();
        $client->setClientId(config('services.youtube.client_id'));
        $client->setClientSecret(config('services.youtube.client_secret'));
        $client->setRedirectUri(config('services.youtube.redirect_uri'));
        $client->addScope(YouTube::YOUTUBE);
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        return $client;
    }

    public function redirect()
    {
        return redirect($this->client()->createAuthUrl());
    }

    public function callback(Request $request)
    {
        $client = $this->client();
        $token = $client->fetchAccessTokenWithAuthCode($request->get('code'));

        if (isset($token['error'])) {
            \Illuminate\Support\Facades\Log::error('YouTube OAuth callback error', $token);

            return redirect()
                ->route('webtv')
                ->with('error', 'Erreur de connexion YouTube : ' . ($token['error_description'] ?? $token['error']));
        }

        YoutubeToken::query()->delete();
        YoutubeToken::create([
            'access_token'  => $token['access_token'],
            'refresh_token' => $token['refresh_token'] ?? null,
            'expires_at'    => now()->addSeconds($token['expires_in']),
        ]);

        return redirect()->route('webtv')->with('success', 'Connexion YouTube réussie !');
    }
}