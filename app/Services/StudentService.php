<?php

namespace App\Services;

use App\Helpers\CrudHelper;
use App\Models\Student;
use App\Models\User;
use App\Services\Contracts\StudentServiceInterface;
use Illuminate\Support\Str;

class StudentService implements StudentServiceInterface
{
    public function listAll(): object
    {
        return Student::with(['user', 'religion'])->get();
    }

    public function createWithUser(array $payload): Student
    {
        $user = User::create([
            'name' => $payload['nama'],
            'email' => Str::slug($payload['nama']) . rand(100, 999) . '@gmail.com',
            'password' => bcrypt('password123'),
        ]);

        if (method_exists($user, 'assignRole')) {
            $user->assignRole('siswa');
        }

        $student = Student::create([
            'user_id' => $user->id,
            'nisn' => $payload['nisn'],
            'religion_id' => $payload['religion_id'] ?? null,
            'birthdate' => $payload['birthdate'] ?? null,
            'birthplace' => $payload['birthplace'] ?? null,
            'address' => $payload['address'] ?? null,
            'nik' => $payload['nik'] ?? null,
            'no_kk' => $payload['no_kk'] ?? null,
            'no_birth_certificate' => $payload['no_birth_certificate'] ?? null,
            'order_child' => $payload['order_child'] ?? null,
            'count_siblings' => $payload['count_siblings'] ?? null,
            'point' => $payload['point'] ?? 0,
            'class' => $payload['kelas'] ?? null,
            'major' => $payload['jurusan'] ?? null,
        ]);

        return $student->load(['user', 'religion']);
    }

    public function findById(int $studentId): ?Student
    {
        return Student::with(['user', 'religion'])->find($studentId);
    }

    public function update(Student $student, array $payload): Student
    {
        $updated = CrudHelper::update($student, $payload);
        return $updated->load(['user', 'religion']);
    }

    public function delete(Student $student): bool
    {
        return CrudHelper::delete($student);
    }
}


