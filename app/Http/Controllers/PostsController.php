<?php

namespace App\Http\Controllers;

use App\Post;
use App\Review;
use App\Tag;
use App\Http\Requests\PostRequest;
use App\Http\Requests\SearchRequest;
use Illuminate\Support\Facades\Auth;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class PostsController extends Controller
{
    public function index(SearchRequest $request)
    {
        $rawKeyword = $request->input('keyword');
        $keyword = ltrim($rawKeyword, '#');
        $posts = $this->basePostQuery()
            ->when($keyword, function ($query) use ($keyword) {
                $query->where(function ($subQuery) use ($keyword) {
                    $subQuery->where('content', 'like', "%{$keyword}%")
                        ->orWhereHas('tags', function ($tagQuery) use ($keyword) {
                            $tagQuery->where('name', $keyword);
                        });
                });
            })
            ->paginate(9);
        $data = [
            'keyword' => $rawKeyword,
            'tag' => Tag::where('name', $keyword)->first(),
            'posts' => $posts,
        ];

        return view('welcome', $data);
    }

    public function show($id)
    {
        $post = Post::with(['user', 'tags'])
            ->withCount(['likes'])
            ->findOrFail($id);

        $reviews = $post->reviews()
            ->with('user')
            ->orderBy('id', 'desc')
            ->paginate(10);
            
        $latestReview = Review::latestReview($post);
        $hasReviewed = false;

        if (Auth::check() && Auth::id() !== $post->user_id) {
            $hasReviewed = Review::hasReviewed(Auth::user(), $post);
        }
        $data = [
            'post' => $post,
            'reviews' => $reviews,
            'latestReview' => $latestReview,
            'hasReviewed' => $hasReviewed,
        ];
        $data += Review::reviewCounts($post);

        return view('posts.show', $data);
    }

    public function create()
    {
        $keyword = '';
        $posts = $this->basePostQuery()->paginate(9);
        $data = [
            'posts' => $posts,
            'keyword' => $keyword,
        ];

        return view('posts.create', $data);
    }

    public function store(PostRequest $request)
    {
        $post = new Post;
        $post->shop_name = $request->input('shop_name');
        $post->address   = $request->input('address');
        $post->content   = $request->input('content');
        $post->user_id   = Auth::id();

        if ($request->hasFile('image')) {
        $uploadedFileUrl = Cloudinary::upload($request->file('image')->getRealPath())->getSecurePath();
        $post->image = $uploadedFileUrl;
    }
        $post->lat = $request->input('lat');
        $post->lng = $request->input('lng');
        $post->save();
        $rawTags = $request->input('tags');
        $tagNames = Tag::parseTagNames($rawTags);

        if (!empty($tagNames)) {
            Tag::syncToPost($post, $tagNames);
        }

        return back()->with('flash_message', '投稿しました。ありがとう！');
    }

    public function edit($id)
    { 
        $post = Post::findOrFail($id);
        if (Auth::id() !== $post->user_id) {
            abort(403);
        }
        return view('posts.edit', ['post' => $post]);
    }

    public function update(PostRequest $request, $id)
    {
        $post = Post::findOrFail($id);
        $post->shop_name = $request->input('shop_name');
        $post->address   = $request->input('address');
        $post->content   = $request->input('content');
        $post->lat = $request->input('lat');
        $post->lng = $request->input('lng');

        if ($request->hasFile('image')) {
        $post->deleteImage(); // これがローカルファイル削除なら、不要になるかも
        $uploadedFileUrl = Cloudinary::upload($request->file('image')->getRealPath())->getSecurePath();
        $post->image = $uploadedFileUrl;
        }

        $post->save();
        $rawTags = $request->input('tags');
        $tagNames = Tag::parseTagNames($rawTags);

        if (!empty($tagNames)) {
            Tag::syncToPost($post, $tagNames);
        } else {
            $post->detachTags();
        }

        return redirect()->route('posts.show', $post->id)
            ->with('flash_message', '投稿を更新しました');
    }

    public function destroy($id)
    {
        $post = Post::findOrFail($id);

        if (Auth::id() !== $post->user_id) {
            abort(403);
        }

        $post->deleteImage();
        $post->deleteReviews();
        $post->detachTags();
        $post->delete();

        return redirect()->route('posts.index')
            ->with('flash_message', '投稿を削除しました');
    }

    private function basePostQuery()
    {
        return Post::with([
            'user',
            'tags',
            'reviews' => function ($query) {
                $query->whereNull('deleted_at');
            },
            'likes',
        ])
        ->withCount([
            'reviews' => function ($query) {
                $query->whereNull('deleted_at');
            },
            'likes',
        ])
        ->orderBy('id', 'desc');
    }
}
