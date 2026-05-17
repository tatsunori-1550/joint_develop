<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Post;
use App\Http\Requests\UserRequest;
use Illuminate\Support\Facades\Auth;

class UsersController extends Controller
{
    public function show($id)
    {
        $keyword = '';
        $user = User::findOrFail($id);
        $followedUserIds = $user->followings()->pluck('users.id')->toArray();
        $followedUserIds[] = $user->id;
        $posts = Post::whereIn('user_id', $followedUserIds)
            ->orderBy('id', 'desc')
            ->paginate(9);
        $data = [
            'user' => $user,
            'posts' => $posts,
            'keyword' => $keyword,
        ];
        return view('users.show', $data);
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        if (Auth::id() != $id) {
            abort(403);
        }
        return view('users.edit', ['user' => $user]);
    }

    public function update(UserRequest $request, $id)
    {
        $user = User::findOrFail($id);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->save();
        return redirect()->route('user.show', $user->id)
            ->with('flash_message', 'ユーザー情報を更新しました');
    }

    public function destroy($id)
    {
        $user = User::with(['posts.tags', 'posts.reviews'])->findOrFail($id);
        if (Auth::id() !== $user->id) {
            abort(403);
        }
        foreach ($user->posts as $post) {
            $post->deleteImage();
            $post->deleteReviews();
            $post->detachTags();
            $post->delete();
        }
        $user->reviews()->delete();
        $user->followings()->detach();
        $user->followers()->detach();
        $user->delete();
        return redirect()->route('posts.index')
            ->with('flash_message', '退会が完了しました。ご利用ありがとうございました');
    }

    //自分がフォローしているユーザー一覧
    public function followings($id)
    {
        $user = User::findOrFail($id);
        $search = request('search');
        $query = $user->followings();
        if (!empty($search)) {
            $query->where('name', 'like', '%' . $search . '%');
        }
        $followings = $query->get();
        $data = [
            'user' => $user,
            'users' => $followings,
        ];
        $data += $user->userCounts();
        return view('users.followings', $data);
    }
        
    //自分をフォローしているユーザー一覧
    public function followers($id)
    {
        $user = User::findOrFail($id);
        $search = request('search');
        $query = $user->followers();
        if (!empty($search)) {
            $query->where('name', 'like', '%' . $search . '%');
        }
        $followers = $query->get();
        $data = [
            'user' => $user,
            'users' => $followers,
        ];
        $data += $user->userCounts();
        return view('users.followers', $data);
    }
}
