<?php

namespace App\Helpers;

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
}
