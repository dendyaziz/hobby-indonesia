<?php

namespace App\Models;

use Database\Factories\ArticleFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Article extends Model implements HasMedia
{
    /** @use HasFactory<ArticleFactory> */
    use HasFactory, HasUuids;

    use InteractsWithMedia;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'read_duration' => 'integer',
        ];
    }

    public static function calculateReadDuration(string $content): int
    {
        $plainText = trim(preg_replace('/\s+/u', ' ', strip_tags($content)) ?? '');

        if ($plainText === '') {
            return 1;
        }

        preg_match_all('/[\p{L}\p{N}]+/u', $plainText, $matches);

        return max(1, (int) ceil(count($matches[0]) / 200));
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('featured_images')
            ->registerMediaConversions(function (Media $media): void {
                $this
                    ->addMediaConversion('small')
                    ->height(40)
                    ->format('webp');

                $this
                    ->addMediaConversion('thumbnail')
                    ->height(400)
                    ->format('webp');
            });
    }

    protected static function booted(): void
    {
        static::saving(function (Article $article): void {
            $article->read_duration = self::calculateReadDuration((string) $article->content);
        });

        static::saved(function (Article $article): void {
            Cache::tags(['public', 'article'])->flush();
        });

        static::deleted(function (Article $article): void {
            Cache::tags(['public', 'article'])->flush();
        });

        static::deleting(function (Article $article) {
            if (empty($article->content)) {
                return;
            }

            preg_match_all('/<img[^>]+src="([^">]+)"/', $article->content, $matches);

            if (! empty($matches[1])) {
                $disk = config('filament.default_filesystem_disk', 'public');
                $root = config("filesystems.disks.{$disk}.root");

                foreach ($matches[1] as $url) {
                    $path = parse_url($url, PHP_URL_PATH);
                    $path = ltrim($path, '/');

                    // Strip 'storage/' prefix if present (local public disk)
                    if (Str::startsWith($path, 'storage/')) {
                        $path = Str::after($path, 'storage/');
                    }

                    // Strip S3 root prefix if present.
                    // Laravel Storage automatically prepends the root, so we shouldn't pass it in.
                    if ($root && Str::startsWith($path, $root.'/')) {
                        $path = Str::after($path, $root.'/');
                    }

                    Storage::disk($disk)->delete($path);
                }
            }
        });
    }
}
