<!doctype html><html lang="pt"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="/app.css"><link rel="stylesheet" href="/operation.css"><title>@yield('title','Operação') · O Cantinho da Mariana</title>
</head><body class="operation-body">
<a class="skip-link" href="#conteudo">Saltar para o conteúdo</a>
<header class="operation-header"><a class="wordmark" href="{{ route('operation.dashboard') }}">O Cantinho <span>da Mariana</span></a><form method="post" action="{{ route('logout') }}">@csrf<button class="btn">Sair</button></form></header>
<main id="conteudo" class="@yield('shell','app-shell')">@yield('content')</main>
<nav class="operation-nav" aria-label="Operação">
@foreach(['dashboard'=>'Início','edit'=>'Hoje','catalogue'=>'Carta','editorial'=>'Conteúdos','media'=>'Fotografias','loyalty'=>'Clientes','settings'=>'Definições'] as $key=>$label)
<a href="{{ route('operation.'.$key) }}" @if(request()->routeIs('operation.'.$key,'operation.'.$key.'.*')) aria-current="page" @endif>{{ $label }}</a>
@endforeach
</nav></body></html>
