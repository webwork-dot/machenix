# Leads & Customer Calls Dashboard — UI and Reporting Implementation Specification

## 1. Objective

Extend the **existing Leads / Customer Calls dashboard** with detailed reporting without changing or removing the dashboard data that is already displayed.

The current dashboard already contains basic customer-call aging/count cards. **Keep all existing dashboard content exactly as it is.**

All new reporting widgets, charts, tables, and summaries must be added **below the existing dashboard section**.

The implementation must:

- Follow the existing project's coding patterns.
- Keep the code simple and easy to maintain.
- Reuse the existing CodeIgniter 3 structure, models, controllers, views, session handling, query style, helper functions, and frontend libraries already used by the project.
- Avoid introducing unnecessary architecture, repositories, services, APIs, classes, or libraries.
- Do not create new database tables for this dashboard unless the existing data absolutely cannot support a required report.
- Use the existing `customer`, `customer_calls`, and `customer_log` data.
- Respect company and staff visibility rules on **every** report/query.
- Do not expose internal status terminology on the dashboard that is intended only for application workflow handling.

---

# 2. Existing Data Sources

The reporting dashboard should primarily use these existing tables.

## 2.1 `customer`

Important fields:

- `id`
- `type` → `customer` / `leads`
- `company_id`
- `company_name`
- `state_name`
- `city_name`
- `status`
- `status_label`
- `status_date`
- `remark`
- `is_move`
- `move_date`
- `added_by_id`
- `added_by_name`
- `added_date`
- `is_deleted`

The table stores both customers and leads using the `type` field.

The current dump also shows that `company_id` can contain multiple company IDs in a comma-separated string, for example `6,7`. Therefore company filtering must support both single-company and multi-company records.

Do not use a simple equality condition such as:

```php
WHERE company_id = '$company_id'
```

because it will fail for records such as `6,7`.

Use a CSV-safe match consistent with the existing database design, preferably:

```sql
FIND_IN_SET(?, company_id)
```

with proper query parameters.

---

## 2.2 `customer_calls`

Important fields:

- `id`
- `customer_id`
- `customer_name`
- `is_lead`
- `status`
- `date`
- `remark`
- `added_by`
- `added_by_name`
- `created_at`

This table represents call/follow-up activity.

Important distinction:

- `created_at` = when the call/follow-up record was created.
- `date` = scheduled/follow-up date stored for the call.

Do not treat these two fields as the same thing.

For reporting:

- Use `created_at` for **activity volume / calls created**.
- Use `date` for **scheduled, upcoming, due, or overdue follow-up reporting**.

---

## 2.3 `customer_log`

Important fields:

- `id`
- `customer_id`
- `label`
- `action`
- `message`
- `json`
- `added_by`
- `added_by_name`
- `added_date`

This is the historical activity/audit data.

Use this table when historical activity or chronological customer/lead events are required.

Do not expose internal workflow action names directly in the UI.

Use the existing `message` and/or a human-readable mapped label.

---

# 3. Important Status Display Rule

The database contains internal workflow statuses that are implementation details.

**Do not use the internal follow-up workflow terminology as dashboard labels.**

In particular, do not display the internal status that represents repeated follow-up of an existing customer.

The dashboard should instead use business-friendly terms such as:

- New Lead
- Needs Follow Up
- Tentative
- Follow Up
- Lost
- Customer
- Converted
- Active
- Overdue
- Upcoming
- Due Today

Use `status_label` where it already contains an appropriate user-facing label.

Where a raw status needs to be shown, map it to a dashboard-friendly label rather than exposing the internal status value.

---

# 4. Access Control — MUST FOLLOW THIS EVERYWHERE

All dashboard reporting must use the same visibility rules.

Do not create one access rule for KPI cards and another for charts/tables.

Every query must use the same base scope.

## 4.1 Admin visibility

Admin can see data for the **currently selected/session company**.

The base customer filter is:

```text
customer.is_deleted = 0
AND current session company ID exists inside customer.company_id
```

Because `customer.company_id` may contain values such as:

```text
6
6,7
7
```

the company filter must be CSV-safe.

Example concept:

```sql
FIND_IN_SET(session_company_id, customer.company_id) > 0
```

Use the project's existing database escaping/query binding pattern.

---

## 4.2 Staff visibility

Staff is identified by:

```php
$this->session->userdata('super_type_id') == 7
```

When the current login is staff:

The staff may only see records belonging to:

