<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    protected $table = 'blog_posts';

    protected $primaryKey = 'id';

    protected $fillable = [
        'title',
        'content',
        'image_path',
    ];

    /**
     * Rata-rata kecepatan membaca orang dewasa (kata per menit).
     */
    public const WORDS_PER_MINUTE = 200;

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
}
