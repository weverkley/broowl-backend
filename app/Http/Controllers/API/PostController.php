<?php

namespace App\Http\Controllers\API;

use App\Post;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Poll;
use Google\Cloud\Storage\StorageClient;
use Validator;

class PostController extends BaseController
{

    public function index()
    {
        return $this->sendResponse(Post::with('polls')->get());
    }

    public function show($id)
    {
        return $this->sendResponse(Post::find($id));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => ['required', 'string'],
            'channel_id' => ['required', 'numeric'],
            'user_id' => ['required', 'numeric']
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }


        try {
            $url = null;

            if ($request->type == 'IMAGE' || $request->type == 'VIDEO') {
                $folder = $request->type == 'IMAGE' ? 'images' : 'videos';
                $id = $request->type == 'IMAGE' ? 'img_' : 'vid_';
                $id .= uniqid();
                $storage = new StorageClient();
                $bucket = $storage->bucket('broowl-dev');
                $extension = explode('/', $request->mimetype)[1];
                $data = explode(',', $request->image);
                $url = $request->user_id . '/' . $folder . '/channel/' . $request->channel_id . '/' . $id . '.' . $extension;
                $object = $bucket->upload(
                    base64_decode($data[1]),
                    [
                        'name' => $url,
                        'predefinedAcl' => 'publicRead',
                    ]
                );
                // $data = $object->info();
                $request->request->add(['url' => 'https://storage.googleapis.com/broowl-dev/' . $url]);
            } else if ($request->type == 'POLL' || $request->type == 'TEXT') {
                $request->request->add(['url' => null]);
            } else {
                return $this->sendError("You must post one valid type.");
            }

            $post = Post::create($request->all());

            if ($request->type == 'POLL') {
                $options = $request->options;
                foreach ($options as $k => $v) {
                    Poll::create(['post_id' => $post['id'], 'title' => $v['title']]);
                }
                return $this->sendResponse($post, "Poll created successfully", 201);
            } else {
                return $this->sendResponse($post, "Post created successfully", 201);
            }
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
