<?php

namespace App\Http\Controllers\API;

use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuthController extends BaseController
{

    public function signin(Request $request)
    {
        if ($request->username) {
            $validator = Validator::make($request->all(), [
                'username' => 'required|exists:users',
                'password' => 'required'
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }

            if (Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
                $user = Auth::user();

                $token = $user->createToken($user->username . '-' . now());

                return $this->sendResponse([
                    'user' => $user,
                    'token' => $token->accessToken
                ], "");
            } else {
                return $this->sendError('Invalid username or password.', [], 403);
            }
        } else {
            $validator = Validator::make($request->all(), [
                'email' => 'required|exists:users',
                'password' => 'required'
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }

            if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                $user = Auth::user();

                $token = $user->createToken($user->username . '-' . now());

                return $this->sendResponse([
                    'user' => $user,
                    'token' => $token->accessToken
                ], "");
            } else {
                return $this->sendError('Invalid email or password.', [], 403);
            }
        }
    }

    public function signup(Request $request)
    {
        try {
            $input = $request->all();

            $validator = Validator::make($input, [
                'name' => 'required',
                'email' => 'required|email',
                'username' => 'required'
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }

            $password = Str::random(8);
            $request->request->add(['password' => bcrypt($password)]);

            $user = User::create($request->all());

            return $this->sendResponse([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'password' => $password,
                'created_at' => $user->created_at,
                'admin' => $user->admin,
                'active' => $user->active
            ], "User created successfully", 201);
        } catch (Exception $e) {
            return $this->sendError("", $e->getMessage(), 500);
        }
    }
}