1. Their current/session `company_id`
2. Their own `added_by_id`

The effective visibility condition is therefore:

```text
company_id matches session company_id
AND added_by_id = logged-in staff user ID
AND is_deleted = 0
```

Use the existing session field used by this project for the logged-in staff ID, currently expected to be available through:

```php
$this->session->userdata('super_user_id')
```

Do not hard-code the staff ID.

Do not allow a staff user to see another salesperson's customer/lead records.

---

## 4.3 Customer-call visibility

`customer_calls` does not contain `company_id`.

Therefore do **not** try to filter calls directly by `customer_calls.company_id`.

Instead:

```text
customer_calls
    JOIN customer
        ON customer.id = customer_calls.customer_id
```

Then apply the normal customer visibility scope to the joined `customer`.

For staff visibility, the safest default is:

```text
Visible customer/lead belongs to the staff
→ therefore all call/follow-up history belonging to that visible customer/lead may be reported
```

Do not independently restrict `customer_calls.added_by` unless the existing Calls module already uses that rule.

This prevents a staff member's historical call/follow-up report from becoming incomplete just because another user created a call against a customer that the staff member owns.

If the existing Calls module already has a different visibility rule, follow that existing rule rather than inventing a new one.

---

# 5. General Implementation Rules

Before writing code:

1. Inspect the existing dashboard controller.
2. Inspect the existing dashboard view.
3. Inspect how dashboard data is currently passed from controller to view.
4. Inspect existing model methods used by the dashboard.
5. Inspect existing chart/table libraries already loaded by the project.
6. Reuse the existing page layout and component styling.
7. Reuse existing session/company filtering patterns.
8. Reuse existing helper functions for formatting dates, labels, numbers, etc.

Do not introduce a new framework or frontend library.

Do not restructure unrelated dashboard code.

Keep each reporting method focused and simple.

Prefer a small number of clear model methods over one giant SQL query.

---

# 6. Dashboard Placement

Do not modify or remove the current dashboard cards.

The new dashboard should be:

```text
Existing Dashboard
        ↓
NEW Reporting Section
        ↓
Detailed Reporting Widgets
```

The current five basic call-aging cards remain at the top.

All new reporting content starts underneath them.

---

# 7. Section 1 — Lead / Customer KPI Cards

Create a new row below the existing dashboard cards.

Recommended cards:

### Total Leads

Count of active lead records:

```text
customer.type = 'leads'
AND is_deleted = 0
```

### New Leads

Number of leads added during the selected reporting period.

Example:

```text
This Month
```

### Active Leads

Leads that are not currently lost and have not been converted into customers.

Do not use internal workflow status names in the UI.

### Converted Leads

Lead records that have been moved into customer state using the existing system's conversion/move fields.

Use existing `is_move` / `move_date` and existing conversion logic rather than inventing a new conversion definition.

### Lost Leads

Current leads with the existing lost state.

### Total Calls

Number of visible call records for the selected period.

Each card should support the dashboard's visibility rules.

---

# 8. Section 2 — Lead Pipeline

Create a visual lead pipeline/funnel.

The purpose is to show:

```text
New Leads
    ↓
Needs Follow Up
    ↓
Active Follow-up
    ↓
Tentative
    ↓
Converted
```

Lost leads may be shown separately rather than as a pipeline stage.

Important:

- Do not show the internal repeated-follow-up workflow status.
- Use `status_label` where appropriate.
- Keep the pipeline understandable to a manager who does not know the database implementation.

Display each stage with:

- Count
- Percentage of total leads

Example:

```text
New Leads        24
Needs Follow Up  15
Tentative         8
Converted         5
Lost              6
```

The exact status-to-display mapping must use the existing system's current values and labels.

Do not invent new workflow behavior.

---

# 9. Section 3 — Lead Creation Trend

Add a line or bar chart showing lead creation over time.

Default:

```text
Last 30 Days
```

Allow the existing dashboard date/reporting filter to determine the range if the project already has one.

Use:

```text
customer.added_date
```

Count only visible records:

```text
type = 'leads'
AND is_deleted = 0
```

Group by date.

Recommended display:

```text
Mon  Tue  Wed  Thu  Fri  Sat  Sun
 4    6    2    8    5    3    7
```

A monthly grouping may be used where a longer date range is selected.

---

# 10. Section 4 — Calls Activity Trend

Create a chart showing call/follow-up activity.

Use:

```text
customer_calls.created_at
```

because this represents when the activity record was actually created.

