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
   - **Image Handling**: If a file upload is specifically for an image, always apply the `->image()` validator and `->imageEditor()` to allow users to edit the uploaded image on the form's `FileUpload` component. Use an `ImageColumn` on the table to display preview thumbnails.
     - **Form Example**:
       ```php
       FileUpload::make('image')
           ->image()
           ->imageEditor()
           ->directory('plural-resource-name')
       ```
     - **Table Example**:
       ```php
       ImageColumn::make('image')
       ```

5. **Form & Table Styling Conventions**:
   - **Status Badges (Capitalization)**: For Filament table columns displaying a status using a text badge, capitalize it using:
     ```php
     ->formatStateUsing(fn (string $state): string => \Illuminate\Support\Str::headline($state))
     ```
   - **Status Form Fields**: Always use the `Radio` button component (e.g., `Radio::make('status')`) instead of `Select` or `Checkbox` for status fields.

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

7. **Pest Integration Testing**:
   - Write robust, functional Pest tests under `tests/Feature/<ModelName>Test.php` asserting successful index and create page loading, form creation validations, and editing/saving capabilities. Use `RefreshDatabase` and fake S3 storage (`Storage::fake('s3')`) if file uploads are executed during testing.

8. **Navigation Grouping, Icons, & Parent Breadcrumbs**:
   - **Collapsible Sidebar Folders**: Register collapsible group folders in your panel provider (e.g. `AdminPanelProvider.php`) to group related resources cleanly in the sidebar:
     ```php
     ->navigationGroups([
         NavigationGroup::make()
             ->label('Homepage')
             ->icon(Heroicon::OutlinedHome)
             ->collapsed(),
     ])
     ```
   - **Union Type Safety**: Subclasses overriding `$navigationGroup` must match the parent signature union type exactly. Always use the `use UnitEnum;` import and define:
     ```php
     protected static string|UnitEnum|null $navigationGroup = 'Homepage';
     ```
   - **Proper UX Icon Rule (No Clutter)**: Either the parent folder or its child items can have icons, but **not both**. When placing items inside a parent navigation group folder that has a main icon, set the child resource's `$navigationIcon` to `null` to render them as clean, text-only indented links.
   - **Custom Parent Breadcrumbs**: Prepend the parent navigation group folder label (e.g., `'Homepage'` or `'Reseller'`) dynamically as a **non-clickable parent breadcrumb** (using a `'#'` key) at the beginning of the breadcrumbs trail (e.g. `Homepage > Hero Banners > List`).
     - To keep the codebase DRY, simply import and use the shared `HasGroupBreadcrumbs` trait inside the resource's three page classes (`List`, `Create`, `Edit`).

9. **Singular Filament Resources**:
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
    - **Seeding**: Always seed the default singular record using `firstOrCreate()` within `DatabaseSeeder.php` rather than directly in database migrations, allowing standard application-wide seeding practices.

10. **Database Migrations Safety**:
    - **NEVER use or suggest `php artisan migrate:fresh`**: This command wipes the entire database which will reset all user-entered records, even during local development.
    - **Migration Modification Flow**: If a migration needs changes or a table needs recreation:
      - Instruct the user to use target rollbacks (e.g. `php artisan migrate:rollback --step=1` to roll back the single last migration).
      - Modify the specific migration file and run `php artisan migrate` to re-apply the schema changes safely.
