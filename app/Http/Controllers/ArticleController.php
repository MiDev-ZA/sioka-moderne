<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ArticleController extends Controller
{
    /**
     * Règles de validation communes à la création et à la modification.
     */
    protected function rules(Request $request, ?Article $article = null): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'slug' => [
                'nullable', 'string', 'max:200',
                Rule::unique('articles', 'slug')->ignore($article?->id),
            ],
            'summary' => ['required', 'string', 'max:200'],
            'content' => ['required', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10000'],
            'image_caption' => ['nullable', 'string', 'max:255'],
            'image_alt' => ['nullable', 'string', 'max:255'],
            'is_published' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Génère un slug unique à partir du titre si aucun slug n'a été fourni.
     */
    protected function resolveSlug(Request $request, ?Article $article = null): string
    {
        $base = $request->filled('slug') ? $request->input('slug') : $request->input('title');
        $slug = Str::slug($base);
        $original = $slug;
        $i = 1;

        while (
            Article::where('slug', $slug)
                ->when($article, fn ($query) => $query->where('id', '!=', $article->id))
                ->exists()
        ) {
            $slug = $original . '-' . $i++;
        }

        return $slug;
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules($request));

        $validated['slug'] = $this->resolveSlug($request);
        $validated['user_id'] = auth()->id();
        $validated['is_published'] = $request->boolean('is_published');

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('articles', 'public');
        }

        Article::create($validated);

        return redirect()
            ->route('actualites')
            ->with('success', 'Article créé avec succès.');
    }

    public function update(Request $request, Article $article)
    {
        $validated = $request->validate($this->rules($request, $article));

        $validated['slug'] = $this->resolveSlug($request, $article);
        $validated['is_published'] = $request->boolean('is_published');

        if ($request->hasFile('image')) {
            if ($article->image) {
                Storage::disk('public')->delete($article->image);
            }
            $validated['image'] = $request->file('image')->store('articles', 'public');
        }

        $article->update($validated);

        return redirect()
            ->route('actualites')
            ->with('success', 'Article mis à jour avec succès.');
    }

    public function destroy(Article $article)
    {
        if ($article->image) {
            Storage::disk('public')->delete($article->image);
        }

        $article->delete();

        return redirect()
            ->route('actualites')
            ->with('success', 'Article supprimé.');
    }

    public function show(Article $article)
    {
        abort_unless($article->is_published, 404);

        $article->load(['category', 'user']);

        $relatedArticles = Article::with(['category', 'user'])
            ->where('is_published', true)
            ->where('id', '!=', $article->id)
            ->whereHas('category', fn ($q) => $q->where('slug', $article->category->slug))
            ->latest()
            ->take(3)
            ->get();

        return view('article-show', compact('article', 'relatedArticles'));
    }
}