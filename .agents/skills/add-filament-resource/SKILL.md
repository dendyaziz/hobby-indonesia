---
name: add-filament-resource
description: Guidelines and conventions for creating new models, database migrations, and Filament resources in the Hobby Indonesia project. Activates when adding or modifying models, database migrations, or generating new Filament CRUD resources.
---

Use this skill whenever you are tasked with creating a new model, database migration, or Filament CRUD resource in the Hobby Indonesia application. It establishes key architectural and implementation rules.

## Step-by-Step Generation Flow

1. **Model, Migration, and Factory Generation**:
   - Run the Artisan model generator:
     ```bash
     php artisan make:model <ModelName> -m -f
     ```

2. **Database Schema Design Rules**:
   - **Primary Key**: Always configure UUID v7 as the primary key (`$table->uuid('id')->primary()`) and apply the `Illuminate\Database\Eloquent\Concerns\HasUuids` trait on the model.
   - **Varchar Lengths**: Limit `varchar` columns logically if possible (e.g. `name` columns for people, brands, or titles should be limited to 30 characters: `$table->string('name', 30)`).
   - **Text Columns**: For descriptive text or columns that can exceed short varchar limits, always use the `text` data type.

3. **Filament Resource Generation**:
   - Run the Filament resource generator with automatic schema generation and no-interaction flags:
     ```bash
     php artisan make:filament-resource <ModelName> --generate --no-interaction
     ```

