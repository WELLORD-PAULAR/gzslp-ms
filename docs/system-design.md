# GZSLP-MS System Design

**Document type:** Living, repository-local system design description  
**Source:** ENGGSF2 Software Design Document, Prelim Design Check, dated 2026-08-22  
**Last reviewed:** 2026-10-22
**Implementation status:** Early MVP; this document distinguishes implemented behavior from proposed work.

This document mirrors the supplied SDD's organization while describing the application as it exists in this repository. It is not a replacement for the course SDD or the approved SRS. Keep the course document and this implementation record aligned as requirements and code change.

## Status Legend

- **Implemented:** Present in the application and covered by automated tests.
- **Partial:** Some foundational behavior exists, but the SDD capability is incomplete.
- **Planned:** Required or described by the SDD but not implemented in the current application.
- **Decision needed:** The source documents or current model leave a consequential design choice unresolved.

## 1. Introduction

### 1.1 Purpose

Record the current architecture, data, interfaces, security boundaries, implementation state, and next development work for GZSLP-MS. Developers should update this file when a feature, schema, or system boundary changes.

### 1.2 Scope

The SDD describes an online selling platform for Graphic Zone's printing services. Customers should be able to request quotations for supplied designs and customize services. Employees should manage offered services, accounts, orders, inventory, and reports. The SDD excludes the rice retail operation and an AI chatbot.

The current code is an early Laravel storefront foundation: customer/employee/admin accounts, profile and password management, verification/reset flows, employee shop operations, admin account/report controls, searchable product/service catalogs, persisted carts, order submission/tracking, and raw-supply inventory linked to finished-product variants. Payment processing remains outside the current implementation.

### 1.3 Terms

| Term | Meaning in this document |
|---|---|
| Customer | A registered account with the `customer` role. |
| Employee | An account with the `employee` role; may manage shop operations but not accounts or reports. |
| Admin | An account with the `admin` role and access to shop operations, account management, and reports. |
| Product | A finished item in the catalog, such as a paper product, mug, shirt, tarpaulin, or custom product. Paper specifications are optional and apply only to paper products. |
| Service | A printing or design service in the catalog; a starting price may be unspecified. |
| Set design | A pre-created product listing customers can order; no separate design entity is used in this version. |
| Custom design | Customer-supplied artwork attached privately to an order, optionally paired with a quote-only service. |

### 1.4 Document Overview

Sections 2-5 describe references, architecture, modules, and dependencies. Sections 6-8 describe interfaces, data, and detailed behavior. Section 9 maps requirements to implementation evidence. Appendix A records implementation decisions and revisions.

## 2. References

- Cabrera, G. et al. (2026). *Graphic Zone Software Requirements Specification*. Internal, unpublished course deliverable, as cited by the supplied SDD.
- University of Baguio, School of Information Technology. (2026-08-22). *Graphic Zone Sustainable Livelihood Program – Management System: Software Design Document*, Prelim Design Check.
- IEEE Std 1016-2009, *Systems Design—Software Design Descriptions*.
- Laravel 12 application code and feature tests in this repository.

## 3. Architectural Design

### 3.1 Logical View

The application uses Laravel's server-rendered MVC style:

1. A browser requests a named route.
2. Route middleware checks guest/authenticated/admin access.
3. A controller or route action validates/loads application data through Eloquent models.
4. A Blade view renders HTML; Vite supplies CSS and JavaScript assets.
5. Laravel's configured SQL connection persists records.

The main code locations are `routes/web.php`, `app/Http/Controllers`, `app/Http/Requests`, `app/Models`, `resources/views`, and `database/migrations`.

### 3.2 Physical View

Users access the app through a web browser. Laravel/PHP and the web server handle application logic; SQLite is the current local database configuration. Production database, hosting, backups, and deployment topology are not specified in this repository and remain deployment decisions.

The application uses Vite with Tailwind CSS for compiled frontend assets. Payments are processed through a swappable `PaymentGateway` contract whose only driver is a local dummy gateway (`DUM-` reference, always approved, no real charge). Account verification uses one-time codes delivered by Laravel mail and a custom `sms` notification channel backed by a log-only SMS gateway. Both providers are wired in dedicated service providers and can be replaced with real integrations without changing controllers.

## 4. Decomposition Description

### 4.1 Module Decomposition and Status

