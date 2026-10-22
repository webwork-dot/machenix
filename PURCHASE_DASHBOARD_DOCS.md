# Purchase Dashboard Documentation

## Overview
The **Purchase Dashboard** module provides executive-level monitoring and analytics for procurement operations in Machenix CRM. It strictly displays the **current session company's data** (as selected in the global header navbar). It breaks down purchases across **Import** and **Local** channels, categorizes every transaction into **White (Official / Tax-compliant)** and **Black (Unofficial / Cash)** values, tracks **PO Operational Expenses**, monitors **Supplier Payments** (Bank vs. Cash), and calculates real-time **Supplier Outstanding Liabilities**, complete with a consolidated **Procurement Channel Matrix** for the active company.

---

## 1. Architecture & File Structure

| Layer | File Path | Description |
| :--- | :--- | :--- |
| **Routes** | `application/config/routes.php` | Maps `/purchase-dashboard` and `/purchase-dashboard/get_data_ajax` |
| **Navigation** | `application/views/backend/inventory/navigation.php` | Main menu navigation link placed directly below Sales Dashboard |
| **Controller** | `application/controllers/Purchase_dashboard.php` | Handles authentication, session company resolution, date filter parsing, data aggregation, and AJAX responses |
| **Model** | `application/models/Purchase_dashboard_model.php` | Encapsulates all SQL queries for Import, Local, PO Expenses, Payments, Outstanding balances, and Trends |
| **Main View** | `application/views/backend/inventory/purchase_dashboard.php` | Renders the dashboard UI, KPI metric panels, company procurement summary matrix, transaction lists, and ApexCharts |
| **Filter View** | `application/views/backend/inventory/filter/purchase_dashboard_filter.php` | Date range picker with quick shortcuts (Today, Yesterday, This Week, This Month, Last Month, This Year) and AJAX triggers (no company dropdown; follows active session company) |

---

## 2. Business Logic & Calculation Formulas

### 2.1 Local Purchases (White vs. Black)
* **Source Table:** `purchase_order` (`method IN ('local', 'company')`, `is_deleted = 0`)
* **White Local Purchase:** $\sum(\text{po.grand\_total})$ (Basic Value + CGST + SGST + IGST)
* **Black Local Purchase:** $\sum(\text{po.total\_black\_amount\_summary})$ (Cash / Non-tax amount)
* **Gross Local Purchase:** $\text{White Local Purchase} + \text{Black Local Purchase}$
* **Currencies:** Local procurement is exclusively in INR; USD and RMB are explicitly displayed as $0.00$ and $¥0.00$.

### 2.2 Import Purchases (White vs. Black & Multi-Currency)
* **Source Tables:** `purchase_order po` (`method = 'import'`, `is_deleted = 0`) joined with `purchase_in_product pip` (received shipments) or `purchase_order_product pop` (initial orders)
* **White Import (INR):** $\sum(\text{pip.official\_total\_rs})$ (Official Customs Invoice in INR)
* **Black Import (INR):** $\sum(\text{pip.black\_total\_price})$ or $\sum(\text{pip.actual\_qty} \times \text{pip.actual\_inr} - \text{pip.official\_total\_rs})$
* **Gross Import (INR):** $\text{White Import INR} + \text{Black Import INR}$
* **Foreign Currency Metrics:**
  * **White USD:** $\sum(\text{pip.total\_amount\_usd})$
  * **Actual / Total USD:** $\sum(\text{pip.actual\_qty} \times \text{pip.actual\_usd})$
  * **White RMB:** $\sum(\text{pip.official\_ci\_qty} \times \text{pip.unit\_price\_rmb})$
  * **Actual / Total RMB:** $\sum(\text{pip.actual\_qty} \times \text{pip.actual\_rmb})$

### 2.3 PO Operational Expenses (White vs. Black & Multi-Currency)
* **Source Table:** `po_expense` (`is_delete = 0`)
* **White PO Expense:** $\sum(\text{grand\_total})$ where `type = 'official'` (with GST)
* **Black PO Expense:** $\sum(\text{grand\_total})$ where `type IN ('unofficial', 'extras')` (0% GST)
* **Total PO Expense:** $\text{White PO Expense} + \text{Black PO Expense}$
* **Foreign Currency Metrics:**
  * **Total USD:** $\sum(\text{usd})$ across all PO expense entries
  * **Total RMB:** $\sum(\text{rmb})$ across all PO expense entries

### 2.4 Supplier Payments vs. Vendor Payments (Official vs. Unofficial)
The dashboard strictly segregates **Suppliers** (raw product/inventory providers) from **Vendors** (operational expense/service providers):
1. **Supplier Payments (`payments` table):**
   * Filter: `p.is_delete = 0 AND p.type = 'payment' AND (p.company_id = cid OR s.company_id = cid)`
   * Encompasses both Local and Import suppliers.
   * Split: **Official** (`p.payment_type = 'official' OR LOWER(payment_method) = 'cheque'`) vs. **Unofficial** (`p.payment_type = 'unofficial' OR LOWER(payment_method) = 'cash'`).
   * Breakdown across all currencies: INR (`amount_rs`), USD (`amount_dollar`), RMB (`amount_rmb`), plus Local vs. Import supplier sub-totals.
