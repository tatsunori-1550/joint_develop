@if ($posts->isEmpty())
    <p class="text-center text-muted py-3">該当する投稿やタグが見つかりませんでした。</p>
@endif
<div class="row">
    @foreach ($posts as $post)
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm d-flex flex-column">
                <div class="card-body flex-grow-1">
                    <div class="d-flex align-items-center mb-3">
                        <img src="{{ Gravatar::src($post->user->email,55) }}" class="rounded-circle mr-3" alt="ユーザのアバター画像">
                        <div>
                        <p class="mb-1 font-weight-bold text-break">
                            <a href="{{ route('user.show', $post->user->id) }}"
                            class="text-dark fw-bold"
                            style="text-decoration: none; transition: all 0.2s ease;"
                            onmouseover="this.style.textDecoration='underline';"
                            onmouseout="this.style.textDecoration='none';">
                                {{ $post->user->name }}
                            </a>
                        </p>
                            @if (Auth::check() && Auth::id() !== $post->user->id)
                                <div>
                                    @if (Auth::user()->isFollowing($post->user->id))
                                        <form method="POST" action="{{ route('unfollow', $post->user->id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">フォロー解除</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('follow', $post->user->id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-primary">フォローする</button>
                                        </form>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                    @php
                        $defaultImage = config('constants.no_image_path');
                        $imageUrl = $post->image
                            ? $post->image
                            : asset($defaultImage);
                    @endphp
                    <a href="{{ route('posts.show', $post->id) }}">
                        <img src="{{ $imageUrl }}" class="img-fluid rounded mb-3 w-100" style="height: 200px; object-fit: contain; background-color: #f8f9fa;" alt="投稿画像">
                    </a>
                    @php
                        $overall = $post->average_ratings['overall'] ?? null;
                    @endphp
                    <a href="{{ route('posts.show', $post->id) }}" style="text-decoration: none; color: inherit;">
                        <div class="mb-2">
                            <small class="text-muted">評価：</small>
                            @if (!empty($overall))
                                <span class="fw-bold">{{ number_format($overall, 1) }}</span>
                                <span class="star-rating">
                                    @for ($i = 1; $i <= 5; $i++)
                                        @php
                                            $fillRatio = $overall - $i + 1;
                                            $fill = 0;
                                            if ($fillRatio >= 1) {
                                                $fill = 100;
                                            } elseif ($fillRatio >= 0.75) {
                                                $fill = 75;
                                            } elseif ($fillRatio >= 0.5) {
                                                $fill = 50;
                                            } elseif ($fillRatio >= 0.25) {
                                                $fill = 25;
                                            } else {
                                                $fill = 0;
                                            }
                                        @endphp
                                        <span class="star">
                                            <span class="star-fill" style="width: {{ $fill }}%;">★</span>
                                            <span class="star-base">★</span>
                                        </span>
                                    @endfor
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                    </a>
                    <a href="{{ route('posts.show', $post->id) }}" style="text-decoration: none; color: inherit;">
                        <h5 class="card-title mb-1 font-weight-bold">
                            <i class="fas fa-wrench mr-1"></i>{{ $post->shop_name }}
                        </h5>
                        <p class="text-muted small mb-2">
                            <i class="fas fa-map-marker-alt mr-1"></i>{{ $post->address }}
                        </p>
                    </a>
                    <p class="card-text mb-2" style="max-height: 100px; overflow: hidden;">
                        <a href="{{ route('posts.show', $post->id) }}" style="color: #212529; text-decoration: none;">
                            {{ Str::limit(strip_tags($post->content), 120, '... 続きを読む') }}
                        </a>
                    </p>
                    @if ($post->tags->isNotEmpty())
                        <div class="mb-2">
                            @foreach ($post->tags as $tag)
                                <a href="{{ route('posts.index', ['keyword' => $tag->name]) }}" style="font-size: 0.8rem; color: #6c757d; margin-right: 0.4em; text-decoration: none;">
                                    #{{ $tag->name }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                    <p class="text-muted small mb-1">レビュー {{ $post->reviews_count }} 件</p>
                    <p class="text-muted small">{{ $post->created_at }}</p>
                </div>
            </div>
        </div>
    @endforeach
</div>
<div class="d-flex justify-content-center">
    {{ $posts->appends(['keyword' => $keyword])->links('pagination::bootstrap-4') }}
</div>