4. **File & Image Upload Guidelines**:
   - **Polymorphic Media Storage**: Do NOT define an `image` column in database migrations. Instead, use the **Spatie Laravel Media Library** polymorphic `media` table structure.
     > [!IMPORTANT]
     > Because all models in this application use UUIDs as primary keys, the default `$table->morphs('model')` in the Spatie media migration MUST be changed to `$table->uuidMorphs('model')`. Failing to do so will result in SQL truncation errors when trying to insert string UUIDs into an unsigned big integer column.
     > [!TIP]
     > By default, Spatie Media Library processes conversions in a background queue. To prevent missing/unprocessed conversions in local development (when queue workers aren't active), ensure `QUEUE_CONVERSIONS_BY_DEFAULT=false` is declared in the `.env` file so conversions run synchronously upon upload.
   - **Public/Private Asset Separation**:
     - S3 file access is managed strictly via **Bucket Policies** instead of file-level ACLs. To support both public and private assets:
       1. The S3 Bucket Policy is configured to grant public read access (`s3:GetObject`) **only** to the public directory prefix (e.g. `/local/public/*`).
       2. All public files are saved inside a `public/` directory:
          - For Spatie Media Library uploads, the global prefix `'prefix' => 'public'` is set in `config/media-library.php`.
          - For standard RichEditor file attachments, the directory must start with `public/` (e.g., `fileAttachmentsDirectory('public/articles/attachments')`).
       3. Future private files (or private models) must be stored outside of the `public/` folder (such as directly under the root or in a `private/` folder). S3 will automatically deny public access to these files, keeping them secure.
   - **Eloquent Model Configuration**:
     - Implement `Spatie\MediaLibrary\HasMedia` and use the `Spatie\MediaLibrary\InteractsWithMedia` trait.
     - Register the media collection and define responsive media conversions using **height only** so that the image engine scales the width proportionally, preserving the original aspect ratio without distortion.
     - Register a `'small'` conversion `height(40)` for list/table previews, and an optional `'thumbnail'` conversion for public views (e.g. `height(150)` for logos, `height(400)` for banners/events). Always force conversions to modern WebP format by chaining `->format('webp')`.
     - **Model Example**:
       ```php
       use Spatie\MediaLibrary\HasMedia;
       use Spatie\MediaLibrary\InteractsWithMedia;
       use Spatie\MediaLibrary\MediaCollections\Models\Media;

       class Banner extends Model implements HasMedia
       {
           use InteractsWithMedia;

           public function registerMediaCollections(): void
           {
               $this
                   ->addMediaCollection('banners')
                   ->registerMediaConversions(function (Media $media): void {
                       $this->addMediaConversion('small')
                           ->height(40)
                           ->format('webp');

                       $this->addMediaConversion('thumbnail')
                           ->height(400)
                           ->format('webp');
                   });
           }
       }
       ```
   - **Filament Form Layout**:
     - **Image Preview Height Workaround**: Spatie Media Library components often render blurry previews in Filament because they try to load tiny thumbnails. To resolve this, always append `->imagePreviewHeight(250)` to every `SpatieMediaLibraryFileUpload` component.
     - **Spatie Form Example**:
       ```php
       use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

       SpatieMediaLibraryFileUpload::make('image')
           ->collection('banners')
           ->image()
           ->imageEditor()
           ->imagePreviewHeight(250)
           ->required()
       ```
     - **RichEditor File Attachments**:
       - RichEditor components that allow file uploads (e.g., `->toolbarButtons(['attachFiles', ...])`) must direct uploads to the public folder:
         ```php
         RichEditor::make('content')
             ->fileAttachmentsDirectory('public/articles/attachments')
             ->fileAttachmentsMaxSize(2048)
         ```
       - **Automatic Attachment Deletion**: Because Filament does not automatically track or clean up files uploaded via the `RichEditor` when a record is deleted, you must clean them up manually to prevent orphaned files in S3 or local storage. Hook into the model's `deleting` event inside its `booted()` method, parse the HTML for image URLs, clean the storage path (accounting for prefixes like `/storage/` or S3 roots like `AWS_ROOT`), and delete the files:
         ```php
         use Illuminate\Support\Facades\Storage;
         use Illuminate\Support\Str;

         protected static function booted(): void
         {
             static::deleting(function (TheModel $model) {
                 if (empty($model->content)) {
                     return;
                 }

                 // Extract all image 'src' attributes
                 preg_match_all('/<img[^>]+src="([^">]+)"/', $model->content, $matches);
                 
                 if (! empty($matches[1])) {
                     $disk = config('filament.default_filesystem_disk', 'public');
                     $root = config("filesystems.disks.{$disk}.root");

                     foreach ($matches[1] as $url) {
                         $path = parse_url($url, PHP_URL_PATH);
                         $path = ltrim($path, '/');
                         
                         // Strip local 'storage/' prefix if present
                         if (Str::startsWith($path, 'storage/')) {
                             $path = Str::after($path, 'storage/');
                         }
                         
                         // Strip S3 root prefix if present (Laravel Storage auto-prepends root)
                         if ($root && Str::startsWith($path, $root . '/')) {
                             $path = Str::after($path, $root . '/');
                         }
                         
                         Storage::disk($disk)->delete($path);
                     }
                 }
             });
         }
         ```
   - **Filament Table Layout**:
     - Use the `SpatieMediaLibraryImageColumn` component, specifying the collection and targeting the lightweight `'small'` conversion.
     - **Table Example**:
       ```php
       use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;

       SpatieMediaLibraryImageColumn::make('image')
           ->collection('banners')
           ->conversion('small')
       ```
   - **Database Factories**:
     - Do NOT add an `'image'` key in the factory's default `definition()` array since there is no `image` database column. Seed media attachments, if needed, via `afterCreating` hooks using Spatie's faked media APIs in feature tests.

5. **Form & Table Styling Conventions**:
   - **Filament Form Sections**: Always wrap all page-level resource form inputs inside a Filament Form `Section::make()`. If a Resource form does not have status-related fields, do not configure a columns value (`->columns()`) on the section or schema, and make sure to append `->columnSpanFull()` to the section component so it occupies the whole space (1-column full width).
     - *Popup/Modal Forms Exception*: Do NOT use `Section::make()` or `Group::make()` containers for forms displayed inside popup modals (e.g. in RelationManagers or Modal Actions). Place the form components directly in the schema/components array.
   - **Form Grouping & Layout**:
     - If the model has status or date-related fields (e.g. `status`, `started_at`, `ended_at`, `availability`), organize the form into a two-group layout using a parent column span structure of 3 (`->columns(3)`):
       - **First Group (Main Fields)**: Occupies 2 columns (`Group::make()->columnSpan(['lg' => 2])`). Contains the main fields wrapped inside one or more `Section::make()`. If the main fields exceed 5 components, split them into multiple sections. The first/main section commonly does not have a label.
       - **Second Group (Status/Date Fields)**: Occupies 1 column (`Group::make()->columnSpan(['lg' => 1])`). Contains the status-related fields. If the group has both status and other fields (e.g. date ranges like `start_date`, `end_date`), split the actual status field and those other fields into separate sections (e.g. one `Section::make('Visibility')` for the status, and another `Section::make('Schedule')` for the dates) to keep them clean. Add context-related labels to both sections. Use a simple label like `'Status'` for the status input itself, preventing repetitive copywriting.
     - **Slug Fields Layout**: For forms containing a `slug` field, place the `slug` side-by-side on large screens with its original field (such as `name` or `title`) by setting `->columns(2)` on the containing `Section::make()`.
       - *Exception*: Keep them vertically stacked (do not put them side-by-side) if the original field's content is typically very long (for example, an article's `title`).
       - *Grid Alignment*: If the section layout is changed to 2 columns, make sure other full-width fields in that same section (like image uploads or rich editors) explicitly specify `->columnSpanFull()` so they span the full grid.
   - **Status Badges (Capitalization)**: For Filament table columns displaying a status using a text badge, capitalize it using:
     ```php
     ->formatStateUsing(fn (string $state): string => \Illuminate\Support\Str::headline($state))
     ```
   - **View Action in Tables**: Every Filament resource table must include a `ViewAction::make(),` in its `recordActions` array alongside the `EditAction::make(),` to match the project's standard action patterns.
   - **Status Form Fields**: Always use the `Radio` button component (e.g., `Radio::make('status')`) instead of `Select` or `Checkbox` for status fields.
   - **Relationship Select Fields (Preloading & Sorting)**:
     - **Preloading**: If the Select options are retrieved from another model (relationship options), always append `->preload()` to ensure that the dropdown option suggestions are loaded when the page loads, rather than requiring user search input.
     - **Searching & Sorting**: Always make relationship select fields searchable (`->searchable()`) and sort options by the latest created data (using `modifyQueryUsing(fn (Builder $query) => $query->latest())`) so users can easily find and pick recent records.
     - **Multiple Selection & Pivot Tables**: If a Select field allows multiple choices (`->multiple()`) pointing to relationship options, always create a distinct database table (a pivot lookup table like `event_partner` with cascading foreign keys and composite primary keys) instead of storing selections inside JSON or comma-separated columns.

