<span>{!! (!empty($feed->timeUp)) ? date('d-m-Y H:i', $feed->timeUp) : 'chưa cập nhật' !!}</span>
@if (\Auth::hasCap('productsFeedEdit'))
    <button class="btn pt-1 pb-1 js_productsFeed_btn_created_xml" data-id="{!! $feed->id !!}"><i class="fa-light fa-arrows-rotate"></i></button>
@endif