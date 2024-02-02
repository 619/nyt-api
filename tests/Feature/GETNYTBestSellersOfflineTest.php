<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

class GETNYTBestSellersOfflineTest extends TestCase {
    
    public function testFetchWithNoParams() {
        Http::fake([
            'https://api.nytimes.com/svc/books/v3/lists/best-sellers/history.json*' => Http::response(["status" => "OK", "results" => [], "num_results" => 0], 200),
        ]);
        $response = $this->json('GET', 'api/1/nyt/best-sellers');
        
        $response
        ->assertStatus(200);
    }

    //Offset tests
    public function testFetchWithNegativeOffset() {
        Http::fake([
            'https://api.nytimes.com/svc/books/v3/lists/best-sellers/history.json*' => Http::response(["status" => "OK", "results" => [], "num_results" => 0], 200),
        ]);
        $response = $this->json('GET', 'api/1/nyt/best-sellers', ['offset' => -100]);
        
        $response
            ->assertStatus(422);
    }

    public function testFetchWithInvalidOffset() {
        Http::fake([
            'https://api.nytimes.com/svc/books/v3/lists/best-sellers/history.json*' => Http::response(["status" => "OK", "results" => [], "num_results" => 0], 200),
        ]);
        $response = $this->json('GET', 'api/1/nyt/best-sellers', ['offset' => 19]);
        
        $response
            ->assertStatus(422);
    }

    //Title tests
    public function testFetchWithTooLongTitle() {
        Http::fake([
            'https://api.nytimes.com/svc/books/v3/lists/best-sellers/history.json*' => Http::response(["status" => "OK", "results" => [], "num_results" => 0], 200),
        ]);
        $response = $this->json('GET', 'api/1/nyt/best-sellers', ['title' => "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in repr"]);
        
        $response
        ->assertStatus(422);
    }

    public function testFetchWithTypoInTitle() {
        Http::fake([
            'https://api.nytimes.com/svc/books/v3/lists/best-sellers/history.json*' => Http::response(["status" => "OK", "results" => [], "num_results" => 0], 200),
        ]);
        $response = $this->json('GET', 'api/1/nyt/best-sellers', ['title' => "hary potter"]);
        
        $response
        ->assertStatus(200)
        ->assertJson([
            'num_results' => 0,
            'results' => [],
        ]);
    }

    public function testFetchWithEmptyTitle() {
        Http::fake([
            'https://api.nytimes.com/svc/books/v3/lists/best-sellers/history.json*' => Http::response(["status" => "OK", "results" => [], "num_results" => 0], 200),
        ]);
        $response = $this->json('GET', 'api/1/nyt/best-sellers', ['title' => ""]);
        
        $response
        ->assertStatus(422);
    }

    public function testFetchWithIntTitle() {
        Http::fake([
            'https://api.nytimes.com/svc/books/v3/lists/best-sellers/history.json*' => Http::response(['data' => ["status" => "ERROR", "results" => [], "num_results" => 0]], 422),
        ]);
        $response = $this->json('GET', 'api/1/nyt/best-sellers', ['title' => 12]);
        
        $response
        ->assertStatus(422);
    }

