<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

it('returns unified envelope for api route not found', function (): void {
    getJson('/api/does-not-exist')
        ->assertNotFound()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Not Found.')
        ->assertJsonPath('code', 'ROUTE_NOT_FOUND')
        ->assertJsonPath('errors', []);
});

it('returns unified envelope for method not allowed on api route', function (): void {
    getJson('/api/auth/login')
        ->assertStatus(405)
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Method Not Allowed.')
        ->assertJsonPath('code', 'METHOD_NOT_ALLOWED')
        ->assertJsonPath('errors', []);
});
