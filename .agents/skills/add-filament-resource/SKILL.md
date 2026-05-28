---
name: add-filament-resource
description: Guidelines and conventions for creating new models, database migrations, and Filament resources in the Hobby Indonesia project. Activates when adding or modifying models, database migrations, or generating new Filament CRUD resources.
---

Use this skill whenever you are tasked with creating a new model, database migration, or Filament CRUD resource in the Hobby Indonesia application. It establishes key architectural and implementation rules.

## Step-by-Step Generation Flow

1. **Ask about Soft Deletes (Mandatory)**:
   - **Rule**: Before writing any code, always ask the user whether the resource should support soft deletes.
   - **Implementation**: If yes, include `$table->softDeletes()` in the migration, use the `SoftDeletes` trait on the Eloquent model, and enable soft delete actions/filters in the Filament resource.

2. **Model, Migration, and Factory Generation**:
   - Run the Artisan model generator:
     ```bash
     php artisan make:model <ModelName> -m -f
     ```

3. **Database Schema Design Rules**:
   - **Primary Key**: Always configure UUID v7 as the primary key (`$table->uuid('id')->primary()`) and apply the `Illuminate\Database\Eloquent\Concerns\HasUuids` trait on the model.
   - **Varchar Lengths**: Limit `varchar` columns logically if possible (e.g. `name` columns for people, brands, or titles should be limited to 30 characters: `$table->string('name', 30)`).
   - **Text Columns**: For descriptive text or columns that can exceed short varchar limits, always use the `text` data type.

4. **Filament Resource Generation**:
   - Run the Filament resource generator with automatic schema generation and no-interaction flags:
     ```bash
     php artisan make:filament-resource <ModelName> --generate --no-interaction
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

