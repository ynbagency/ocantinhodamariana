@extends('operation.layout')
@section('title','Conteúdos')
@section('shell','app-shell')
@section('content')
<header class="topbar"><div><small>OPERAÇÃO</small><h1>Conteúdos da TV</h1></div><div class="actions"><a class="btn secondary" href="{{ route('operation.media') }}">Fotografias</a><a class="btn secondary" href="{{ route('operation.dashboard') }}">Voltar</a></div></header>@if(session('status'))<div class="notice">{{ session('status') }}</div>@endif
<section class="card"><h2>Novo conteúdo</h2><form method="post" action="{{ route('operation.editorial.store') }}" class="form-grid">@csrf
<label>Família<select name="type"><option value="food">Comida</option><option value="bulgaria">Bulgária</option><option value="mariana">Mariana</option><option value="service">Serviço</option><option value="promotion">Promoção</option></select></label>
<label>Título<input name="title" maxlength="120" required></label><label>Etiqueta<input name="eyebrow" maxlength="60"></label>
<label>Fotografia<select name="media_asset_id"><option value="">Sem fotografia</option>@foreach($media as $asset)<option value="{{ $asset->id }}">{{ $asset->title ?: 'Imagem '.$asset->id }}</option>@endforeach</select></label>
<label class="span-2">Texto<textarea name="body" maxlength="600" rows="4"></textarea></label>
<label>Duração (s)<input type="number" name="duration_seconds" value="12" min="8" max="30"></label><label>Peso<input type="number" name="weight" value="10" min="1" max="100"></label><label><input type="checkbox" name="active" value="1" checked> Ativo</label><div><button class="btn primary">Guardar</button></div></form></section>
<section class="card"><h2>Biblioteca editorial</h2><div class="content-list">@forelse($items as $item)<article class="content-row"><div class="content-main">@if($item->media)<img class="content-thumb" src="{{ Storage::disk($item->media->disk)->url($item->media->preferredPath('thumb')) }}" alt="">@endif<div><small>{{ strtoupper($item->type) }}</small><strong>{{ $item->title }}</strong><p>{{ $item->body }}</p></div></div><form method="post" action="{{ route('operation.editorial.toggle',$item) }}">@csrf<button class="btn secondary">{{ $item->active?'Desativar':'Ativar' }}</button></form></article>@empty<p>Ainda não existem conteúdos editoriais.</p>@endforelse</div></section>
@endsection
