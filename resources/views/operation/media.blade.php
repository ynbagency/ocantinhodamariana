@extends('operation.layout')
@section('title','Fotografias')
@section('shell','app-shell')
@section('content')

<header class="topbar"><div><small>OPERAÇÃO</small><h1>Fotografias</h1></div><a class="btn secondary" href="{{ route('operation.dashboard') }}">Voltar</a></header>
@if(session('status'))<div class="notice">{{ session('status') }}</div>@endif
@if($errors->any())<div class="alert error">{{ $errors->first() }}</div>@endif
<section class="card"><h2>Adicionar fotografia</h2>
<form method="post" enctype="multipart/form-data" action="{{ route('operation.media.store') }}" class="form-grid">@csrf
<label class="span-2">Imagem<input type="file" name="image" accept="image/jpeg,image/png,image/webp" required><small>JPEG, PNG ou WebP · máximo 12 MB</small></label>
<label>Título<input name="title" maxlength="120"></label>
<label>Ponto focal horizontal<input type="number" name="focal_x" min="0" max="1" step=".01" value=".5"></label>
<label>Ponto focal vertical<input type="number" name="focal_y" min="0" max="1" step=".01" value=".5"></label>
<div><button class="btn primary">Carregar fotografia</button></div></form></section>
<section class="card"><div class="section-head"><div><h2>Biblioteca</h2><p class="muted">O original é preservado. O sistema cria versões para web, miniatura e TV.</p></div></div>
<div class="media-grid">@forelse($assets as $asset)
<article class="media-card">
<img src="{{ $asset->publicUrl('thumb') }}" alt="">
<div class="media-meta"><strong>{{ $asset->title }}</strong><small>{{ $asset->width }}×{{ $asset->height }} · {{ number_format($asset->size_bytes/1024/1024,1,',','.') }} MB</small>
<form method="post" action="{{ route('operation.media.focus',$asset) }}" class="focus-form">@csrf
<label>X<input type="number" name="focal_x" min="0" max="1" step=".01" value="{{ $asset->focal_x }}"></label>
<label>Y<input type="number" name="focal_y" min="0" max="1" step=".01" value="{{ $asset->focal_y }}"></label>
<button class="btn secondary">Guardar foco</button></form></div></article>
@empty<p>Ainda não existem fotografias.</p>@endforelse</div>
{{ $assets->links() }}</section>
@endsection
