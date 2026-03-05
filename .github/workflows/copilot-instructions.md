**Copilot Instructions - Nexa System Cliente**

Documento de instruções concisas para desenvolvedores — padrões e decisões essenciais.

Visão geral
- Stack: Laravel | Vue 3 | Vite | TailwindCSS | Pinia.
- Comandos rápidos: `npm run dev`, `npm run build`, `composer install`, `php artisan migrate`.

Arquivos de referência
- `resources/js/components/ui/button/Button.vue` — botão padrão (variants, loading, `Primitive`).
- `resources/js/components/ui/button/index.ts` — definição de variantes do botão.
- `resources/js/components/ui/input/Input.vue` — input com `v-maska` e `useVModel`.
- `resources/js/components/ui/select/Select.vue` — select controlado com `useVModel`.
- `resources/js/lib/utils.ts` — `cn()` (clsx + twMerge).
- `resources/js/stores/loading.ts` — store de loading global.
- `resources/css/_custom.scss` — tokens CSS (fonte de verdade) e keyframes; dark mode via `.dark`.
- `resources/css/_text-utilities.css` — utilitários de texto mapeados às variáveis.
- `app/Models/BaseModel.php` — base para models.
- `app/TenantFinder.php` — lógica custom de multitenancy.

Padrões rápidos (ao criar componentes)
- Controle: use `modelValue` + `useVModel()` e emita `update:modelValue`.
- Erros: mantenha `localError` e emita `clear-error` quando o valor mudar.
- Classes: use `cn()` para compor classes Tailwind e evitar duplicatas.
- Acessibilidade: use `aria-busy` para loading e `sr-only` para textos auxiliares.
- Slots: exponha `<slot>` e suporte `as`/`asChild` quando aplicar wrappers.

Loading
- Padrão recomendado: híbrido — loading local por componente; `useGlobalLoading` para ações globais.

SCSS & Theming
- Fonte de verdade: `resources/css/_custom.scss` (adicione novas variáveis aqui).
- Utilitários de texto: `resources/css/_text-utilities.css`.
- Dark mode: aplicar classe `.dark` no root/app para alternar variáveis.

Models & Queries
- Extenda `app/Models/BaseModel.php` para comportamento compartilhado.
- Prefira Eloquent + Scopes + `with()` (eager loading); extraia queries complexas para Repositories/QueryObjects.

Multitenancy
- Repo contém `spatie/laravel-multitenancy` e `stancl/tenancy`. Decida uma solução por projeto; consulte `app/TenantFinder.php`.

Ferramentas principais
- Frontend: Vue 3, Vite, Tailwind, Pinia, @vueuse/core, maska, reka-ui, axios, sweetalert2.
- Backend: Laravel, tenancy packages, tymon/jwt.

Bibliotecas implementadas (frontend)
- `vue` (Vue 3)
- `vite` / `laravel-vite-plugin`
- `tailwindcss` (v4) + `@tailwindcss/vite`
- `pinia`
- `@vueuse/core`
- `maska` (v-mask directive)
- `reka-ui` (Primitive)
- `clsx` + `tailwind-merge` (usadas por `cn()`)
- `axios`
- `sweetalert2`
- `lucide-vue-next` (ícones)
- `swiper` (carrossel)
- `fullcalendar` (opcional em views)

Bibliotecas implementadas (backend / PHP)
- `laravel/framework`
- `spatie/laravel-multitenancy` (referência)
- `stancl/tenancy` (referência)
- `tymon/jwt-auth`
- `pusher/pusher-php-server`
- `mercadopago/dx-php`


Checklist ao adicionar novo componente
- Local: `resources/js/components/ui/<category>/<Component>.vue`.
- Nome: PascalCase; exporte helpers/variants em `index.ts` no diretório.
- Registrar diretivas localmente por padrão (ex.: `v-maska`).
- Adicionar testes se o componente tiver lógica reusável.

- Utilitários textuais: `resources/css/_text-utilities.css` para classes utilitárias mapeadas às variáveis.