2. **Vendor Payments (`vendor_payments` table):**
   * Filter: `vp.is_delete = 0 AND (vp.company_id = cid OR v.company_id = cid)`
   * Operational / PO Expense vendors (`my_companies`).
   * Split: **Official** vs. **Unofficial**.
   * Breakdown across all currencies: INR (`inr`), USD (`usd`), RMB (`rmb`).

### 2.5 Real-Time Outstandings (Ledger-Based Snapshot)
1. **Total Supplier Outstandings (Local + Import Suppliers):**
   * Computes authentic ledger-based running balances across all active suppliers of the session company:
     * **Local Suppliers:** Seed Opening (`s.outstanding_inr` treated as unofficial) + Local Purchases (`net_sales_value_1` as official, `total_black_amount_summary` as unofficial) − Supplier Payments (`type = 'payment'`) $\pm$ Supplier Adjustments (`supplier_adjustments`).
     * **Import Suppliers:** Seed Opening (`s.outstanding_inr`, `s.outstanding_usd`, `s.outstanding_rmb` as unofficial) + Import Purchases (`pip.official_total_rs`, `pip.total_amount_usd`, `official_ci_qty * unit_price_rmb` as official; actual − official as unofficial) − Supplier Payments $\pm$ Adjustments.
   * Presented in separate Official vs. Unofficial buckets across INR, USD, and RMB, with Local Dues vs. Import Dues breakdown.
2. **Total Vendors Outstanding (Operational / PO Expense Vendors):**
   * Computes authentic ledger-based running balances across all active vendors (`my_companies`):
     * Seed Opening (`v.outstanding` as unofficial) + PO Expenses (`grand_total`, `usd`, `rmb` official vs. unofficial) − Vendor Payments (`vendor_payments` official vs. unofficial) $\pm$ Vendor Adjustments (`vendor_adjustments`).
   * Presented in separate Official vs. Unofficial buckets across INR, USD, and RMB.

### 2.6 Active Company Procurement Channel Matrix
Aggregates all procurement dimensions for the active session company:
1. Local Purchases (White, Black, Basic Value, GST, Gross Total, Orders Count, Supplier Payments, Supplier Outstanding)
2. Import Purchases (White, Black, Customs Duty/Taxes, Gross Total, Shipments Count, Supplier Payments, Supplier Outstanding)
3. PO Operational Expenses (White, Black, GST, Gross Total, Entries Count, Vendor Payments, Vendor Outstanding)
4. Consolidated Procurement Totals for the company

### 2.7 Top Suppliers by Volume (Item-Level Multi-Supplier Apportionment)
* Since an individual purchase order can contain products sourced from multiple suppliers (or store multiple/CSV supplier IDs), procurement volume is aggregated directly at the line-item level:
  * **Local Items (`purchase_order_product pop`):** Apportioned to `pop.supplier_id` (fallback `po.supplier_id`) with White = `pop.grand_total`, Black = `pop.black_amt_total`.
  * **Import Received Items (`purchase_in_product pip`):** Apportioned to `pip.supplier_id` with White = `pip.official_total_rs`, Black = non-negative cash/valuation difference.
  * **Import Pending Items (`purchase_order_product pop`):** Apportioned to `pop.supplier_id` with White = `pop.total_val`, Black = `pop.black_amt_total`.
* Accurately groups by supplier identity, linking each supplier to their true order count, White amount, Black amount, and gross volume.

---

## 3. API Endpoints

### `GET/POST /purchase-dashboard`
Loads the main dashboard view with server-side rendered initial data for the default period (1st of the current month to today) for the active session company.

### `GET/POST /purchase-dashboard/get_data_ajax`
Asynchronous endpoint returning JSON for instant filtering without reloading the page.
* **Request Parameters:**
  * `date_range`: String in `d-m-Y - d-m-Y` format
  * `period`: Shortcut string (`today`, `yesterday`, `this_week`, `last_week`, `this_month`, `last_month`, `this_year`, `all_time`)
* **Response:**
  * `status`: `"success"`
  * `purchase_overview`: Combined White vs Black metrics
  * `import_summary`: Multi-currency and White/Black import metrics
  * `local_summary`: Basic, GST, White/Black local purchase metrics
  * `po_expense_summary`: Official and Unofficial operational expense metrics
  * `payment_summary`: White (Bank) and Black (Cash) payments made
  * `outstanding_summary`: Real-time White and Black supplier liabilities
  * `channel_matrix`: Complete procurement channel breakdown rows and totals for the active company
  * `daily_trend`: Time-series categories and series arrays for ApexCharts
  * `top_suppliers`: Top 5 suppliers by purchase volume
  * `top_outstanding`: Top 5 suppliers with highest payable balances

---

## 4. Unified Dashboard Switcher

* **Header Dropdown**: Positioned at the top right of the header card across all three dashboards:
  1. `1. Leads & Customers` (`site_url('inventory/dashboard')`)
  2. `2. Sales` (`site_url('sales-dashboard')`)
  3. `3. Purchase` (`site_url('purchase-dashboard')`)
* **Active State Detection**: Automatically pre-selects the active dashboard based on `$page_name` and URI routing segments (`dashboard_switcher.php`).
* **Sidebar Navigation**: Individual links for Sales Dashboard and Purchase Dashboard are hidden from the sidebar menu, centralizing dashboard navigation through the top switcher while keeping the primary `Dashboard` sidebar item active.

