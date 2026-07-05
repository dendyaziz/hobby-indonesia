<?php

use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

test('it returns 404 if no contact exists', function () {
    $this->getJson('/api/v1/contact')
        ->assertNotFound();
});

test('it returns the first contact model as a resource if it exists', function () {
    $contact = Contact::factory()->create([
        'company_name' => 'Hobby Indonesia Test',
        'telephone' => '021-99998888',
        'email' => 'test@hobbyindonesia.com',
        'address' => 'Jakarta, ID',
    ]);

    $this->getJson('/api/v1/contact')
        ->assertOk()
        ->assertJson([
            'data' => [
                'id' => $contact->id,
                'company_name' => 'Hobby Indonesia Test',
                'telephone' => '021-99998888',
                'email' => 'test@hobbyindonesia.com',
                'address' => 'Jakarta, ID',
            ],
        ])
        ->assertJsonMissing([
            'created_at',
            'updated_at',
        ]);
});

test('it caches the contact data for 30 days', function () {
    expect(Cache::tags(['public_app'])->has('app_contact'))->toBeFalse();

    $contact = Contact::factory()->create();

    // Call endpoint to populate cache
    $this->getJson('/api/v1/contact')->assertOk();

    // Verify cache has it
    expect(Cache::tags(['public_app'])->has('app_contact'))->toBeTrue();
    expect(Cache::tags(['public_app'])->get('app_contact')->id)->toBe($contact->id);
});

test('it clears the cache when the contact model is updated', function () {
    $contact = Contact::factory()->create();

    // Call endpoint to populate cache
    $this->getJson('/api/v1/contact')->assertOk();
    expect(Cache::tags(['public_app'])->has('app_contact'))->toBeTrue();

    // Update the contact model
    $contact->update([
        'company_name' => 'Updated Company Name',
    ]);

    // Verify cache is cleared
    expect(Cache::tags(['public_app'])->has('app_contact'))->toBeFalse();
});

test('it clears the cache when the contact model is deleted', function () {
    $contact = Contact::factory()->create();

    // Call endpoint to populate cache
    $this->getJson('/api/v1/contact')->assertOk();
    expect(Cache::tags(['public_app'])->has('app_contact'))->toBeTrue();

    // Delete the contact model
    $contact->delete();

    // Verify cache is cleared
    expect(Cache::tags(['public_app'])->has('app_contact'))->toBeFalse();
});

test('it can clear all public caches by tag', function () {
    $contact = Contact::factory()->create();

    // Call endpoint to populate cache
    $this->getJson('/api/v1/contact')->assertOk();
    expect(Cache::tags(['public_app'])->has('app_contact'))->toBeTrue();

    // Flush the tag
    Cache::tags(['public_app'])->flush();

    // Verify cache is cleared
    expect(Cache::tags(['public_app'])->has('app_contact'))->toBeFalse();
});
