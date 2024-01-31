<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use App\Http\Requests\GETNYTBestSellersRequest;

class GETNYTBestSellersController extends Controller {

    public function fetch(GETNYTBestSellersRequest $request) {
        $nytQuery = [
            'api-key' => env('NYT_API_KEY')
        ];

        $nytQuery["author"] = $request->input('author');
        $nytQuery["isbn"] = $request->input('isbn');
        $nytQuery["title"] = $request->input('title');
        $nytQuery["offset"] = $request->input('offset');

        $response = Http::get('https://api.nytimes.com/svc/books/v3/lists/best-sellers/history.json', $nytQuery);
        if ($response->successful()) {
            if ($this->validateResponseData($response)) {
                return response()->json($response->json());
            } else {
                return response()->json(['error' => 'Invalid response data'], 422);
            }
        } else if ($response->status() === 429) {
            return response()->json(['error' => 'Rate limit exceeded.'], 429);
        } else {
            return response()->json(['error' => 'Failed to fetch data from NYT API'], 500);
        }
    }

    protected function validateResponseData($data) {
        print('37: =-------------------------------- ');
        // print($data);
        // Check if status is "OK"
        if (!isset($data['status']) || $data['status'] !== 'OK') {
            print('41: =-------------------------------- ');
            return false;
        }

        // Check if results array exists and is not empty
        if (!isset($data['results']) || !is_array($data['results']) || empty($data['results'])) {
            print('47: =-------------------------------- ');
            return false;
        }

        foreach ($data['results'] as $result) {
            // Check for non-empty title, author, and publisher
            if (empty($result['title']) || empty($result['author'])) {
                print('54: =-------------------------------- ' . $result['title'] . ' | ' . $result['author']);
                return false;
            }

            // If isbns array is present, validate it
            if (isset($result['isbns']) && is_array($result['isbns'])) {
                foreach ($result['isbns'] as $isbn) {
                    if (!isset($isbn['isbn10']) && !isset($isbn['isbn13'])) {
                        print('62: =-------------------------------- ');
                        return false;
                    }
                    // if (isset($isbn['isbn10']) && !preg_match('/^\d{10}$/', $isbn['isbn10'])) {
                    //     print('66: =-------------------------------- ');
                    //     return false;
                    // }
                    // if (isset($isbn['isbn13']) && !preg_match('/^\d{13}$/', $isbn['isbn13'])) {
                    //     print('70: =-------------------------------- ');
                    //     return false;
                    // }
                }
            }
        }

    return true;
    }
}
