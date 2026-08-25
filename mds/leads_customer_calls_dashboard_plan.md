# Leads & Customer Calls Dashboard — UI + Reporting Implementation Plan

## 1. Objective

Build a detailed **Leads & Customer Calls dashboard** for management and sales reporting using the existing CodeIgniter 3 application, existing database tables, existing UI conventions, and existing coding patterns.

The dashboard should answer these questions quickly:

- How many leads and customers are currently active?
- How many new leads were added?
- How many calls/follow-ups are being created?
- What follow-ups are due today, upcoming, or overdue?
- How is each sales staff member performing?
- How many leads were lost?
- How many leads moved toward customer conversion?
- Which leads/customers need attention?
- What has changed recently?
- Are lead and call activities increasing or decreasing?

The implementation must be **simple, readable, and consistent with the current project**. Do not introduce a new architecture, unnecessary abstraction, new framework, or complicated reporting engine.

---

## 2. Important Existing Data Model

Use the existing tables as the primary reporting sources.

### `customer`

This is the primary source for the **current state of a lead/customer**.

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

Current database status values include:

- `fresh`
- `follow`
- `stalking`
- `lost`

However, **do not use the term `stalking` anywhere in the dashboard UI, charts, cards, filters, legends, reports, tooltips, or user-facing documentation**.

The database value can remain unchanged for compatibility with the existing module, but for reporting purposes it should be treated and displayed simply as:

> **Follow-up**

This status represents taking follow-up of an existing customer. It is an internal database value and should not become a business/reporting term.

Also do not create a dashboard category called "Stalking".

---

### `customer_calls`

Use this table for **call/follow-up activity history**.

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

Important distinction:

- `created_at` = when the call/follow-up entry was created
- `date` = scheduled/follow-up date stored against the call record

Do not treat every `customer_calls` row as a unique customer or unique lead. A single customer/lead can have many call rows.

---

### `customer_log`

Use this table for **historical activity/audit reporting**.

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

This table is useful for:

- Recent activity
- Historical status changes
- Lead creation history
- Lost lead history
- Follow-up history
- Staff activity timeline

Do not use `customer_log` as the primary source for current lead counts or current status. Current state should come from `customer`.

---

## 3. Core Reporting Rules

Keep the reporting logic consistent.

### Rule 1 — Current state

For current lead/customer counts, use the `customer` table.

Examples:

- Active leads
- Fresh leads
- Follow-up leads
- Lost leads
- Current customers

Do not count these from `customer_log`.

---

### Rule 2 — Activity

For call/follow-up activity, use `customer_calls`.

Examples:

- Number of calls created
- Calls created today
- Calls created this month
- Calls by staff
- Lead vs customer call activity

---

### Rule 3 — Historical timeline

For audit/history display, use `customer_log`.

Examples:

- Lead added
- Lead moved to follow-up
- Lead marked lost
- Follow-up activity
- Recent staff activity

---

### Rule 4 — Do not duplicate records

When calculating "number of leads/customers":

Use `COUNT(DISTINCT customer.id)` where the metric represents unique entities.

When calculating call activity:

Use `COUNT(customer_calls.id)` because each row represents one call/follow-up record.

---

### Rule 5 — Respect deleted records

Always exclude:

```sql
customer.is_deleted = 0
```

when reporting from `customer`.

---

## 4. Dashboard Filters

Add a filter area at the top of the dashboard.

### Filters

1. Company
2. Staff
3. Period
4. Type
5. Status

Recommended period options:

- Today
- This Week
- This Month
- Last Month
- Last 30 Days
- Custom Range

Recommended type options:

- All
- Leads
- Customers

Recommended staff option:

- All Staff
- Individual Staff

Recommended status values shown to the user:

- Fresh
- Follow-up
- Lost
- Converted/Customer where applicable

Do not show the raw `stalking` database value.

### Filter behavior

The selected filters must affect all relevant dashboard reports.

Example:

If Staff = `Sales`, then:

- lead counts should use `added_by_id` / `added_by_name`
- call counts should use `added_by` / `added_by_name`
- historical activity should use `added_by` / `added_by_name`

Do not build different filtering rules for different widgets unless the data source requires it.

---

# 5. Dashboard Layout

Use the existing dashboard visual style.

Do not redesign the entire application.

Keep the current:

- rounded cards
- light background
- simple Bootstrap-style layout
- existing button styles
- existing font sizing
- existing icon style
- existing spacing patterns

Recommended dashboard structure:

