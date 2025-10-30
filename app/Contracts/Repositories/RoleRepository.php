<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\RoleInterface;
use Spatie\Permission\Models\Role;

class RoleRepository extends BaseRepository implements RoleInterface
{
    public function __construct(Role $role)
    {
        $this->model = $role;
    }

    public function get(): mixed
    {
        return $this->model->query()->get();
    }

    public function store(array $data): mixed
    {
        return $this->model->query()->create($data);
    }

    public function show(mixed $id): mixed
    {
        return $this->model->query()->findOrFail($id);
    }

    public function update(mixed $id, array $data): mixed
    {
        return $this->show($id)->update($data);
    }

    public function delete(mixed $id): mixed
    {
        return $this->show($id)->delete();
    }
    
    private function getRoleLabel(string $roleName): string
    {
        $labels = [
            'school_operator' => 'Operator Sekolah',
            'student' => 'Siswa',
            'teacher' => 'Guru Pengajar',
            'staff_tu' => 'Staff TU',
            'homeroom_teacher' => 'Wali Kelas',
            'counselor' => 'Guru BK',
            'curriculum_coordinator' => 'Waka Kurikulum',
        ];

        return $labels[$roleName] ?? ucfirst(str_replace('_', ' ', $roleName));
    }
}