| Module | Current status | Present behavior | Remaining work from the SDD |
|---|---|---|---|
| Account management | Partial | Customer registration, login/logout, profile/phone/password editing, six-digit email/SMS verification codes, password reset, active-state protection, and customer/employee/admin roles. Admins create/update/deactivate accounts and may delete accounts without order or inventory history; self-deletion and deletion of the last active admin are blocked. | Verification is advisory, not required for shopping; audit trail, fine-grained staff permissions, and broader privacy workflows remain. |
| Product catalog | Partial | Employee/admin create/list/edit/delete; optional public image, paper/mug/shirt/tarpaulin/custom categories, default and custom option variants, and per-variant pricing. Variant recipes derive current finished-unit availability from on-hand supplies; customers see availability on product details. | Advanced discovery, price-changing variant bundles, and broader product metadata remain. |
| Service management | Partial | Employee/admin create/list/edit/delete; optional public image, starting price/visibility and stale-edit protection. Customers search/view details and add priced/quote-only services. Orders support private custom-artwork uploads. | Multi-message quote conversation, estimate/fulfillment workflow, richer service options. |
| Cart management | Partial | Each customer has a persisted cart; add products/services, merge repeated adds, update/remove quantities, show item count, and calculate prices from current catalog records. Product cart lines carry the selected variant and variant price; cart changes validate aggregate raw-supply requirements against current stock; quote-only services show a pre-quote subtotal. Inventory is not reserved in the cart. | Cart customization/options, quote request submission, payment, and inventory reservation. |
| Order management | Partial | Customers submit carts with notes and optional private artwork, view their own history/details, and cancel eligible requests. Admins review orders, download artwork, send quotes, and advance status. Order lines preserve catalog snapshots including the selected product variant and encoded options. | Payment, more advanced fulfillment estimates, shipping/address data, notifications, and richer staff workflows. |
| Transaction processing | Partial | Customers pay a priced or accepted-quote order from its detail page through the dummy gateway. Payments are recorded with gateway, method, amount, currency, status, reference and provider response; partial payments, declines, duplicate-payment guards and staff visibility are covered by tests. | Real gateway driver, refunds, webhooks, and staff-recorded manual payments remain. |
| Inventory management | Partial | Staff track raw supplies separately from finished catalog products; suppliers link to supplies; receipts, usage and stocktakes are recorded with balance history; low-stock items are flagged. Product variant availability is calculated from its supply recipe and current balances. Production start consumes each product variant’s recipe once, and insufficient stock blocks the transition. | Purchase orders/cost valuation, deeper fulfillment/status reporting, and inventory-driven quote rules remain. |
| Report generation | Partial | Admin-only date-filtered order activity report with status counts, completed-order count/value, catalog active/hidden counts, pagination, and browser print. Payment status is visible per order in the admin order list/detail, but the report still does not label completed value as paid revenue. | Paid-revenue report from successful payments, inventory report from stock/supplier data, CSV/PDF export and additional measures remain. |
| Customer storefront | Partial | Auth overlays, protected navigation, searchable/paginated product/service catalog with images/details, cart/order flow, set designs as products and private custom-artwork attachment. Product details show per-variant inventory-derived quantities and disable sold-out options. | Payment/fulfillment flow and broader product discovery remain. |

### 4.2 Current Key Processes

**Registration:** Guest submits name, email, optional phone, password, and confirmation. A Form Request validates the input; registration creates an active customer account, dispatches Laravel's registered event for email verification, logs it in, regenerates the session, and redirects to the customer account page. Role is not accepted from public input.

**Login:** Guest submits email/password. The login Form Request validates credentials, applies a five-failure rate limit keyed by normalized email and IP, authenticates only active accounts, regenerates the session, and routes employees/admins to shop management and customers to their account.

**Catalog management:** An employee/admin submits a create/update form. A role-aware Form Request authorizes and validates fields. Updates compare the submitted `lock_version` with the current database version, increment on success, and reject stale changes. Delete uses a CSRF-protected DELETE form. Products support paper, mug, shirt, tarpaulin, and custom categories, with paper specifications limited to paper products. Each product can have multiple `product_variants`, each with a price, option fields, and optional raw-supply recipe. A default `Standard` variant preserves compatibility with products that have no custom variants.

**Customer catalog viewing:** Active authenticated users request Products or Services. Routes query only `is_active` records, support search/filter and pagination, and expose individual detail pages. Guests are redirected to login. Product details calculate each variant's available finished-unit quantity from the minimum producible quantity across its active supply recipe; variants without a recipe are labeled as not inventory-tracked. Sold-out options cannot be selected.

**Cart management:** An authenticated non-admin customer adds an active product/service with a quantity from 1 to 99. The application creates or reuses that customer's cart and combines repeated additions of the same catalog entry. Product cart lines include the selected `product_variant_id`, which keeps the chosen option set and variant price in the cart. Additions and quantity updates check the combined supply requirements for all product lines against current stock in a transaction. Quantity updates and removal are scoped to the authenticated user's cart. The cart reads the current catalog price and calculates amounts in integer cents. Quote-only services remain in the cart but make the final total pending; the view shows a pre-quote subtotal. Cart contents do not reserve inventory, so production-start validation remains authoritative if stock changes after a cart update.

**Order submission:** A customer submits a non-empty cart with an optional note. Within one database transaction, the system locks the cart and lines, rechecks that catalog entries remain active, calculates the subtotal from current catalog prices, creates the order and immutable name/description/price/quantity/product-variant snapshots, then clears the cart. If a catalog item became inactive, the transaction is rejected and the cart remains intact. For a priced order, the same transaction also deducts the ordered variants' supply recipes (see Supply inventory), so a shortage rejects the checkout and nothing is saved. A cart containing a quote-only service creates a `quote_requested` order with a pre-quote subtotal and no final total.

Customers may also attach one JPG, PNG, or PDF artwork file (up to 10 MB) to the order. It is stored on Laravel's private local disk and is not served through a public URL. Customer downloads are scoped to the customer's order; staff downloads are behind the shop-management Gate. If order creation fails, the just-uploaded file is removed.

**Order tracking and administration:** Customers can list and view only their own orders. They may cancel orders in `pending`, `quote_requested`, or `quote_sent`; later statuses cannot be customer-cancelled. Admin status transitions are constrained: `pending` to `processing`/`cancelled`; `quote_requested` to `quote_sent`/`cancelled`; `quote_sent` to `cancelled`; `processing` to `completed`/`cancelled`. A customer must explicitly accept a sent quote, which records `customer_accepted_at` and moves the order to `processing`. Sending a quote requires a valid quoted total. Supplies are deducted once per order: at checkout for priced orders, and when the customer accepts the quote for quote orders. Moving a legacy order (one with no deduction recorded) into `processing` still deducts at that point, and missing stock rejects the transition. Cancelling an order, by the customer or by an admin (including from `processing`), returns the deducted supplies with `returned` movements.