    public function testFetchWithCorrectTitle() {
        Http::fake([
            'https://api.nytimes.com/svc/books/v3/lists/best-sellers/history.json*' => Http::response([
                "status" => "OK",
                "copyright" => "Copyright (c) 2024 The New York Times Company.  All Rights Reserved.",
                "num_results" => 1,
                "results" => [
                    [
                        "title" => "BURIED DREAMS",
                        "description" => "The ghoulish case of the serial killer John Wayne Gacy. First published in 1986.",
                        "contributor" => "by Tim Cahill",
                        "author" => "Tim Cahill",
                        "contributor_note" => "",
                        "price" => "0.00",
                        "age_group" => "",
                        "publisher" => "Premier Digital",
                        "isbns" => [
                        [
                            "isbn10" => "1937957055",
                            "isbn13" => "9781937957056"
                        ]
                        ]
                    ]
                ]
            ])
        ], 200);
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

    public function testFetchReturningNoTitle() {
        Http::fake([
            'https://api.nytimes.com/svc/books/v3/lists/best-sellers/history.json*' => Http::response([
                "status" => "OK",
                "copyright" => "Copyright (c) 2024 The New York Times Company.  All Rights Reserved.",
                "num_results" => 1,
                "results" => [
                    [
                        "description" => "The ghoulish case of the serial killer John Wayne Gacy. First published in 1986.",
                        "contributor" => "by Tim Cahill",
                        "author" => "Tim Cahill",
                        "contributor_note" => "",
                        "price" => "0.00",
                        "age_group" => "",
                        "publisher" => "Premier Digital",
                        "isbns" => [
                        [
                            "isbn10" => "1937957055",
                            "isbn13" => "9781937957056"
                        ]
                        ]
                    ]
                ]
            ])
        ], 200);
        $response = $this->json('GET', 'api/1/nyt/best-sellers', ['title' => "Buried Dreams"]);
        $response
            ->assertStatus(422);
    }

    //author tests
    public function testFetchWithSpecificAuthor() {
        Http::fake([
            'https://api.nytimes.com/svc/books/v3/lists/best-sellers/history.json*' => Http::response([
                "status" => "OK",
                "copyright" => "Copyright (c) 2024 The New York Times Company.  All Rights Reserved.",
                "num_results" => 1,
                "results" => [
                [
                    "title" => "\"MOST BLESSED OF THE PATRIARCHS\"",
                    "description" => "A character study that attempts to make sense of Jefferson’s contradictions.",
                    "contributor" => "by Annette Gordon-Reed and Peter S. Onuf",
                    "author" => "Annette Gordon-Reed and Peter S Onuf",
                    "contributor_note" => "",
                    "price" => "0.00",
                    "age_group" => "",
                    "publisher" => "Liveright",
                    "isbns" => [
                    [
                        "isbn10" => "0871404427",
                        "isbn13" => "9780871404428"
                    ]
                    ],
                    "ranks_history" => [
                    [
                        "primary_isbn10" => "0871404427",
                        "primary_isbn13" => "9780871404428",
                        "rank" => 16,
                        "list_name" => "Hardcover Nonfiction",
                        "display_name" => "Hardcover Nonfiction",
                        "published_date" => "2016-05-01",
                        "bestsellers_date" => "2016-04-16",
                        "weeks_on_list" => 1,
                        "rank_last_week" => 0,
                        "asterisk" => 1,
                        "dagger" => 0
                    ]
                    ],
                    "reviews" => [
                    [
                        "book_review_link" => "",
                        "first_chapter_link" => "",
                        "sunday_review_link" => "",
                        "article_chapter_link" => ""
                    ]
                    ]
                ]
                ]
                
            ])
        ]);
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

    public function testFetchWithTooLongAuthor() {
        Http::fake([
            'https://api.nytimes.com/svc/books/v3/lists/best-sellers/history.json*' => Http::response(["status" => "OK", "results" => [], "num_results" => 0], 200),
        ]);
        $response = $this->json('GET', 'api/1/nyt/best-sellers', ['author' => "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in repr"]);
        
        $response
        ->assertStatus(422);
    }

    public function testFetchReturningNoAuthor() {
        Http::fake([
            'https://api.nytimes.com/svc/books/v3/lists/best-sellers/history.json*' => Http::response([
                'data' => [
                    "status" => "OK",
                    "copyright" => "Copyright (c) 2024 The New York Times Company.  All Rights Reserved.",
                    "num_results" => 1,
                    "results" => [
                        [   "title" => "BURIED DREAMS",
                            "description" => "The ghoulish case of the serial killer John Wayne Gacy. First published in 1986.",
                            "contributor" => "by Tim Cahill",
                            "contributor_note" => "",
                            "price" => "0.00",
                            "age_group" => "",
                            "publisher" => "Premier Digital",
                            "isbns" => [
                            [
                                "isbn10" => "1937957055",
                                "isbn13" => "9781937957056"
                            ]
                            ]
                        ]
                    ]
                ]
            ])
        ], 200);
        $response = $this->json('GET', 'api/1/nyt/best-sellers', ['title' => "Buried Dreams"]);
        $response
            ->assertStatus(422);
    }
    //ISBN tests
    public function testFetchWithInvalidISBN() {
        Http::fake([
            'https://api.nytimes.com/svc/books/v3/lists/best-sellers/history.json*' => Http::response(['data' => []], 422),
        ]);
        $response = $this->json('GET', 'api/1/nyt/best-sellers', ['isbn' => "123#@"]);
        $response
            ->assertStatus(422);
    }

    public function testFetchWithNonexistantISBN() {
        Http::fake([
            'https://api.nytimes.com/svc/books/v3/lists/best-sellers/history.json*' => Http::response(['data' => [
                "status" => "OK",
                "copyright" => "Copyright (c) 2024 The New York Times Company.  All Rights Reserved.",
                "num_results" => 0,
                "results" => [],
            ]], 200),
        ]);
        $response = $this->json('GET', 'api/1/nyt/best-sellers', ['isbn' => "123456789X"]);
        $response
            ->assertStatus(422);
        
    }

    public function testFetchWithOneISBN() {
        Http::fake([
            'https://api.nytimes.com/svc/books/v3/lists/best-sellers/history.json*' => Http::response([
                "status" => "OK",
                "copyright" => "Copyright (c) 2024 The New York Times Company.  All Rights Reserved.",
                "num_results" => 1,
                "results" => [
                    [
                        "title" => "HARRY POTTER",
                        "description" => "A wizard hones his conjuring skills in the service of fighting evil.",
                        "contributor" => "by J.K. Rowling",
                        "author" => "J.K. Rowling",
                        "contributor_note" => "",
                        "price" => "0.00",
                        "age_group" => "",
                        "publisher" => "Scholastic",
                        "isbns" => [
                        [
                            "isbn10" => "0590353421",
                            "isbn13" => "9780590353427"
                        ],
                        [
                            "isbn10" => "0439064872",
                            "isbn13" => "9780439064873"
                        ]
                        ]
                ]
                ]
            ], 200),
        ]);
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

    //Compound/Complex tests
    public function testFetchReturningNoResults() {
        Http::fake([
            'https://api.nytimes.com/svc/books/v3/lists/best-sellers/history.json*' => Http::response([
                'data' => [
                    "status" => "OK",
                    "copyright" => "Copyright (c) 2024 The New York Times Company.  All Rights Reserved.",
                    "num_results" => 0
                ]
            ])
        ], 200);
        $response = $this->json('GET', 'api/1/nyt/best-sellers', ['title' => "Buried Dreams"]);
        $response
            ->assertStatus(422);
    }

    public function testFetchReturningInvalidISBNs() {
        Http::fake([
            'https://api.nytimes.com/svc/books/v3/lists/best-sellers/history.json*' => Http::response([
                'data' => [
                    "status" => "OK",
                    "copyright" => "Copyright (c) 2024 The New York Times Company.  All Rights Reserved.",
                    "num_results" => 1,
                    "results" => [
                        [   "title" => "BURIED DREAMS",
                            "author" => "First Last",
                            "description" => "The ghoulish case of the serial killer John Wayne Gacy. First published in 1986.",
                            "contributor" => "by Tim Cahill",
                            "contributor_note" => "",
                            "price" => "0.00",
                            "age_group" => "",
                            "publisher" => "Premier Digital",
                            "isbns" => [
                            [
                                "isbn11" => "19379570525"
                            ]
                            ]
                        ]
                    ]
                ]
            ])
        ], 200);
        $response = $this->json('GET', 'api/1/nyt/best-sellers', ['title' => "Buried Dreams"]);
        $response
            ->assertStatus(422);
    }

    public function testFetchWithISBNAndTitle() {
        Http::fake([
            'https://api.nytimes.com/svc/books/v3/lists/best-sellers/history.json*' => Http::response([
                "status" => "OK",
                "copyright" => "Copyright (c) 2024 The New York Times Company.  All Rights Reserved.",
                "num_results" => 1,
                "results" => [
                    [
                        "title" => "HARRY POTTER",
                        "description" => "A wizard hones his conjuring skills in the service of fighting evil.",
                        "contributor" => "by J.K. Rowling",
                        "author" => "J.K. Rowling",
                        "contributor_note" => "",
                        "price" => "0.00",
                        "age_group" => "",
                        "publisher" => "Scholastic",
                        "isbns" => [
                        [
                            "isbn10" => "0590353421",
                            "isbn13" => "9780590353427"
                        ],
                        [
                            "isbn10" => "0439064872",
                            "isbn13" => "9780439064873"
                        ]
                        ]
                ]
                ]
            ], 200),
        ]);

        $requestedISBN = "9780590353427";
        $response = $this->json('GET', 'api/1/nyt/best-sellers', ['isbn' => "9780590353427", "title" => "harry potter"]);
        $response
            ->assertStatus(200);

        $response->assertJsonFragment([
            'num_results' => 1 
        ]);

        $response->assertJsonFragment([
            'title' => 'HARRY POTTER'
        ]);
        
        $decodedResponse = json_decode($response->getContent(), true);
        $isbnDictionary = [];

        foreach ($decodedResponse['results'][0]['isbns'] as $isbn) {
            if (isset($isbn['isbn13'])) {
                $isbnDictionary[$isbn['isbn13']] = 1;
            }
            if (isset($isbn['isbn10'])) {
                $isbnDictionary[$isbn['isbn10']] = 1;
            }
        }

        $this->assertArrayHasKey($requestedISBN, $isbnDictionary);
        $response->assertJsonCount(1, 'results');
    }

    public function testFetchWithTypoTitleAndISBN() {
        Http::fake([
            'https://api.nytimes.com/svc/books/v3/lists/best-sellers/history.json*' => Http::response(["status" => "OK", "results" => [], "num_results" => 0], 200),
        ]);
        $response = $this->json('GET', 'api/1/nyt/best-sellers', ['isbn' => "9780590353427", "title" => "hary potter"]);
        
        $response
        ->assertStatus(200)
        ->assertJson([
            'num_results' => 0,
            'results' => [],
        ]);
    }

    public function testFetchWithInvalidOffsetAndISBN() {
        Http::fake([
            'https://api.nytimes.com/svc/books/v3/lists/best-sellers/history.json*' => Http::response(["status" => "OK", "results" => [], "num_results" => 0], 200),
        ]);
        $response = $this->json('GET', 'api/1/nyt/best-sellers', ['offset' => -1, "title" => "hary potter"]);
        
        $response
        ->assertStatus(422);
    }

    public function testFetchWithHarryPotterButTooHighOffset() {
        Http::fake([
            'https://api.nytimes.com/svc/books/v3/lists/best-sellers/history.json*' => Http::response(["status" => "OK", "results" => [], "num_results" => 0], 200),
        ]);
        $response = $this->json('GET', 'api/1/nyt/best-sellers', ['offset' => 20, "title" => "harry potter"]);
        
        $response
        ->assertStatus(200)
        ->assertJson([
            'num_results' => 0,
            'results' => [],
        ]);
    }
}