<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::apiResource('expenses', 'Api\ExpenseController');
Route::apiResource('categories', 'Api\CategoryController');

// GitHub Webhook for PR Review Bot
Route::post('/webhooks/github/pr', 'Api\WebhookController@handlePullRequest');

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
