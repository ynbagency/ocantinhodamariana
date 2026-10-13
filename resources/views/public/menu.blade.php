@extends('public.layout')
@section('title','Menu · O Cantinho da Mariana')
@section('content')
<section class="public-section narrow"><p class="kicker">MENU</p><h1>Hoje à mesa</h1>
@if($publication && collect($publication->payload['items']??[])->contains(fn($item)=>$item['available']??true))
@foreach(($publication->payload['items']??[]) as $item)
@if($item['available']??true)<div class="menu-line"><strong>{{ $item['name'] }}</strong></div>@endif
@endforeach
@elseif($publication)<p>Pergunte-nos pelos pratos disponíveis.</p>
@else<p>O menu de hoje ainda não foi publicado.</p>@endif
<p class="muted">A carta completa será apresentada aqui quando os conteúdos finais forem aprovados.</p>
</section>
@endsection
