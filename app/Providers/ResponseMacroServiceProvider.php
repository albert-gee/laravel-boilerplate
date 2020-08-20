<?php

namespace App\Providers;

use App\Http\Models\JsonResponse;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;

class ResponseMacroServiceProvider extends ServiceProvider
{
    /**
     * Register the application's response macros.
     *
     * @return void
     */
    public function boot()
    {
        Response::macro('errorJson', function (int $statusCode, array $data = [], array $errors = [], array $meta = []) {
            return response()->json(new JsonResponse($statusCode, $data, $errors, $meta), $statusCode);
        });
    }
}