```text
-------------------------------------------------------------
Filters
Company | Staff | Period | Type | Status
-------------------------------------------------------------

KPI CARDS
-------------------------------------------------------------
Total Leads | Active Leads | New Leads | Calls | Follow-ups | Lost
-------------------------------------------------------------

LEAD SUMMARY
-------------------------------------------------------------
Lead Pipeline              | Lead Status Distribution
-------------------------------------------------------------

LEAD TREND
-------------------------------------------------------------
Leads Added / Lost / Converted over time
-------------------------------------------------------------

CALLS & FOLLOW-UPS
-------------------------------------------------------------
Call Activity               | Follow-up Performance
-------------------------------------------------------------

STAFF REPORT
-------------------------------------------------------------
Staff Performance Table
-------------------------------------------------------------

ATTENTION
-------------------------------------------------------------
Needs Attention             | Recent Activity
-------------------------------------------------------------

ADDITIONAL REPORTS
-------------------------------------------------------------
Lead Aging                  | Lost Lead Analysis
-------------------------------------------------------------
```

---

# 6. KPI Cards

Create KPI cards at the top.

## Card 1 — Total Leads

Meaning:

Number of current non-deleted records where:

```sql
type = 'leads'
AND is_deleted = 0
```

Do not count call rows.

---

## Card 2 — Active Leads

Active leads should exclude lost leads.

Suggested logic:

```sql
type = 'leads'
AND is_deleted = 0
AND status != 'lost'
```

---

## Card 3 — New Leads

Number of leads added during the selected period.

Use:

```sql
added_date
```

Example:

```sql
added_date >= start_date
AND added_date <= end_date
```

---

## Card 4 — Calls

Number of call/follow-up records created during the selected period.

Use:

```sql
customer_calls.created_at
```

Do not use `date` for this metric because `date` represents the scheduled/follow-up date.

---

## Card 5 — Follow-ups Due

Number of current lead/customer records with a follow-up date in the selected period.

The existing customer table stores:

- `status_date`
- `status_label`

Use the current `status_date` when the dashboard represents the **current next follow-up**.

---

## Card 6 — Lost Leads

Number of currently lost leads.

```sql
type = 'leads'
AND status = 'lost'
AND is_deleted = 0
```

---

## Optional Card 7 — Customers

Number of current customer records.

```sql
type = 'customer'
AND is_deleted = 0
```

---

## Optional Card 8 — Conversion Rate

Only implement this if the existing customer movement logic can reliably identify lead-to-customer conversion.

The existing database contains:

- `is_move`
- `move_date`

Do not invent a new conversion calculation if the current application does not provide a reliable conversion event.

If the implementation confirms that `is_move = 1` represents a lead moved to customer, use:

```text
Conversion Rate =
Converted Leads / Total Leads × 100
```

Document the exact interpretation in code comments.

---

# 7. Lead Pipeline Report

Create a horizontal funnel or grouped bar chart.

User-facing stages should be:

```text
Fresh
Follow-up
Lost
Converted / Customer
```

Do not expose the internal `stalking` status.

### Recommended current-state mapping

```text
customer.status = fresh
    -> Fresh

customer.status = follow
    -> Follow-up

customer.status = stalking
    -> Follow-up

customer.status = lost
    -> Lost
```

This mapping should be centralized in one small reporting method/helper instead of repeated throughout the code.

Do not alter the database status merely for dashboard reporting.

---

# 8. Lead Status Distribution

Create a donut/pie chart showing the current lead distribution.

Example:

```text
Fresh            40%
Follow-up        45%
Lost             15%
```

If converted leads are reliably available, add:

```text
Converted
```

Do not show internal database status names that are not business-friendly.

---

# 9. Leads Added Trend

Create a line or bar chart.

Default:

**Last 30 days**

Allow the selected dashboard period to control the range.

Data:

```text
Date
Lead count
```

Query source:

```text
customer.added_date
```

Filter:

```text
type = leads
is_deleted = 0
```

Group by date.

The query should remain simple.

Do not calculate the complete chart in PHP when MySQL can perform the grouping.

---

# 10. Lost Leads Trend

Create a small line/bar series for lost leads.

Do not rely only on the current `customer.status`, because that only tells us current state.

For historical lost events, use:

```text
customer_log
```

with:

```text
action = 'lost'
```

and:

```text
added_date
```

This provides the historical number of leads marked lost during each period.

This is different from:

> Current Lost Leads

Both metrics can exist:

- Current Lost Leads → `customer`
- Leads Lost During Period → `customer_log`

