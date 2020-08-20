<?php

namespace App\Http\Models;

class JsonResponse
{
    public array   $data;
    public array   $errors;
    public array   $meta;
    private int    $statusCode;

    public function __construct($statusCode, array $data = [], array $errors = [], array $meta = []) {
        $this->statusCode = $statusCode;
        $this->data = $data;
        $this->errors = $errors;
        $this->meta = $meta;
    }

}
