# 🛒 Laravel E‑Commerce Platform

**A full‑stack e‑commerce application built with Laravel 12 — role‑based access control, a service‑oriented architecture, a token‑based REST API, and a pluggable e‑payment layer (Visa/Mastercard cards + PayPal).**

[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Sanctum](https://img.shields.io/badge/Auth-Sanctum-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com/docs/sanctum)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)](https://getbootstrap.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge)](#-license)

---

## 📖 Overview

This project is an e‑commerce platform that demonstrates modern backend engineering with the **Laravel 12** framework. It ships with **two authenticated roles** — `admin` and `customer` — plus **guest** browsing, a complete order lifecycle with live inventory adjustment, and a **factory‑driven payment layer**.

The application is delivered in two ways from a single codebase:

- A **server‑rendered web application** (Blade + Bootstrap 5 admin theme) for guests, customers and administrators.
- A **stateless REST API** authenticated with **Laravel Sanctum** personal‑access tokens, for headless or mobile clients.

The codebase is organised around clean, testable boundaries: **Service classes** encapsulate business logic, a **Factory + Interface** pair drives payment‑gateway selection, **Form Requests** centralise validation, and **middleware‑enforced roles** guarantee authorization at the route level.

**Who it's for:** developers studying a realistic Laravel service‑layer architecture, role/permission enforcement, third‑party payment integration, and secure webhook handling.

---

## ✨ Key Features

### 👤 Role‑Based Access Control

Powered by [`spatie/laravel-permission`](https://spatie.be/docs/laravel-permission), enforced through route middleware on **both** the web and API layers.

| Role         | Capabilities                                                                                                                        |
| ------------ | ----------------------------------------------------------------------------------------------------------------------------------- |
| **Guest**    | Browse products by category, view product details, register / log in                                                                |
| **Customer** | Everything a guest can do **+** build a pending order, add / remove items (with live stock updates), cancel orders, and pay for them |
| **Admin**    | Everything a customer can do **+** create & delete categories and full product CRUD                                                 |

Seeded permissions include: `manage products`, `view products`, `place orders`, `manage orders`, `manage users`, `charge wallet`, `view wallet`, `make payment`.

### 🔐 Authentication & Verification

- Web authentication scaffolding via **`laravel/ui`** (login, register, password confirm/reset).
- **Email verification** enforced (`User implements MustVerifyEmail`, routes guarded by the `verified` middleware).
- **API authentication** via **Laravel Sanctum** tokens (register / login / logout issue and revoke tokens).

### 🛍️ Order & Inventory Management

- A single **pending order** acts as the active cart; it is created on demand when the first product is added.
- Adding a product **decrements** `available_quantity` and increases the order total; removing or cancelling **restores** stock.
- Order status lifecycle: `pending → paid → shipped → canceled`.

### 💳 E‑Payments (Factory Pattern)

A `PaymentGateway` interface plus a `PaymentFactory` keep the payment layer extensible — new gateways plug in behind a common contract:

- **PayPal** — via [`srmklive/paypal`](https://github.com/srmklive/laravel-paypal): create order → approve → capture, recording a `transactions` row on success.
- **Card payments (Visa / Mastercard)** — hosted checkout in sandbox mode: the gateway creates a payment session, redirects the customer to the provider's secure card form, and confirms the result through a **signed webhook** validated with **HMAC** signature comparison (`hash_equals`) before the order is marked paid. No card data ever touches the application.

Both gateways share the same `pay / success / cancel` contract, so the checkout screen simply lists whatever is registered in `config/payments.php`.

### 📦 Catalog & Categories

- Products belong to categories through a `category_product` many‑to‑many pivot (with an "All" catch‑all category).
- Product images are uploaded to the `public` storage disk on create/update and cleaned up on delete.
- Guests can filter the storefront by category.

---

## 🏗️ Architecture

The application separates concerns into thin controllers, dedicated service classes, and a contract‑based payment abstraction.

```
Request
  │
  ▼
Route (web.php / api.php)  ──►  Middleware  (auth · verified · role:*  · auth:sanctum)
  │
  ▼
Controller (thin)  ──►  Form Request (validation)
  │
  ▼
Service  (AuthService · ProductService · CategoryService · OrderService)
  │
  ├──►  Eloquent Models  (User · Product · Category · Order)
  │
  └──►  PaymentFactory ──► PaymentGateway (PayPalGateway · CardGateway)
```

**Design patterns in use**

| Pattern                              | Where                                                                       |
| ------------------------------------ | --------------------------------------------------------------------------- |
| **Service Layer**                    | `app/Services/*` — all business logic lives here; controllers just delegate |
| **Factory**                          | `PaymentFactory::make($method)` resolves a gateway implementation           |
| **Strategy / Interface**             | `App\Contracts\PaymentGateway` — a common `pay/success/cancel` contract     |
| **Constructor Dependency Injection** | Services injected into controllers via Laravel's container                  |
| **Form Request validation**          | `app/Http/Requests/*`                                                       |
| **Eloquent relationships**           | Models expose the relations consumed by services                            |

---

## 🧰 Technology Stack

### Backend

| Technology                    | Purpose                           |
| ----------------------------- | --------------------------------- |
| **PHP 8.2+**                  | Language                          |
| **Laravel 12**                | Application framework             |
| **Laravel Sanctum**           | API token authentication          |
| **Laravel UI**                | Web auth scaffolding (Bootstrap)  |
| **spatie/laravel-permission** | Roles & permissions               |
| **srmklive/paypal**           | PayPal REST integration           |
| **paypal/paypal-server-sdk**  | PayPal SDK (installed)            |
| **Laravel HTTP client**       | Card gateway REST calls           |
| **tightenco/ziggy**           | Expose named routes to JavaScript |
| **Laravel Tinker**            | REPL                              |

### Frontend

| Technology                              | Purpose                                    |
| --------------------------------------- | ------------------------------------------ |
| **Blade**                               | Server‑side templating                     |
| **Bootstrap 5**                         | UI framework / layout                      |
| **jQuery**                              | DOM utilities used by the admin theme      |
| **ApexCharts, Tabler Icons, SimpleBar** | Bundled admin dashboard theme assets       |
| **Tailwind CSS 4**                      | Installed & configured via Vite plugin     |
| **Sass**                                | `resources/sass/app.scss` compiled by Vite |
| **@paypal/paypal-js**                   | Client‑side PayPal SDK                     |

### Data, Build & Tooling

| Area                         | Technology                                                              |
| ---------------------------- | ----------------------------------------------------------------------- |
| **Database**                 | SQLite by default (MySQL/PostgreSQL supported via config)               |
| **Build tool**               | Vite 6 + `laravel-vite-plugin`                                          |
| **Testing**                  | PHPUnit 11, Mockery, Faker                                              |
| **Dev tooling**              | Laravel Pint (formatting), Pail (log tailing), Sail (Docker), Collision |
| **Queue / Cache / Sessions** | Database drivers (tables migrated)                                      |

---

## 📂 Folder Structure

```
E-commerce-project/
├── app/
│   ├── Contracts/            # PaymentGateway interface
│   ├── Http/
│   │   ├── Controllers/      # Web controllers (Home, Product, Category, Order, Auth)
│   │   │   └── Api/          # API controllers (Auth, Product, Category, Order)
│   │   └── Requests/         # Form Request validation classes
│   ├── Models/               # User, Product, Category, Order
│   ├── Providers/            # App & Event service providers
│   └── Services/             # Business logic
│       └── Payments/         # PaymentFactory, PayPalGateway, CardGateway
├── bootstrap/                # app.php (middleware aliases, routing) & providers.php
├── config/                   # Framework + payments, paypal, card, permission, sanctum
├── database/
│   ├── factories/            # UserFactory
│   ├── migrations/           # Schema (users, products, orders, pivots, transactions, permissions…)
│   └── seeders/              # Roles/permissions, categories, products, admin user
├── public/                   # Front controller + compiled admin theme assets
├── resources/
│   ├── css/ · sass/          # Styles (Tailwind entry + Sass)
│   ├── js/                   # Vite entry, bootstrap, payment helpers
│   └── views/                # Blade templates (auth, home, orders, products, payments, layouts)
├── routes/
│   ├── web.php               # Storefront, dashboard & payment routes
│   ├── api.php               # Sanctum‑guarded REST API
│   └── console.php           # Artisan closures
├── tests/                    # Feature & Unit (PHPUnit)
├── composer.json · package.json · vite.config.js · phpunit.xml
```

| Folder                  | Responsibility                                                         |
| ----------------------- | ---------------------------------------------------------------------- |
| `app/Services`          | All business logic; the heart of the application                       |
| `app/Services/Payments` | Gateway implementations + the factory that selects them                |
| `app/Http/Requests`     | Input validation, decoupled from controllers                           |
| `database/migrations`   | Full relational schema                                                 |
| `database/seeders`      | Bootstraps roles, permissions, categories, demo products, and an admin |
| `resources/views`       | Blade UI for both storefront and admin dashboard                       |

---

## 🚀 Installation

### Prerequisites

- **PHP 8.2+** with common extensions (`pdo_sqlite` or your DB driver of choice)
- **Composer**
- **Node.js 18+** and **npm**

### 1. Clone

```
git clone https://github.com/tardib777/E-commerce-project.git
cd E-commerce-project
```

### 2. Install dependencies

```
composer install
npm install
```

### 3. Environment

```
# No .env.example is tracked in this repo — create a .env, then generate a key:
php artisan key:generate
```

Populate the variables listed in [Environment Variables](#-environment-variables).

### 4. Database

The default connection is **SQLite**. Create the database file and migrate + seed:

```
# SQLite (default)
type nul > database/database.sqlite   # Windows
# touch database/database.sqlite       # macOS/Linux

php artisan migrate --seed
```

To use MySQL/PostgreSQL instead, set `DB_CONNECTION` and the related `DB_*` variables in `.env`, then run `php artisan migrate --seed`.

### 5. Storage

Product images are served from the public disk — link storage:

```
php artisan storage:link
```

### 6. Build the frontend

```
npm run dev      # development (hot reload)
# or
npm run build    # production assets
```

### 7. Run

```
php artisan serve
```

Or start the whole dev stack (server + queue worker + log tail + Vite) in one command:

```
composer run dev
```

The app is available at **<http://127.0.0.1:8000>** and redirects to the storefront (`/home`).

---

## ⚙️ Configuration

| File                    | Purpose                                                                               |
| ----------------------- | ------------------------------------------------------------------------------------- |
| `config/payments.php`   | Registry of available gateway keys/labels shown at checkout                           |
| `config/paypal.php`     | PayPal sandbox/live credentials & options                                             |
| `config/card.php`       | Card gateway (Visa/Mastercard) sandbox/live API, public & webhook keys                |
| `config/permission.php` | spatie roles/permissions configuration                                                |
| `config/sanctum.php`    | API token / stateful‑domain settings                                                  |
| `config/database.php`   | Default connection is `sqlite`                                                        |
| `bootstrap/app.php`     | Registers middleware aliases (`auth`, `role`, `verified`, `stateful`) and route files |

---

## 🔑 Environment Variables

> No `.env.example` is committed, so the following are **inferred from `config/*`**. Only the payment‑specific keys are project‑specific; the rest are standard Laravel variables.

**Core**

```
APP_NAME="E-Commerce"
APP_ENV=local
APP_KEY=              # php artisan key:generate
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=sqlite  # or mysql / pgsql
# DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD when not using sqlite

MAIL_MAILER=log       # required for email verification / password reset
```

**PayPal** (`config/paypal.php`)

```
PAYPAL_MODE=sandbox
PAYPAL_SANDBOX_CLIENT_ID=
PAYPAL_SANDBOX_CLIENT_SECRET=
PAYPAL_LIVE_CLIENT_ID=
PAYPAL_LIVE_CLIENT_SECRET=
PAYPAL_CURRENCY=USD
```

**Card payments — Visa / Mastercard** (`config/card.php`)

```
CARD_MODE=sandbox
CARD_SANDBOX_KEY=
CARD_SANDBOX_PUBLIC_KEY=
CARD_SANDBOX_WEBHOOK_SECRET=
CARD_CURRENCY=USD
# Live equivalents: CARD_KEY, CARD_PUBLIC_KEY, CARD_WEBHOOK_SECRET
```

---

## 🖥️ Usage

1. **Browse** the storefront at `/home` and filter products by category (guest‑accessible).
2. **Register** an account — new users are automatically assigned the `customer` role and must verify their email.
3. **As a customer**, open a product, add it to your order, review the order, and proceed to **checkout** to pay by **Visa/Mastercard** or **PayPal**.
4. **As an admin** (seeded), use the sidebar dashboard to create/delete categories and manage the product catalog.

**Seeded admin account** (from `UserSeeder`).

---

## 🔀 Route Files: `web.php` vs `api.php`

The application exposes **two entirely separate routing surfaces**, registered independently in `bootstrap/app.php`. They differ in prefix, middleware stack, authentication mechanism, and response format.

|                        | `routes/web.php`                                                   | `routes/api.php`                                                |
| ---------------------- | ------------------------------------------------------------------ | --------------------------------------------------------------- |
| **URL prefix**         | *(none)* — e.g. `/home`, `/orders/checkout/{order}`                | `/api` — e.g. `/api/login`, `/api/orders/{id}`                  |
| **Consumer**           | Browser (server‑rendered Blade pages)                              | Headless / mobile / SPA clients (JSON)                          |
| **Middleware group**   | `web` (sessions, cookies, CSRF)                                    | `api` (stateless)                                               |
| **Authentication**     | **Session / cookie** based — the `auth` middleware (session guard) | **Token** based — the `auth:sanctum` middleware (bearer tokens) |
| **Auth scaffolding**   | `Auth::routes(['verify' => true])` from **`laravel/ui`**           | Custom `Api\Auth\AuthController` + `AuthService`                |
| **Responses**          | `view(...)` / `redirect(...)`                                      | `response()->json(...)`                                         |
| **Verification guard** | `verified` middleware enforced                                     | —                                                               |
| **Controllers**        | `App\Http\Controllers\*`                                           | `App\Http\Controllers\Api\*`                                    |

Both files share the **same role middleware** (`role:` from spatie), but layer it on top of their respective authentication guards.

---

## 🔓 Authentication

The two route surfaces use **two different, independent authentication mechanisms** — it's important not to conflate them.

### 1. Web — session/cookie auth via `laravel/ui`

Defined in **`routes/web.php`** with `Auth::routes(['verify' => true])`, which registers the scaffolding shipped by the **`laravel/ui`** package:

- Login is handled by `App\Http\Controllers\Auth\LoginController` (the `AuthenticatesUsers` trait); registration, password reset/confirm and email verification are handled by the sibling `Auth\*` controllers.
- Authentication is **stateful**: Laravel's default **session guard** stores the authenticated user in the session and a cookie. Requests run through the `web` middleware group (**CSRF tokens**, session).
- Protected routes require the `auth` **and** `verified` middleware; after a successful login the user is redirected to `/home`.
- **No token is involved** — the browser is authenticated purely by its session cookie.

### 2. API — token auth via Laravel Sanctum

Defined in **`routes/api.php`** and handled by `App\Http\Controllers\Api\Auth\AuthController` → `AuthService`:

- Authentication is **stateless**: on login, `AuthService::login()` calls `createToken()` and returns a Sanctum **personal‑access token** (`plainTextToken`).
- Protected routes are guarded by the `auth:sanctum` middleware; there is **no session or CSRF** — every request must present the token.
- `logout` revokes the caller's current token (`currentAccessToken()->delete()`).

| Method | Endpoint        | Description                                 |
| ------ | --------------- | ------------------------------------------- |
| `POST` | `/api/register` | Create a customer account                   |
| `POST` | `/api/login`    | Returns a bearer token                      |
| `POST` | `/api/logout`   | Revokes the current token *(auth:sanctum)*  |
| `GET`  | `/api/user`     | Current authenticated user *(auth:sanctum)* |

Send the token as `Authorization: Bearer <token>` on protected API requests.

> **In short:** `web.php` → **`laravel/ui` session/cookie auth** (stateful, CSRF‑protected, cookie‑carried). `api.php` → **Sanctum token auth** (stateless, bearer‑token‑carried). They do not share a login flow or a guard.

---

## 🛡️ Authorization

Roles and permissions are managed by **spatie/laravel-permission** and enforced with the `role:` middleware.

- **Two roles:** `admin` and `customer` (seeded in `RolesAndPermissionsSeeder`).
- Web route groups are wrapped in `role:admin`, `role:customer`, or `role:admin|customer`.
- API route groups are wrapped in `auth:sanctum` + the same role middleware.
- Newly registered users receive the `customer` role automatically (`AuthService::register`).

---

## 🗄️ Database

Core relational model (SQLite by default):

| Table              | Key columns                                                        | Notes                                      |
| ------------------ | ------------------------------------------------------------------ | ------------------------------------------ |
| `users`            | firstname, lastname, email (unique), password, email\_verified\_at | Sanctum + spatie traits, `MustVerifyEmail` |
| `categories`       | name                                                               | Includes an "All" catch‑all                |
| `products`         | name, description, price, available\_quantity, category\_id, image |                                            |
| `orders`           | user\_id, quantity, total\_price, status                           | status ∈ `pending/paid/shipped/canceled`   |
| `category_product` | category\_id, product\_id                                          | Many‑to‑many pivot                         |
| `order_product`    | order\_id, product\_id, **quantity, price**                        | Cart line items (pivot with payload)       |
| `transactions`     | user\_id, order\_id, amount, method, status, transaction\_id       | method ∈ `paypal/card`                     |
| spatie tables      | roles, permissions, model pivots                                   | RBAC                                       |
| framework tables   | sessions, cache, jobs, personal\_access\_tokens                    | Sanctum + DB drivers                       |

**Relationships**

```
User  1─────*  Order
Order *─────*  Product   (via order_product, withPivot quantity & price)
Product *───*  Category  (via category_product)
User  1─────*  Transaction
Order 1─────*  Transaction
```

> Note: an early `orders.product_ids` column exists in the schema from an initial single‑product design; the working relationship is the `order_product` pivot.

---

## 🧭 Web Routes

Server‑rendered routes defined in **`routes/web.php`**. All routes below the auth group require the `auth` + `verified` middleware; role scoping is noted per group. Handlers return Blade **views** or **redirects**.

### Public

| Method | URI                   | Name            | Handler                                                       |
| ------ | --------------------- | --------------- | ------------------------------------------------------------- |
| `GET`  | `/`                   | —               | Redirects to `home`                                           |
| `GET`  | `/home/{id?}`         | `home`          | `HomeController@index` — storefront, optional category filter |
| `GET`  | `/products/show/{id}` | `products.show` | `ProductController@show`                                      |

### Authentication (`laravel/ui` scaffolding)

`Auth::routes(['verify' => true])` registers login, registration, logout, password reset/confirm, and email‑verification routes.

### Admin only (`auth` + `verified` + `role:admin`)

| Method   | URI                     | Name               | Handler                     |
| -------- | ----------------------- | ------------------ | --------------------------- |
| `GET`    | `/products/create`      | `products.create`  | `ProductController@create`  |
| `POST`   | `/products/store`       | `products.store`   | `ProductController@store`   |
| `GET`    | `/products/edit/{id}`   | `products.edit`    | `ProductController@edit`    |
| `POST`   | `/products/update/{id}` | `products.update`  | `ProductController@update`  |
| `DELETE` | `/products/delete/{id}` | `products.destroy` | `ProductController@destroy` |

### Customer only (`auth` + `verified` + `role:customer`)

| Method   | URI                                              | Name                             | Handler                                |
| -------- | ------------------------------------------------ | -------------------------------- | -------------------------------------- |
| `GET`    | `/orders/index`                                  | `orders.index`                   | List the customer's orders             |
| `GET`    | `/orders/addProduct/{product_id}`                | `orders.addProductPage`          | Add‑to‑order page                      |
| `POST`   | `/orders/addProduct`                             | `orders.addProduct`              | Add product to the pending order       |
| `DELETE` | `/orders/product/delete/{order}/{product_id}`    | `orders.removeProduct`           | Remove an item (restores stock)        |
| `PUT`    | `/orders/cancel/{order_id}`                      | `orders.cancel`                  | Cancel order (restores stock)          |
| `GET`    | `/orders/checkout/{order}`                       | `orders.checkout`                | Checkout + payment method selection    |
| `GET`    | `/orders/pay/{method}/{order_id}`                | `orders.payment.pay`             | Start payment via the chosen gateway   |
| `GET`    | `/orders/success/{method}/{order_id}/{request?}` | `orders.payment.success`         | Payment success callback               |
| `GET`    | `/orders/cancel/{method}/{order_id}`             | `orders.payment.cancel`          | Payment cancel callback                |
| `POST`   | `/payments/callback`                             | `orders.payment.card.callback`   | Card gateway webhook — **CSRF‑exempt** |

> All order/payment actions are handled by `App\Http\Controllers\OrderController`. Payment dispatch goes through `PaymentFactory::make($method)`.

---

## 🌐 API

The API is served under `/api` and returns JSON.

### Public

| Method | Endpoint                       | Description            |
| ------ | ------------------------------ | ---------------------- |
| `GET`  | `/api/categories`              | List categories        |
| `GET`  | `/api/categories/{id}`         | Products in a category |
| `POST` | `/api/register` · `/api/login` | Auth                   |

### Admin only (`auth:sanctum` + `role:admin`)

| Method   | Endpoint                    | Description             |
| -------- | --------------------------- | ----------------------- |
| `POST`   | `/api/categories/store`     | Create category         |
| `POST`   | `/api/categories/delete`    | Delete category by name |
| `POST`   | `/api/products/create`      | Create product          |
| `PUT`    | `/api/products/update/{id}` | Update product          |
| `DELETE` | `/api/products/delete/{id}` | Delete product          |

### Customer / Admin (`auth:sanctum` + `role:admin|customer`)

| Method | Endpoint                 | Description                    |
| ------ | ------------------------ | ------------------------------ |
| `POST` | `/api/orders/{order_id}` | Add/remove an item on an order |

**Response format:** controllers return `response()->json([...], <status>)`; validation failures return Laravel's standard `422` error payload. Requests are validated through Form Request classes (`ProductRequest`, `CategoryRequest`, `RegisterRequest`, …).

> **Status note:** several order endpoints declared in `routes/api.php` (`index`, `show`, `store`, `cancel`, item add/delete) map to controller methods that are currently **commented out / in progress**, so the API's order surface is **partially implemented**. The web `OrderController` is the complete, working order flow.

---

## ⏱️ Background Jobs, Events & Scheduler

- **Queues:** the `jobs` table is migrated and `composer run dev` starts a `queue:listen` worker, but **no application jobs are defined** in this repository yet.
- **Events / Listeners:** an `EventServiceProvider` is registered with an empty listener map — no custom events/listeners are implemented.
- **Scheduler:** `routes/console.php` contains only the default `inspire` command; **no scheduled tasks** are defined.

*(Documented here for completeness; the scaffolding exists but is currently unused.)*

---

## 🧪 Testing

The project is configured for **PHPUnit 11** with separate `Unit` and `Feature` suites (`phpunit.xml`). The repository currently contains the framework's **example tests only**.

```
php artisan test
# or
./vendor/bin/phpunit
```

The test environment forces `APP_ENV=testing`, `array` cache/session drivers, and a `sync` queue.

---

## 🔒 Security

- **Password hashing** via Laravel's `hashed` cast and `Hash::make`.
- **Email verification** required before accessing protected areas.
- **RBAC** enforced by middleware on every protected web and API route.
- **No card data stored or handled:** card details are entered on the gateway's hosted page, so the application never sees or persists a PAN.
- **CSRF protection** on web forms, with a **deliberate, scoped exemption** for the card gateway webhook.
- **Signed webhooks:** the callback handler recomputes an **HMAC** signature over the raw payload and compares it with `hash_equals` before trusting the notification.
- **Strong registration rules:** `RegisterRequest` enforces MX‑validated, spoof‑resistant emails and passwords requiring mixed case, numbers and symbols (min 8).
- **API tokens** issued and revocable via Sanctum.

> Hardening reminder: rotate the seeded admin credentials and use sandbox keys only in non‑production before going live.

---

## ⚡ Performance

- **Eager loading** of order line items (`Auth::user()->orders()->with('products')`) to avoid N+1 queries.
- **Foreign‑key constrained** relationships across the schema.
- **Atomic payment writes** wrapped in `DB::transaction` when recording successful captures.
- **Database‑backed cache, queue and session** drivers (tables migrated) ready for scaling out.
- **Vite** production bundling (`npm run build`) with `optimize-autoloader` enabled in Composer.

---

## 🚢 Deployment

Based on the repository configuration:

1. Provision **PHP 8.2+**, Composer and Node.js on the target host.
2. `composer install --optimize-autoloader --no-dev` and `npm ci && npm run build`.
3. Set production `.env` values (real DB, mailer, and **live** PayPal and card‑gateway keys).
4. `php artisan migrate --force` and `php artisan storage:link`.
5. Cache framework state: `php artisan config:cache route:cache view:cache`.
6. Run a **queue worker** if/when jobs are added, and serve behind a web server (Nginx/Apache) pointing at `public/`.
7. `laravel/sail` is available for a Docker‑based local/staging environment.

*(No Dockerfile, CI/CD workflow, or reverse‑proxy config is committed — deployment specifics beyond the above are left to the operator.)*

---

## 🧑‍💻 Development

- **One‑command dev stack:** `composer run dev` runs the PHP server, queue listener, log tailer (Pail), and Vite concurrently.
- **Code style:** format with **Laravel Pint** — `./vendor/bin/pint`.
- **Named routes in JS:** Ziggy is available for referencing Laravel routes from the frontend.
- **Local mail:** set `MAIL_MAILER=log` to capture verification/reset emails in `storage/logs`.

---

## 🗺️ Future Improvements

Natural extensions of the current architecture:

- **Complete the REST API** order endpoints (implement list, show, create, cancel, item management) to match the web flow.
- **Add saved payment methods** so returning customers can re‑use a stored card token.
- Introduce **queued notifications** (e.g. order confirmation emails) using the already‑migrated jobs table.
- Add **feature tests** covering the order lifecycle, RBAC, and payment callbacks.
- Extract an **`uploadFile()` helper** into a committed, autoloaded helpers file (it is referenced by `ProductService` but not defined in the tracked source).
- Add **API Resource classes** for consistent JSON shaping.

---

## 📄 License

The Composer manifest declares the **MIT License** (`composer.json`), consistent with the Laravel skeleton. Note that `package.json` separately declares `ISC`. **No standalone `LICENSE` file is committed** to the repository — add one to make the license unambiguous.