Do not use `customer_calls.date` for activity-volume charts.

Recommended:

```text
Calls Created — Last 30 Days
```

Group by day.

Apply the correct customer visibility scope through the `customer` table.

---

# 11. Section 5 — Follow-up Performance

Create a summary widget for follow-up scheduling.

Show:

```text
Due Today
Upcoming
Overdue
```

### Due Today

Visible call/follow-up records where:

```text
customer_calls.date = today
```

Use the application's timezone.

### Upcoming

Visible future follow-ups after today.

### Overdue

Visible follow-ups where:

```text
customer_calls.date < current time/date
```

and the follow-up has not been closed/resolved according to the existing module's current behavior.

Do not invent a new completion field.

Inspect the existing Calls/Follow-up implementation before deciding how completed vs pending calls are identified.

---

# 12. Section 6 — Upcoming Follow-ups Table

Create a compact table below the charts.

Columns:

```text
#
Company Name
Customer / Lead
Type
Assigned To
Follow-up Date
Status
Remark
```

Limit the dashboard widget to the next 5–10 records.

Sort by:

```text
customer_calls.date ASC
```

Only show future/active follow-ups.

Reuse existing table styling.

Do not duplicate the full DataTable functionality if the dashboard only needs a small preview.

Provide a link/button to the existing detailed Calls/Follow-up page where appropriate.

---

# 13. Section 7 — Missed / Overdue Follow-ups

Create a dedicated attention widget.

Show:

```text
Overdue Follow-ups: X
```

Below it, show a compact list:

```text
Customer
Staff
Scheduled Date
Days Overdue
```

Example:

```text
ABC Company
Sales
20 Aug 2026
5 days overdue
```

This section should be visually prominent because it represents actionable sales work.

Use existing dashboard button/card styles rather than creating a completely new visual system.

---

# 14. Section 8 — Staff Performance

Create a staff performance table.

For each visible salesperson show:

```text
Staff
Leads
Calls
Follow-ups
Lost Leads
Converted
Conversion %
```

Possible formulas:

```text
Conversion % =
Converted Leads / Total Leads × 100
```

Do not divide by zero.

Use:

```text
0%
```

when the denominator is zero.

For admin:

- Show staff members belonging to the current company context.
- Respect the existing staff/company relationships.

For staff login:

- Do not show performance data of other staff.
- Only show the logged-in staff's own visible records.

---

# 15. Section 9 — Staff Activity Comparison

Create a simple horizontal bar chart or ranking widget.

Possible metrics:

```text
Calls Created
Leads Added
Conversions
```

Provide only one metric at a time if the existing UI is simple.

Example:

```text
Top Call Activity

Sales          74
Rahul          52
Amit           41
```

Do not overcomplicate the chart.

---

# 16. Section 10 — Lead Aging

Create a lead-aging report.

Calculate age based on:

```text
current date - customer.added_date
```

Only active leads should be included.

Suggested buckets:

```text
0–7 Days
8–15 Days
16–30 Days
31–60 Days
60+ Days
```

Display:

```text
0–7 Days       12
8–15 Days       8
16–30 Days      6
31–60 Days      9
60+ Days       4
```

The purpose is to identify leads that have been sitting without being converted.

Below the chart, optionally display the oldest active leads.

Columns:

```text
Lead
Added Date
Age
Current Status
Next Follow-up
```

---

# 17. Section 11 — Conversion Reporting

Create conversion statistics.

Use the existing movement/conversion data.

Primary KPIs:

```text
Total Leads
Converted Leads
Conversion Rate
```

Formula:

```text
Conversion Rate =
Converted Leads / Total Leads × 100
```

Do not assume that `type = customer` alone means the record was originally a lead unless the existing conversion implementation confirms this.

Use existing `is_move`, `move_date`, and/or existing log actions to identify true lead-to-customer movement.

---

# 18. Section 12 — Monthly Conversion Trend

Create a chart with:

```text
Month
Leads Added
Converted
Lost
```

Example:

```text
Month     Leads    Converted    Lost
January     30         5          4
February    42         8          6
March       51        10          7
```

Use existing historical fields.

Where conversion history cannot be reliably reconstructed from the current records, use only the data the existing implementation actually records.

Do not fabricate historical conversion numbers.

---

# 19. Section 13 — Lost Leads

Create a small lost-lead report.

Show:

```text
Total Lost
Lost This Month
Lost Trend
```

Table:

