<?php

namespace App\Http\Controllers;

class FollowController extends Controller
{
    public function store($id)
    {
        \Auth::user()->follow($id);
        return back()->with('flash_message', 'フォローしました');
    }

    public function destroy($id)
    {
        \Auth::user()->unfollow($id);
        return back()->with('flash_message', 'フォローを解除しました');
    }
}