**Account maintenance:** Customers update their own contact details/password; changing email or phone clears that channel's verification and sends a fresh code. Laravel's `Registered` hook and the `MustVerifyEmail` notification hook now deliver a hashed, single-use six-digit code by mail; a phone number additionally receives an SMS code through the custom channel. Codes expire after 10 minutes, allow five attempts, and are throttled to one request per 60 seconds per channel; the newest code supersedes earlier ones. Password reset still uses Laravel's broker. Admins create/update/deactivate accounts and may delete accounts with no order or inventory-movement history; admins cannot delete their current account or the last active administrator. Employees can manage shop operations but cannot manage accounts or view reports. Verification prompts but does not block shopping.

**Payment:** After an order has a final total (priced at checkout, or quoted and accepted), the customer can pay the outstanding balance from the order detail page. `PaymentService` locks the order, records a pending payment, asks the configured gateway to charge it, and stores the succeeded/failed outcome with the provider response. Failed charges stay visible as failed records; cancelled and quote-pending orders cannot be paid, and a fully paid order cannot be charged again. Staff see payment status in the order list and full history on the order detail page.

**Concurrency:** Product and service edit forms submit `lock_version`. If another employee/admin has saved first, the stale request is rejected with a reload/reapply message instead of overwriting the newer record. Product variants also use `lock_version` when edited so the price/recipe update is not overwritten by a stale form.

**Supply inventory:** Staff create supply records with category, unit, reorder threshold and optional suppliers. New records start at zero. Receipts add quantity and require a supplier; usage subtracts quantity and cannot make stock negative; stocktakes set the balance to a physical count. Each movement locks the item in a database transaction, updates its balance and appends a before/after record with the acting user. Low stock means an active supply at or below its reorder threshold. Product variants can define a supply recipe: current availability is the minimum number of finished units producible across the recipe's supplies. Cart changes validate aggregate recipes against current balances. Deduction is atomic: if any required material is short, the order is not created (or keeps its current status) and no stock is deducted. Variants without a recipe are not stock-tracked and deduct nothing. Because the displayed product count is computed from supply balances, it falls as soon as an order deducts supplies and rises when a cancellation returns them.

## 5. Dependency Description

| Component | Depends on | Type | Reason / status |
|---|---|---|---|
| Account/auth | Laravel authentication, sessions, users table | Internal/framework | Registration, authentication, authorization, and session lifecycle. Implemented. |
| Admin catalog | Account/auth Gate `manage-shop`, Product/Service models | Internal | Restricts catalog changes to employees/admins; optimistic versions protect concurrent edits. Implemented at CRUD level. |
| Customer catalog | Product/Service models and authenticated session | Internal | Shows active listings only to signed-in customers. Implemented. |
| Cart | Account, product/service catalog, product variants | Internal | Partially implemented; customer Gate, ownership-scoped cart records, active checks, selected-variant pricing, and server-side pricing are in place. |
| Orders | Account, cart, product/service catalog, product variants | Internal | Partially implemented; cart ownership, availability recheck, price calculation, snapshots, order history, and status workflows are in place. Variant recipe stock consumption starts at production, and payment remains separate. |
| Transactions | Orders, `PaymentGateway` contract and selected method | Internal/external | Partial; dummy gateway, payment records, and balance/status rules are implemented. Real provider and manual payments need decisions. |
| Inventory | Products, product variants, suppliers, raw-supply recipes and movement records | Internal | Partial; raw supply model and variant consumption rules are in place, but purchase-order/cost valuation and broader fulfillment tracking remain open. |
| Supply inventory | Suppliers, staff accounts, inventory movement ledger, product variant recipes | Internal | Partial; receipts/usage/stocktakes, low-stock thresholds, and order-time consumption and cancellation returns are tracked. |
| Reports | Orders and product/service catalog; future transactions/inventory | Internal | Partial; current report reads order/catalog records and is admin-only. Paid revenue and stock reports await their source modules. |
| Account administration | Admin-only Gate and User model | Internal | Admins create/update/deactivate accounts and delete only records without order or inventory history. |
| Email/SMS | Laravel mail, `SmsGateway` contract, verification code records | Internal/external | Partial; one-time verification codes are sent by mail and by a log-only SMS driver. Real SMS provider and cost decisions remain. |
| Persistence | SQLite locally; SQL database abstraction | External/runtime | Laravel database connection. Production database not yet specified. |
| Frontend build | Vite, Tailwind CSS | Build-time | Compiles CSS/JS used by Blade pages. |

## 6. Interface Description

### 6.1 User Interfaces

