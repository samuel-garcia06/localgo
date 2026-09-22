# Arquitectura y decisiones técnicas

Este documento resume el diseño de LocalGo: qué problema resuelve, por qué se
eligió este stack y cómo están organizados los módulos principales.

## Qué resuelve

Una plataforma de pedidos online para negocios de comida local (pollerías,
kebabs, hamburgueserías, pizzerías...) que necesitan:

- Una carta digital navegable sin fricción, sin llamadas ni mensajes confusos.
- Un flujo de pedido completo: carrito, checkout, pago (efectivo o tarjeta),
  y confirmación por email.
- Un panel operativo para el negocio: pedidos en tiempo real, aceptar/rechazar,
  vista de cocina, y asignación a repartidores.
- Una base reutilizable para otros locales con carta, pedidos y seguimiento,
  no una demo de un solo caso de uso.

## Por qué este stack

| Elección | Motivo |
|---|---|
| **Laravel 13 / PHP 8.3** | Framework maduro, convenciones claras, ecosistema first-party (colas, broadcasting, mail) sin depender de piezas sueltas. |
| **Livewire** | Permite construir interfaces reactivas (carrito, checkout, cocina) sin separar frontend/backend en una SPA aparte — menos superficie, iteración más rápida para un equipo pequeño. |
| **Filament** | Panel de administración production-ready (CRUD de productos/categorías, tablas, formularios) sin reinventar un admin desde cero. |
| **Laravel Reverb** | WebSockets first-party para pedidos en tiempo real (cocina, admin, repartidor) sin depender de un servicio externo de terceros. |
| **Stripe** | Checkout de pago con tarjeta, estándar de la industria. |
| **Resend** | Envío transaccional de emails de confirmación de pedido. |
| **Tailwind CSS** | Utilidades para iterar rápido en UI sin mantener hojas de estilo grandes. |

Se descartó deliberadamente usar un framework SPA (React/Angular) para el
frontend: la complejidad añadida (API separada, estado cliente, build aparte)
no aporta valor para el tamaño de este producto, y Livewire cubre la
interactividad necesaria manteniendo un único codebase.

## Módulos principales

```
app/
├── Livewire/           Componentes de página completa (Storefront, Cart, KitchenDisplay)
├── Filament/            Panel admin (Resources, Pages, Widgets) + panel de repartidor
├── Http/Controllers/Api Endpoints públicos (productos, categorías, pedidos, Stripe)
├── Services/            Lógica de negocio (OrderService, StripeCheckoutService...)
├── Models/              Eloquent (Order, Product, Category, User...)
├── Enums/               Estados tipados (OrderStatus, DeliveryType, PaymentStatus...)
├── Observers/           Efectos secundarios al cambiar un pedido (broadcast, emails)
└── Events/              Eventos de broadcasting (OrderUpdated)
```

### Flujo de un pedido

1. El cliente navega la carta (`Storefront`) y arma el carrito (`Cart`), ambos
   componentes Livewire de página completa.
2. Al confirmar, `OrderService` crea el pedido y sus líneas dentro de una
   transacción.
3. Si hay email, el pedido queda `pending_email_confirmation` hasta que el
   cliente confirma desde el enlace recibido — evita pedidos basura.
4. `OrderObserver` reacciona a cada cambio de estado: dispara `OrderUpdated`
   (Reverb, canal privado `orders`) y envía el email correspondiente.
5. Cocina (`/kitchen`) acepta/rechaza y marca como listo; el admin ve todo
   desde Filament; el repartidor (`/repartidor`, panel Filament aparte) toma
   el pedido y lo marca como entregado.

### Paneles separados por rol

En vez de un único panel con permisos condicionales, cada rol operativo tiene
su propio "panel" de Filament (`AdminPanelProvider`, `RepartidorPanelProvider`),
cada uno con su propio login y `canAccessPanel()` en el modelo `User`. Esto
mantiene la navegación y las páginas de cada rol completamente aisladas sin
lógica de visibilidad condicional dispersa por la UI.

### Estados como enums nativos de PHP

`OrderStatus`, `DeliveryType`, `PaymentStatus`, etc. son enums nativos de PHP
(no strings sueltos ni constantes), con métodos propios (`getLabel()`,
`getColor()`, helpers como `visibleToRestaurantValues()`) para que las reglas
de qué estados ve cada rol vivan en un solo sitio tipado.

## Roadmap / mejoras futuras

- Gestión de repartidores desde el panel admin (alta/baja de cuentas sin usar
  `tinker`/seeder).
- Multi-tenant real (varios locales bajo la misma instalación).
- Métricas y reporting histórico más allá del resumen diario actual.
