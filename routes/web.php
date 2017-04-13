<?php

Route::get('/', function (\Illuminate\Http\Request $request) {
    $search = $request->get('search');

    return \App\Comment::whereHasCommentable(function ($query) use ($search) {
        $query->where('title', 'like', "%{$search}%");
    })->with('commentable')->get();
});
