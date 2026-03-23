# Adshqip Ad Tracker — Technical Documentation

> How the ad tracking system works end-to-end.

---

## Table of Contents

1. [Overview](#overview)
2. [Tracking Flow Diagram](#tracking-flow-diagram)
3. [Endpoints](#endpoints)
4. [How Each Tracking Type Works](#how-each-tracking-type-works)
5. [Revenue Calculation](#revenue-calculation)
6. [Database Schema](#database-schema)
7. [Metrics Glossary](#metrics-glossary)
8. [How to Test](#how-to-test)

---

## Overview

The Adshqip tracker records **5 types of events** when ads are displayed on publisher websites:

| # | Event Type | What It Means |
|---|-----------|---------------|
| 1 | **Impression** | The ad HTML was loaded (iframe rendered) |
| 2 | **View** | The ad was actually visible on screen for at least 1 second |
| 3 | **Click** | The user clicked on the ad |
| 4 | **Conversion** | The user completed the desired action (purchase, signup, etc.) |
| 5 | **AdBlock Detected** | An ad blocker was detected hiding/blocking the ad |

All events are stored in the `aq_stats_daily` table, grouped by **date**, **ad**, **campaign**, and **device type**.

---

## Tracking Flow Diagram

```
Publisher Website
┌─────────────────────────────────────────────────────┐
│                                                     │
│  <iframe src="/serve/ad/{id}">                      │
│  ┌───────────────────────────────────────────────┐  │
│  │                                               │  │
│  │   1. IMPRESSION (automatic)                   │  │
│  │      Server records impression when           │  │
│  │      the iframe HTML is served                │  │
│  │                                               │  │
│  │   2. VIEW (after 1 second visible)            │  │
│  │      JavaScript IntersectionObserver          │  │
│  │      detects ad is 50%+ visible on screen     │  │
│  │      → waits 1 second                         │  │
│  │      → fires beacon: GET /serve/ad/{id}/view  │  │
│  │                                               │  │
│  │   3. ADBLOCK DETECTION (after 2 seconds)      │  │
│  │      JavaScript checks if ad elements are     │  │
│  │      hidden or have zero dimensions           │  │
│  │      → fires beacon: GET /serve/ad/{id}/adblock│ │
│  │                                               │  │
│  │   4. CLICK (user clicks)                      │  │
│  │      User clicks the ad link                  │  │
│  │      → GET /serve/ad/{id}/click               │  │
│  │      → Server records click, redirects to     │  │
│  │        the advertiser's destination URL       │  │
│  │                                               │  │
│  └───────────────────────────────────────────────┘  │
│                                                     │
└─────────────────────────────────────────────────────┘

   5. CONVERSION (triggered separately)
      Advertiser places a conversion pixel on their
      "thank you" or confirmation page:
      <img src="/serve/ad/{id}/conversion" width="1" height="1">
      → Server records the conversion
```

---

## Endpoints

All tracking endpoints live under `/serve/ad/{id}/` and are **CSRF-exempt** (no authentication required, since they're called from external publisher sites).

### `GET /serve/ad/{id}` — Serve the Ad

- **What it does:** Returns a full HTML page containing the ad creative (image, popunder, etc.)
- **Tracks:** Records 1 **impression** automatically on every request
- **Includes:** Inline JavaScript for view tracking and adblock detection
- **Response:** HTML page with the ad + tracking scripts

### `GET /serve/ad/{id}/click` — Track Click

- **What it does:** Records a click event, then redirects the user to the ad's destination URL
- **Tracks:** Records 1 **click** (and checks if it's a unique click via session)
- **Response:** 302 redirect to the campaign's target URL

### `GET /serve/ad/{id}/view` — Track Viewable Impression

- **What it does:** Called automatically by the tracking JavaScript when the ad is visible
- **Tracks:** Records 1 **viewable impression** (`viewable_impressions` column)
- **Response:** 1x1 transparent GIF pixel (43 bytes)

### `GET /serve/ad/{id}/adblock` — Track AdBlock Detection

- **What it does:** Called automatically by the tracking JavaScript when an adblocker is detected
- **Tracks:** Records 1 **adblock detection** (`adblock_detected` column)
- **Response:** 1x1 transparent GIF pixel (43 bytes)

### `GET /serve/ad/{id}/conversion` — Track Conversion

- **What it does:** Called by a pixel placed on the advertiser's confirmation/thank-you page
- **Tracks:** Records 1 **conversion** (`conversions` column)
- **Response:** 1x1 transparent GIF pixel (43 bytes)

---

## How Each Tracking Type Works

### 1. Impressions

Impressions are tracked **server-side** inside the `serve()` method. Every time the iframe loads, the server calls `trackStat($ad, 'impression')` before returning the HTML.

- **Total impressions** — incremented on every request
- **Unique impressions** — only incremented once per user per ad per day (tracked via session: `aq_tracked_impression_{adId}_{date}`)

### 2. Views (Viewable Impressions)

Views use the browser's **IntersectionObserver API** to detect when the ad is actually visible to the user. This is injected as inline JavaScript inside the served ad HTML.

**How it works step-by-step:**

```
1. Ad iframe loads → JavaScript starts running
2. IntersectionObserver watches document.body with 50% visibility threshold
3. When 50%+ of the ad is visible on screen:
   → A 1-second timer starts
4. After 1 second of continuous visibility:
   → Fires a beacon: new Image().src = '/serve/ad/{id}/view?_=' + Date.now()
   → The beacon is fired ONLY ONCE (uses a `fired` flag)
5. Server receives the GET request → increments viewable_impressions
```

**The JavaScript (injected into served HTML):**

```javascript
(function(){
  var vFired = false;
  var obs = new IntersectionObserver(function(entries){
    if(entries[0].isIntersecting && !vFired){
      setTimeout(function(){
        if(!vFired){
          vFired = true;
          new Image().src = '/serve/ad/{id}/view?_=' + Date.now();
        }
      }, 1000);
    }
  }, {threshold: 0.5});
  obs.observe(document.body);
})();
```

**Why `?_=Date.now()`?** — Cache-busting parameter. Prevents the browser from caching the request.

### 3. Clicks

Clicks are tracked when the user clicks the ad link, which points to `/serve/ad/{id}/click`.

```
1. User clicks ad → browser navigates to /serve/ad/{id}/click
2. Server calls trackStat($ad, 'click')
3. Server redirects (302) to the ad's destination URL
4. User lands on the advertiser's website
```

- **Total clicks** — incremented on every click
- **Unique clicks** — only incremented once per user per ad per day (session-based)

### 4. AdBlock Detection

AdBlock detection runs 2 seconds after the ad loads. It checks if the ad's DOM elements have been hidden or removed by an ad blocker.

**How it works:**

```
1. Ad iframe loads → 2-second timer starts
2. After 2 seconds, JavaScript checks:
   - Can it find any element matching '#aq-ad, img, a'?
   - Does the element have height > 0?
   - Is the element's display NOT 'none'?
3. If any check fails (element missing, hidden, or zero-height):
   → Fires beacon: new Image().src = '/serve/ad/{id}/adblock?_=' + Date.now()
4. Server receives the GET request → increments adblock_detected
```

**The JavaScript:**

```javascript
setTimeout(function(){
  var el = document.querySelector('#aq-ad, img, a');
  if(!el || el.offsetHeight === 0 || getComputedStyle(el).display === 'none'){
    new Image().src = '/serve/ad/{id}/adblock?_=' + Date.now();
  }
}, 2000);
```

### 5. Conversions

Conversions are tracked via a **pixel** that the advertiser places on their confirmation page (e.g., after a purchase or signup).

```html
<!-- Place this on your "Thank You" page -->
<img src="https://yourdomain.com/serve/ad/{id}/conversion" width="1" height="1" style="display:none">
```

When the page loads, the browser requests the pixel → server records the conversion.

---

## Revenue Calculation

Revenue is calculated automatically inside `trackStat()` based on the campaign's **pricing model** and **bid amount**.

| Pricing Model | When Revenue is Added | Formula |
|--------------|----------------------|---------|
| **CPM** (Cost Per Mille) | On each **impression** | `bid_amount / 1000` per impression |
| **CPC** (Cost Per Click) | On each **click** | `bid_amount` per click |
| **CPA** (Cost Per Action) | On each **conversion** | `bid_amount` per conversion |
| **CPV** (Cost Per View) | On each **view** | `bid_amount` per viewable impression |
| **CPV_CTW** (Cost Per View — Click to Watch) | On each **view** | `bid_amount` per viewable impression |

### Example

A campaign with **CPC** pricing and a **$0.50 bid amount**:
- 1,000 impressions → $0.00 revenue (CPC doesn't charge for impressions)
- 50 clicks → **$25.00 revenue** (50 × $0.50)
- 3 conversions → $0.00 revenue (CPC doesn't charge for conversions)

A campaign with **CPM** pricing and a **$2.00 bid amount**:
- 1,000 impressions → **$2.00 revenue** (1,000 × $2.00/1,000)
- 50 clicks → $0.00 additional (CPM doesn't charge per click)

---

## Database Schema

### Table: `aq_stats_daily`

All tracking data is stored in a single table, aggregated per day.

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `date` | date | The day this row covers |
| `ad_id` | bigint | Foreign key → `aq_ads.id` |
| `campaign_id` | bigint | Foreign key → `aq_campaigns.id` |
| `zone_id` | bigint (nullable) | Publisher zone (if applicable) |
| `site_id` | bigint (nullable) | Publisher site (if applicable) |
| `country_code` | string (nullable) | 2-letter country code |
| `device_type` | string | `desktop`, `mobile`, or `tablet` |
| `impressions` | int | Total times the ad was served |
| `unique_impressions` | int | Unique users who saw the ad (per day) |
| `clicks` | int | Total clicks |
| `unique_clicks` | int | Unique users who clicked (per day) |
| `conversions` | int | Total conversions |
| `viewable_impressions` | int | Times the ad was actually visible 1+ second |
| `adblock_detected` | int | Times an adblocker was detected |
| `revenue` | decimal(10,4) | Total advertiser spend |
| `publisher_earnings` | decimal(10,4) | Publisher's share |
| `ecpm` | decimal(10,4) | Effective CPM (auto-calculated) |
| `ctr` | decimal(8,4) | Click-through rate % (auto-calculated) |
| `fill_rate` | decimal(8,4) | Fill rate % |

### How Rows Are Created

Each combination of `date + ad_id + device_type` gets its own row. If a row doesn't exist yet for today's date and ad, `trackStat()` creates one with all counters at 0, then increments the relevant counter.

### Device Detection

Device type is detected from the `User-Agent` header:
- Contains `mobile` or `android` (but not `tablet`/`ipad`) → **mobile**
- Contains `tablet` or `ipad` → **tablet**
- Everything else → **desktop**

---

## Metrics Glossary

| Metric | Formula | Description |
|--------|---------|-------------|
| **CTR** (Click-Through Rate) | `(clicks / impressions) × 100` | Percentage of impressions that resulted in clicks |
| **eCPM** (Effective CPM) | `(revenue / impressions) × 1000` | How much revenue per 1,000 impressions |
| **eCPC** (Effective CPC) | `revenue / clicks` | Average cost per click |
| **eCPA** (Effective CPA) | `revenue / conversions` | Average cost per conversion |
| **Conv. Rate** | `(conversions / clicks) × 100` | Percentage of clicks that became conversions |
| **Viewability Rate** | `(viewable_impressions / impressions) × 100` | Percentage of served ads that were actually seen |

---

## How to Test

### Testing Impressions
Simply load the ad in your browser:
```
http://127.0.0.1:8000/serve/ad/{id}
```
Each page load = 1 impression. Refresh the Campaigns page to see the count increase.

### Testing Views
1. Open the ad URL: `http://127.0.0.1:8000/serve/ad/{id}`
2. Keep the page visible for at least 1 second
3. The tracking JavaScript fires automatically
4. Check Campaigns index → the **Views** column should increment

### Testing Clicks
1. Open the ad URL: `http://127.0.0.1:8000/serve/ad/{id}`
2. Click on the ad image/link
3. You'll be redirected to the destination URL
4. Check Campaigns index → the **Clicks** column should increment

### Testing Conversions
**Option A — Direct URL:**
Visit the conversion endpoint directly in your browser:
```
http://127.0.0.1:8000/serve/ad/{id}/conversion
```
You'll see a tiny image (1x1 pixel). The conversion is recorded.

**Option B — Browser Console:**
Open any page, press F12 → Console tab, and run:
```javascript
new Image().src = 'http://127.0.0.1:8000/serve/ad/26/conversion?_=' + Date.now();
```

### Testing AdBlock Detection
**Option A — Install an ad blocker:**
1. Install uBlock Origin (or any ad blocker) on Chrome
2. Load `http://127.0.0.1:8000/serve/ad/{id}`
3. If the blocker hides the ad elements, the adblock beacon fires automatically
4. Check Campaigns index → the **AdBlock** column should increment

**Option B — Browser Console (manual test):**
```javascript
new Image().src = 'http://127.0.0.1:8000/serve/ad/26/adblock?_=' + Date.now();
```

### Verifying in the Database
You can check the raw stats directly:
```sql
SELECT * FROM aq_stats_daily WHERE ad_id = 26 ORDER BY date DESC;
```

---

## Key Files

| File | Purpose |
|------|---------|
| `app/Http/Controllers/Admin/AdCreativeController.php` | All tracking logic: `serve()`, `click()`, `view()`, `adblock()`, `conversion()`, `trackStat()`, `trackingScript()` |
| `app/Models/StatDaily.php` | Eloquent model for `aq_stats_daily` table |
| `app/Models/Campaign.php` | Campaign model (has `campaign_type` and `bid_amount`) |
| `routes/web.php` | Route definitions for all 5 tracking endpoints |
| `resources/views/admin/campaigns/index.blade.php` | Displays all campaign stats (Views, AdBlock, Impressions, Clicks, etc.) |
| `resources/views/admin/adformats/reports.blade.php` | Detailed reports with date/device/country filters |

---

## Beacon Response

The `/view`, `/adblock`, and `/conversion` endpoints all return the same response — a **1x1 transparent GIF** with anti-caching headers:

```
HTTP 200 OK
Content-Type: image/gif
Cache-Control: no-store, no-cache, must-revalidate
Pragma: no-cache
Access-Control-Allow-Origin: *

[43 bytes — smallest valid GIF]
```

The `Access-Control-Allow-Origin: *` header allows the beacon to work from any publisher domain (cross-origin requests).

---

*Last updated: March 2026*
