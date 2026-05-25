<?php

it('has a login page for filament', function () {
    $response = $this->get('/admin/login');

    $response->assertStatus(200);
});
