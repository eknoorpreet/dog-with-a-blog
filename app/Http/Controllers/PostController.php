<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class PostController extends Controller
{
    //
    public function showCreateForm()
    {
        return view('create-post');
    }

    public function createPost(Request $request)
    {
        $incomingFields = $request->validate(
            [
                'title' => 'required',
                'body' => 'required',
            ]
        );
        // Strip out potential malicious HTML tags in the fields
        $incomingFields['title'] = strip_tags($incomingFields['title']);
        $incomingFields['body'] = strip_tags($incomingFields['body']);
        $incomingFields['user_id'] = auth()->id();

        $newPost = Post::create($incomingFields);
        return redirect("/post/{$newPost->id}")->with('success', 'You have successfully created the post!');
    }

    // Laravel will automatically query the database through the lens of the Post model
    public function viewPost(Post $post)
    {
        $post['body'] = Str::markdown($post->body);
        return view('single-post', ['post' => $post]);
    }

    public function deletePost(Post $post, Request $request)
    {
        $post->delete();
        return redirect('/profile/' . auth()->user()->username)->with('success', 'Post successfully deleted!');
    }

    public function showEditForm(Post $post)
    {
        return view(
            'edit-post',
            ['post' => $post]
        );
    }

    public function updateForm(Post $post, Request $request)
    {
        $incomingFields = $request->validate(
            [
                'title' => 'required',
                'body' => 'required',
            ]
        );
        // Strip out potential malicious HTML tags in the fields
        $incomingFields['title'] = strip_tags($incomingFields['title']);
        $incomingFields['body'] = strip_tags($incomingFields['body']);

        $post->update($incomingFields);
        return redirect("/post/{$post->id}")->with('success', 'You have successfully updated the post!');
    }
}
