@extends('operation.layout')
@section('title','Carta permanente')
@section('content')
<div class="topbar"><div><p class="brand">MENU</p><h1>Carta permanente</h1></div><a class="btn primary" href="{{ route('operation.catalogue.create') }}">Adicionar prato</a></div>
@if(session('status'))<p class="notice" role="status">{{ session('status') }}</p>@endif
@if($errors->any())<p class="alert error" role="alert">{{ $errors->first() }}</p>@endif
<section class="card"><h2>Categorias</h2>
<form class="form-grid" method="post" action="{{ route('operation.catalogue.category') }}">@csrf
<label>Nome da categoria<input name="name" maxlength="80" required></label><label>Tipo<select name="kind"><option value="dish">Pratos</option><option value="soup">Sopas</option><option value="other">Outros</option></select></label><label>Ordem<input type="number" name="position" min="0" max="9999" value="0" required></label><div><button class="btn soft">Criar categoria</button></div></form>
@foreach($categories as $category)
<details class="catalogue-category"><summary>{{ $category->name }} · {{ $category->active?'Ativa':'Oculta' }}</summary>
<form class="form-grid" method="post" action="{{ route('operation.catalogue.category.update',$category) }}">@csrf
<label>Nome<input name="name" value="{{ $category->name }}" required maxlength="80"></label><label>Tipo<select name="kind">@foreach(['dish'=>'Pratos','soup'=>'Sopas','other'=>'Outros'] as $kind=>$label)<option value="{{ $kind }}" @selected($category->kind===$kind)>{{ $label }}</option>@endforeach</select></label><label>Ordem<input type="number" name="position" min="0" max="9999" value="{{ $category->position }}" required></label><input type="hidden" name="active" value="0"><label class="availability-check"><input type="checkbox" name="active" value="1" @checked($category->active)> Categoria ativa</label><div><button class="btn soft">Guardar categoria</button></div></form>
</details>
@endforeach
<p class="muted">Ocultar uma categoria retira os seus pratos da carta pública e da seleção diária. As publicações anteriores mantêm-se.</p></section>
<section class="card"><h2>Pratos</h2>
@forelse($items as $item)
<article class="content-row"><div class="content-main">@if($item->media)<img class="content-thumb" src="{{ $item->media->publicUrl('thumb') }}" alt="{{ $item->name }}">@endif<div><small>{{ $item->category->name }} · {{ $item->active?'Ativo':'Oculto' }}</small><strong>{{ $item->name }}</strong><p>{{ $item->description }}</p><p>{{ $item->price_cents!==null?number_format($item->price_cents/100,2,',',' ').' €':'Preço por confirmar' }}</p></div></div><a class="btn" href="{{ route('operation.catalogue.edit',$item) }}">Editar prato</a></article>
@empty<p>A carta ainda não tem pratos. Crie uma categoria e adicione o primeiro prato.</p>@endforelse
{{ $items->links() }}</section>
@endsection
