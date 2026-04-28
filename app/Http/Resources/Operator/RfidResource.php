<?php

namespace App\Http\Resources\Operator;

use App\Traits\Resources\HasEnumLabelsTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class RfidResource extends JsonResource
{
    use HasEnumLabelsTrait;
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'rfid' => $this->rfid,
            'status' => [
                'value' => $this->getEnumValue($this->status),
                'label' => $this->getEnumLabel($this->status),
            ],
            'classroom' => $this->classroom,
            'name' => $this->name,
            'type' => $this->type,
        ];
    }
}