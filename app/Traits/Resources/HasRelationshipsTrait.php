<?php

namespace App\Traits\Resources;

trait HasRelationshipsTrait
{
    protected function getRelatedName($relation, string $field = 'name'): ?string
    {
        return $this->whenLoaded($relation, function () use ($relation, $field) {
            return $this->{$relation}?->{$field};
        });
    }

    protected function getRelatedId($relation): ?string
    {
        return $this->whenLoaded($relation, function () use ($relation) {
            return $this->{$relation}?->id;
        });
    }
}