```text
Lead
Lost Date
Added By
Last Remark
```

Use the existing log data when historical lost date/actor information is required.

The current system already stores loss actions in `customer_log`.

Do not create a new lost-reason report unless a real lost-reason field exists.

If a future lost-reason feature is added, this report can be extended later.

---

# 20. Section 14 — Customer vs Lead Call Activity

Since `customer_calls.is_lead` exists, show a simple comparison:

```text
Lead Calls
Customer Calls
```

Example:

```text
Lead Calls       64%
Customer Calls   36%
```

Use this only as an activity distribution.

Do not interpret it as sales performance by itself.

---

# 21. Section 15 — Recent Activity

Use `customer_log` to create a compact recent activity feed.

Show the last 5–10 visible events.

Possible display:

```text
Lead Added
Follow-up Added
Lead Status Changed
Lead Lost
Customer Updated
```

Display:

```text
Action
Customer / Lead
Staff
Date/Time
```

Use the existing `message` field when it is already human-readable.

Do not display raw JSON from the `json` column.

Do not display raw internal action names when a friendly label can be used.

Sort:

```text
added_date DESC
```

Apply the same company/staff visibility rules.

---

# 22. Section 16 — Needs Attention

Create a compact dashboard section specifically for actionable issues.

Possible items:

### Overdue Follow-ups

```text
7
```

### Leads Without a Future Follow-up

```text
12
```

### Leads Older Than 30 Days

```text
9
```

### Fresh Leads Not Yet Followed Up

```text
5
```

Only include metrics that can be reliably determined from the current schema and existing workflow.

Do not invent a "completed" concept when the database does not record it.

Every count must link to or correspond with the existing detailed module when practical.

---

# 23. Section 17 — Geographic Reporting

The `customer` table already contains:

- `state_name`
- `city_name`

Create a simple report:

```text
Leads by State
```

Example:

```text
Maharashtra   34
Gujarat       22
Odisha        15
```

Optional second report:

```text
Top Cities by Leads
```

Keep this lightweight.

A simple horizontal bar chart or table is enough.

Do not introduce a map unless the project already has a map component/library that can be reused easily.

---

# 24. Section 18 — Company Reporting

The dashboard is company-aware.

Show data only for the current company context.

For admin:

```text
Current Session Company
```

is the reporting scope.

Do not expose data from unrelated companies.

Remember that `customer.company_id` can contain multiple company IDs.

Use the same safe matching logic consistently across every report.

---

# 25. Date and Time Rules

The project currently sets the PHP timezone to:

```php
Asia/Calcutta
```

Use the application's existing timezone configuration and date helper behavior.

Do not introduce a second timezone configuration just for the dashboard.

Be consistent when determining:

- Today
- Tomorrow
- Overdue
- Upcoming
- Daily activity
- Monthly activity

Use database datetime fields consistently.

---

# 26. Suggested Model Structure

Do not create one enormous `get_dashboard_data()` method containing every query.

Keep methods simple and separated by reporting purpose.

Example structure:

```php
public function get_dashboard_lead_summary($company_id, $user_id = 0, $is_staff = false)

public function get_dashboard_call_summary($company_id, $user_id = 0, $is_staff = false)

public function get_dashboard_lead_pipeline($company_id, $user_id = 0, $is_staff = false)

public function get_dashboard_lead_trend($company_id, $user_id = 0, $is_staff = false, $from = '', $to = '')

public function get_dashboard_call_trend($company_id, $user_id = 0, $is_staff = false, $from = '', $to = '')

public function get_dashboard_upcoming_followups($company_id, $user_id = 0, $is_staff = false)

public function get_dashboard_overdue_followups($company_id, $user_id = 0, $is_staff = false)

public function get_dashboard_staff_performance($company_id, $user_id = 0, $is_staff = false)

public function get_dashboard_lead_aging($company_id, $user_id = 0, $is_staff = false)

public function get_dashboard_recent_activity($company_id, $user_id = 0, $is_staff = false)
```

The actual names do not have to be exactly the same.

Follow the naming conventions already used in the project.

---

# 27. Reusable Visibility Scope

The most important implementation detail is avoiding different visibility conditions in different methods.

Create a simple reusable helper/method if the existing project pattern allows it.

Conceptually:

```php
private function get_dashboard_customer_scope($company_id, $user_id = 0, $is_staff = false)
```

The method should represent:

### Admin

```text
customer.is_deleted = 0
AND FIND_IN_SET(company_id, customer.company_id)
```