---

# 11. Call Activity Report

Create a line/bar chart for calls created over time.

Source:

```text
customer_calls
```

Date:

```text
created_at
```

Group by:

- day for short periods
- month for long periods

Do not over-engineer this into multiple queries.

One grouped query should be sufficient for each chart.

---

# 12. Lead Calls vs Customer Calls

Create a small comparison chart.

Source:

```text
customer_calls.is_lead
```

Show:

```text
Lead Calls
Customer Calls
```

Example:

```text
Lead Calls       38
Customer Calls   24
```

This should represent call records, not unique customers.

---

# 13. Follow-up Performance

Create a summary card/table.

Example:

```text
Follow-ups Due Today    10
Completed                6
Pending                  3
Overdue                  1
```

Important:

The existing tables contain scheduled dates but do not appear to contain a dedicated "completed" flag.

Therefore **do not pretend a row is completed just because its date is in the past**.

For the first version, use only metrics that are supported by the current data.

Recommended first-version metrics:

- Due Today
- Upcoming
- Overdue
- Total Follow-up Records

If a true completed/completion state is needed later, add or use an existing explicit field/event rather than guessing.

---

# 14. Upcoming Follow-ups

Create a table showing the next upcoming follow-ups.

Columns:

```text
Customer / Lead
Type
Staff
Follow-up Date
Status
Remark
```

Sort:

```text
nearest follow-up first
```

Use the current `customer.status_date` where the existing module treats that as the current next follow-up.

Only show future dates.

Example:

```text
Yuz          Lead      Sales     30 Aug 2026 08:43 PM   Tentative
Streamtech   Customer  Sales     26 Aug 2026 08:00 PM   Needs Follow Up
```

---

# 15. Today's Follow-ups

Create a dedicated compact table.

Columns:

```text
Customer / Lead
Type
Staff
Follow-up Date
Status
```

Use today's date against the current follow-up date.

Do not use the phrase `stalking`.

Use:

```text
Follow-up
```

for all dashboard terminology.

---

# 16. Overdue / Missed Follow-ups

Create a high-visibility report.

An overdue follow-up means:

```text
follow-up date < current date/time
```

and the record is still considered active for follow-up.

Do not incorrectly mark already lost records as overdue active follow-ups unless the business logic specifically requires it.

Suggested filter:

```text
status != lost
status_date < current_datetime
```

Show:

```text
Customer / Lead
Staff
Follow-up Date
Days Overdue
Current Status
```

Sort by oldest first.

---

# 17. Staff Performance

Create a table for sales staff.

Columns:

```text
Staff
Leads Added
Calls Created
Follow-ups
Lost Leads
Current Active Leads
Converted
Conversion %
```

Only include columns that can be reliably calculated from the current schema.

### Leads Added

Use:

```text
customer.added_by_id
customer.added_by_name
```

where:

```text
type = leads
```

### Calls Created

Use:

```text
customer_calls.added_by
customer_calls.added_by_name
```

### Lost Leads

For current lost leads:

```text
customer
status = lost
```

For lost events during a period:

```text
customer_log
action = lost
```

Do not mix those two meanings.

---

# 18. Staff Activity Ranking

Optional small widget.

Example:

```text
Top Call Activity

Sales             42 calls
Susanta            31 calls
Vahid               17 calls
```

Use simple SQL `GROUP BY`.

Avoid complicated ranking logic in PHP.

---

# 19. Lead Aging Report

This is a management report showing how long active leads have been in the system.

Calculate:

```text
current_date - customer.added_date
```

Only include:

```text
type = leads
is_deleted = 0
status != lost
```

Recommended buckets:

```text
0 - 7 days
8 - 15 days
16 - 30 days
31 - 60 days
60+ days
```

Example:

```text
0 - 7 days       8
8 - 15 days      5
16 - 30 days     7
31 - 60 days     4
60+ days         10
```

Also provide a table for the oldest active leads.

---

# 20. Needs Attention Widget

Create a management-oriented widget.

Possible rules:

### Rule A — Overdue follow-up

```text
status_date < now
AND status != lost
```

### Rule B — Fresh lead without a follow-up

```text
status = fresh
AND no valid future/current follow-up
```

### Rule C — Old active lead

```text
active lead
AND age > 30 days
```

### Rule D — Lost leads

Show total lost count as a warning item.

### Rule E — No recent activity

Only implement this when the definition is clearly supported by the existing call/log data.

Do not create vague assumptions such as "inactive for 7 days" without documenting the exact calculation.

