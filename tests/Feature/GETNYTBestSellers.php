<?php

namespace Tests\Feature;

use Tests\TestCase;

class NYTBestSellersTest extends TestCase {
    public function testBasicRequest() {
        $response = $this->get('/api/nyt/best-sellers');

        $response->assertStatus(200);
    }
}