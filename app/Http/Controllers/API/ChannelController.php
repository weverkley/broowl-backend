<?php

namespace App\Http\Controllers\API;

use App\Channel;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Str;
use Google\Cloud\Storage\StorageClient;
use \Validator;

class ChannelController extends BaseController
{
    public function index()
    {
        return $this->sendResponse(Channel::all());
    }

    public function show($id)
    {
        return $this->sendResponse(Channel::find($id));
    }

    public function store(Request $request)
    {
        try {
            $input = $request->all();

            $validator = Validator::make($input, [
                'user_id' => 'required|exists:users',
                'name' => 'required'
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }

            $channel = Channel::create($request->all());

            return $this->sendResponse($channel, "Channel created successfully", 201);
        } catch (Exception $e) {
            return $this->sendError("", $e->getMessage(), 500);
        }
    }

    public function update(Request $request, $id)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'user_id' => 'required',
            'name' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $channel = Channel::find($id);
        $channel->name = $request->input('name');
        $channel->email = $request->input('email');
        $channel->website = $request->input('website');
        $channel->description = $request->input('description');
        $channel->phone_number = $request->input('phone_number');
        $channel->description = $request->input('description');

        $channel->save();

        return $this->sendResponse($channel, "Channel updated successfully", 200);
    }

    public function delete($id)
    {
        $channel = Channel::find($id);
        $channel->delete();

        return $this->sendResponse(null, "Channel deleted successfully", 204);
    }

    public function getPosts($id)
    {
        return $this->sendResponse(Channel::with('posts')->find($id));
    }

    public function uploadCover(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'channel_id' => 'required',
            'image' => 'required',
            'mimetype' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $channel = Channel::find($request->channel_id);

        if (!$channel) {
            return $this->sendError("Channel not found", [], 404);
        }

        try {
            $storage = new StorageClient();

            $bucket = $storage->bucket('broowl-dev');

            $extension = explode('/', $request->mimetype)[1];

            $data = explode(',', $request->image);
            $object = $bucket->upload(
                base64_decode($data[1]),
                [
                    'name' => $channel->id . '/images/channel/'.$channel->id.'/cover.' . $extension,
                    'predefinedAcl' => 'publicRead',
                ]
            );
            return $this->sendResponse($object->info(), "File uploaded successfully", 200);
        } catch (Exception $e) {
            return $this->sendError("", $e->getMessage(), 500);
        }
    }

    public function uploadTest(Request $request)
    {
        try {
            $storage = new StorageClient([
                // 'keyFile' => json_decode(file_get_contents(base_path().'/Broowl-32bb419f956e.json'), true)
            ]);

            $bucket = $storage->bucket('broowl-dev');

            // Using Predefined ACLs to manage object permissions, you may
            // upload a file and give read access to anyone with the URL.
            $object = $bucket->upload(
                fopen(base_path() . '/google-credentials.json', 'r'),
                [
                    'name' => 'images/google-credentials.json',
                    'predefinedAcl' => 'publicRead',
                ]
            );
            return $this->sendResponse($object->info(), "File uploaded successfully", 200);
        } catch (Exception $e) {
            return $this->sendError("", $e->getMessage(), 500);
        }
    }
}