6. **Reorderable Models & Columns**:
   - **Database Migration**: If a model uses `->reorderable('column_name')`, select the database integer type for the orderable column based on the expected maximum records:
     - Use `unsignedTinyInteger('column_name')` for models expected to have <= 255 records (e.g., banners, categories, partner companies).
     - Use `unsignedSmallInteger('column_name')` for models expected to have > 255 but <= 65,535 records (e.g., products/board games).
   - **Eloquent Model Auto-Positioning**: Add automatic last position assignment on creation via the model's `booted` method:
     ```php
     protected static function booted(): void
     {
         static::creating(function (TheModel $theModel) {
             $theModel->position = (static::max('order_column') ?? 0) + 1;
         });
     }
     ```
     *(Note: Replace `TheModel`, `$theModel`, `position` (model property), and `order_column` (database column name) with your actual class/column names).*
   - **Filament Form Layout**: Hide/omit the orderable column completely from the Filament resource form layout since it must only be changed via the table reordering interface.
   - **Filament Table Layout**: Always show/include the orderable column in the table columns (e.g. `TextColumn::make('position')->sortable()`) so the list remains explicitly ordered and verifiable.

7. **Embedded Relation Managers & Livewire Components**:
   - **Embedding in Form Schemas**: Relation managers can be embedded directly into parent form schemas using `Livewire::make(SomeRelationManager::class, fn (?Model $record) => $record ? ['ownerRecord' => $record, 'pageClass' => EditModel::class] : [])->visible(fn (?Model $record) => $record !== null)`.
   - **CRITICAL Livewire Unique Key (`->key()`)**: When embedding multiple `Livewire::make()` components inside a parent form schema, you **MUST** chain an explicit unique key to each component (e.g., `->key('characters-relation-manager')`, `->key('guides-relation-manager')`). Without explicit keys, Livewire cannot differentiate components during DOM diffing after a parent form save action ("Save changes"), causing Livewire to swap or re-hydrate components with another component's state on the page.
   - **Width Alignment & Cleanup**: Embed the relation manager components inside `Group::make()->columnSpan(['lg' => 2])` to match the main column group width. Remove the relation manager classes from `getRelations()` in the main resource class to prevent duplicate footer rendering.
   - **Modal Form & Footer Actions**: For single-column relation manager popup modals where row actions are simplified, move the `Delete` action into the `Edit` modal popup footer using:
     ```php
     ->recordActions([
         EditAction::make()
             ->extraModalFooterActions([
                 DeleteAction::make(),
             ]),
     ])
     ```

8. **Pest Integration Testing**:
   - Write robust, functional Pest tests under `tests/Feature/<ModelName>Test.php` asserting successful index and create page loading, form creation validations, and editing/saving capabilities. Use `RefreshDatabase` and fake S3 storage (`Storage::fake('s3')`) if file uploads are executed during testing.

