<?php

namespace App\Contracts\Interfaces;

use App\Contracts\Interfaces\Eloquent\DeleteInterface;
use App\Contracts\Interfaces\Eloquent\GetInterface;
use App\Contracts\Interfaces\Eloquent\PaginateInterface;
use App\Contracts\Interfaces\Eloquent\SearchInterface;
use App\Contracts\Interfaces\Eloquent\ShowInterface;
use App\Contracts\Interfaces\Eloquent\StoreInterface;
use App\Contracts\Interfaces\Eloquent\UpdateInterface;
use Illuminate\Http\Request;

interface StudentInterface extends GetInterface, StoreInterface, UpdateInterface, ShowInterface, DeleteInterface, PaginateInterface, SearchInterface
{
    public function search(Request $request): mixed;
    public function paginate(): mixed;
    // public function doesntHaveClassroom(Request $request): mixed;
    // public function whereClassroomStudent(mixed $id): mixed;
    public function getAllCategories(): mixed;
    public function filterByGender(string $gender): mixed;
    public function filterByMajor(string $major): mixed;
    public function filterByLevel(string $level): mixed;
}