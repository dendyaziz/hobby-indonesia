<?php

use App\Filament\Resources\Articles\Pages\CreateArticle;
use App\Filament\Resources\Articles\Pages\EditArticle;
use App\Filament\Resources\Articles\Pages\ListArticles;
use App\Models\Article;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $user = User::factory()->create();
    $this->actingAs($user);
    Storage::fake('s3');
});

it('can load the article index and create pages', function () {
    Livewire::test(ListArticles::class)->assertOk();
    Livewire::test(CreateArticle::class)->assertOk();
});

it('can create an article and save to database', function () {
    Livewire::test(CreateArticle::class)
        ->fillForm([
            'title' => 'My New Hobby Article',
            'image' => UploadedFile::fake()->image('article.jpg'),
            'content' => '<p>This is a wonderful article content about a hobby.</p>',
            'status' => 'draft',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('articles', [
        'title' => 'My New Hobby Article',
        'content' => '<p>This is a wonderful article content about a hobby.</p>',
        'status' => 'draft',
    ]);

    $article = Article::where('title', 'My New Hobby Article')->first();
    expect($article)->not->toBeNull();
    expect($article->hasMedia('featured_images'))->toBeTrue();
});

it('validates required and maximum length constraints for title', function () {
    Livewire::test(CreateArticle::class)
        ->fillForm([
            'title' => '',
            'content' => '<p>Test content</p>',
        ])
        ->call('create')
        ->assertHasFormErrors(['title' => 'required']);

    Livewire::test(CreateArticle::class)
        ->fillForm([
            'title' => str_repeat('a', 151),
            'content' => '<p>Test content</p>',
        ])
        ->call('create')
        ->assertHasFormErrors(['title']);
});

it('can edit and update an article', function () {
    $article = Article::factory()->create([
        'title' => 'Original Title',
        'content' => '<p>Original content</p>',
        'status' => 'draft',
    ]);
    $article->addMedia(UploadedFile::fake()->image('original.jpg'))->toMediaCollection('featured_images');

    Livewire::test(EditArticle::class, [
        'record' => $article->getKey(),
    ])
        ->assertFormSet([
            'title' => 'Original Title',
            'content' => '<p>Original content</p>',
            'status' => 'draft',
        ])
        ->fillForm([
            'title' => 'Updated Title',
            'content' => '<p>Updated content</p>',
            'status' => 'published',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('articles', [
        'id' => $article->id,
        'title' => 'Updated Title',
        'content' => '<p>Updated content</p>',
        'status' => 'published',
    ]);
});
