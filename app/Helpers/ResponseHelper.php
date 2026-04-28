<?php

namespace App\Helpers;

class ResponseHelper
{
    public static function success($data = null, $message = 'Success', $code = 200, $statusValue = true)
    {
        return response()->json([
            'status' => $statusValue,
            'message' => $message,
            'data' => $data,
            'errors' => null
        ], $code);
    }

    public static function error(string $message = 'Error', $code = 400, $errors = null)
    {
        $validErrorCodes = [400, 401, 403, 404, 422, 500];
        $code = in_array($code, $validErrorCodes) ? $code : 400;

        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => null,
            'errors' => $errors
        ], $code);
    }

    public static function notFound(string $message = 'Data not found')
    {
        return self::error($message, 404);
    }

    public static function validationError($errors, string $message = 'Validation Error')
    {
        return self::error($message, 422, $errors);
    }

    public static function unauthorized(string $message = 'Unauthorized')
    {
        return self::error($message, 401);
    }

    public static function pagination($data, $resourceClass, string $message = 'Success', int $code = 200)
    {
        $code = in_array($code, [200, 201]) ? $code : 200;

        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $resourceClass::collection($data->items()),
            'errors' => null,
            'meta' => [
                'current_page' => $data->currentPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'last_page' => $data->lastPage(),
            ]
        ], $code);
    }
}
