<?php

namespace App\Traits;


trait ApiResponse
{

    protected function success($data, $message = null, int $code = 200)
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message
        ], $code);
    }
    protected function error($data, $message = null, int $code = 422)
    {
        return response()->json([
            'success' => false,
            'data' => $data,
            'message' => $message
        ], $code);
    }
}
