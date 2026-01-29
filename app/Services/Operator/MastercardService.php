<?php

namespace App\Services\Operator;

use App\Contracts\Repositories\Operator\MastercardRepository;
use App\Http\Requests\Operator\StoreMastercardRequest;
use App\Http\Requests\Operator\UpdateMastercardRequest;
use Illuminate\Http\Request;

class MastercardService
{
    private MastercardRepository $mastercardRepository;

    public function __construct(MastercardRepository $mastercardRepository)
    {
        $this->mastercardRepository = $mastercardRepository;
    }

    public function getWithFilter(Request $request)
    {
        return $this->mastercardRepository->search($request);
    }

    public function store(StoreMastercardRequest $request)
    {
        $data = $request->validated();
        return $this->mastercardRepository->store($data);
    }

    public function show(string $id)
    {
        return $this->mastercardRepository->show($id);
    }

    public function update(UpdateMastercardRequest $request, string $id)
    {
        $data = $request->validated();
        return $this->mastercardRepository->update($id, $data);
    }

    public function delete(string $id)
    {
        return $this->mastercardRepository->delete($id);
    }

    public function checkRfid(string $rfid): bool
    {
        $mastercard = $this->mastercardRepository->findByRfid($rfid);
        return $mastercard !== null;
    }
}
