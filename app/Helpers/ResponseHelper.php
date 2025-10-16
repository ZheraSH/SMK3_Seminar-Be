<?php

namespace App\Helpers;

class ResponseHelper
{
    public static function success($data = null, $message = 'Success', $code = 200)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    public static function error($message = 'Error', $code = 400, $errors = null)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'errors' => $errors,
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
