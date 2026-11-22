<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportWordpress extends Command
{
    protected $signature = 'wp:import
        {--user= : ID de l\'utilisateur Laravel de repli (auteurs sans correspondance)}
        {--category= : ID de la catégorie par défaut}
        {--uploads= : Chemin du dossier wp-content/uploads}
        {--dry-run : Simule sans rien écrire}';

    protected $description = 'Importe les articles WordPress (sioka_wp) dans la table articles';

    /**
     * Correspondance auteur WordPress => utilisateur Laravel.
     * 16 = Xavino  => 7
     * 6  = Tolotra => à ajouter quand son compte Laravel existe (ex. 6 => 8)
     */
    private const AUTHOR_MAP = [
        16 => 7,
    ];

    public function handle(): int
    {
        $userId = (int) $this->option('user');
        $defaultCat = (int) $this->option('category');
        $uploads = rtrim((string) $this->option('uploads'), '/\\');
        $dry = (bool) $this->option('dry-run');

        if (!$userId || !$defaultCat) {
            $this->error('Les options --user et --category sont obligatoires.');
            return self::FAILURE;
        }

        if (!DB::table('users')->where('id', $userId)->exists()) {
            $this->error("L'utilisateur de repli {$userId} n'existe pas dans la table users.");
            return self::FAILURE;
        }

        if (!DB::table('categories')->where('id', $defaultCat)->exists()) {
            $this->error("La catégorie par défaut {$defaultCat} n'existe pas dans la table categories.");
            return self::FAILURE;
        }

        foreach (self::AUTHOR_MAP as $wpId => $laravelId) {
            if (!DB::table('users')->where('id', $laravelId)->exists()) {
                $this->error("AUTHOR_MAP : l'utilisateur Laravel {$laravelId} (auteur WP {$wpId}) n'existe pas.");
                return self::FAILURE;
            }
        }

        $wp = DB::connection('wordpress');
        $categories = DB::table('categories')->pluck('id', 'slug');

        $imported = 0;
        $skipped = 0;

        $wp->table('posts')
            ->where('post_type', 'post')
            ->where('post_status', 'publish')
            ->chunkById(200, function ($posts) use ($wp, $categories, $userId, $defaultCat, $uploads, $dry, &$imported, &$skipped) {
                foreach ($posts as $p) {
                    $slug = $p->post_name ?: Str::slug($p->post_title);

                    if (DB::table('articles')->where('slug', $slug)->exists()) {
                        $skipped++;
                        continue;
                    }

                    $authorId = self::AUTHOR_MAP[$p->post_author] ?? $userId;

                    // Catégorie : première catégorie WP qui correspond à une catégorie Laravel
                    $categoryId = $defaultCat;
                    $terms = $wp->table('term_relationships as tr')
                        ->join('term_taxonomy as tt', 'tt.term_taxonomy_id', '=', 'tr.term_taxonomy_id')
                        ->join('terms as t', 't.term_id', '=', 'tt.term_id')
                        ->where('tr.object_id', $p->ID)
                        ->where('tt.taxonomy', 'category')
                        ->get(['t.name', 't.slug']);
                    foreach ($terms as $t) {
                        $key = $categories->has($t->slug) ? $t->slug : Str::slug($t->name);
                        if ($categories->has($key)) {
                            $categoryId = $categories[$key];
                            break;
                        }
                    }

                    // Contenu : retire les blocs Gutenberg et shortcodes caption
                    $content = preg_replace('/<!--\/?wp:.*?-->/s', '', $p->post_content);
                    $content = preg_replace('/\[\/?caption[^\]]*\]/', '', $content);
                    $content = trim($content);
                    if ($content !== '' && !Str::contains($content, '<p')) {
                        $content = '<p>' . preg_replace("/\n{2,}/", '</p><p>', $content) . '</p>';
                    }

                    $summary = trim($p->post_excerpt) !== ''
                        ? trim($p->post_excerpt)
                        : Str::limit(trim(strip_tags($content)), 200);

                    // Image à la une
                    $image = null;
                    $caption = null;
                    $alt = null;
                    $thumbId = $wp->table('postmeta')
                        ->where('post_id', $p->ID)
                        ->where('meta_key', '_thumbnail_id')
                        ->value('meta_value');

                    if ($thumbId) {
                        $file = $wp->table('postmeta')
                            ->where('post_id', $thumbId)
                            ->where('meta_key', '_wp_attached_file')
                            ->value('meta_value');
                        $alt = $wp->table('postmeta')
                            ->where('post_id', $thumbId)
                            ->where('meta_key', '_wp_attachment_image_alt')
                            ->value('meta_value');
                        $caption = $wp->table('posts')->where('ID', $thumbId)->value('post_excerpt');

                        if ($file && $uploads && File::exists($uploads . '/' . $file)) {
                            $image = 'articles/' . basename($file);
                            if (!$dry) {
                                Storage::disk('public')->put($image, File::get($uploads . '/' . $file));
                            }
                        }
                    }

                    if (!$dry) {
                        DB::table('articles')->insert([
                            'user_id' => $authorId,
                            'category_id' => $categoryId,
                            'title' => $p->post_title,
                            'slug' => $slug,
                            'summary' => $summary,
                            'content' => $content,
                            'image' => $image,
                            'image_caption' => $caption ?: null,
                            'image_alt' => $alt ?: $p->post_title,
                            'is_published' => 1,
                            'created_at' => $p->post_date,
                            'updated_at' => $p->post_modified,
                        ]);
                    }

                    $imported++;
                }
            }, 'ID');

        $this->info(($dry ? '[SIMULATION] ' : '') . "Importés : {$imported} | Déjà présents (ignorés) : {$skipped}");

        return self::SUCCESS;
    }
}