Display this as:

```text
Needs Attention

7 overdue follow-ups
4 old active leads
3 fresh leads without follow-up
5 lost leads this month
```

Each item should be clickable and take the user to the relevant existing module/filter where practical.

---

# 21. Recent Activity Feed

Use `customer_log`.

Show the latest 5-10 activities.

Example:

```text
25 Aug, 04:39 PM
Sales scheduled a follow-up for Yuz

25 Aug, 02:57 PM
Machenix marked Dharmesh as Lost

24 Aug, 07:01 PM
Sales added Dharmesh as a lead
```

Use:

```text
label.message
```

where available.

Fallback to:

```text
message
```

if label information is not available.

Do not expose raw JSON.

Do not display raw `action` values if they are technical/internal terms.

For example, display:

```text
Follow-up Added
Lead Lost
Lead Added
```

instead of raw database action names where appropriate.

---

# 22. Lead Creation vs Lost vs Conversion Trend

For monthly reporting, create a grouped chart:

```text
Month
Leads Added
Leads Lost
Leads Converted
```

The data sources can differ:

- Leads Added → `customer.added_date`
- Leads Lost → `customer_log` with `action = lost`
- Leads Converted → existing move/conversion event only if reliably identifiable

Do not force all metrics from one table when their meanings differ.

---

# 23. Geographic Reporting

The customer table contains:

- `state_name`
- `city_name`

Create:

### Leads by State

```text
Maharashtra   20
Gujarat       12
Odisha         8
```

### Top Cities

```text
Mumbai        10
Nagpur         8
Ahmedabad      7
```

Keep this as a later-stage report.

Do not introduce maps unless there is an actual reporting benefit.

---

# 24. Company Reporting

Because `customer` contains `company_id` and `company_name`, support company-level reporting.

Example:

```text
Company
Total Leads
Active Leads
Calls
Lost
Converted
```

This should integrate with the existing company selector already visible in the application.

Do not create a second unrelated company selection system.

---

# 25. Dashboard API / Controller Structure

Follow the existing CodeIgniter 3 application patterns.

Do not introduce:

- Laravel-style services
- repository patterns
- DTO layers
- dependency injection frameworks
- unnecessary classes
- external reporting libraries

Use the existing:

```text
Controller
    ↓
Model
    ↓
MySQL
    ↓
View / AJAX
```

A simple structure is preferred.

Example controller methods:

```php
public function calls_leads_dashboard()
```

Then AJAX endpoints can be added only where necessary.

Possible endpoints:

```php
get_dashboard_summary()
get_dashboard_lead_pipeline()
get_dashboard_lead_trend()
get_dashboard_call_trend()
get_dashboard_staff_report()
get_dashboard_upcoming_followups()
get_dashboard_attention()
get_dashboard_activity()
```

Do not create an endpoint for every tiny number.

Where possible, return multiple values from one query/request.

---

# 26. Model Methods

Add reporting methods to the existing model structure.

Keep methods small and clearly named.

Example:

```php
get_dashboard_summary($filters)
get_dashboard_lead_pipeline($filters)
get_dashboard_lead_trend($filters)
get_dashboard_call_trend($filters)
get_dashboard_staff_performance($filters)
get_dashboard_upcoming_followups($filters)
get_dashboard_attention($filters)
get_dashboard_recent_activity($filters)
```

Do not create generic methods such as:

```text
get_everything()
get_dashboard_data()
get_report()
```

because they become difficult to maintain.

---

# 27. Filter Builder

Use one small internal pattern for shared filters.

Example conceptual structure:

```php
$company_id
$staff_id
$start_date
$end_date
$type
$status
```

Then build SQL conditions consistently.

Do not duplicate five different versions of the same filter logic.

However, do not over-abstract it into a complicated query builder.

Keep it readable.

---

# 28. Date Handling

The existing project sets the application timezone to:

```php
Asia/Calcutta
```

Keep the existing project timezone behavior.

Do not introduce a second timezone system for the dashboard.

For date filtering:

- use full-day boundaries for date-only filters
- use proper datetime comparisons for follow-up times
- be careful with `23:59:59`

Example:

```text
start = 2026-08-01 00:00:00
end   = 2026-08-31 23:59:59
```

Do not mix formatted display dates with database comparison dates.

---

# 29. SQL Simplicity Rules

The reporting SQL must remain simple.

Prefer:

```sql
COUNT()
COUNT(DISTINCT ...)
SUM()
GROUP BY
ORDER BY
WHERE
BETWEEN
```