| Interface | Users | Current exchange / behavior | Status |
|---|---|---|---|
| Storefront home | Guest/customer | Shows print-shop introduction; guests see login/register; signed-in customers see catalog links. Admins are redirected to admin dashboard. | Partial |
| Login overlay | Guest | Email, password, optional remember-me; validation and credential errors return to overlay. | Implemented |
| Registration overlay | Guest | Name, email, password, confirmation; successful signup creates customer role. | Implemented |
| Customer Products | Active authenticated account | Search/filter active paper products, paginate, view detail and add product/quantity to cart. | Partial |
| Customer Services | Active authenticated account | Search services, view details, add priced services or request a quote. | Partial |
| Cart | Authenticated customer | Add product/service, change quantity, remove items, view subtotal, add an optional shop note, and submit an order or quote request. Payment checkout is not present. | Partial |
| Customer order history/details | Authenticated customer | View own order status, item snapshots, subtotal/quote, shop notes, and cancel an eligible order. | Partial |
| Admin order management | Admin | Review all submitted orders, send a quoted total, add customer-facing note, and perform allowed status transitions. | Partial |
| Admin order report | Admin | Filter order activity by date range; see order statuses, completed order value, and active/hidden catalog counts; browser print. | Partial |
| Customer account settings | Active account | Edit own name/email/phone; change password after current-password check. | Partial |
| Admin account management | Admin | Search/filter users; create/update customer, employee, admin roles; deactivate; protect last active admin. | Partial |
| Staff supply inventory | Employee/admin | Create/edit/deactivate supplies and suppliers; receive/use/stocktake; review low stock and movement history. | Partial |
| Admin dashboard | Employee/admin | Links to shop operations; Reports/Accounts visible only to admin. | Partial |
| Admin product/service management | Employee/admin | List, create, edit, hide/show, delete; stale updates are rejected. | Implemented at CRUD level |

### 6.2 External and Internal Interfaces

No external payment or SMS API is called: the dummy gateway approves locally and the SMS gateway writes to a log channel. Email verification codes use Laravel's configured mailer. Internal route authorization uses Laravel's `auth`, `guest`, and Gate middleware. Form Requests enforce validation/authorization, controllers use Eloquent models, and Blade templates display data.

## 7. Data Design

### 7.1 Current Entities and Relationships

```mermaid
erDiagram
    USERS ||--o| CARTS : owns
    CARTS ||--o{ CART_ITEMS : contains
    PRODUCTS ||--o{ PRODUCT_VARIANTS : has
    PRODUCTS o|--o{ CART_ITEMS : referenced_by
    PRODUCT_VARIANTS ||--o{ CART_ITEMS : selected_variant
    SERVICES o|--o{ CART_ITEMS : referenced_by
    USERS ||--o{ ORDERS : places
    ORDERS ||--|{ ORDER_ITEMS : snapshots
    PRODUCTS o|--o{ ORDER_ITEMS : source
    PRODUCT_VARIANTS o|--o{ ORDER_ITEMS : variant_snapshot
    SERVICES o|--o{ ORDER_ITEMS : source
    INVENTORY_ITEMS }o--o{ SUPPLIERS : sourced_from
    INVENTORY_ITEMS ||--o{ INVENTORY_MOVEMENTS : records
    PRODUCT_VARIANTS }o--o{ INVENTORY_ITEMS : consumes_via_recipe
    SUPPLIERS o|--o{ INVENTORY_MOVEMENTS : received_from
    USERS ||--o{ INVENTORY_MOVEMENTS : records
```

`users` has a one-to-zero-or-one relationship with `carts` (`carts.user_id` is unique) and a one-to-many relationship with `orders` and `inventory_movements`. A cart has zero or more items. Each cart item references either a product or a service; the application enforces this exclusive choice, while the schema does not currently include an XOR check constraint. Each product now has one or more variants; cart and order records keep the selected variant so pricing and option metadata stay stable even if the catalog later changes. An order belongs to one user and has one or more item snapshots. Order-item links to products/services are nullable and use `ON DELETE SET NULL`; snapshot fields preserve history if a catalog record is deleted. Product variants can consume raw supplies via a `product_variant_supplies` recipe, and when an order moves to `processing` (or a quote is accepted) the system validates that recipe and deducts the required quantity. Inventory items represent raw supplies, not finished catalog products. Supplies link to multiple suppliers; movement history records each balance change and actor. Payment/address entities are still absent.

### 7.2 Current Data Dictionary

