# Creating Public API Endpoints for Public App Integration

This document outlines the workflow and rules for creating a new public-facing API endpoint for landing page / public app consumption in the Hobby Indonesia project.

---

## 1. Route Registration & Versioning

API endpoints must be stateless, versioned, and safe.
- **Prefix**: Prefix routes with a version number (e.g. `v1`) under the `routes/api.php` file:
  ```php
  Route::prefix('v1')->group(function (): void {
      Route::get('contact', [ContactController::class, 'show']);
      Route::get('articles', [ArticleController::class, 'index']);
      Route::get('articles/{article:slug}', [ArticleController::class, 'show']);
  });
  ```
- **Configuration**: Ensure the api file is configured in `bootstrap/app.php` inside the `withRouting` block:
  ```php
  api: __DIR__.'/../routes/api.php',
  ```

---

## 2. API Controllers & Resources (Public Security)

Public API endpoints must never leak database IDs, timestamps, or internal columns. Always transform the output using a Laravel Resource.

### A. Singular Detail Data (e.g. `show()`)
- For data that has an alternative identity like a **slug**, use the slug in the URL binding (e.g., `/api/v1/articles/{article:slug}`) instead of database IDs.
- **Hide the ID** and timestamps (`created_at`, `updated_at`) from all public resources.
- Map only public fields inside `toArray(Request $request)` of the API Resource:
  ```php
  public function toArray(Request $request): array
  {
      return [
          'slug' => $this->slug,
          'title' => $this->title,
          'content' => $this->content,
      ];
  }
  ```

### B. List and Pagination Data (e.g. `index()`)
- If paginated, always include both the **limit** and **page** number in the cache key because a page number alone is meaningless without knowing the number of items per page.
- Do not cache the database paginator object directly if it contains state that breaks. Cache the resource collection or a structured array representation instead.

---

## 3. Caching & Invalidation (Redis Cache Tags)

All public endpoints are cached using Redis with the general tag `public_app` for maintenance/global flushing.

### A. Cache Key Naming Conventions
Differentiate keys clearly between lists, paginated lists, and individual details:
- **Singular/Detail key**: `{model_plural}:show:{slug_or_identifier}` (e.g., `articles:show:my-first-article`)
- **List/Collection key**: `{model_plural}:index:limit:{limit}:page:{page}` (e.g., `articles:index:limit:10:page:1`)

### B. Caching Implementation
Store cache under the general `public_app` tag for global clearing:
```php
$contact = Cache::tags(['public_app'])->remember('app_contact', now()->addDays(30), function () {
    return Contact::first();
});
```

### C. Invalidation & Cache Clearing
- **Note on Laravel Limitation**: In Laravel, if an item is stored with a tag (`Cache::tags(['public_app'])->remember(...)`), you **must** specify the tag when forgetting it: `Cache::tags(['public_app'])->forget($key)`. Calling direct `Cache::forget($key)` will not remove tagged cache entries.
- **Detail vs. List Clearing**:
  - When a model is updated or saved, **only** invalidate its own detail cache key and the associated lists. Do not clear detail caches of other models.
  - To clear list caches without clearing other details, tag list caches with a model-specific list tag (e.g. `articles:list`) and flush that tag when any model changes:
  ```php
  // Caching a paginated list:
  Cache::tags(['public_app', 'articles:list'])->remember("articles:index:limit:{$limit}:page:{$page}", ...);

  // In Model booted() method when saved/deleted:
  // 1. Invalidate own detail cache:
  Cache::tags(['public_app'])->forget("articles:show:{$this->slug}");
  // 2. Invalidate all article lists (using model-specific list tag):
  Cache::tags(['articles:list'])->flush();
  ```

---

## 4. Unserialization Whitelisting

If caching Eloquent models, add the model class name to the `serializable_classes` array in `config/cache.php`:
```php
'serializable_classes' => [
    \App\Models\Contact::class,
    \App\Models\Article::class,
],
```

---

## 5. Testing with Pest

Verify cache invalidation and correct field masking (specifically checking that `id`, `created_at`, and `updated_at` are absent from the JSON) in your Pest feature tests.
