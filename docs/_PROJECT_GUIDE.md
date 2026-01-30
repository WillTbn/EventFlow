# Project Guide – Fact Sphere (EventFlow) – Multi-tenant SaaS (Single DB)

## Objetivo
Aplicação SaaS para gestão de eventos com:
- Landing-page pública (central)
- Área administrativa como PWA (dentro do tenant/workspace)
- Gestão de eventos, fotos, convidados e tarefas
- Multi-tenancy em **banco único** com isolamento por `tenant_id`
- Controle de planos (Free/Plus/Enterprise) e demo (trial)

---

## Arquitetura Multi-tenant (Single DB)
### Conceitos
- **User é global** (tabela `users`)
- **Tenant** é um workspace/conta (tabela `tenants`)
- Usuário participa de tenant via pivot `tenant_users` (papel por tenant)
- Todas entidades do app que pertencem a um tenant possuem `tenant_id`
  - events, photos, tasks, guests, etc.

### Tenant atual
- Tenant atual é definido por **middleware** (`SetCurrentTenant`) e armazenado em sessão + container
- Toda rota autenticada (app/admin) exige `currentTenant`
- Enforcer:
  - usuário precisa ser membro do tenant (tenant_users)
  - tenant precisa estar `active`
  - demo/trial precisa estar válida (se aplicável)

### Isolamento
- Models tenant-aware devem:
  - aplicar **Global Scope** por `tenant_id`
  - preencher `tenant_id` automaticamente em `creating`
- Nunca confiar em `tenant_id` vindo do request

---

## Stack Oficial
- Backend: Laravel 12
- Frontend: Inertia.js + Vue 3
- UI: TailwindCSS + shadcn-vue
- Build: Vite
- PWA: vite-plugin-pwa (admin)
- Auth: Laravel Auth + Sessions (login global)
- Permissões:
  - MVP: papel por tenant em `tenant_users.role` (admin/moderator/member)
  - (Opcional futuro) spatie/laravel-permission com teams
- Storage: local (default), compatível com S3/R2
- Testes: PHPUnit (Feature tests)

---

## Estrutura de Rotas
### Central (sem tenant)
- routes/web.php → landing, pricing, signup, login, seleção de workspace

### Tenant (exige tenant atual)
- **Path-based tenancy (DEV):** `/t/{tenantSlug}/...`
- routes/web.php → rotas públicas do tenant
- routes/admin.php → rotas administrativas (prefixo `/admin`) dentro do tenant

### Middlewares obrigatórios para tenant
- auth (quando rota exigir login)
- setCurrentTenant (sempre em rotas do tenant)
- tenantRole:admin|moderator (para admin)

---

## Estrutura de Pastas (Backend)
app/
├── Actions/
│   ├── Tenancy/
│   ├── Events/
│   ├── Photos/
│   └── Billing/        # planos e quotas
├── Http/
│   ├── Controllers/
│   │   ├── Central/    # landing/signup/workspaces
│   │   ├── Tenant/     # app dentro do tenant
│   │   └── Admin/      # admin PWA dentro do tenant
│   └── Requests/
│       ├── Events/
│       ├── Photos/
│       └── Tenancy/
├── Models/
│   ├── Tenant.php
│   ├── TenantUser.php
│   ├── Event.php
│   └── EventPhoto.php
├── Policies/
├── Scopes/             # TenantScope
├── Services/
│   ├── TenantContext.php
│   └── PlanService.php
├── Middleware/
│   ├── SetCurrentTenant.php
│   └── EnsureTenantRole.php
└── Providers/

---

## Estrutura Frontend (Inertia)
resources/js/
├── layouts/
│   ├── PublicLayout.vue     # landing (central)
│   ├── TenantLayout.vue     # app dentro do tenant (opcional)
│   └── AdminLayout.vue      # admin PWA
├── pages/
│   ├── central/
│   │   ├── Welcome.vue
│   │   ├── Pricing.vue
│   │   ├── Signup.vue
│   │   └── Workspaces.vue
│   ├── tenant/
│   │   └── (páginas internas do tenant se houver)
│   └── admin/
│       ├── Dashboard.vue
│       ├── Events/
│       └── Photos/
├── components/
│   └── ui/              # shadcn-vue components
├── lib/
│   ├── utils.ts
│   └── tenant.ts        # helpers de tenant/features

---

## Convenções Importantes
- Controllers devem ser magros
- Validação sempre em FormRequest
- Autorização:
  - Policies para recursos (Event, Photo, Task…)
  - Gates/Middleware para papel do tenant (admin/moderator/member)
- Queries sempre paginadas
- Actions para lógica (criação, upload, quotas)
- Upload de imagem gera: original + medium + thumb
- Toda entidade tenant-aware:
  - `tenant_id` obrigatório
  - TenantScope aplicado

---

## Planos e Quotas
- Plano atual fica em `tenants.plan`
- Demo via `tenants.trial_ends_at`
- Regras e limites centralizados em `Services/PlanService.php`
- Nunca espalhar `if (plan === ...)` pela aplicação
- UI deve refletir limites (desabilitar botões + CTA upgrade)

---

## PWA (Admin)
- Apenas área administrativa é PWA
- Cache da shell (JS/CSS/layout)
- CRUD e uploads exigem internet
- Exibir aviso de offline quando necessário
- Escopo PWA preferencial: `/admin`

---

## Padrão de Rotas (admin dentro do tenant)
- admin.events.index
- admin.events.create
- admin.events.store
- admin.events.edit
- admin.events.update
- admin.events.destroy

---

## Objetivo Open Source
- Código simples, legível e seguro
- Priorizar clareza sobre complexidade
- Sem abstrações pesadas desnecessárias
