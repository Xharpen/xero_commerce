<?php
use Xpressengine\Plugins\XeroCommerce\Handlers\ProductHandler;
?>
{{ XeFrontend::css('plugins/xero_commerce/src/Components/Widget/ProductListWidget/Skins/Common/assets/style.css')->load() }}





<section class="section-basic">
	<div>
    	<h2 class="title-event">추천 상품</h2>
        <ul class="list-basic">
            @foreach ($products as $key => $product)
            <li class="item-basic">
                <a href="{{ route('xero_commerce::product.show', ['slug' => $product->slug]) }}" class="link-basic">
                    <span class="thumnail" style="background-image:url('{{$product->getThumbnailSrc()}}')"></span>
                        <div class="box_content">
                        <strong>{{$product->name}}</strong>
                        <p class="price">
                            <span class="xe-sr-only">할인 전</span>
                            <span class="sale">{{ number_format($product->original_price)}}원</span>
                            <span class="xe-sr-only">할인 후</span>
                            <span>{{number_format($product->sell_price)}}원</span>
                        </p>
                        @foreach($product->labels as $label)
                            <span class="xe-shop-tag" @if($label->background_color && $label->text_color)style="background: {{$label->background_color}}; color:{{$label->text_color}}" @endif>{{$label->name}}</span>
                        @endforeach
                    </div>
                </a>
            </li>
             @endforeach
        </ul>
	</div>
</section>

<section class="xe-shop list" style="display: none;">

    <div class="container" style="padding-left:0; padding-right:0">
        <div class="search-results">
            @if ($products->total() == 0)
                <p class="search-results-text">검색된 상품이 존재하지 않습니다.</p>
            @else
                <p class="search-results-text"><span class="search-results-text-num">{{ $products->total() }}</span>개의 상품이
                    검색 되었습니다.</p>
            @endif
        </div>
        <form action="{{url()->current()}}" method="get">
            <div class="range-box">
                <div class="research-box">
                    <input type="text" name="product_name" class="xe-form-control" placeholder value="{{( request()->product_name)? : ''}}">
                    <button type="submit">
                        <i class="xi-search"></i><span class="xe-sr-only">검색</span>
                    </button>
                </div>

                <div class="xe-dropdown">
                    <button class="xe-btn" type="button" >{{ request()->sort_type? ProductHandler::getSortAble()[request()->sort_type] : '정렬 옵션' }}</button>
                    <ul class="xe-dropdown-menu">
                        <li><a href="#">정렬 옵션</a></li>
                        @foreach (ProductHandler::getSortAble() as $key => $sort)
                            <li><a href="#" value="{{ $key }}">{{ $sort }}</a></li>
                        @endforeach
                    </ul>
                    <input type="hidden" name="sort_type">
                </div>
            </div>
        </form>

        <ul class="default-list">
            @foreach ($products as $key => $product)
                <li>
                    <div class="default-list-img">
                        <a href="{{ route('xero_commerce::product.show', ['slug' => $product->slug]) }}"><img
                                    src="{{$product->getThumbnailSrc()}}" alt=""></a>
                        <h4 class="xe-sr-only">sns 공유</h4>
                        <ul class="default-list-sns">
                            @php
                                $userWish = $product->wishes()->where('user_id', Auth::id())->count();
                            @endphp
                            <!-- [D] a 클릭시 내부에 xi-heart-o 클래스 명을 xi-heart 로 변경 부탁드립니다. -->
                            <li><a href="#" onclick="event.preventDefault();toggleHeart('{{$product->id}}')"><i id="heart{{$product->id}}" class="{{ $userWish ? 'xi-heart' : 'xi-heart-o'}}"></i><span class="xe-sr-only">좋아요</span></a></li>
                            <li><a href="#"><i class="xi-facebook"></i><span class="xe-sr-only">페이스북 공유</span></a></li>
                            <li><a href="#"><i class="xi-instagram"></i><span class="xe-sr-only">인스타그램 공유</span></a>
                            </li>
                        </ul>
                    </div>
                    <div class="default-list-text">
                        <a href="{{ route('xero_commerce::product.show', ['slug' => $product->slug]) }}">
                            <h3 class="default-list-text-title">
                                @foreach($product->labels as $label)
                                    <span class="xe-shop-tag" @if($label->background_color && $label->text_color)style="background: {{$label->background_color}}; color:{{$label->text_color}}" @endif>{{$label->name}}</span>
                                @endforeach
                                {{$product->name}}</h3>
                            <p class="default-list-text-price">
                                <span class="xe-sr-only">할인 전</span>
                                <span class="through">{{ number_format($product->original_price)}}원</span>
                                <i class="xi-arrow-right"></i>
                                <span class="xe-sr-only">할인 후</span>
                                <span>{{number_format($product->sell_price)}}원</span>
                            </p>
                            @foreach($product->labels as $label)
                                <span class="xe-shop-tag" @if($label->background_color && $label->text_color)style="background: {{$label->background_color}}; color:{{$label->text_color}}" @endif>{{$label->name}}</span>
                            @endforeach
                        </a>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="product_pagination">
        {{ $products->render() }}
    </div>
</section>

<script>
    function toggleHeart(id)
    {
        $.ajax({
            url: '{{route('xero_commerce::product.wish.toggle',['product'=>''])}}/'+id
        }).done(()=>{
            toggleClass('#heart'+id,'xi-heart-o')
            toggleClass('#heart'+id,'xi-heart')
        })
    }
    function toggleClass(target, className)
    {
        if ($(target).hasClass(className)){
            $(target).removeClass(className)
        } else {
            $(target).addClass(className)
        }
    }

    $('.xe-dropdown .xe-btn, .xe-dropdown-menu a').on('click', function () {
        toggleClass('.xe-dropdown','open')
    });

    $('.xe-dropdown-menu a').on('click', function (e) {
        e.preventDefault();

        $("input[name=sort_type]").val($(this).attr('value'));

        $("form").submit();
    });
</script>
