<?php

namespace App\Http\Resources\Operator;

use Illuminate\Http\Resources\Json\JsonResource;

class LogStudentTapResource extends JsonResource
{
    public function toArray($request): array
    {
        $results = $this->resource;

        return [
            'status'    => 'success',
            'message'   => 'Data absensi berhasil diupload',
            'summary'   => [
                'total'     => $results['total'] ?? 0,
                'saved'     => $results['saved'] ?? 0,
                'skipped'   => $results['skipped'] ?? 0,
            ],
            'details'   => $results['details'] ?? [],
        ];
    }
}
