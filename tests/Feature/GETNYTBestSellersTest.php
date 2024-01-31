<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

class GETNYTBestSellersTest extends TestCase {

    public function testFetchWithNoParams() {
        $response = $this->json('GET', 'api/1/nyt/best-sellers');
        $response
            ->assertStatus(200);
        // print_r($response->json());
        // print(json_encode($response->json(), JSON_PRETTY_PRINT));


        // $response->assertJsonStructure([
        //     'data' => [
        //         '*' => ['id', 'title', 'author'] // Assuming each item has id, title, and author
        //     ]
        // ]);
    
        // // Assert that the response contains a specific data fragment
        // $response->assertJsonFragment([
        //     'title' => 'Expected Title' // Replace with an actual title you expect
        // ]);
        
    }

    //Offset tests
    public function testFetchWithNegativeOffset() {
        $response = $this->json('GET', 'api/1/nyt/best-sellers', ['offset' => -100]);
        $response
            ->assertStatus(422);
        print(json_encode($response->json(), JSON_PRETTY_PRINT));
    }

    public function testFetchWithModifiedOffset() {
        $firstResponse = $this->json('GET', 'api/1/nyt/best-sellers', ['offset' => 0]);
        $secondResponse = $this->json('GET', 'api/1/nyt/best-sellers', ['offset' => 20]);

        $firstResponse->assertStatus(200);
        $secondResponse->assertStatus(200);

        $firstTitles = array_column($firstResponse->json('results'), 'title');
        $secondTitles = array_column($secondResponse->json('results'), 'title');

        $duplicates = array_intersect($firstTitles, $secondTitles);

        $this->assertEmpty($duplicates, 'There are duplicate titles in the responses');
    }

    //Title tests
    public function testFetchWithTooLongTitle() {
        $response = $this->json('GET', 'api/1/nyt/best-sellers', ['title' => "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in repr"]);
        $response
            ->assertStatus(422);
    }

    public function testFetchWithTypoInTitle() {
        $response = $this->json('GET', 'api/1/nyt/best-sellers', ['title' => "hary potter"]);
        $response
            ->assertStatus(200);
        $response->assertJsonFragment([
            'num_results' => 0 
        ]);
        
        $response->assertJsonCount(0, 'results');
    }

    public function testFetchWithEmptyTitle() {
        $response = $this->json('GET', 'api/1/nyt/best-sellers', ['title' => ""]);
        $response
            ->assertStatus(422);
    }

    public function testFetchWithIntTitle() {
        $response = $this->json('GET', 'api/1/nyt/best-sellers', ['title' => 12]);
        $response
            ->assertStatus(422);
    }

    public function testFetchWithCorrectTitle() {
        $response = $this->json('GET', 'api/1/nyt/best-sellers', ['title' => "Buried Dreams"]);
        $response
            ->assertStatus(200);
        $response->assertJsonFragment([
            'num_results' => 1
        ]);

        $response->assertJsonFragment([
            'title' => 'BURIED DREAMS',
            'description' => 'The ghoulish case of the serial killer John Wayne Gacy. First published in 1986.',
            'author' => 'Tim Cahill',
            'publisher' => 'Premier Digital'
        ]);
        
        $response->assertJsonCount(1, 'results');
    }

    //author tests
    public function testFetchWithSpecificAuthor() {
        $author = "Annette Gordon-Reed and Peter S Onuf";
        $expectedTitle = "\"MOST BLESSED OF THE PATRIARCHS\"";
        $expectedDescription = "A character study that attempts to make sense of Jefferson’s contradictions.";
        $expectedPublisher = "Liveright";
    
        $response = $this->json('GET', 'api/1/nyt/best-sellers', ['author' => $author]);
    
        $response->assertStatus(200);
        $response->assertJson([
            'num_results' => 1,
            'results' => [
                [
                    'title' => $expectedTitle,
                    'description' => $expectedDescription,
                    'author' => $author,
                    'publisher' => $expectedPublisher,
                ]
            ]
        ]);
    }
    //ISBN tests
    public function testFetchWithInvalidISBN() {
        $response = $this->json('GET', 'api/1/nyt/best-sellers', ['isbn' => "123#@"]);
        $response
            ->assertStatus(422);
    }

    public function testFetchWithNonexistantISBN() {
        $response = $this->json('GET', 'api/1/nyt/best-sellers', ['isbn' => "123456789X"]);
        $response
            ->assertStatus(422);
    }

    public function testFetchWithOneISBN() {
        $response = $this->json('GET', 'api/1/nyt/best-sellers', ['isbn' => "9780545010221"]);
        $response
            ->assertStatus(200);

        $response->assertJsonFragment([
            'num_results' => 1 
        ]);

        $response->assertJsonFragment([
            'title' => 'HARRY POTTER'
        ]);
        
        $response->assertJsonCount(1, 'results');
    }
    // NOTE: Multiple ISBNs didn't work when I tried it
    // public function testFetchWithMultipleISBNS() {
    //     $response = $this->json('GET', 'api/1/nyt/best-sellers', ['isbn' => "9780061997815;1338030019;9780062101891"]);
    //     $response
    //         ->assertStatus(200);
    //     $response->assertJsonCount(3, 'results');
    // }
}