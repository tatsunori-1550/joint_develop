<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    public function store($id)
    {
         /** @var \App\User $user */
        $user = Auth::user();
        $user->like($id);
        
        return back()->with('flash_message', 'いいねしました');
    }

    public function destroy($id)
    {
        /** @var \App\User $user */
        $user = Auth::user();
        $user->unlike($id);        
    
        return back()->with('flash_message', 'いいねを解除しました');
    }
}