### Staff

```text
customer.is_deleted = 0
AND FIND_IN_SET(company_id, customer.company_id)
AND customer.added_by_id = user_id
```

If adding a private helper does not fit the existing coding style, duplicate a small and clear condition instead of introducing an unnecessary abstraction.

The priority is **consistency and simplicity**, not architectural purity.

---

# 28. Query Safety

The project contains older CodeIgniter 3 query patterns.

For the new dashboard implementation:

- Follow the existing database abstraction.
- Use CodeIgniter query bindings/escaping for user/session-derived values where practical.
- Do not directly concatenate untrusted request values into SQL.
- Do not accept company ID or staff ID from the browser as the authority for access.
- Always get access-control values from the server-side session.

The frontend must never be trusted to decide which company or staff data is visible.

---

# 29. Controller Logic

The controller should:

1. Get the current company ID from the session.
2. Determine whether the login is staff:

```php
$is_staff = ($this->session->userdata('super_type_id') == 7);
```

3. Get the current staff/user ID from the session when required.
4. Call the dashboard model methods.
5. Pass the resulting data to the existing dashboard view.

Do not put complex SQL inside the controller.

Do not put access-control decisions in JavaScript.

---

# 30. View Logic

Keep the view responsible for:

- Rendering cards
- Rendering tables
- Rendering chart containers
- Showing formatted values
- Showing empty states

Do not put database queries in the view.

Do not perform company/staff authorization in the view.

Do not expose raw database values unnecessarily.

Use existing CSS classes/components where possible.

---

# 31. Charts

Use whatever charting library is already installed and used by this project.

Before adding a new library, inspect the existing dashboard/frontend assets.

Do not add Chart.js/ApexCharts/another chart library if the project already has a charting solution that can be reused.

Keep charts simple:

- Bar chart
- Line chart
- Doughnut/pie where useful

Avoid excessive chart types.

---

# 32. Empty States

Every widget must work when there is no data.

Example:

```text
No lead data available
```

```text
No upcoming follow-ups
```

```text
No activity found
```

Do not show broken charts, NaN, undefined, or empty HTML fragments.

Handle zero values correctly.

---

# 33. Performance Rules

The dashboard may execute many reports, so avoid unnecessary repeated queries.

Important:

- Do not load every customer/call/log row into PHP and then calculate everything manually if SQL aggregation can do it efficiently.
- Use `COUNT`, `GROUP BY`, and date filtering for summary reports.
- Use `LIMIT` for dashboard preview tables.
- Avoid querying the same data multiple times unnecessarily.
- If several small counts can safely be combined into one simple query, that is acceptable.
- Do not create a giant, unreadable SQL query just to reduce query count.

Prefer **simple and reasonably efficient SQL** over complex abstractions.

---

# 34. Indexing Considerations

Before optimizing further, check the existing indexes.

The current SQL dump shows only the primary keys for these tables.

If dashboard queries become slow with real production data, consider indexes around frequently filtered/grouped fields such as:

### `customer`

```text
company_id
type
status
added_by_id
added_date
status_date
move_date
is_deleted
```

### `customer_calls`

```text
customer_id
date
created_at
added_by
is_lead
```

### `customer_log`

```text
customer_id
added_by
added_date
action
```

Do not automatically add every index without checking existing production usage and query plans.

Also note that storing multiple company IDs inside `customer.company_id` as CSV limits indexing efficiency. Do not redesign that schema as part of this dashboard task unless explicitly requested.

---

# 35. Reporting Filter

If the existing dashboard already has a company/date filtering mechanism, extend that implementation rather than creating a second filtering system.

The preferred reporting controls are:

```text
Company
Staff
Period
Lead/Customer
Status
```

However:

### Admin

Admin can select/operate within the current company context already provided by the application.

### Staff

Staff must not be allowed to change visibility to another company or another staff member by manipulating filter parameters.

A staff filter can be displayed as the logged-in staff member if needed, but it must remain server-enforced.

---

# 36. Recommended Initial Dashboard Order

Implement the new section in this order:

## Row 1

```text
Total Leads
New Leads
Active Leads
Converted Leads
Lost Leads
Total Calls
```

## Row 2

```text
Lead Pipeline
Lead Status Distribution
```

## Row 3

```text
Lead Creation Trend
```

## Row 4

```text
Call Activity Trend
Follow-up Performance
```

## Row 5

```text
Upcoming Follow-ups
Overdue Follow-ups
```

