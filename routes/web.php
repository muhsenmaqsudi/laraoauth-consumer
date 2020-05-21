<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Http\Request;

Route::get('/', function () {
    $query = http_build_query([
        'client_id' => env('LARAOAUTH_CLIENT_ID'), // Replace with Client ID
        'redirect_uri' => env('LARAOAUTH_REDIRECT_URI'),
        'response_type' => 'code',
        'scope' => ''
    ]);

    return redirect('http://127.0.0.1:8000/oauth/authorize?' . $query);
});

Route::get('/callback', function (Request $request) {
    $response = (new GuzzleHttp\Client)->post('http://127.0.0.1:8000/oauth/token', [
        'form_params' => [
            'grant_type' => 'authorization_code',
            'client_id' => env('LARAOAUTH_CLIENT_ID'), // Replace with Client ID
            'client_secret' => env('LARAOAUTH_CLIENT_SECRET'), // Replace with client secret
            'redirect_uri' => 'http://127.0.0.1:8001/callback',
            'code' => $request->code,
        ]
    ]);

    session()->put('token', json_decode((string)$response->getBody(), true));

    return redirect('/todos');
});

Route::get('/todos', function () {
    $response = (new GuzzleHttp\Client)->get('http://127.0.0.1:8000/api/todos', [
        'headers' => [
            'Authorization' => 'Bearer ' . session()->get('token.access_token')
        ]
    ]);

    return json_decode((string)$response->getBody(), true);
});
