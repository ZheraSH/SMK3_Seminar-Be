<?php

namespace App\Traits\Migrations;

use App\Enums\GenderEnum;
use Illuminate\Database\Schema\Blueprint;

trait HasGender
{
    /**
     * Adds a foreign key constraint with cascade on delete and update.
     *
     * @param Blueprint $table
     * @param string $column
     * @param string $foreignTable
     * @return void
     */
    public function addGender(Blueprint $table): void
    {
        $table->enum('gender', [GenderEnum::MALE->value, GenderEnum::FEMALE->value]);
    }
}