| Table | Field | Type / constraint | Meaning |
|---|---|---|---|
| `users` | `id` | Integer primary key | Account identifier. |
| `users` | `name` | String, required | Display name. |
| `users` | `email` | String, unique, required | Login identifier. |
| `users` | `password` | String, hashed by model cast | Credential hash. |
| `users` | `role` | String, indexed, default `customer` | Authorization role: `customer`, `employee`, or `admin`; public signup cannot assign it. |
| `users` | `phone` | Nullable Varchar(25) | Optional contact number. |
| `users` | `is_active` | Boolean, indexed, default true | Inactive accounts cannot sign in or access protected account/store routes. |
| `users` | `email_verified_at`, `phone_verified_at`, `remember_token`, timestamps | Nullable/framework fields | Verification timestamps for each contact channel and framework account/session support. |
| `products` | `id` | Integer primary key | Catalog identifier. |
| `products` | `name` | String, required | Product title. |
| `products` | `description` | Nullable text | Product details. |
| `products` | `category` | String, default `paper`, indexed | Finished-item category such as `paper`, `mug`, `shirt`, `tarpaulin`, or `other`. |
| `products` | `price` | Decimal(10,2), default 0 | Base/default price for the standard variant. |
| `products` | `paper_size`, `paper_weight`, `paper_finish` | Nullable strings (80) | Optional paper specifications for legacy paper-based products. |
| `products` | `image_path` | Nullable string | Path to optional catalog image on the public storage disk. |
| `products` | `is_active` | Boolean, indexed, default true | Whether customers can see the product. |
| `products` | `lock_version` | Unsigned integer, default 0 | Optimistic concurrency version for catalog edits. |
| `products` | timestamps | Datetime | Created/updated times. |
| `product_variants` | `id` | Integer primary key | Product option/variant identifier. |
| `product_variants` | `product_id` | Foreign key to `products.id`, cascade on delete | Parent product. |
| `product_variants` | `name`, `sku` | String, optional unique SKU | Variant name such as `Large / Black` and optional stock code. |
| `product_variants` | `options` | JSON | Customer-visible option values such as size, color, or dimensions. |
| `product_variants` | `price` | Decimal(10,2) | Variant-specific price charged when this option is selected. |
| `product_variants` | `is_default`, `is_active` | Boolean flags | Default variant is used for legacy compatibility; inactive variants are hidden from customers. |
| `product_variants` | `lock_version` | Unsigned integer, default 0 | Optimistic concurrency version for variant edits. |
| `product_variants` | timestamps | Datetime | Created/updated times. |
| `product_variant_supplies` | `product_variant_id`, `inventory_item_id` | Composite primary key and foreign keys | Recipe linking a finished variant to the raw supplies consumed per unit. |
| `product_variant_supplies` | `quantity_per_unit` | Decimal(12,3) | Raw material quantity needed for one finished unit. |
| `services` | `id` | Integer primary key | Service identifier. |
| `services` | `name` | String, required | Service title. |
| `services` | `description` | Nullable text | Service details. |
| `services` | `starting_price` | Nullable Decimal(10,2) | Starting price; null renders as quote required. |
| `services` | `image_path` | Nullable string | Path to optional service image on the public storage disk. |
| `services` | `is_active` | Boolean, indexed, default true | Whether customers can see the service. |
| `services` | `lock_version` | Unsigned integer, default 0 | Optimistic concurrency version; stale service edits are rejected. |
| `services` | timestamps | Datetime | Created/updated times. |
| `carts` | `id` | Integer primary key | Cart identifier. |
| `carts` | `user_id` | Foreign key to `users.id`, unique, cascade on delete | The customer who owns this cart; at most one cart per account. |
| `carts` | timestamps | Datetime | Cart created/updated times. |
| `cart_items` | `id` | Integer primary key | Cart line identifier. |
| `cart_items` | `cart_id` | Foreign key to `carts.id`, cascade on delete | Parent cart. |
| `cart_items` | `product_id` | Nullable foreign key to `products.id`, cascade on delete | Product target when the line is a product. |
| `cart_items` | `product_variant_id` | Nullable foreign key to `product_variants.id`, set null on delete | Selected product variant for that cart line. |
| `cart_items` | `service_id` | Nullable foreign key to `services.id`, cascade on delete | Service target when the line is a service. |
| `cart_items` | `quantity` | Unsigned integer, default 1 | Requested quantity; application validation limits it to 1-99. |
| `cart_items` | `(cart_id, product_variant_id)` | Composite unique index | Prevents duplicate selections of the exact same product variant in one cart. |
| `cart_items` | `(cart_id, service_id)` | Composite unique index | Prevents duplicate service lines in one cart. |
| `cart_items` | timestamps | Datetime | Line created/updated times. |
| `orders` | `id` | Integer primary key | Order identifier. |
| `orders` | `user_id` | Foreign key to `users.id`, restrict on delete | Customer who placed the order. |
| `orders` | `status` | String, indexed, default `pending` | Workflow state: `pending`, `quote_requested`, `quote_sent`, `processing`, `completed`, `cancelled`. |
| `orders` | `subtotal` | Decimal(10,2), default 0 | Sum of priced line snapshots before any quote-only service is priced. |
| `orders` | `quoted_total` | Nullable Decimal(10,2) | Admin-provided total required when status becomes `quote_sent`. |
| `orders` | `customer_accepted_at` | Nullable timestamp | Time the customer accepted the quoted total. |
| `orders` | `artwork_path` | Nullable string | Path to customer artwork on private local storage; download uses authorization-scoped routes. |
| `orders` | `customer_note`, `admin_note` | Nullable text | Customer instructions and customer-visible staff update. |
| `orders` | timestamps | Datetime | Order created/updated times. |
| `order_items` | `id` | Integer primary key | Order-line identifier. |
| `order_items` | `order_id` | Foreign key to `orders.id`, cascade on delete | Parent order. |
| `order_items` | `product_id`, `product_variant_id`, `service_id` | Nullable foreign keys, set null on delete | Optional source catalog records; snapshots remain if source is deleted. Exactly one source is enforced in application flow, not by a database check. |
| `order_items` | `item_name`, `item_description` | String, nullable text | Purchase-time display snapshots. |
| `order_items` | `unit_price` | Nullable Decimal(10,2) | Purchase-time unit price; null denotes an item requiring a quote. |
| `order_items` | `quantity` | Unsigned integer | Quantity submitted from cart. |
| `order_items` | `variant_name` | Nullable string | Selected product variant name, such as `Large / Black`. |
| `order_items` | `variant_options` | Nullable JSON | Selected option details captured at the time of order submission. |
| `order_items` | `paper_size`, `paper_weight`, `paper_finish` | Nullable strings (80) | Product paper-option snapshots. |
| `order_items` | timestamps | Datetime | Order-line created/updated times. |
| `payments` | `id` | Integer primary key | Payment attempt identifier. |
| `payments` | `order_id`, `user_id` | Foreign keys to `orders.id`/`users.id`, restrict on delete | Order being paid and customer who paid. |
| `payments` | `gateway`, `method`, `currency` | Strings | Gateway driver, payment method, three-letter currency. |
| `payments` | `amount` | Decimal(10,2) | Amount charged for this attempt. |
| `payments` | `status` | String, indexed | `pending`, `succeeded`, or `failed`. |
| `payments` | `reference`, `failure_reason`, `gateway_response` | Unique nullable string, nullable string, nullable JSON | Provider reference, decline reason, and raw provider response. |
| `payments` | timestamps | Datetime | Payment created/updated times. |
| `verification_codes` | `id` | Integer primary key | Verification code identifier. |
| `verification_codes` | `user_id` | Foreign key to `users.id`, cascade on delete | Account the code belongs to. |
| `verification_codes` | `channel`, `destination` | Indexed string, string | `email` or `sms` and the contact the code was sent to. |
| `verification_codes` | `code_hash`, `expires_at`, `verified_at`, `attempts` | Hash string, indexed timestamp, nullable timestamp, unsigned tinyint | Hashed one-time code, expiry, consumption time, and wrong-attempt count. |
| `verification_codes` | timestamps | Datetime | Code created/updated times. |
| `suppliers` | `id` | Integer primary key | Supplier identifier. |
| `suppliers` | `name` | String, required | Supplier name. |
| `suppliers` | `email`, `phone`, `address` | Nullable strings | Supplier contact details. |
| `suppliers` | `is_active` | Boolean, indexed, default true | Whether staff can select the supplier for new receipts; historical movements remain. |
| `suppliers` | timestamps | Datetime | Supplier timestamps. |
| `inventory_items` | `id` | Integer primary key | Raw-supply identifier. |
| `inventory_items` | `name`, `sku` | Required string name; nullable unique SKU | Supply name and optional stock code; separate rows can represent sizes/colors. |
| `inventory_items` | `category` | Indexed string | `paper`, `blank_mug`, `blank_shirt`, `tarpaulin_material`, `ink`, or `other`. |
| `inventory_items` | `unit` | String(32) | Counting unit, for example sheet, piece, meter, roll, or bottle. |
| `inventory_items` | `quantity_on_hand` | Decimal(12,3), default 0 | Current supply balance; changed through movement recording. |
| `inventory_items` | `reorder_level` | Decimal(12,3), default 0 | Active supplies at/below this level are low stock. |
| `inventory_items` | `is_active` | Boolean, indexed, default true | Inactive supply record is retained but excluded from active operations. |
| `inventory_items` | `lock_version` | Unsigned integer, default 0 | Optimistic locking for metadata edits. |
| `inventory_item_supplier` | `inventory_item_id`, `supplier_id` | Composite primary key, cascading foreign keys | Many-to-many supplier association for a supply. |
| `inventory_movements` | `id` | Integer primary key | Stock movement identifier. |
| `inventory_movements` | `inventory_item_id` | Foreign key, restrict on delete | Supply whose balance changed. |
| `inventory_movements` | `supplier_id` | Nullable foreign key, set null on delete | Supplier associated with a receipt. |
| `inventory_movements` | `user_id` | Foreign key to `users.id`, restrict on delete | Staff member who recorded the movement. |
| `inventory_movements` | `order_id` | Nullable foreign key to `orders.id`, set null on delete | Order whose placement, quote acceptance or cancellation caused the movement. |
| `inventory_movements` | `type` | String | `received`, `used`, `stocktake`, or `returned` (supplies put back when an order is cancelled). |
| `inventory_movements` | `quantity_change` | Signed Decimal(12,3) | Balance delta; stocktake is counted balance minus prior balance. |
| `inventory_movements` | `quantity_before`, `quantity_after` | Decimal(12,3) | Balance before/after movement for audit. |
| `inventory_movements` | `notes` | Nullable text | Receipt reference, usage reason, count notes, or production-order reference. |
| `inventory_movements` | `created_at` | Timestamp | Movement timestamp; application exposes append-only movements. |

