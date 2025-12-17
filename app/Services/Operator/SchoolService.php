<?php

namespace App\Services\Operator;

use App\Contracts\Repositories\Operator\SchoolRepository;
use App\Traits\UploadTrait;
use App\Enums\UploadDiskEnum;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;

class SchoolService
{
    use UploadTrait;

    private SchoolRepository $schoolRepository;

    public function __construct(SchoolRepository $schoolRepository)
    {
        $this->schoolRepository = $schoolRepository;
    }

    public function get(): mixed
    {
        return $this->schoolRepository->get();
    }

    public function update(int|string $id, array $data): mixed
    {
        $school = $this->schoolRepository->show($id);

        if (
            isset($data['logo']) &&
            $data['logo'] instanceof UploadedFile
        ) {
            $data['logo'] = $this->upload(
                UploadDiskEnum::LOGO->value,
                $data['logo']
            );
        } else {
            $data = Arr::except($data, ['logo']);
        }

        $this->schoolRepository->update($id, $data);

        return $this->schoolRepository->show($id);
    }
}
