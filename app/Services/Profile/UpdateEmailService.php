<?php

namespace App\Services\Profile;

use App\Contracts\Repositories\UserRepository;
use App\Http\Requests\Profile\UpdateEmailRequest;
use Illuminate\Support\Facades\Auth;

class UpdateEmailService
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function execute(UpdateEmailRequest $request): void
    {
        $user = Auth::user();

        if (!$user) {
            throw new \Exception('User tidak terautentikasi', 401);
        }

        $this->userRepository->update($user->id, [
            'email' => $request->email,
        ]);
    }
}
