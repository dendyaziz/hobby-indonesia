<?php

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

test('it returns only published articles without content in the list', function () {
    $published = Article::factory()->create([
        'title' => 'Published Article',
        'slug' => 'published-article',
        'content' => '<p>Published content.</p>',
        'status' => 'published',
    ]);
    Article::factory()->create(['status' => 'draft']);

    $this->getJson('/api/v1/articles')
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.slug', $published->slug)
        ->assertJsonPath('data.0.read_duration', 1)
        ->assertJsonMissingPath('data.0.content')
        ->assertJsonMissing(['id', 'created_at', 'updated_at']);
});

test('it paginates published articles newest first', function () {
    $oldest = Article::factory()->create([
        'title' => 'Oldest Article',
        'slug' => 'oldest-article',
        'status' => 'published',
        'created_at' => now()->subDays(2),
    ]);
    $newest = Article::factory()->create([
        'title' => 'Newest Article',
        'slug' => 'newest-article',
        'status' => 'published',
        'created_at' => now(),
    ]);
    Article::factory()->create([
        'title' => 'Middle Article',
        'slug' => 'middle-article',
        'status' => 'published',
        'created_at' => now()->subDay(),
    ]);

    $this->getJson('/api/v1/articles?limit=2&page=1')
        ->assertSuccessful()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.slug', $newest->slug)
        ->assertJsonPath('data.1.slug', 'middle-article')
        ->assertJsonPath('meta.current_page', 1)
        ->assertJsonPath('meta.per_page', 2)
        ->assertJsonPath('meta.total', 3)
        ->assertJsonPath('meta.last_page', 2)
        ->assertJsonPath('links.next', fn (string $value): bool => str_contains($value, 'page=2'));

    $this->getJson('/api/v1/articles?limit=2&page=2')
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.slug', $oldest->slug);
});

test('it returns a published article by slug', function () {
    Article::factory()->create([
        'title' => 'Published Article',
        'slug' => 'published-article',
        'content' => '<p>Published content.</p>',
        'status' => 'published',
    ]);

    $this->getJson('/api/v1/articles/published-article')
        ->assertSuccessful()
        ->assertJsonPath('data.slug', 'published-article')
        ->assertJsonPath('data.content', '<p>Published content.</p>')
        ->assertJsonPath('data.read_duration', 1)
        ->assertJsonMissing(['id', 'created_at', 'updated_at']);
});

test('it does not return draft articles by slug', function () {
    Article::factory()->create([
        'slug' => 'draft-article',
        'status' => 'draft',
    ]);

    $this->getJson('/api/v1/articles/draft-article')->assertNotFound();
});

test('it caches article list and detail data and invalidates both when an article changes', function () {
    $article = Article::factory()->create([
        'slug' => 'cached-article',
        'status' => 'published',
    ]);

    $this->getJson('/api/v1/articles')->assertSuccessful();
    $this->getJson('/api/v1/articles/cached-article')->assertSuccessful();

    expect(Cache::tags(['public', 'article'])->has('article__pagination_limit_10_page_1'))->toBeTrue();
    expect(Cache::tags(['public', 'article'])->has('article__cached-article'))->toBeTrue();

    $article->update(['title' => 'Updated Article']);

    expect(Cache::tags(['public', 'article'])->has('article__pagination_limit_10_page_1'))->toBeFalse();
    expect(Cache::tags(['public', 'article'])->has('article__cached-article'))->toBeFalse();
});
