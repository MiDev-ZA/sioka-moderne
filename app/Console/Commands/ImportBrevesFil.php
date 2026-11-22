<?php

namespace App\Console\Commands;

use App\Models\Fil;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportBrevesFil extends Command
{
    protected $signature = 'wp:import-breves
        {--user= : ID de l\'utilisateur Laravel de repli}
        {--champ=contenu : Texte du fil : "contenu" (corps de l\'article) ou "titre"}
        {--limit= : N\'importer que les N brèves les plus récentes}
        {--inactive : Crée les fils désactivés (is_active = false)}
        {--dry-run : Simule sans rien écrire}';

    protected $description = 'Ajoute les articles WordPress de la catégorie brève dans le fil en continu';

    /**
     * Correspondance auteur WordPress => utilisateur Laravel.
     * 16 = Xavino  => 7
     * 6  = Tolotra => à ajouter quand son compte Laravel existe
     */
    private const AUTHOR_MAP = [
        16 => 7,
    ];

    public function handle(): int
    {
        $userId = (int) $this->option('user');
        $champ = (string) $this->option('champ');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;
        $active = !$this->option('inactive');
        $dry = (bool) $this->option('dry-run');

        if (!$userId || !DB::table('users')->where('id', $userId)->exists()) {
            $this->error("L'option --user est obligatoire et doit être un utilisateur existant.");
            return self::FAILURE;
        }

        if (!in_array($champ, ['contenu', 'titre'], true)) {
            $this->error('--champ doit valoir "contenu" ou "titre".');
            return self::FAILURE;
        }

        foreach (self::AUTHOR_MAP as $wpId => $laravelId) {
            if (!DB::table('users')->where('id', $laravelId)->exists()) {
                $this->error("AUTHOR_MAP : l'utilisateur Laravel {$laravelId} (auteur WP {$wpId}) n'existe pas.");
                return self::FAILURE;
            }
        }

        $query = DB::connection('wordpress')->table('posts as p')
            ->join('term_relationships as tr', 'tr.object_id', '=', 'p.ID')
            ->join('term_taxonomy as tt', 'tt.term_taxonomy_id', '=', 'tr.term_taxonomy_id')
            ->join('terms as t', 't.term_id', '=', 'tt.term_id')
            ->where('p.post_type', 'post')
            ->where('p.post_status', 'publish')
            ->where('tt.taxonomy', 'category')
            ->where(function ($q) {
                $q->where('t.name', 'like', '%br_ve%')
                  ->orWhere('t.slug', 'like', '%breve%');
            })
            ->select('p.ID', 'p.post_title', 'p.post_content', 'p.post_author', 'p.post_date')
            ->distinct()
            ->orderBy('p.post_date', 'desc');

        if ($limit) {
            $query->limit($limit);
        }

        // Ordre chronologique : les plus anciennes d'abord, pour des IDs cohérents
        $breves = $query->get()->reverse()->values();

        $imported = 0;
        $skipped = 0;

        foreach ($breves as $b) {
            $source = $champ === 'titre' ? $b->post_title : $b->post_content;
            $text = $this->clean($source);

            if ($text === '') {
                $text = $this->clean($b->post_title);
            }
            if ($text === '') {
                $skipped++;
                continue;
            }

            if (mb_strlen($text) > 280) {
                $text = rtrim(mb_substr($text, 0, 279)) . '…';
            }

            if (Fil::where('content', $text)->where('created_at', $b->post_date)->exists()) {
                $skipped++;
                continue;
            }

            if (!$dry) {
                $fil = new Fil([
                    'user_id' => self::AUTHOR_MAP[$b->post_author] ?? $userId,
                    'category_id' => null,
                    'content' => $text,
                    'is_active' => $active,
                ]);
                $fil->created_at = $b->post_date;
                $fil->updated_at = $b->post_date;
                $fil->save();
            }

            $imported++;
        }

        $this->info(($dry ? '[SIMULATION] ' : '') . "Brèves trouvées : {$breves->count()} | Ajoutées au fil : {$imported} | Ignorées : {$skipped}");

        return self::SUCCESS;
    }

    private function clean(?string $html): string
    {
        $html = preg_replace('/<!--\/?wp:.*?-->/s', '', (string) $html);
        $html = preg_replace('/\[\/?caption[^\]]*\]/', '', $html);
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $text));
    }
}