## Row 6

```text
Staff Performance
```

## Row 7

```text
Lead Aging
Lost Lead Summary
```

## Row 8

```text
Needs Attention
Recent Activity
```

## Row 9

```text
Geographic / Additional Reports
```

This keeps the most important information near the top and the deeper analytics below it.

---

# 37. First Implementation Scope

Do NOT implement all advanced reports in one step.

The first implementation should contain:

### Existing dashboard

Keep unchanged.

### New section

Implement only:

1. Lead KPI cards
2. Lead pipeline
3. Lead creation trend
4. Call activity trend
5. Due/upcoming/overdue follow-up summary
6. Upcoming follow-up table
7. Staff performance
8. Lead aging
9. Recent customer/lead activity
10. Needs Attention

After this is stable, add:

- Conversion trend
- Lost-lead analytics
- Geographic reporting
- Customer vs lead call distribution
- More advanced performance metrics

---

# 38. Important Conversion Logic Warning

Do not make unsupported assumptions about conversion.

The current schema contains both:

```text
customer.type
```

and:

```text
is_move
move_date
```

and historical records in:

```text
customer_log
```

Before implementing the final conversion report, inspect the existing code that moves a lead to a customer.

Use the exact existing business logic.

The dashboard must report what the application actually means by "converted", not what the developer assumes it means.

---

# 39. Important Follow-up Logic Warning

Do not invent a new definition of "completed call".

The `customer_calls` table stores:

```text
date
created_at
status
remark
```

but it does not, from the provided schema alone, provide a dedicated `completed_at` / `completed` field.

Therefore:

- Due today → can be calculated from `date`.
- Upcoming → can be calculated from future `date`.
- Overdue → can be calculated from past `date` only when the existing application workflow confirms that the record is still actionable.
- Completed → must use the existing module's actual business logic if one exists.

Do not create fake completion statistics from insufficient data.

---

# 40. UI Principles

The dashboard should look like a natural extension of the existing application.

Follow the existing:

- Card radius
- Shadows
- Spacing
- Button style
- Typography
- Badge style
- Table style
- Colors
- Responsive grid

Do not redesign the entire dashboard.

Use clear titles such as:

```text
Lead Pipeline
Lead Activity
Call Activity
Follow-up Summary
Upcoming Follow-ups
Staff Performance
Lead Aging
Recent Activity
Needs Attention
```

Avoid technical/database terminology.

---

# 41. Final Acceptance Criteria

The implementation is complete only when all of the following are true:

- Existing dashboard cards remain unchanged.
- New reporting sections appear below the current dashboard.
- Admin sees only current-company data.
- Staff sees only current-company + own-added data.
- Staff cannot gain access to another staff member by modifying request parameters.
- Call reports are scoped through the visible customer/lead records.
- Multi-company `customer.company_id` records are handled correctly.
- Internal workflow terminology is not exposed on the dashboard.
- No raw JSON is displayed.
- No database query exists in the view.
- No authorization logic exists only in JavaScript.
- No broken charts appear when data is empty.
- No divide-by-zero errors occur in percentages.
- Existing project coding patterns are followed.
- Existing libraries/components are reused.
- Code remains simple and readable.
- Existing lead/call/follow-up module behavior is not changed.
- No unrelated files/modules are modified.
- Conversion and completion metrics are based on verified existing business logic.
- Dashboard queries remain reasonably efficient.

---

# 42. Agentic AI Implementation Instruction

When implementing this specification:

1. First inspect the existing dashboard controller, model, view, routes, JavaScript, CSS, and any existing chart/table implementation.
2. Then inspect the existing lead/customer call/follow-up methods to understand their exact business rules.
3. Reuse existing code patterns instead of creating a new architecture.
4. Verify the existing session field names before writing filters.
5. Implement the shared company/staff visibility rule first.
6. Add one reporting section at a time.
7. Test every report with:
   - Admin + Company A
   - Admin + Company B
   - Staff A + Company A
   - Staff B + Company A
   - Multi-company customer records
   - No-data case
8. Verify that staff cannot see another staff member's leads/customers.
9. Verify that calls belonging to visible customers are reported correctly.
10. Verify date boundaries using the application's timezone.
11. Do not alter existing lead/call/follow-up behavior unless explicitly required.
12. Keep implementation simple. Do not over-engineer.

The final result should feel like a **reporting layer added on top of the existing CRM**, not a rewrite of the existing Leads/Calls module.
