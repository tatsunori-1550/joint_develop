<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Post extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'content', 'image', 'lat', 'lng',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'post_tag', 'post_id', 'tag_id')
            ->withTimestamps();
    }

    // 投稿と関連するレビューをすべて削除する
    public function deleteReviews()
    {
        $this->reviews()->delete();
    }

    // 投稿と関連するいいねをすべて削除する
    public function deleteLikes()
    {
        $this->likes()->delete();
    }
    
    // 投稿と関連する画像を削除する
    public function deleteImage()
    {
        if ($this->image) {
            Storage::disk('public')->delete($this->image);
        }
    }

    // 投稿と関連するタグの紐付けを削除
    public function detachTags()
    {
        $this->tags()->detach();
    }

    // 論理削除されていないレビューの各評価項目の平均値を連想配列で返すアクセサ
    public function getAverageRatingsAttribute()
    {
        $reviews = $this->reviews()->whereNull('deleted_at')->get();
        return Review::averageRatingsFromCollection($reviews);
    }
}
