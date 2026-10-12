@extends('public.layout') @section('title','Contactos · O Cantinho da Mariana') @section('content')
<section class="public-section narrow"><p class="kicker">CONTACTOS</p><h1>Venha ao Cantinho.</h1>
@if(!empty($restaurantFacts['address']))<p><strong>{{$restaurantFacts['address']}}</strong>@if(!empty($restaurantFacts['postal_code']))<br>{{$restaurantFacts['postal_code']}}@endif {{ $restaurantFacts['locality']??'' }}</p>@else<p>{{$restaurantFacts['locality']??'Condeixa-a-Nova'}}</p>@endif
@if(!empty($restaurantFacts['phone']))<p><a class="btn primary" href="tel:{{preg_replace('/\s+/','',$restaurantFacts['phone'])}}">Ligar · {{$restaurantFacts['phone']}}</a></p>@endif
@if(!empty($restaurantFacts['opening_hours']))<h2>Horário</h2><p style="white-space:pre-line">{{$restaurantFacts['opening_hours']}}</p>@endif
@if(empty($restaurantFacts['address'])||empty($restaurantFacts['phone'])||empty($restaurantFacts['opening_hours']))<p class="muted">Alguns dados públicos ainda aguardam confirmação.</p>@endif</section>@endsection