Use joins only when needed.

Do not create huge nested queries when a simple query is enough.

Example:

```sql
SELECT COUNT(*) AS total
FROM customer
WHERE type = 'leads'
  AND is_deleted = 0
```

Example:

```sql
SELECT added_by_id, added_by_name, COUNT(*) AS total
FROM customer
WHERE type = 'leads'
  AND is_deleted = 0
GROUP BY added_by_id, added_by_name
ORDER BY total DESC
```

---

# 30. Avoid Incorrect Reporting

Do not make these mistakes.

### Mistake 1

Counting every call as a lead.

Wrong:

```text
COUNT(customer_calls.id) = lead count
```

Correct:

```text
COUNT(DISTINCT customer.id)
```

---

### Mistake 2

Using `customer_log` to calculate current status.

Logs represent history.

Use `customer.status`.

---

### Mistake 3

Using `customer_calls.date` as call creation activity.

For call activity use:

```text
created_at
```

For scheduled follow-up use:

```text
date
```

or the current customer `status_date`, depending on the existing module's behavior.

---

### Mistake 4

Showing internal database terminology.

Never show:

```text
stalking
```

Use:

```text
Follow-up
```

---

### Mistake 5

Calling a past follow-up "completed" just because its date passed.

There is no explicit completion field in the supplied schema.

Use:

```text
Overdue
```

until an explicit completion state exists.

---

### Mistake 6

Calculating conversion without a reliable conversion event.

Only use `is_move` / `move_date` after verifying how the existing move-to-customer functionality writes those fields.

---

# 31. UI Implementation

Use the existing frontend conventions.

Prefer the existing:

- Bootstrap/grid structure
- cards
- buttons
- icons
- badges/chips
- DataTables
- AJAX style
- modal style
- date formatting

Do not introduce a new UI library only for the dashboard.

For charts, use the chart library already present in the project.

Do not add a second chart framework.

---

# 32. Chart Recommendations

Recommended chart types:

### Lead Pipeline
Horizontal bar / funnel

### Lead Distribution
Donut

### Lead Trend
Line or bar

### Call Activity
Line or bar

### Lead vs Customer Calls
Donut or small bar

### Staff Performance
Table + horizontal bar if useful

### Lead Aging
Bar chart

### Lost Reason
Donut/bar only if a proper lost-reason field exists

Avoid creating charts just because data exists.

A report should exist only when it answers a useful business question.

---

# 33. DataTables

For detailed report tables, use the same DataTables patterns already used in the project.

Recommended DataTables:

- Staff performance
- Upcoming follow-ups
- Overdue follow-ups
- Old leads
- Recent activity

Use server-side processing if the result can become large.

Do not load thousands of records into the browser unnecessarily.

Follow the existing server-side DataTables implementation instead of creating a new custom pagination system.

---

# 34. Performance

For the first version:

- Use SQL aggregation for counts.
- Avoid loading all records into PHP.
- Avoid loops that execute one query per customer.
- Avoid N+1 queries.
- Select only required columns.
- Use indexed fields for date/filter columns where appropriate.

Potentially useful indexes to review later:

```text
customer:
(type, is_deleted)
(added_date)
(status)
(status_date)
(added_by_id)
(company_id)

customer_calls:
(customer_id)
(created_at)
(date)
(added_by)
(is_lead)

customer_log:
(customer_id)
(action)
(added_date)
(added_by)
```

Do not add indexes blindly. Check existing indexes and actual query usage first.

---

# 35. No Unnecessary Database Changes

Do not modify existing schema just to make the first dashboard version easier.

Use the current data first.

Only consider schema changes when a required metric cannot be calculated reliably.

Examples of future fields that may eventually be useful:

```text
call_status / completion_status
lost_reason
lead_source
conversion_date
next_followup_date
```

But these should be separate future enhancements, not assumed to exist now.

---

# 36. Important Terminology Rules

Use business-friendly dashboard terminology.

### Use

```text
Lead
Customer
Follow-up
Fresh Lead
Lost
Upcoming Follow-up
Overdue Follow-up
Calls
Conversions
Active Leads
Lead Aging
Staff Performance
Recent Activity
Needs Attention
```

### Do NOT use

```text
Stalking
Leads Moved To Stalking
Stalking Count
Stalking Leads
```

The internal database value may remain `stalking` because the existing application uses it, but all dashboard/reporting presentation must map it to:

```text
Follow-up
```

---

# 37. Recommended First-Version Dashboard

Implement these first.

## Section A — KPI

