<?php

namespace App\Services\Contracts;

use App\Models\Student;

interface StudentServiceInterface
{
    /** @return \Illuminate\Database\Eloquent\Collection<Student> */
    public function listAll(): object;

    public function createWithUser(array $payload): Student;

    public function findById(int $studentId): ?Student;

    public function update(Student $student, array $payload): Student;

    public function delete(Student $student): bool;
}


