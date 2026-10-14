@extends('public.layout')
@section('title','Menu · O Cantinho da Mariana')
@section('content')
<link rel="stylesheet" href="/menu.css">
<section class="public-section narrow"><p class="kicker">MENU</p><h1>Hoje à mesa</h1>
@if($publication && collect($publication->payload['items']??[])->contains(fn($item)=>$item['available']??true))
@foreach(($publication->payload['items']??[]) as $item)
@if($item['available']??true) @include('public.daily-item',['item'=>$item]) @endif
@endforeach
@elseif($publication)<p>Pergunte-nos pelos pratos disponíveis.</p>
@else<p>O menu de hoje ainda não foi publicado.</p>@endif
@forelse($categories->filter(fn($category)=>$category->items->isNotEmpty()) as $category)
<section class="permanent-category"><h2>{{ $category->name }}</h2>
@foreach($category->items as $item)
<article class="permanent-item">
@if($item->media)<img src="{{ $item->media->publicUrl('web') }}" alt="{{ $item->name }}" loading="lazy">@endif
<div><h3>{{ $item->name }}</h3>@if($item->description)<p>{{ $item->description }}</p>@endif
@if($item->price_cents!==null)<strong>{{ number_format($item->price_cents/100,2,',',' ') }} €</strong>@endif
<p class="muted allergen-note">Alergénios: {{ $item->allergens?implode(', ',\App\Domain\Restaurant\Allergens::labels($item->allergens)):'informação por confirmar' }}.</p></div>
</article>
@endforeach
</section>
@empty<p class="muted">A carta permanente ainda não foi disponibilizada.</p>@endforelse
</section>
@endsection
