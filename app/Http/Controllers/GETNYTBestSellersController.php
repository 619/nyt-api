<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GETNYTBestSellersController extends Controller
{
    
    public function index(Request $request) {
        // Implement the logic to call the NYT API and return the response
    }

    public function fetch($author = "", $isbn = "", $title = "", $offset = 0) {
        $response = Http::get('https://api.example.com/1/nyt/best-sellers');
    }


}
