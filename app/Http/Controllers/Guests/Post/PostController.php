<?php

namespace App\Http\Controllers\Guests\Post;

use App\Http\Controllers\Controller;
use App\Models\Post\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\File;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Render the view
        return Inertia::render("Guests/Posts/Index", [
            "posts" => null,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $slug The slug of the post.
     * @return \Inertia\Response A rendered view with post data or an error view if needed.
     */
    public function show(Post $post, $slug)
    {
        // controller not in use
        // The view that will be rendered
        $postRenderView = "Guests/Posts/Show";

        // Decoding the slug parameter
        $slug = urldecode($slug);

        $author = null;

        // Check if the post has an author and the author ID is not null
        if ($post->show_author === 1 && $post->author_id !== null) {
            $author = User::select("first_name", "last_name", "username")->find(
                $post->author_id
            );
        }

        // If the post is unpublished and the user is not authenticated, return a 404 error
        if ($post->published === 0 && !Auth::check()) {
            return Inertia::render("Error", [
                "customError" => "Please try another route.", // Error message for the user.
                "status" => 404, // HTTP status code for the response.
            ]);
        }

        // if ($posts->first() && $posts->first()->author !== null) {
        //     $posts->makeHidden(["id", "user_id", "team_id", "author_id"]);
        // }

        if ($author !== null) {
            // Post: hide certain attributes from the JSON response
            $post->makeHidden(["id", "user_id", "team_id", "author_id"]);
        }

        // User: hide the user id from the response
        if ($post->user !== null) {
            $post->user->makeHidden([
                "id",
                "profile_photo_url",
                "profile_photo_path",
            ]);
        }
        // Team: hide the team id from the response
        if ($post->team) {
            $post->team->makeHidden(["id"]);
        }

        // Check if show_author flag is 1, if not hide user details
        if (
            ($post->show_author === 0 || $post->show_author === 1) &&
            $author !== null
        ) {
            $post->user->makeHidden(array_keys($post->user->getAttributes()));
        }

        // Check if team public flag is true or 1, if not hide team details
        if ($post->team->public === 0) {
            $post->team->makeHidden(array_keys($post->team->getAttributes()));
        }

        // If the post is unpublished and the user is a superadmin, render the post anyway
        if ($post->published === 0 && Auth::user()->superadmin === 1) {
            return Inertia::render($postRenderView, [
                "post" => $post,
            ]);
        }

        // If the post is unpublished and the user does not belong to the team that owns the post, return a 404 error
        if (
            $post->published === 0 &&
            $post->team_id !== Auth::user()->current_team_id
        ) {
            return Inertia::render("Error", [
                "customError" => "Please try another route.", // Error message for the user.
                "status" => 404, // HTTP status code for the response.
            ]);
        }

        // Render the post
        return Inertia::render($postRenderView, [
            "post" => $post,
            "author" => $author,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