**Future data design to resolve before implementing ordering:**

- Cart and cart-item ownership/quantity/target are implemented. Keep enforcing exactly one catalog target per line in application code, or add a portable database check constraint if supported database engines are finalized.
- Order and order-item records/snapshots are implemented. Still decide shipping/address, estimated completion, inventory reservation, and payment fields before extending checkout.
- Payment records are implemented and never store payment secrets; refunds, webhooks and real provider references still need a design.
- Verification codes store only a hash and the destination; no plain-text code is persisted.
- Supplies are tracked separately from finished products. Product variants define their own material consumption rates; the next design decision is whether one product can have multiple active recipes or whether recipes should be locked to a single approved production method.
- Addresses and notification preferences are not current user fields.

**SDD consistency notes:** The supplied data dictionary names both `Transaction Details` and `Transactions` in ways that appear reversed or conflated. Before building those tables, agree on distinct `orders`, `order_items`, and `payments`/`transactions` entities and update the course ERD/data dictionary. The SDD also includes size/material pivots that may describe apparel better than the current paper-print scope; do not implement these without confirming they apply.

## 8. Detailed Design

### 8.1 Component Design

- Laravel resource routes provide the admin catalog CRUD endpoints, except `show`.
- Product and service Form Requests own validation and require the `manage-shop` Gate.
- Public registration uses a dedicated Form Request and never accepts role assignment from the request.
- Email and SMS verification use hashed single-use codes with expiry, attempt limits and resend throttling; password reset uses Laravel's broker. Verification prompts but does not currently gate shopping.
- Payment charges run in a locked transaction that records the gateway outcome; failed charges are retained for staff visibility.
- Customer profile routes are owner-scoped; admin account management is admin-only, preserves accounts linked to order or inventory history, and protects the current/last active administrator from deletion.
- Product/service updates compare and increment `lock_version` to reject stale forms.
- Product/service images are MIME-validated and stored on the public disk; replaced/deleted catalog images are removed.
- Customer artwork is MIME/size-validated and stored on the private local disk; only the owning customer or authorized staff can download it.
- The admin seeder reads credentials from environment-backed config; secrets must not be committed.
- Customer catalog routes query active records and are behind authentication middleware.
- Cart routes are protected by the `access-customer` Gate; item lookup is scoped through the signed-in user's cart.
- Order submission is transactional and locks the cart/lines while it checks active catalog records, copies snapshots, creates the order, and clears cart lines.
- Customer order routes scope lookups through the authenticated user's `orders()` relation; admin order routes are protected by `access-admin`.
- Inventory movements use a row-locked transaction, reject negative balances, update `quantity_on_hand`, and append before/after balance history. Suppliers can be deleted; inventory movement records remain and their supplier reference is nulled.
- Feature tests use SQLite `:memory:` and `RefreshDatabase`; they do not modify the developer's local database file.

