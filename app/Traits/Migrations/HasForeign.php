<?php

namespace App\Traits\Migrations;

use Illuminate\Database\Schema\Blueprint;

trait HasForeign
{
    /**
     * Adds a foreign key constraint with cascade on delete and update.
     *
     * @param Blueprint $table
     * @param string $column
     * @param string $foreignTable
     * @return void
     */
    public function addForeignId(Blueprint $table, string $column): void
    {
        $table->foreignId($column)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
    }

        /**
     * Adds a foreign key constraint with cascade on delete and update.
     *
     * @param Blueprint $table
     * @param string $column
     * @param string $foreignTable
     * @return void
     */
    public function addForeignIdNull(Blueprint $table, string $column): void
    {
        $table->foreignId($column)->nullable()->constrained()->cascadeOnDelete()->cascadeOnUpdate();
    }

        /**
     * Adds a foreign key constraint with cascade on delete and update.
     *
     * @param Blueprint $table
     * @param string $column
     * @param string $foreignTable
     * @return void
     */
    public function addForeignIdTo(Blueprint $table, string $column, string $table2): void
    {
        $table->foreignId($column)->constrained($table2)->cascadeOnDelete()->cascadeOnUpdate();
    }

        /**
     * Adds a foreign key constraint with cascade on delete and update.
     *
     * @param Blueprint $table
     * @param string $column
     * @param string $foreignTable
     * @return void
     */
    public function addForeignUuid(Blueprint $table, string $column): void
    {
        $table->foreignUuid($column)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
    }
}
