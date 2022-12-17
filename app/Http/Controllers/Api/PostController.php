<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Post;
use App\Models\Media;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostDetailResource;
use App\Http\Resources\PostResource;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::with('user', 'category', 'image')->orderByDesc('created_at');
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->search) {
            $query->where(function ($q1) use ($request) {
                $q1->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }
        $posts = $query->paginate(10);
        return PostResource::collection($posts)->additional(['message' => 'success']);
    }
    public function create(Request $request)
    {
        // Validate
        $request->validate(
            [
                'title' => 'required|string',
                'description' => 'required|string',
                'category_id' => 'required',
                'user_id' => 'required',
            ],
            [
                'category_id.required' => 'The category field is required',
                'user_id.required' => 'The User field is required'
            ]
        );
        // Use Database Transaction
        DB::beginTransaction();
        try {
            // Check file include
            $file_name = null;
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $file_name = uniqid() . '-' . date('Y-m-d-H-i-s') . '.' . $file->getClientOriginalExtension();
                // Save Image Project
                Storage::put('media/' . $file_name, file_get_contents($file));
            }
            // Add Post
            $post = new Post();
            $post->title = $request->title;
            $post->description = $request->description;
            $post->user_id = $request->user_id;
            $post->category_id = $request->category_id;
            $post->save();

            if ($request->hasFile('image')) {
                // Add Media Database
                $media = new Media();
                $media->file_name = $file_name;
                $media->file_type = 'image';
                $media->model_id = $post->id;
                $media->model_type = Post::class;
                $media->save();
            }
            DB::commit();
            return ResponseHelper::success([], 'Post add success');
        } catch (Exception $e) {
            DB::rollBack();
            return ResponseHelper::fail($e->getMessage());
        }
    }

    public function show( $id){
        $post = Post::with('user', 'category', 'image')->where('id',$id)->firstOrFail();
        return ResponseHelper::success(new PostDetailResource($post));
    }
}
