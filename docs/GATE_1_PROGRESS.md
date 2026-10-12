# Gate 1 — progresso local

Implementado como código-fonte (ainda sem runtime Composer/vendor):

- composer.json para Laravel 13 e Filament 5;
- bootstrap, front controller e rotas;
- migrações iniciais: utilizadores, menus diários, itens, publicações e tentativas por canal;
- modelos e serviço transacional de publicação com versionamento;
- formulário mobile-first para guardar rascunho/publicar;
- página pública de pratos de hoje;
- controlos de autenticação e papel de operador;
- separação de estado por canal, sem fingir publicação Meta.

**Não concluído**: dependências não instaladas, framework não arrancado, autenticação/login ainda sem scaffolding, Filament não configurado, testes de integração não executados, adaptadores de publicação por canal por implementar. Este é um checkpoint de código, não Gate 1 aprovado.

## Comandos num ambiente com Composer

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

Antes de executar, integrar com um skeleton Laravel 13 oficial e configurar autenticação/admin Filament. Não criar utilizadores públicos com role admin. O website lê o último snapshot publicado, pelo que guardar um rascunho não altera o conteúdo público.
