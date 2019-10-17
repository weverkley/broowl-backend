<?php

namespace App\Http\Controllers\API;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Google\Cloud\Storage\StorageClient;
use Validator;

class UserController extends BaseController
{

    public function index()
    {
        return $this->sendResponse(User::all());
    }

    public function show($id)
    {
        return $this->sendResponse(User::with('channels')->find($id));
    }

    public function store(Request $request)
    {
        try {
            $user = User::create($request->all());

            return $this->sendResponse($user, "User created successfully", 201);
        } catch (Exception $e) {
            return $this->sendError("", $e->getMessage(), 500);
        }
    }

    public function update(Request $request, $id)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'name' => 'required',
            'email' => 'required',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user = User::find($id);
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->password = $request->input('password');

        $user->save();

        return $this->sendResponse($user, "User updated successfully", 200);
    }

    public function delete($id)
    {
        $user = User::find($id);
        $user->delete();

        return $this->sendResponse(null, "User deleted successfully", 204);
    }

    public function uploadProfile(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'user_id' => 'required',
            'image' => 'required',
            'mimetype' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user = User::find($request->user_id);

        if (!$user) {
            return $this->sendError("User not found", [], 404);
        }

        try {
            $storage = new StorageClient();

            $bucket = $storage->bucket('broowl-dev');

            $extension = explode('/', $request->mimetype)[1];

            $data = explode(',', $request->image);
            $object = $bucket->upload(
                base64_decode($data[1]),
                [
                    'name' => $user->id . '/images/profile.' . $extension,
                    'predefinedAcl' => 'publicRead',
                ]
            );
            return $this->sendResponse($object->info(), "File uploaded successfully", 200);
        } catch (Exception $e) {
            return $this->sendError("", $e->getMessage(), 500);
        }
    }
}

// 200: OK. The standard success code and default option.
// 201: Object created. Useful for the store actions.
// 204: No content. When an action was executed successfully, but there is no content to return.
// 206: Partial content. Useful when you have to return a paginated list of resources.
// 400: Bad request. The standard option for requests that fail to pass validation.
// 401: Unauthorized. The user needs to be authenticated.
// 403: Forbidden. The user is authenticated, but does not have the permissions to perform an action.
// 404: Not found. This will be returned automatically by Laravel when the resource is not found.
// 500: Internal server error. Ideally you're not going to be explicitly returning this, but if something unexpected breaks, this is what your user is going to receive.
// 503: Service unavailable. Pretty self explanatory, but also another code that is not going to be returned explicitly by the application.
