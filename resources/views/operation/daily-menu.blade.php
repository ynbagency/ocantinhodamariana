<!doctype html>
<html lang="pt">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="/app.css"><title>Pratos de hoje — Operação</title></head>
<body><main class="shell">
<a href="{{ route('operation.dashboard') }}">← Início</a>
<div class="brand" style="margin-top:24px">O CANTINHO DA MARIANA</div>
<h1>Pratos de hoje</h1><p class="muted">{{ now()->translatedFormat('l, d F Y') }}</p>
@if(session('success'))<p class="alert" role="status">{{ session('success') }}</p>@endif
@if($errors->any())<div class="alert error" role="alert">{{ implode(' ', $errors->all()) }}</div>@endif
@php($soup=$menu->items->firstWhere('category','sopa'))
@php($dishes=$menu->items->where('category','prato')->values())
@if(!$menu->items->count())
<form method="post" action="{{ route('operation.copy-yesterday') }}">@csrf<button class="btn soft full">Usar os de ontem</button></form>
@endif
<section class="card"><h2>Preparar menu</h2>
<form method="post" action="{{ route('operation.store') }}">@csrf
<label>Sopa<input name="soup" value="{{ old('soup',$soup?->name) }}" autocomplete="off" maxlength="160" placeholder="Ex.: Sopa de legumes"></label>
<input type="hidden" name="soup_available" value="0"><label class="availability-check"><input type="checkbox" name="soup_available" value="1" @checked(old('soup_available',$soup?->available??true))> Sopa disponível</label>
<label>Prato 1<input name="dish1" value="{{ old('dish1',$dishes->get(0)?->name) }}" autocomplete="off" maxlength="160" placeholder="Nome do prato"></label>
<input type="hidden" name="dish1_available" value="0"><label class="availability-check"><input type="checkbox" name="dish1_available" value="1" @checked(old('dish1_available',$dishes->get(0)?->available??true))> Prato 1 disponível</label>
<label>Prato 2<input name="dish2" value="{{ old('dish2',$dishes->get(1)?->name) }}" autocomplete="off" maxlength="160" placeholder="Nome do prato"></label>
<input type="hidden" name="dish2_available" value="0"><label class="availability-check"><input type="checkbox" name="dish2_available" value="1" @checked(old('dish2_available',$dishes->get(1)?->available??true))> Prato 2 disponível</label>
<label>Preço (€)<input type="number" inputmode="decimal" step="0.01" min="0" name="price" value="{{ old('price',$menu->price_cents===null?'':number_format($menu->price_cents/100,2,'.','')) }}" placeholder="0,00"></label>
<div class="grid grid2"><button class="btn soft" name="intent" value="draft">Guardar rascunho</button><button class="btn primary" name="intent" value="publish">Publicar agora</button></div>
</form>
<p class="muted" style="margin-top:12px">Publicar guarda os campos acima e atualiza o site e o conteúdo da TV. Guardar rascunho mantém a versão pública anterior.</p>
<div class="grid grid2"><a class="btn" href="{{ route('operation.preview',['channel'=>'tv']) }}">Pré-visualizar TV</a><a class="btn" href="{{ route('operation.preview',['channel'=>'site']) }}">Pré-visualizar site</a></div>
<p class="muted" style="margin-top:12px">As pré-visualizações usam o último rascunho guardado.</p>
</section>
<section class="card"><h2>Agendar para hoje</h2>
@if($menu->scheduled_at)
<p role="status">Publicação agendada para as <strong>{{ $menu->scheduled_at->format('H:i') }}</strong>.</p>
<form method="post" action="{{ route('operation.cancel-schedule') }}">@csrf<button class="btn">Cancelar agendamento</button></form>
@else
<form method="post" action="{{ route('operation.schedule') }}">@csrf
<label>Hora de publicação<input type="datetime-local" name="scheduled_at" min="{{ now()->addMinute()->format('Y-m-d\TH:i') }}" max="{{ today()->endOfDay()->format('Y-m-d\TH:i') }}" required></label>
<button class="btn soft full">Agendar rascunho guardado</button></form>
@endif
<p class="muted" style="margin-top:12px">Guarde primeiro os pratos. Guardar novas alterações cancela o agendamento anterior.</p>
</section>
<section class="card"><h2>Menu publicado</h2>
@if($publication)
<p class="muted">Versão {{ $publication->version }} · {{ $publication->created_at->format('H:i') }}</p>
@foreach($publication->payload['items'] as $item)
<div class="statusrow"><div><strong>{{ $item['name'] }}</strong><div class="muted">{{ $item['available']?'Disponível':'Indisponível' }}</div></div>
<form method="post" action="{{ route('operation.availability') }}">@csrf
<input type="hidden" name="version" value="{{ $publication->version }}"><input type="hidden" name="position" value="{{ $item['position'] }}"><input type="hidden" name="available" value="{{ $item['available']?0:1 }}">
<button class="btn {{ $item['available']?'soft':'primary' }}">{{ $item['available']?'Esgotou':'Voltar a disponibilizar' }}</button></form></div>
@endforeach
<p class="muted" style="margin-top:12px">A disponibilidade altera apenas o menu publicado. O rascunho em preparação mantém-se separado.</p>
@foreach($publication->attempts as $attempt)
<div class="statusrow"><span>{{ ucfirst($attempt->channel) }}</span><span class="badge {{ $attempt->status==='published'?'ok':'warn' }}"><span class="dot"></span>{{ $attempt->status==='published'?'Publicado':'Pendente' }}</span></div>
@endforeach
@else<p class="muted">Ainda não existe uma publicação para hoje.</p>@endif
</section></main></body></html>
