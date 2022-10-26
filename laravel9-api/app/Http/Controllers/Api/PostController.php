<?php

namespace App\Http\Controllers\Api;

use App\Models\Post;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\PostResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function index()
    {
        // get posts
        $posts = Post::latest()->paginate(5);

        // return collection of posts as a resource
        return new PostResource(true, 'List Data Posts', $posts);
    }


    public function store(Request $request)
    {
        //define validation rules
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'title' => 'required',
            'content' => 'required',
        ]);

        // check if validation fails
        if ($validator->fails()){
            return response()->json($validator->errors(), 442);
        }

        //upload image
        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());


        //create post
        $post = Post::create([
            'image' => $image->hashName(),
            'title' => $request->title,
            'content' => $request->content,
        ]);

      //return response
      return new PostResource(true, 'Data Post Berhasil Ditambahkan', $post);
    }

    public function show(Post $post)
    {
    //return single post a resource
        return new PostResource(true, 'Data Post Ditemukan!', $post);
    }

    public function update(Request $request, Post $post)
    {

        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'content' => 'required',
        ]);


        if ($validator->fails()){
            return response()->json($validator->errors(), 442);
        }

        if ($request->hasFile('image')){

            $image = $request->file('image');
            $image->storeAs('public/posts', $image->hashName());
        
            Storage::delete('public/posts/'.$post->image);

            //update post with image
        $post->update([
            'image'    => $image->hashName(),
            'title'    => $request->title,
            'content'  => $request->content,
        ]);
        
        } else {

            //update post without image
            $post->update([
                'title'   => $request->title,
                'content' => $request->content,
            ]);
        }

        return new PostResource(true, 'Data Berhasil Dirubah!', $post);
    }

    public function destroy(Post $post)
    {
        //delete image
        Storage::delete('public/posts/'.$post->image);

        //delete post
        $post->delete();

        //return response
        return new PostResource(true, 'Data Post Berhasil Dihapus', null);
    }
}
