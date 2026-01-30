# Definition of Done – Checklist (Multi-tenant Single DB)

Todo módulo só é considerado pronto se:

## Multi-tenancy (obrigatório quando aplicável)
- [ ] Tabelas tenant-aware possuem `tenant_id` + índice adequado
- [ ] Model tenant-aware aplica TenantScope (global scope)
- [ ] `tenant_id` é preenchido automaticamente (não vem do request)
- [ ] Rotas do tenant usam middleware `SetCurrentTenant`
- [ ] Middleware `EnsureTenantRole` aplicado em rotas admin-only
- [ ] Teste garante que usuário NÃO acessa dados de outro tenant

## Backend
- [ ] Migration criada
- [ ] Model criado
- [ ] Factory criada (se aplicável)
- [ ] FormRequest para validação
- [ ] Policy criada e aplicada
- [ ] Controller magro
- [ ] Actions usadas para lógica
- [ ] Queries paginadas
- [ ] Upload validado (se houver)

## Planos / Quotas (quando aplicável)
- [ ] Regra implementada via PlanService (sem if espalhado)
- [ ] Limites aplicados no backend (Policy/Request/Service)
- [ ] UI mostra bloqueio e CTA upgrade

## Frontend (Inertia)
- [ ] Página criada em `pages/admin`, `pages/tenant` ou `pages/central`
- [ ] Usa layout correto
- [ ] Componentes shadcn-vue reutilizáveis
- [ ] Loading state
- [ ] Empty state
- [ ] Mensagens de erro claras

## Segurança
- [ ] Autorização via Policy / role do tenant
- [ ] Middleware aplicado
- [ ] Rate limit (quando necessário)
- [ ] Tenant status/trial verificados no acesso

## Testes
- [ ] Feature test para Create
- [ ] Feature test para List
- [ ] Feature test para Update
- [ ] Feature test para Delete (se aplicável)
- [ ] Feature test de isolamento entre tenants (mínimo 1)

## Geral
- [ ] Código segue PROJECT_GUIDE.md
- [ ] Nenhuma lib nova sem justificativa
- [ ] Código legível para contribuição open source