```text
Total Leads
Active Leads
New Leads
Total Customers
Calls
Lost Leads
```

## Section B — Funnel

```text
Fresh
Follow-up
Lost
Converted (only if reliable)
```

## Section C — Trends

```text
Leads Added
Calls Created
Leads Lost
```

## Section D — Follow-ups

```text
Today's Follow-ups
Upcoming Follow-ups
Overdue Follow-ups
```

## Section E — Staff

```text
Staff
Leads
Calls
Lost
Active Leads
Converted
```

## Section F — Attention

```text
Overdue
Old Leads
Fresh Leads Without Follow-up
```

## Section G — Activity

```text
Recent Customer/Lead Activity
```

This gives a complete dashboard without making it unnecessarily large.

---

# 38. Development Order

Implement in this order so debugging remains easy.

### Step 1 — Dashboard page shell

Create the dashboard page using existing layout/header/sidebar conventions.

No reporting logic yet.

---

### Step 2 — Filter UI

Add:

- Company
- Staff
- Period
- Type
- Status

Make the filter state work before adding charts.

---

### Step 3 — Summary API

Implement KPI queries.

Return:

```json
{
    "total_leads": 0,
    "active_leads": 0,
    "new_leads": 0,
    "customers": 0,
    "calls": 0,
    "lost_leads": 0
}
```

Use real DB values.

---

### Step 4 — Lead pipeline

Implement the current lead status report.

Map the internal database value `stalking` to `Follow-up` in the reporting result.

---

### Step 5 — Lead/call trends

Implement:

- leads added
- calls created
- leads lost

---

### Step 6 — Follow-up reports

Implement:

- today
- upcoming
- overdue

---

### Step 7 — Staff report

Implement staff aggregation.

---

### Step 8 — Recent activity

Use `customer_log`.

---

### Step 9 — Needs Attention

Add derived warning conditions.

---

### Step 10 — Performance optimization

After the dashboard works:

- inspect slow queries
- verify indexes
- remove unnecessary queries
- reduce duplicate API calls

Do not optimize blindly before the actual dashboard works.

---

# 39. Testing Requirements

Test each widget independently.

### Test filters

Verify:

```text
All Companies
Single Company

All Staff
Single Staff

All Types
Leads
Customers

All Period
Today
This Week
This Month
Custom
```

---

### Test date boundaries

Verify:

```text
00:00:00
23:59:59
```

for selected date ranges.

---

### Test duplicate calls

One customer with multiple `customer_calls` records must still count as one customer/lead.

---

### Test deleted customers

Deleted customers must not appear in current reports.

---

### Test status mapping

Database:

```text
stalking
```

Dashboard:

```text
Follow-up
```

Never display the raw internal value.

---

### Test empty states

Every widget should work when there is no data.

Examples:

```text
No lead data available
No calls found
No upcoming follow-ups
No recent activity
```

Do not show broken charts or PHP warnings.

---

# 40. Code Quality Rules for the Agentic AI

The implementation agent must follow these rules strictly.

1. **Inspect the existing code before writing new code.**
2. Reuse existing controller/model/view patterns.
3. Reuse existing helper functions.
4. Reuse existing DataTables implementation.
5. Reuse existing chart library.
6. Reuse existing Bootstrap/classes/styles.
7. Keep SQL readable.
8. Keep PHP methods small.
9. Do not introduce unnecessary architecture.
10. Do not rewrite unrelated modules.
11. Do not refactor existing working lead/call functionality unless required.
12. Do not change existing database status values.
13. Do not expose the internal `stalking` value in user-facing reporting.
14. Add comments only where the business logic is not obvious.
15. Avoid unnecessary abstractions.
16. Do not duplicate the same SQL/filter logic across many methods when a simple shared helper is sufficient.
17. Keep the implementation compatible with the existing PHP/CodeIgniter version.
18. Do not add a package/library when the current project already provides the required functionality.

---

# 41. Final Implementation Principle

The dashboard should be a **reporting layer over the existing working module**, not a replacement of the lead/call system.

Use:

```text
customer
    ↓
Current state

customer_calls
    ↓
Call / follow-up activity

customer_log
    ↓
Historical activity / audit
```

The dashboard should combine these three sources carefully.

The first priority is **correct numbers**, then useful visualization, then performance.

Do not add a chart unless its underlying metric has a clear and reliable definition.

Do not infer business events from missing fields.

Do not expose internal database terminology.

Keep the implementation simple, consistent with the existing codebase, and easy for another developer to maintain.
