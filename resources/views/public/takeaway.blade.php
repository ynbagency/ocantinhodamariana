@extends('public.layout') @section('title','Takeaway · O Cantinho da Mariana') @section('content')
<section class="public-section narrow"><p class="kicker">TAKEAWAY</p><h1>Leve o Cantinho consigo.</h1><p>{{$restaurantFacts['takeaway_note']??'As encomendas são feitas por telefone na fase inicial.'}}</p>
@if(!empty($restaurantFacts['phone']))<a class="btn primary" href="tel:{{preg_replace('/\s+/','',$restaurantFacts['phone'])}}">Ligar para encomendar · {{$restaurantFacts['phone']}}</a>@else<div class="notice">O telefone ainda não foi confirmado.</div>@endif
@if($publication)
<h2>Disponível hoje</h2>
@foreach(($publication->payload['items']??[]) as $i)
@if($i['available']??true)<div class="menu-line"><strong>{{$i['name']}}</strong></div>@endif
@endforeach
@endif
</section>@endsection
