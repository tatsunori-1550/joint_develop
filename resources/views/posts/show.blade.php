@extends('layouts.app')
@section('title', '投稿の詳細とレビュー | クルマの名医ナビ')
@section('meta_description', '整備工場の詳細な投稿内容とレビューの一覧を確認できるページです。利用者の評価や体験談をもとに信頼できる工場を見つけましょう。')
@section('content')
    <div class="container">
        <div class="mx-auto" style="max-width: 700px; width: 100%;">
            <div class="mb-4 mb-3">
                <h4 class="fw-bold border-bottom pb-2 mb-3">
                    <i class="fas fa-user-circle me-2"></i>
                    <a href="{{ route('user.show', $post->user->id) }}" class="text-dark fw-bold"
                    style="text-decoration: none; transition: all 0.2s ease;"
                    onmouseover="this.style.textDecoration='underline';"
                    onmouseout="this.style.textDecoration='none';">
                        {{ $post->user->name }}
                    </a>
                    さんの投稿
                </h4>
                @if ($post->average_ratings)
                    <div class="mt-3 mb-3">
                        <h6 class="fw-bold mb-1">平均評価</h6>
                        <div class="d-flex align-items-center">
                            <span class="me-1" style="font-size: 1.4rem; color: #000;">
                                {{ $post->average_ratings['overall'] !== null ? number_format($post->average_ratings['overall'], 1) : '-' }}
                            </span>
                            <span style="color: gold; font-size: 1.4rem;">
                                {{ $post->average_ratings['overall'] !== null ? '★' : '' }}
                            </span>
                        </div>
                        <small class="text-muted">
                            接客：{{ display_star_rating($post->average_ratings['service']) }}／
                            料金：{{ display_star_rating($post->average_ratings['cost']) }}／
                            技術：{{ display_star_rating($post->average_ratings['quality']) }}
                        </small>
                    </div>
                @endif
                <div class="p-4 border rounded bg-light mb-4">
                    <h5 class="mb-3 text-break">
                        <i class="fas fa-tools me-2 text-secondary"></i>
                        <strong>{{ $post->shop_name }}</strong>
                    </h5>
                    <div class="mb-3">
                        <p class="mb-0 text-break text-muted">
                            <i class="fas fa-map-marker-alt me-2 text-secondary"></i>{{ $post->address }}
                        </p>
                    </div>
                    @if ($post->lat && $post->lng)
                        <div class="mb-4">
                            <div id="map" style="height: 400px;"></div>
                            <script>
                                function initMap() {
                                    const location = { lat: {{ $post->lat }}, lng: {{ $post->lng }} };
                                    const map = new google.maps.Map(document.getElementById("map"), {
                                        zoom: 15,
                                        center: location,
                                    });
                                    new google.maps.Marker({
                                        position: location,
                                        map: map,
                                    });
                                }
                            </script>
                            <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.GoogleMapsApiKey') }}&callback=initMap" async defer></script>
                        </div>
                    @endif
                    <div class="mb-3">
                        <div class="fw-bold text-dark mb-1">投稿内容：</div>
                        <p class="mb-0 text-break">{{ $post->content }}</p>
                    </div>
                    @if ($post->tags->isNotEmpty())
                        <div class="mt-1">
                            @foreach ($post->tags as $tag)
                                <a href="{{ route('posts.index', ['keyword' => $tag->name]) }}"
                                style="font-size: 0.85rem; color: #6c757d; margin-right: 0.5em; text-decoration: none;">
                                    #{{ $tag->name }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                    @if (Auth::check() && Auth::id() === $post->user_id)
                        <div class="d-flex justify-content-between align-items-center mt-4" style="flex-wrap: nowrap;">
                            <small class="text-muted">投稿日：{{ $post->created_at }}</small>
                            <div class="d-flex gap-2" style="white-space: nowrap;">
                                <a href="{{ route('posts.edit', $post->id) }}" class="btn btn-sm btn-primary mr-2">編集</a>
                                <form method="POST" action="{{ route('posts.delete', $post->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('本当に削除しますか？')">削除</button>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="text-end">
                            <small class="text-muted">投稿日：{{ $post->created_at }}</small>
                        </div>
                    @endif
                </div>
                @if ($post->image)
                    <div class="text-center">
                        <img src="{{ $post->image }}"
                            class="img-fluid rounded shadow-sm mb-3"
                            style="max-height: 400px; object-fit: contain; background-color: #f8f9fa;"
                            alt="投稿画像">
                    </div>
                @endif
            </div>
            @include('commons.error_messages')
            @if (Auth::check())
                @if ($hasReviewed)
                    <p class="text-muted">※あなたはこの投稿にすでにレビューしています</p>
                @endif
                <form action="{{ route('reviews.store', $post->id) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="comment">レビュー内容</label>
                        <textarea name="comment" id="comment" class="form-control" rows="3">{{ old('comment') }}</textarea>
                    </div>
                    <div class="form-group mt-3">
                        <label>接客・対応の満足度（1〜5☆）※任意</label><br>
                        @for ($i = 1; $i <= 5; $i++)
                            <label class="me-2">
                                <input type="radio" name="rating_service" value="{{ $i }}" {{ old('rating_service') == $i ? 'checked' : '' }}>
                                {{ $i }}<span style="color: gold;">★</span>
                            </label>
                        @endfor
                    </div>
                    <div class="form-group mt-2">
                        <label>料金の妥当性について（1〜5☆）※任意</label><br>
                        @for ($i = 1; $i <= 5; $i++)
                            <label class="me-2">
                                <input type="radio" name="rating_cost" value="{{ $i }}" {{ old('rating_cost') == $i ? 'checked' : '' }}>
                                {{ $i }}<span style="color: gold;">★</span>
                            </label>
                        @endfor
                    </div>
                    <div class="form-group mt-2">
                        <label>修理の仕上がり精度（1〜5☆）※任意</label><br>
                        @for ($i = 1; $i <= 5; $i++)
                            <label class="me-2">
                                <input type="radio" name="rating_quality" value="{{ $i }}" {{ old('rating_quality') == $i ? 'checked' : '' }}>
                                {{ $i }}<span style="color: gold;">★</span>
                            </label>
                        @endfor
                    </div>
                    <button type="submit" class="btn btn-primary mt-2">レビューする</button>
                </form>
            @else
                <p class="text-muted">※レビューするにはログインが必要です。</p>
            @endif
            <hr>
            <h5>みんなのレビュー（{{ $countReviews }} 件）</h5>
            @if ($reviews->isEmpty())
                <p class="text-muted">まだレビューはありません。</p>
            @else
                @foreach ($reviews as $review)
                    <div
                        class="card mb-3 {{ $latestReview && $review->id === $latestReview->id ? 'border border-dark' : '' }}"
                        @if ($latestReview && $review->id === $latestReview->id)
                            style="background-color: #f8f9fa;"
                        @endif
                    >
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong>
                                    {{ $review->user->name }}
                                    @if ($latestReview && $review->id === $latestReview->id)
                                        <span class="badge border border-dark text-dark ms-2">最新</span>
                                    @endif
                                </strong>
                                <small class="text-muted">{{ $review->created_at }}</small>
                            </div>
                            <p class="mt-2 mb-2">{{ $review->comment }}</p>
                            <ul class="list-unstyled ms-2">
                                <li>
                                    接客・対応の満足度　：
                                    @if ($review->rating_service !== null)
                                        @for ($i = 1; $i <= 5; $i++)
                                            <span style="color: {{ $i <= $review->rating_service ? 'gold' : 'lightgray' }}">★</span>
                                        @endfor
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </li>
                                <li>
                                    料金の妥当性について：
                                    @if ($review->rating_cost !== null)
                                        @for ($i = 1; $i <= 5; $i++)
                                            <span style="color: {{ $i <= $review->rating_cost ? 'gold' : 'lightgray' }}">★</span>
                                        @endfor
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </li>
                                <li>
                                    修理の仕上がり精度　：
                                    @if ($review->rating_quality !== null)
                                        @for ($i = 1; $i <= 5; $i++)
                                            <span style="color: {{ $i <= $review->rating_quality ? 'gold' : 'lightgray' }}">★</span>
                                        @endfor
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </li>
                            </ul>
                            @if (Auth::check() && Auth::id() === $review->user_id)
                                <div class="text-end">
                                    <a href="{{ route('reviews.edit', ['post_id' => $post->id, 'review_id' => $review->id]) }}" class="btn btn-sm btn-outline-primary me-1">編集</a>
                                    <form action="{{ route('reviews.delete', ['review_id' => $review->id]) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('本当に削除しますか？')">削除</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
    <div class="m-auto" style="width: fit-content">
    {{ $reviews->links('pagination::bootstrap-4') }}
    </div>
@endsection