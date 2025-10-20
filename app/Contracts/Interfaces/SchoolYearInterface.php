<?php

namespace App\Contracts\Interfaces;

interface SchoolYearInterface
{
    public function all();
    public function find($id);
    public function store(array $data);
    public function update($model, array $data);
    public function delete($model);
    public function restore($id);
}
