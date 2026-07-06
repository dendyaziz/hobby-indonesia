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
- Do not cache raw Eloquent models, collections, or paginator objects directly, as they contain serialization state that can break upon unserialization. Always cache the resolved resource array representation (`resolve()`).

---

## 3. Caching & Invalidation (Redis Cache Tags)

All public endpoints are cached using Redis with the general tag `public` for maintenance/global flushing.

### A. Cache Key Naming Conventions
Follow the structure below using the kebab-case version of the model name, dash `-` for word-space replacement, and double underscores `__` as separators:
- **Singleton model key**: `{model-kebab}` (e.g., `contact`, `social-media`)
- **Singular/Detail key**: `{model-kebab}__{slug_or_identifier}` (e.g., `article__my-first-article`)
- **List/Collection key**: `{model-kebab}__list` (e.g., `article__list`, `hero-banner__list_{today}`)
- **Paginated List key**: `{model-kebab}__pagination_limit_{limit}_page_{page}` (e.g., `article__pagination_limit_10_page_1`)

### B. Caching Implementation (Caching Resolved Array)
Always cache the transformed and resolved Resource output (plain PHP array) rather than the Eloquent model or collection. This avoids serialization issues:
```php
$data = Cache::tags(['public'])->remember('contact', now()->addDays(30), function () {
    $contact = Contact::first();

    if (! $contact) {
        return null;
    }

    return ContactResource::make($contact)->resolve();
});

if (! $data) {
    abort(404);
}

return response()->json(['data' => $data]);
```

### C. Invalidation & Cache Clearing
- **Note on Laravel Limitation**: In Laravel, if an item is stored with a tag (`Cache::tags(['public'])->remember(...)`), you **must** specify the tag when forgetting it: `Cache::tags(['public'])->forget($key)`. Calling direct `Cache::forget($key)` will not remove tagged cache entries.
- **Detail vs. List Clearing**:
  - When a model is updated or saved, invalidates its own detail cache key and/or associated lists.
  - Forget the key from the tagged store on save or delete in the model's `booted()` method:
  ```php
  static::saved(function (): void {
      Cache::tags(['public'])->forget('contact');
  });
  ```

---

## 4. Banner API Endpoints Guidelines

When implementing API endpoints for banners (e.g., Hero Banners, Product Banners, etc.), adhere to the following rules:

### A. Query Constraints & Filtering
- **Active & Not Expired**: Always filter by active and non-expired records:
  - `status === 'active'`
  - `start_date` is null or in the past (`start_date <= today`)
  - `end_date` is null or in the future (`end_date >= today`)
- **Sorting**: Banners must always be sorted by `position` ASC.
- **Eager Loading**: Eagerly load the `media` relationship (`with('media')`) to avoid N+1 queries when extracting image URLs.

### B. Caching & Key Strategy
- **Today Suffix**: Incorporate the current date string into the cache key to naturally expire caches across different days (e.g., `hero-banner__list_{$today}`).
- **Duration**: Banner lists must only be cached for a duration of **1 day** (`now()->addDay()`).
- **Invalidation**: In the model's `saved` and `deleted` event observers, generate the today string and forget the date-suffixed key:
  ```php
  static::saved(function (): void {
      $today = now()->toDateString();
      Cache::tags(['public'])->forget("hero-banner__list_{$today}");
  });
  ```

### C. Public Resource Fields
- **Hiding Placement**: Do not expose internal categorization columns like `placement` in the public API resource. Keep public banner fields limited to fields required by the UI (e.g., `title`, `position`, `type`, `url`, `image_url`).

---

## 5. Testing with Pest

Verify cache invalidation and correct field masking (specifically checking that `id`, `created_at`, `updated_at`, and database columns like `placement` are absent from the JSON) in your Pest feature tests.
