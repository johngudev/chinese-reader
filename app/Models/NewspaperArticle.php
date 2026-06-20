<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NewspaperArticle extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'summary',
        'body',
        'english',
        'definitions',
        'hsk_level',
        'publication_date',
        'is_published',
    ];

    protected $casts = [
        'definitions'      => 'array',   // glossary: [{word, pinyin, english}, ...]
        'is_published'     => 'boolean',
        'publication_date' => 'date',
    ];

    /**
     * Route-model binding resolves /articles/{newspaperArticle} by slug.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Only published articles, newest first — the public reading list.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
                     ->orderByDesc('publication_date')
                     ->orderByDesc('id');
    }

    /**
     * Keep a unique slug in sync with the title when one isn't supplied.
     * Called from the controller on store/update.
     */
    public static function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'article';
        $slug = $base;
        $i    = 2;

        while (static::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
