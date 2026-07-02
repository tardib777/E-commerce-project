# LinkedIn Project Post — Laravel E-Commerce Platform

Copy-paste ready content for a LinkedIn "Featured" project entry or a project post.

---

## 🔗 GitHub

https://github.com/tardib777/E-commerce-project

---

## 📖 Description

I built a full-stack e-commerce platform with **Laravel 12**, designed around clean, testable architecture rather than a single monolithic controller pile. The app ships from one codebase in two forms: a server-rendered storefront/admin dashboard (Blade + Bootstrap 5) for browsers, and a stateless REST API (Laravel Sanctum) for headless or mobile clients — each with its own authentication mechanism, sharing the same role-based authorization rules underneath.

It supports two roles — **admin** and **customer** — plus guest browsing, a full order lifecycle with live inventory adjustment, and a pluggable payment layer that currently supports **PayPal** and **cryptocurrency payments via NOWPayments**, including signed webhook verification.

---

## ✨ Key Features

- **Role-based access control** (guest / customer / admin) enforced via middleware on both the web and API layers, powered by `spatie/laravel-permission`
- **Dual authentication surfaces** — session/cookie auth (`laravel/ui`) for the web app, Sanctum bearer tokens for the API
- **Live order & inventory management** — adding/removing items adjusts stock in real time; a full status lifecycle (`pending → paid → shipped → canceled`)
- **Pluggable payment gateways** — PayPal (create → approve → capture) and cryptocurrency checkout via NOWPayments, with a coin/currency picker
- **Secure webhook handling** — NOWPayments IPN callbacks are verified with an HMAC-SHA512 signature comparison before an order is marked paid
- **Product catalog** with category filtering, image uploads, and admin CRUD
- **Email verification** enforced end-to-end before customers can access protected areas

---

## 🏗️ Architecture

Rather than fat controllers, the app is organized around clear boundaries:

```
Request → Middleware (auth · verified · role · sanctum) → Controller (thin)
        → Form Request (validation) → Service Layer (business logic)
        → Eloquent Models
        → PaymentFactory → PaymentGateway (PayPal / NOWPayments)
```

**Patterns applied:**
- **Service Layer** — all business logic lives in `app/Services`, keeping controllers thin
- **Factory + Interface (Strategy)** — `PaymentFactory` resolves a `PaymentGateway` implementation at runtime, so adding a new gateway (e.g. Stripe) means implementing one contract, not touching checkout logic
- **Form Requests** — validation is centralized and decoupled from controllers
- **Constructor dependency injection** — services are injected via Laravel's container, keeping code testable

---

## 🧰 Technologies

**Backend:** PHP 8.2, Laravel 12, Laravel Sanctum, Laravel UI, spatie/laravel-permission, srmklive/paypal, PayPal Server SDK, Laravel Cashier (Stripe, scaffolded)

**Frontend:** Blade, Bootstrap 5, jQuery, Tailwind CSS 4, Sass, PayPal JS SDK, Stripe.js

**Tooling:** Vite 6, PHPUnit 11, Laravel Pint, Laravel Sail (Docker), SQLite (default) with MySQL/PostgreSQL support

---

## 🧩 Challenges & What I Learned

- **Designing a payment layer that could grow.** Hardcoding "if PayPal do X, if crypto do Y" checkout logic would have made adding a third gateway painful. Building a `PaymentGateway` interface behind a `PaymentFactory` meant PayPal and NOWPayments share one contract (`pay/success/cancel`), and Stripe can be added later without touching the checkout flow.
- **Trusting external payment webhooks safely.** NOWPayments sends asynchronous IPN callbacks that update order status outside the normal request flow — a prime target for spoofing. I implemented HMAC-SHA512 signature verification with constant-time comparison (`hash_equals`) before ever trusting a webhook payload, and scoped a deliberate CSRF exemption to that single route rather than weakening CSRF protection globally.
- **Keeping inventory consistent under a shopping-cart-as-order model.** Instead of a separate cart table, a single "pending" order acts as the active cart. That simplified the schema but meant every add/remove/cancel action had to correctly decrement or restore `available_quantity` — getting this atomic and consistent took care around where stock mutations happen relative to order totals.
- **Running two authentication systems side by side without leaking concerns.** The web app uses stateful session/cookie auth with CSRF protection; the API uses stateless Sanctum bearer tokens. Both had to enforce the *same* role rules (`admin`/`customer`) without duplicating authorization logic — solved by layering the same `role:` middleware on top of each guard independently.

---

## 💡 Suggested short caption (for the post itself)

> Built a full-stack e-commerce platform on Laravel 12 with role-based access control, a service-oriented architecture, a Sanctum-secured REST API, and a factory-pattern payment layer supporting PayPal and cryptocurrency checkout (with HMAC-verified webhooks). Code on GitHub 👇
>
> #Laravel #PHP #WebDevelopment #SoftwareEngineering #Ecommerce #BackendDevelopment