9. **Navigation Grouping, Icons, & Parent Breadcrumbs**:
   - **Collapsible Sidebar Folders**: Register collapsible group folders in your panel provider (e.g. `AdminPanelProvider.php`) to group related resources cleanly in the sidebar (without group-level icons):
     ```php
     ->navigationGroups([
         NavigationGroup::make()
             ->label('Homepage')
             ->collapsed(),
     ])
     ```
   - **Union Type Safety**: Subclasses overriding `$navigationGroup` must match the parent signature union type exactly. Always use the `use UnitEnum;` import and define:
     ```php
     protected static string|UnitEnum|null $navigationGroup = 'Homepage';
     ```
   - **Proper UX Icon Rule (No Clutter)**: Parent group folders in the sidebar should not have group-level icons. Instead, each child resource inside the group must have its own distinct, appropriate navigation icon defined via `$navigationIcon` (e.g. `Heroicon::OutlinedPhoto`, `Heroicon::OutlinedUser`, etc.).
   - **Custom Parent Breadcrumbs**: Prepend the parent navigation group folder label (e.g., `'Homepage'` or `'Reseller'`) dynamically as a **non-clickable parent breadcrumb** (using a `'#'` key) at the beginning of the breadcrumbs trail (e.g. `Homepage > Hero Banners > List`).
     - To keep the codebase DRY, simply import and use the shared `HasGroupBreadcrumbs` trait inside the resource's three page classes (`List`, `Create`, `Edit`).

10. **Singular Filament Resources**:
    - **Concept**: For models representing singular system settings or configuration pages (e.g., Social Media links, Contact Details), create a custom Filament Page rather than a full multi-page CRUD Resource.
    - **Model Setup**: Set up standard Eloquent traits (`HasUuids`) and define fillable attributes.
    - **Page Setup**: Create a Page class extending `Filament\Pages\Page`, overriding `$view` with a simple blade file that renders the form (`{{ $this->form }}`).
    - **Record Handling**: Implement a `getRecord()` helper method that safely retrieves or creates the singular record via `TheModel::firstOrCreate()`. Mount the form using:
      ```php
      public function mount(): void
      {
          $this->form->fill($this->getRecord()?->attributesToArray());
      }
      ```
    - **Permission-Based Access & Save Action Visibility**:
      - When restricting access to singular resource pages, conditionally disable the entire form based on the permission (e.g. `->disabled(! auth()->user()->can('manage ModelName'))`).
      - You **must also conditionally hide the Save action/button** in the form footer using `->visible()` so it does not render when the user only has view/read-only access.
      - **Example**:
        ```php
        public function form(Schema $schema): Schema
        {
            return $schema
                ->disabled(! auth()->user()->can('manage SocialMedia'))
                ->components([
                    Form::make([
                        // ...
                    ])
                        ->livewireSubmitHandler('save')
                        ->footer([
                            Actions::make([
                                Action::make('save')
                                    ->submit('save')
                                    ->keyBindings(['mod+s'])
                                    ->visible(fn () => auth()->user()->can('manage SocialMedia')),
                            ]),
                        ]),
                ]);
        }
        ```
    - **Seeding**: Always seed the default singular record using `firstOrCreate()` within `DatabaseSeeder.php` rather than directly in database migrations, allowing standard application-wide seeding practices.

11. **Database Migrations Safety**:
    - **NEVER use or suggest `php artisan migrate:fresh`**: This command wipes the entire database which will reset all user-entered records, even during local development.
    - **Migration Modification Flow**: If a migration needs changes or a table needs recreation:
      - Instruct the user to use target rollbacks (e.g. `php artisan migrate:rollback --step=1` to roll back the single last migration).
      - Modify the specific migration file and run `php artisan migrate` to re-apply the schema changes safely.

12. **Resource Permissions Registration**:
    - Every newly created CRUD Resource must be registered in the permission system:
      1. Add the model name to the `$models` array in `database/seeders/RolesAndPermissionsSeeder.php` so its dynamic view and manage permissions are created.
      2. Map the model name to the corresponding permission group category in `app/Filament/Resources/Administrator/Schemas/RoleForm.php` (inside `$groups` array) so it can be managed and assigned to roles in the Filament UI.
      3. Create a corresponding policy for the model in `app/Policies/` extending `BasePolicy` to automatically enforce the permissions (e.g. `CollectionPolicy` for `Collection`).

