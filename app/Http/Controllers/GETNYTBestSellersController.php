<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GETNYTBestSellersController extends Controller {

    public function fetch($author = "", $isbn = "", $title = "", $offset = 0) {

        $nytQuery = [
            'api-key' => 'your-nyt-api-key'
        ];

        $nytQuery[$author] = $author;
        $nytQuery[$isbn] = $isbn;
        $nytQuery[$title] = $title;
        $nytQuery[$offset] = $offset;

        $response = Http::get('https://api.nytimes.com/svc/books/v3/lists/best-sellers/history.json', nytQuery);

        if ($response->successful()) {
            return response()->json($response->json());
        } else {
            return response()->json(['error' => 'Failed to fetch data from NYT API'], 500);
        }
    }
}
