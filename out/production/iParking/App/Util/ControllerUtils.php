<?php

namespace App\Util;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ControllerUtils
{
    public static function getRequestData(Request $request)
    {
        $body = $request->getContent();

        return json_decode((string) $body, true);
    }

    public static function errorResponse(string $message, int $status): JsonResponse
    {
        return new JsonResponse(['Message' => $message], $status);
    }
}
