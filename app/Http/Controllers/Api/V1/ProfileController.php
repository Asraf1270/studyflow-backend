<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\ChangePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProfileController extends Controller
{
    public function show(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    public function update(UpdateProfileRequest $request): UserResource
    {
        $data = $request->validated();

        if (isset($data['email'])) {
            $data['email'] = strtolower($data['email']);
        }

        $request->user()->update($data);

        return new UserResource($request->user()->fresh());
    }

    public function changePassword(ChangePasswordRequest $request): Response
    {
        $request->user()->update([
            'password' => $request->validated('password'), // hashed by cast
        ]);

        return response()->noContent();
    }
}