@extends('operation.layout')
@section('title','Início — Operação')
@section('shell','shell')
@section('content')
<p class="brand">OPERAÇÃO</p><h1 class="dashboard-greeting">Olá, {{ auth()->user()->name }}.</h1>
<p class="muted">{{ now()->translatedFormat('l, d F') }}</p>
<section class="card"><div class="topbar"><h2>Pratos de hoje</h2><span class="badge {{ $publication?'ok':'warn' }}"><span class="dot"></span>{{ $publication?'Publicado':'Por publicar' }}</span></div>
@if($publication)<p class="muted">Última publicação · {{ $publication->created_at->format('H:i') }}</p>@endif
@php($available=collect($snapshot['items']??[])->where('available',true))
@forelse($available as $item)
<div class="dish"><strong>{{ $item['name'] }}</strong><div class="muted">{{ $item['category']==='sopa'?'Sopa':'Prato' }}</div></div>
@empty<p class="muted">{{ $publication?'Os pratos publicados estão indisponíveis.':'Ainda não publicou o menu de hoje.' }}</p>@endforelse
@if($available->isNotEmpty() && ($snapshot['price_cents']??null)!==null)<div class="price">{{ number_format($snapshot['price_cents']/100,2,',',' ') }} €</div>@endif
<a class="btn primary full" style="margin-top:16px" href="{{ route('operation.edit') }}">{{ $menu?'Editar pratos de hoje':'Definir pratos de hoje' }}</a>
</section>
@if($menu?->scheduled_at)
<div class="notice" role="status"><strong>Publicação agendada · {{ $menu->scheduled_at->format('H:i') }}</strong><p>O rascunho guardado será publicado hoje. A versão pública atual mantém-se até essa hora.</p><a href="{{ route('operation.edit') }}">Rever ou cancelar agendamento</a></div>
@elseif($menu?->status==='draft' && $publication)
<div class="alert" role="status">Tem um rascunho em preparação. Abaixo está o menu que os clientes estão a ver.</div>
@endif
@if($alerts->isNotEmpty())
<section class="card operational-alerts" aria-labelledby="avisos"><h2 id="avisos">Precisa de atenção</h2>
@foreach($alerts as $alert)
@php($message=match($alert->type){'schedule_expired'=>'Um agendamento terminou sem publicar. Reveja os pratos de hoje.','schedule_failed'=>'Uma publicação agendada não foi concluída. Reveja e publique os pratos de hoje.','screen_offline'=>'A TV está sem contacto. Confirme se está ligada e, se necessário, contacte o apoio.','social_publish_failed'=>'Uma publicação nas redes sociais falhou. O site e o conteúdo da TV continuam disponíveis.'})
<p>{{ $message }} <small class="muted">{{ $alert->created_at->diffForHumans() }}</small></p>
@endforeach
<a class="btn soft" href="{{ route('operation.edit') }}">Rever pratos de hoje</a></section>
@endif

<section class="grid grid2"><a class="btn soft" href="{{ route('operation.preview',['channel'=>'tv']) }}">Pré-visualizar TV</a><a class="btn soft" href="{{ route('operation.preview',['channel'=>'site']) }}">Pré-visualizar site</a></section>
<section class="card"><h2>TV do restaurante</h2>
@forelse($screens as $screen)
@php($health=$screen->health())
<div class="statusrow"><div><strong>{{ $screen->name }}</strong><div class="muted">{{ $screen->last_seen_at?'Último contacto '.$screen->last_seen_at->diffForHumans():'Ainda sem contacto' }}</div></div><span class="badge {{ $health==='online'?'ok':($health==='warning'?'warn':'off') }}"><span class="dot"></span>{{ $health==='online'?'Online':($health==='warning'?'Aviso':'Offline') }}</span></div>
@empty<p class="muted">A TV ainda não foi registada.</p>@endforelse</section>
<section class="card"><h2>Publicação por canal</h2>
@if($publication)
@foreach($publication->attempts as $attempt)
@php($label=['website'=>'Site','signage'=>'Conteúdo da TV','facebook'=>'Facebook','instagram'=>'Instagram'][$attempt->channel]??'Canal')
<div class="statusrow"><span>{{ $label }}</span><span class="badge {{ $attempt->status==='published'?'ok':'warn' }}"><span class="dot"></span>{{ $attempt->status==='published'?'Publicado':($attempt->status==='failed'?'Precisa de atenção':'Pendente') }}</span></div>
@endforeach
<p class="muted" style="margin-top:12px">As redes sociais aguardam configuração. O estado da TV acima confirma o contacto com o restaurante.</p>
@else<p class="muted">Os canais aparecem depois da primeira publicação.</p>@endif
</section>
<section class="grid grid2 dashboard-shortcuts" aria-label="Outras tarefas">
<a class="card" href="{{ route('operation.media') }}"><strong>Fotografias</strong><p class="muted">Adicionar e escolher imagens.</p></a>
<a class="card" href="{{ route('operation.editorial') }}"><strong>Conteúdos e promoções</strong><p class="muted">Mensagens e histórias da TV.</p></a>
<a class="card" href="{{ route('operation.loyalty') }}"><strong>Clientes</strong><p class="muted">Registar visitas e recompensas.</p></a>
<a class="card" href="{{ route('operation.settings') }}"><strong>Dados do restaurante</strong><p class="muted">Telefone, morada e horário.</p></a>
</section>
@endsection