### 8.2 HCI / UI-UX Notes

- Auth forms render in modal overlays with close controls, Escape-to-close, focus management, and validation feedback.
- Customer navigation hides Products/Services/Cart from guests; server routes independently enforce authentication.
- Customer catalogs provide search/filter, pagination and item detail pages.
- Catalog pages render optional uploaded images; the order form accepts optional private artwork.
- Admin create/edit forms show validation errors beside fields and provide cancel navigation.
- Catalog records have explicit visibility controls; deletion requires browser confirmation.
- The current UI is a functional baseline. Cart has empty, quote-required, unavailable-item, validation, and priced-subtotal states. Order history/detail and admin queue show current statuses; payment checkout/confirmation remains to be designed.

## 9. Requirements Traceability Matrix

| Requirement / capability | SDD location | Design element | Evidence | Status |
|---|---|---|---|---|
| Register/manage accounts and roles | 4.1 Account Management; 6 Account interfaces | Auth/profile/password controllers, admin User resource, active/role Gates | `tests/Feature/AccountManagementTest.php`; auth feature tests | Partial: audit/privacy workflow remains |
| Email/SMS verification and password reset | 5 Dependencies; 6 Interfaces | Hashed one-time codes via mail and the `sms` channel; Laravel password broker | `tests/Feature/AccountManagementTest.php`; `tests/Feature/AccountVerificationTest.php` | Implemented; verification is not enforced for shopping |
| Separate customer, employee and admin access | 4.1 Account Management | User roles and `access-customer`, `manage-shop`, `access-admin` Gates | Account and catalog feature tests | Implemented baseline permissions |
| Add/edit/remove/view services | 4.1 Service Management | Employee/admin Service resource, image upload, optimistic lock, customer search/detail | `tests/Feature/CatalogManagementTest.php`; `tests/Feature/CustomerCatalogTest.php` | Partial: option/quote conversation remains |
| Add/edit/remove/view products | 4.1 Cart/Inventory context; 7 Data Design | Employee/admin Product resource, category fields, default/selected variants, image upload, optimistic lock, customer search/detail | `tests/Feature/CatalogManagementTest.php`; `tests/Feature/CustomerCatalogTest.php`; `tests/Feature/ProductVariantManagementTest.php` | Partial: variant bundles, richer discovery, and set-design configuration remain |
| Display/search product and service information | 6 Home Page | Active-only paginated catalogs with optional images, detail pages, and variant selection | `tests/Feature/CustomerCatalogTest.php`; `tests/Feature/ProductVariantManagementTest.php` | Partial: set-design selection remains |
| Attach custom artwork to order | 1.2 Scope; 6 Order Interface | Private artwork path and owner/staff-only download routes | `tests/Feature/OrderManagementTest.php` | Partial: one attachment per order; no revision conversation |
| Add/remove/update cart items and total | 4.1 Cart Management; 6 Cart Interface | Cart routes/controller/models/views, selected variant pricing and snapshot retention | `tests/Feature/CartManagementTest.php`; `tests/Feature/ProductVariantManagementTest.php` | Partial: cart subtotal works; checkout/order total is not final for quote items |
| Place, track, update, cancel orders | 4.1 Order Management | Customer Order and admin Order resources, order snapshots, constrained state transitions, customer quote acceptance, variant-safe order history | `tests/Feature/OrderManagementTest.php`; `tests/Feature/ProductVariantManagementTest.php` | Partial: payment/fulfillment remain |
| Track raw supply stock and suppliers | 4.1 Inventory Management | Inventory items, supplier associations, movement ledger, low-stock threshold, and recipe-linked material usage | `tests/Feature/InventoryManagementTest.php`; `tests/Feature/OrderManagementTest.php` | Partial: purchase-order/cost valuation remains |
| Select and process payments | 4.1 Transaction Processing | Gateway contract, dummy driver, payment records and checkout action | `tests/Feature/PaymentGatewayTest.php` | Partial: dummy gateway only; refunds/webhooks/real provider remain |
| Track inventory and suppliers | 4.1 Inventory Management | Inventory/supplier entities and policies | No inventory/supplier model exists | Planned |
| Generate sales/inventory reports | 4.1 Report Generation | Admin order-activity report with date filters and catalog visibility counts | `tests/Feature/AdminReportTest.php` | Partial: paid sales and stock quantities are not available |
| SMS notifications | 5 Dependencies; 6 Interfaces | Custom `sms` channel, `SmsGateway` contract, log driver | `tests/Feature/AccountVerificationTest.php` | Partial: local log delivery only; provider decision remains |
| Prevent conflicting concurrent service edits | 4.2 Process Description | Product/service optimistic locking | `tests/Feature/CatalogManagementTest.php` | Implemented |

