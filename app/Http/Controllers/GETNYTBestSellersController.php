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
        $responseJSON = $response->json();
        
        if ($response->successful()) {
            if ($this->validateResponseData($responseJSON)) {
                return $responseJSON;
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
        if (!isset($data['status']) || $data['status'] !== 'OK') {
            return false;
        }

        if (!isset($data['results']) || !is_array($data['results'])) {
            return false;
        }

        foreach ($data['results'] as $result) {
            if (empty($result['title']) || empty($result['author'])) {
                return false;
            }

            if (isset($result['isbns']) && is_array($result['isbns'])) {
                foreach ($result['isbns'] as $isbn) {
                    if (!isset($isbn['isbn10']) && !isset($isbn['isbn13'])) {
                        return false;
                    }
                }
            }
        }
    return true;
    }
}
