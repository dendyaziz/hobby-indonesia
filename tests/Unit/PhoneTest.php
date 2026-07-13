<?php

use App\Helpers\Phone;

test('it returns null for null input', function () {
    expect(Phone::format(null))->toBeNull();
});

test('it returns null for empty string or whitespace input', function () {
    expect(Phone::format(''))->toBeNull();
    expect(Phone::format('   '))->toBeNull();
});

test('it formats zero-prefixed numbers', function () {
    expect(Phone::format('08123456789'))->toBe('628123456789');
    expect(Phone::format('008123456789'))->toBe('628123456789');
});

test('it formats +62 prefixed numbers', function () {
    expect(Phone::format('+628123456789'))->toBe('628123456789');
    expect(Phone::format('+62 812-3456-789'))->toBe('628123456789');
});

test('it formats 62 prefixed numbers', function () {
    expect(Phone::format('628123456789'))->toBe('628123456789');
});

test('it formats numbers without prefix', function () {
    expect(Phone::format('8123456789'))->toBe('628123456789');
});

test('it strips formatting characters', function () {
    expect(Phone::format('021-99998888'))->toBe('622199998888');
    expect(Phone::format('(021) 9999 8888'))->toBe('622199998888');
});
