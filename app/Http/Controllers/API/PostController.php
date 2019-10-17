<?php

namespace App\Http\Controllers\API;

use App\Post;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Validator;

class PostController extends BaseController
{

    public function index()
    {
        return $this->sendResponse(Post::all());
    }

    public function show($id)
    {
        return $this->sendResponse(Post::find($id));
    }

    public function store(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'content' => 'required',
            'channel_id' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        try {
            $post = Post::create($request->all());

            return $this->sendResponse($post, "Post created successfully", 201);
        } catch (Exception $e) {
            return $this->sendError("", $e->getMessage(), 500);
        }
    }

    public function update(Request $request, $id)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'content' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $post = Post::find($id);
        $post->content = $request->input('content');

        $post->save();

        return $this->sendResponse($post, "Post updated successfully", 200);
    }

    public function delete($id)
    {
        $post = Post::find($id);
        $post->delete();

        return $this->sendResponse(null, "Post deleted successfully", 204);
    }
}
