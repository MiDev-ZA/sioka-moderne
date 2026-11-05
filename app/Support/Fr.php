<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

class Fr
{
    public static function compact($n): string
    {
        $n = (int) ($n ?? 0);
        $f = fn ($v) => Number::format($v, maxPrecision: 1, locale: 'fr');

        return match (true) {
            $n >= 1_000_000_000 => $f($n / 1_000_000_000) . ' Md',
            $n >= 1_000_000     => $f($n / 1_000_000) . ' M',
            $n >= 1_000         => $f($n / 1_000) . ' k',
            default             => (string) $n,
        };
    }

    public static function views($n): string
    {
        $n = (int) ($n ?? 0);

        return self::compact($n) . ' ' . ($n > 1 ? 'vues' : 'vue');
    }

    public static function duration($d): string
    {
        $p = array_map('intval', explode(':', (string) $d));

        if (count($p) >= 3) {
            [$h, $m, $s] = array_slice($p, -3);
        } else {
            $h = 0;
            [$m, $s] = array_pad($p, -2, 0);
        }

        $out = trim(
            ($h ? $h . ' h ' : '') .
            ($m || $h ? $m . ' min ' : '') .
            ($s ? $s . ' s' : '')
        );

        return $out !== '' ? $out : '0 s';
    }

    public static function date($date, string $format = 'd F Y'): string
    {
        return Carbon::parse($date)->locale('fr')->translatedFormat($format);
    }

    public static function category(?string $c): string
    {
        $labels = [
            'actualite'     => 'Actualité',
            'actualites'    => 'Actualités',
            'economie'      => 'Économie',
            'societe'       => 'Société',
            'sante'         => 'Santé',
            'education'     => 'Éducation',
            'environnement' => 'Environnement',
        ];

        $c = (string) $c;

        return $labels[Str::slug($c)] ?? Str::ucfirst($c);
    }
}