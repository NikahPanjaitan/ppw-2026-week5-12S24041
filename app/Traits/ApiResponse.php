<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

// Trait pembungkus respon JSON biar seragam sesuai standar modul W9 (REST + RFC 7807)
trait ApiResponse
{
    // Respon sukses standar (200 OK)
    protected function successResponse(
        mixed $data = null,
        string $message = 'Permintaan berhasil diproses.',
        int $status = 200,
        array $meta = []
    ): JsonResponse {
        $payload = [
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ];

        if (!empty($meta)) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $status);
    }

    // Respon sukses create resource baru (201 Created + Header Location)
    protected function createdResponse(
        mixed $data,
        string $locationUrl,
        string $message = 'Resource baru berhasil dibuat.'
    ): JsonResponse {
        return $this->successResponse($data, $message, 201)
            ->header('Location', $locationUrl);
    }

    // Respon sukses hapus tanpa body (204 No Content)
    protected function noContentResponse(): JsonResponse
    {
        return response()->json(null, 204);
    }

    // Respon error terstandar IETF RFC 7807 (Problem Details for HTTP APIs)
    protected function problemResponse(
        string $title,
        string $detail,
        int $status,
        string $errorSlug = 'general-error',
        array $errors = []
    ): JsonResponse {
        $problem = [
            'type'     => url("/docs/errors/{$errorSlug}"),
            'title'    => $title,
            'status'   => $status,
            'detail'   => $detail,
            'instance' => request()->getRequestUri(),
        ];

        if (!empty($errors)) {
            $problem['errors'] = $errors;
        }

        return response()
            ->json($problem, $status)
            ->header('Content-Type', 'application/problem+json');
    }
}
