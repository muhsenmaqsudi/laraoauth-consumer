<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

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


Route::get('/', function (Request $request) {
    \Illuminate\Support\Facades\Cookie::queue('state', $state = \Illuminate\Support\Str::random(40), 1);

    $query = http_build_query([
        'client_id' => env('LARAOAUTH_CLIENT_ID'),
        'redirect_uri' => env('LARAOAUTH_REDIRECT_URI'),
        'response_type' => 'code',
        'scope' => '',
        'state' => $state
    ]);

    return redirect('http://127.0.0.1:8000/oauth/authorize?' . $query);
});



Route::get('/callback', function (Request $request) {
    $state = \Illuminate\Support\Facades\Cookie::get('state');

    throw_unless(
        strlen($state) > 0 && $state === $request->state,
        InvalidArgumentException::class
    );

    $http = new GuzzleHttp\Client;

    $response = $http->post('http://127.0.0.1:8000/oauth/token', [
        'form_params' => [
            'grant_type' => 'authorization_code',
            'client_id' => env('LARAOAUTH_CLIENT_ID'),
            'client_secret' => env('LARAOAUTH_CLIENT_SECRET'),
            'redirect_uri' => env('LARAOAUTH_REDIRECT_URI'),
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
