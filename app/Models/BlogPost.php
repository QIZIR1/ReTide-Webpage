<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BlogPost extends Model
{
    protected $table = 'blog_posts';

    protected $primaryKey = 'id';

    protected $fillable = [
        'title',
        'content',
        'image_path',
        'tags',
    ];

    protected $casts = [
        'tags' => 'array',
    ];

    /**
     * Rata-rata kecepatan membaca orang dewasa (kata per menit).
     */
    public const WORDS_PER_MINUTE = 200;

    /**
     * Jumlah maksimal tag untuk satu artikel.
     */
    public const MAX_TAGS = 5;

    /**
     * Menghitung estimasi waktu baca (dalam menit) dari isi artikel.
     *
     * Tag HTML dibuang dulu, lalu jumlah kata dibagi WORDS_PER_MINUTE
     * dan dibulatkan ke atas. Hasil minimal 1 menit.
     */
    public static function calculateReadingTime(?string $content): int
    {
        $text = trim(strip_tags((string) $content));

        if ($text === '') {
            return 1;
        }

        $wordCount = count(preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY));

        return max(1, (int) ceil($wordCount / self::WORDS_PER_MINUTE));
    }

    /**
     * Accessor agar estimasi waktu baca bisa dipakai sebagai $post->reading_time.
     */
    public function getReadingTimeAttribute(): int
    {
        return self::calculateReadingTime($this->content);
    }

    /**
     * Daftar user yang menyimpan (bookmark) artikel ini.
     */
    public function bookmarkedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'blog_bookmarks')->withTimestamps();
    }

    /**
     * Merapikan satu tag ke format standar.
     *
     * Contoh: "#Coral Rescue" menjadi "coral-rescue". Tag HTML dibuang, huruf
     * dikecilkan, spasi diganti '-', dan hanya huruf a-z, angka, dan '-' yang
     * dipertahankan.
     */
    public static function normalizeTag(string $tag): string
    {
        $tag = mb_strtolower(trim(strip_tags($tag)));
        $tag = ltrim($tag, '#');
        $tag = preg_replace('/\s+/u', '-', $tag);
        $tag = preg_replace('/[^a-z0-9-]/', '', $tag);
        $tag = preg_replace('/-+/', '-', $tag);

        return trim($tag, '-');
    }

    /**
     * Mengubah input tag dari form (dipisah koma) menjadi array tag yang rapi.
     *
     * Tag kosong dan duplikat dibuang, dan hanya MAX_TAGS tag pertama yang
     * disimpan. Input null atau kosong menghasilkan array kosong.
     *
     * @return list<string>
     */
    public static function parseTags(?string $input): array
    {
        if ($input === null || trim($input) === '') {
            return [];
        }

        return collect(explode(',', $input))
            ->map(fn (string $tag) => self::normalizeTag($tag))
            ->filter(fn (string $tag) => $tag !== '')
            ->unique()
            ->take(self::MAX_TAGS)
            ->values()
            ->all();
    }

    /**
     * Scope untuk memfilter artikel berdasarkan tag, contoh: BlogPost::withTag('laut').
     */
    public function scopeWithTag(Builder $query, string $tag): Builder
    {
        $tag = self::normalizeTag($tag);

        // Tag tidak valid (misalnya hanya simbol) tidak boleh cocok dengan artikel apa pun
        if ($tag === '') {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereJsonContains('tags', $tag);
    }

    /**
     * Mengambil tag yang paling sering dipakai, diurutkan dari yang terbanyak.
     *
     * @return list<string>
     */
    public static function popularTags(int $limit = 8): array
    {
        return self::query()
            ->whereNotNull('tags')
            ->get(['tags'])
            ->pluck('tags')
            ->flatten()
            ->filter()
            ->countBy()
            ->sortDesc()
            ->keys()
            ->take($limit)
            ->values()
            ->all();
    }
}