## Development Roadmap

Recommended order, respecting dependencies:

1. **Confirm storefront requirements:** decide whether paper specifications are fixed or selectable price-changing options and whether set-design products need customer-customizable text/colors.
2. **Complete catalog modeling:** add approved design records/options and catalog seed data for demos.
3. **Complete cart/order design:** support multiple artwork revisions/attachments if required; add estimated completion and fulfillment/customer notification rules.
4. **Complete order flow:** add estimated completion/date, fulfillment/customer notification rules, and payment after those requirements are agreed; standardize production scheduling as separate from quote acceptance.
5. **Complete inventory/order integration:** variant recipes now drive displayed availability and cart quantity checks; confirm approved production recipes, decide whether quote requests should also hold stock, and whether cancelled-in-production orders should return all supplies (staff can correct waste with a stocktake).
6. **Transactions:** the dummy gateway, payment records and status handling are in place. Agree on a real provider, webhooks/refunds, and whether manual staff-recorded payments are required.
7. **Complete reports:** add payment-backed revenue reports after transaction processing, then add stock/supplier reports from the implemented inventory data; decide export formats and report permissions.
8. **Notifications and operational hardening:** replace the log-only SMS driver with a selected provider, add audit trail and backup/deployment plan, and extend end-to-end order tests.

## Open Design Decisions

1. **Roles:** The app recognizes `customer`, `employee`, and `admin`; decide whether managers need a separate role from admins and finalize the permission matrix.
2. **Set designs:** Set designs are represented as pre-created products in this version. Confirm whether future customization requires reusable design records.
3. **Custom designs:** Current flow attaches one private JPG/PNG/PDF file to an order and supports quotes. Decide whether customers need multiple files, revision conversations, or upload before cart submission.
4. **Paper options:** Are size/weight/finish fixed specifications per product or selectable variants that change price? Current fields describe fixed product attributes only.
5. **Products vs services in a cart:** Confirm both can be purchased in one checkout and whether services are priced directly or quoted.
6. **Inventory:** Raw supplies are modeled separately from finished products. Define material recipes/consumption rates, supplier purchasing, and whether cost valuation is required.
7. **Payments and notifications:** Choose a real payment provider and SMS provider, and decide whether manual staff-recorded payments and refunds are required. The dummy gateway and log SMS driver are intentional local defaults.
8. **Verification policy:** Decide whether customers must verify email before browsing, ordering, or only before order notifications.

## Appendix A: Revision History

| Date | Milestone | Revision | Evidence |
|---|---|---|---|
| 2026-10-11 | Repository baseline | Created a code-aligned SDD mirror; recorded current implementation, gaps, data fields, and unresolved decisions. | Laravel routes, migrations, models, views, and feature tests in this repository. |
| 2026-10-11 | Cart milestone | Added customer-owned cart persistence, line operations, price calculation, and quote/unavailable states; updated the ERD, dictionary, and traceability. | `tests/Feature/CartManagementTest.php`; `tests/Feature/StorefrontNavigationTest.php`. |
| 2026-10-11 | Order milestone | Added cart-to-order submission, immutable item snapshots, customer order history/cancellation/quote acceptance, and admin quote/status workflow; updated ERD, dictionary, and traceability. | `tests/Feature/OrderManagementTest.php`. |
| 2026-10-11 | Reporting milestone | Added an admin-only date-filtered order activity report and catalog visibility counts; documented that payment-backed sales and inventory reports await their respective data modules. | `tests/Feature/AdminReportTest.php`. |
| 2026-10-11 | Account/storefront milestone | Added employee role boundaries, account profile/password and email verification/reset flows, admin account management, catalog optimistic locking, and searchable product/service detail pages. | `tests/Feature/AccountManagementTest.php`; `tests/Feature/CustomerCatalogTest.php`; `tests/Feature/CatalogManagementTest.php`. |
| 2026-10-11 | Media milestone | Added public product/service images and private order artwork attachments with customer/staff download authorization. | `tests/Feature/CatalogManagementTest.php`; `tests/Feature/OrderManagementTest.php`. |
| 2026-10-12 | Supply inventory milestone | Added supplier records, raw supply balances, receive/use/stocktake movements, low-stock view, and staff screens; kept order consumption decoupled pending production rules. | `tests/Feature/InventoryManagementTest.php`. |
| 2026-10-12 | Product variant + production consumption milestone | Added product categories, default/selected variants, per-variant pricing and option metadata, supply recipes, and production-time material consumption when orders enter processing or a quote is accepted. | `tests/Feature/ProductVariantManagementTest.php`; `tests/Feature/OrderManagementTest.php`; `tests/Feature/CartManagementTest.php`. |
| 2026-10-22 | Inventory-aware catalog and safe deletion milestone | Added inventory-derived variant availability and cart stock checks, expanded product-category workflows, and guarded delete actions for variants, suppliers, and accounts. | `tests/Feature/CustomerCatalogTest.php`; `tests/Feature/ProductVariantManagementTest.php`; `tests/Feature/InventoryManagementTest.php`; `tests/Feature/AccountManagementTest.php`. |
| 2026-11-22 | Dummy payments and OTP verification milestone | Added the gateway contract/dummy driver, payment records, customer pay action and staff payment visibility; replaced signed-link email verification with hashed six-digit codes delivered by mail and a log-only SMS channel, with expiry, attempt limits and resend throttling. | `tests/Feature/PaymentGatewayTest.php`; `tests/Feature/AccountVerificationTest.php`; `tests/Feature/AccountManagementTest.php`. |
