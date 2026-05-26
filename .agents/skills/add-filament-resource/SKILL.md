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

5. **File & Image Upload Guidelines**:
   - **S3 Disk**: All files must be uploaded to the `s3` disk.
   - **Directory Structure**: Store files in an S3 directory named after the resource in plural form (e.g., `testimonies` for a `Testimony` model).
   - **Image Handling**: If a file upload is specifically for an image, always apply the `->image()` validator on the form's `FileUpload` component and use an `ImageColumn` on the table to display preview thumbnails. Make sure both specify the `s3` disk!
     - **Form Example**:
       ```php
       FileUpload::make('image')
           ->image()
           ->disk('s3')
           ->directory('plural-resource-name')
       ```
     - **Table Example**:
       ```php
       ImageColumn::make('image')
           ->disk('s3')
       ```

6. **Pest Integration Testing**:
   - Write robust, functional Pest tests under `tests/Feature/<ModelName>Test.php` asserting successful index and create page loading, form creation validations, and editing/saving capabilities. Use `RefreshDatabase` and fake S3 storage (`Storage::fake('s3')`) if file uploads are executed during testing.
