<?php

namespace App\Helpers;

class ResponseHelper
{
    public static function success($data = null, string $message = 'Success', int $code = 200)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    public static function error(int $code = 400, string $message = 'Error', $errors = null)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'errors' => $errors,
            'data' => null,
        ], $code);
    }

    public static function notFound(string $message = 'Data not found')
    {
        return self::error($message, 404);
    }

    public static function pagination($data, $resourceClass, string $message = 'Success')
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $resourceClass::collection($data->items()),
            'meta' => [
                'current_page' => $data->currentPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'last_page' => $data->lastPage(),
            ]
        ]);
    }
}