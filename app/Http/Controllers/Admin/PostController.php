<?php

namespace App\Http\Controllers\Admin;

use App\Post;
use App\Category;
use App\Tag;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $posts = Post::all();
        return view('admin.posts.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        $categories = Category::all();
        $tags = Tag::all();
        return view('admin.posts.create', compact(['categories', 'tags']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'title' => 'required|min:2|max:255',
            'content' => 'required',
            'category_id' => 'nullable|exists:categories,id',
            'tags' => 'exists:tags,id'
        ]);

        $data = $request->all();
        $post = new Post();
        $post->fill($data);

        $slug = Str::slug($post->title);
        $slug_base = $slug;
        $counter = 1;
        $existingPost = Post::where('slug', $slug)->first();
        while($existingPost){
            $slug = $slug_base . '_' . $counter;
            $counter++;
            $existingPost = Post::where('slug', $slug)->first();
        }

        $post->slug = $slug;
        $post->save();

        if(array_key_exists('tags', $data)){
            $post->tags()->sync($data['tags']);
        }

        return redirect()->route('admin.posts.show', $post->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function show(Post $post)
    {
        //
        return view('admin.posts.show', compact('post'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function edit(Post $post)
    {
        //
        $categories = Category::all();
        $tags = Tag::all();
        return view('admin.posts.edit', compact(['post', 'categories', 'tags']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Post $post)
    {
        //
        $request->validate([
            'title' => 'required|min:2|max:255',
            'content' => 'required',
            'category_id' => 'nullable|exists:categories,id'
        ]);

        $data = $request->all();
        if($post->title != $data['title']){
            $slug = Str::slug($post->title);
            $slug_base = $slug;
            $counter = 1;
            $existingPost = Post::where('slug', $slug)->first();
            while($existingPost){
                $slug = $slug_base . '_' . $counter;
                $counter++;
                $existingPost = Post::where('slug', $slug)->first();
            }

            $data['slug'] = $slug;
        }

        if(array_key_exists('tags', $data)){
            $post->tags()->sync($data['tags']);
        }else{
            $post->tags()->sync([]);
        }

        $post->update($data);
        return redirect()->route('admin.posts.show', $post);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $post)
    {
        //
        $post->tags()->sync([]);
        $post->delete();
        return redirect()->route('admin.posts.index');
    }
}
