@extends('operation.layout')
@section('title','Prato da carta')
@section('content')
<a href="{{ route('operation.catalogue') }}">← Carta permanente</a><h1>{{ $item->exists?'Editar prato':'Adicionar prato' }}</h1>
@if($errors->any())<p class="alert error" role="alert">{{ $errors->first() }}</p>@endif
@if($categories->isEmpty())<p class="notice">Crie primeiro uma categoria na carta permanente.</p>@else
<form class="card form-grid" method="post" action="{{ $item->exists?route('operation.catalogue.update',$item):route('operation.catalogue.store') }}">@csrf
<label>Categoria<select name="menu_category_id" required>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('menu_category_id',$item->menu_category_id)==$category->id)>{{ $category->name }}{{ $category->active?'':' (oculta)' }}</option>@endforeach</select></label>
<label>Nome do prato<input name="name" value="{{ old('name',$item->name) }}" required maxlength="160"></label>
<label class="span-2">Descrição<textarea name="description" maxlength="1000" rows="3">{{ old('description',$item->description) }}</textarea></label>
<label>Preço (€)<input type="number" step="0.01" min="0" max="9999" inputmode="decimal" name="price" value="{{ old('price',$item->price_cents!==null?number_format($item->price_cents/100,2,'.',''):'') }}"></label>
<label>Fotografia<select name="media_asset_id"><option value="">Sem fotografia</option>@foreach($media as $asset)<option value="{{ $asset->id }}" @selected(old('media_asset_id',$item->media_asset_id)==$asset->id)>{{ $asset->title?:'Fotografia '.$asset->id }}</option>@endforeach</select><a href="{{ route('operation.media') }}">Adicionar fotografias à biblioteca</a></label>
@if($item->media)<img class="catalogue-photo" src="{{ $item->media->publicUrl('web') }}" alt="{{ $item->name }}">@endif
<fieldset class="span-2 allergen-fields"><legend>Alergénios</legend><p class="muted">Assinale apenas informação confirmada. Sem seleção, a informação fica por confirmar.</p><div class="allergen-grid">@foreach($allergenLabels as $code=>$label)<label class="availability-check"><input type="checkbox" name="allergens[]" value="{{ $code }}" @checked(in_array($code,old('allergens',$item->allergens??[])))>{{ $label }}</label>@endforeach</div></fieldset>
<input type="hidden" name="active" value="0"><label class="availability-check"><input type="checkbox" name="active" value="1" @checked(old('active',$item->active))> Prato ativo na carta</label>
<div><button class="btn primary">Guardar prato</button></div></form>
@endif
@endsection
