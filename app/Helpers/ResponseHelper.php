<?php

namespace App\Helpers;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ResponseHelper
{
    public static function success($data = null, $message = 'Success', int $code = 200)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    public static function error($errors = null, $message = 'Error', int $code = 400)
    {
        return response()->json([
            'status' => false,
            'errors' => $errors,
            'message' => $message,
            'data' => null
        ], $code);
    }

    public static function notFound($message = 'Data not found')
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => null
        ], 404);
    }

    public static function pagination($data, $resourceClass, $message = 'Success')
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