@extends('public.layout') @section('title','O Cantinho da Mariana · Hoje') @section('content')
<section class="hero-public"><div><p class="kicker">CONDEIXA-A-NOVA</p><h1>O Cantinho<br><em>da Mariana</em></h1><p>Uma casa daqui. Com sabores que vieram de longe.</p><div class="actions"><a class="btn primary" href="#hoje">Ver pratos de hoje</a><a class="btn secondary" href="/takeaway">Takeaway</a></div></div><div class="hero-shape"></div></section>
<section id="hoje" class="public-section today-public"><p class="kicker">HOJE</p><h2>Pratos do dia</h2>
@if($publication && collect($publication->payload['items']??[])->contains(fn($item)=>$item['available']??true)) @foreach(($publication->payload['items']??[]) as $item) @if($item['available']??true) @include('public.daily-item',['item'=>$item]) @endif @endforeach
@if(isset($publication->payload['price_cents']))<div class="public-price">{{ number_format($publication->payload['price_cents']/100,2,',','.') }} €</div>@endif
@else <p class="muted">Pergunte-nos pelos pratos de hoje.</p> @endif</section>
<section class="public-section"><p class="kicker">A NOSSA CASA</p><h2>Comida, pessoas e histórias.</h2><div class="story-grid">@foreach($stories as $story)<article class="story-tile">@if($story->media)<img src="{{ $story->media->publicUrl('web') }}" alt="">@endif<div><small>{{ strtoupper($story->type) }}</small><h3>{{ $story->title }}</h3><p>{{ $story->body }}</p></div></article>@endforeach</div></section>
@endsection
