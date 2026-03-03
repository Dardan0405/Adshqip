# Adshqip — Full Platform Documentation

> **Albanian Ad Network — Modern Advertising Platform**
> Version: 1.0 | Database: 99 tables, 4 views, 5 stored procedures | ~430 API endpoints

---

# TABLE OF CONTENTS

1. [Project Overview](#1-project-overview)
2. [System Architecture](#2-system-architecture)
3. [Technology Stack](#3-technology-stack)
4. [User Roles & Permissions](#4-user-roles--permissions)
5. [Database Schema Reference](#5-database-schema-reference)
6. [API Routes Reference](#6-api-routes-reference)
7. [Business Logic & Workflows](#7-business-logic--workflows)
8. [Ad Formats](#8-ad-formats)
9. [Campaign System](#9-campaign-system)
10. [Ad Serving Pipeline](#10-ad-serving-pipeline)
11. [Feature Modules](#11-feature-modules)
12. [Stored Procedures & Views](#12-stored-procedures--views)
13. [Enum Reference](#13-enum-reference)
14. [Security & Compliance](#14-security--compliance)
15. [Third-Party Integrations](#15-third-party-integrations)
16. [Glossary](#16-glossary)

---

# 1. PROJECT OVERVIEW

## 1.1 What is Adshqip?

Adshqip is a **full-stack advertising network platform** for the Albanian and Balkan digital market. It connects **advertisers** (promote products) with **publishers** (monetize websites/apps).

## 1.2 Key Capabilities

- **16 Ad Formats**: Popunder, Native, Interstitial, Push, Video, Rich Media, Carousel, Clip, CTW, 3× adshqipAI
- **Dual Campaign System**: RTB/Programmatic + Self-Serve Direct with A/B testing
- **Dynamic Creative Optimization**: Token replacement ({city}, {device}, {weather}), rule engine, product feeds
- **Custom Audiences**: Pixel tracking, customer lists, lookalikes, rule-based building
- **MSN Distribution**: "Run on MSN exclusively" — Microsoft properties (MSN.com, Outlook, Edge, Bing)
- **OEM Advertising**: Samsung, Xiaomi, Huawei, Oppo, Vivo — setup wizard, lockscreen, app stores
- **Clip Ads**: TikTok-style vertical video (9:16, 5-60s) with swipe-up, polls, shoppable pins
- **Click-to-Watch Video**: User-initiated, branding overlays, end cards, CPV billing
- **adshqipAI**: AI-powered ad creation from text prompts
- **RTB**: OpenRTB integration with DSP/SSP exchanges
- **Fraud Detection**: Multi-layer anti-fraud system
- **KYC**: Tiered verification (basic/standard/enhanced) with AML/sanctions
- **Referral Program**: Commission-based with multi-link tracking
- **Telegram Mini Apps**: In-Telegram monetization and campaign management
- **Multi-Language**: EN, SQ, IT, DE | **Balkan Geo-Targeting**: AL, XK, MK, ME, RS, BA, HR, SI, BG, RO, GR

## 1.3 Legacy Migration

Modernized rewrite of DJAX/OpenX ad server. Key mappings:

| Legacy | Adshqip |
|--------|---------|
| `ox_clients` / `djax_billing_information` | `aq_users`, `aq_user_profiles` |
| `ox_banners` / `djax_additional_banners` | `aq_ads`, `aq_ad_creatives` |
| `ox_campaigns` | `aq_campaigns` |
| `ox_zones` | `aq_zones`, `aq_zone_ad_assoc` |
| `djax_3rd_party_ad_exchange` | `aq_ad_exchanges` |
| AdGate Self-Serve | `aq_direct_campaigns` (5 tables) |

---

# 2. SYSTEM ARCHITECTURE

```
┌──────────────────────────────────────────────────────────┐
│  Landing Page │ Dashboard SPA │ Mobile App │ Telegram    │
└──────────┬───────────────┬──────────┬──────────┬─────────┘
           ▼               ▼          ▼          ▼
┌──────────────────────────────────────────────────────────┐
│  API Gateway: Auth │ Rate Limit │ CORS │ JWT │ Roles    │
└──────────┬───────────────┬──────────┬──────────┬─────────┘
           ▼               ▼          ▼          ▼
   48 API Routers    Ad Serving    RTB Bidder  Tracking
   (~430 endpoints)  Engine                    Pixel/Events
           ▼               ▼          ▼          ▼
┌──────────────────────────────────────────────────────────┐
│  MySQL / InnoDB — 99 Tables │ 4 Views │ 5 Procedures    │
│  utf8mb4_unicode_ci │ JSON columns │ Foreign Keys        │
└──────────────────────────────────────────────────────────┘
           ▼               ▼          ▼          ▼
   Stripe/PayPal    GeoIP/MaxMind  Weather API  CDN
   Coinbase
```

**Design Principles:**
- `aq_` prefix on all tables
- InnoDB for transactions + FK support
- `utf8mb4` for full Unicode + emoji
- Soft deletes (`is_deleted`) on major entities
- `created_at` + `updated_at` on all tables
- JSON columns for flexible targeting/settings
- Foreign keys with CASCADE/SET NULL
- Strategic denormalized counters

---

# 3. TECHNOLOGY STACK

| Layer | Technology |
|-------|-----------|
| **Database** | MySQL 8+, InnoDB, utf8mb4_unicode_ci |
| **Backend** | Node.js / TypeScript, 48 routers |
| **Auth** | JWT + Sessions, TOTP 2FA |
| **API** | RESTful JSON, `/api/v1` |
| **RTB** | OpenRTB 2.5/2.6 |
| **Payments** | Stripe, PayPal, Coinbase |
| **GeoIP** | MaxMind GeoIP2 |
| **Ad Formats** | VAST, Native JSON, HTML5, Rich Media |
| **AI** | adshqipAI (prompt → creative) |
| **Push** | FCM (Android) / APNs (iOS) |
| **Telegram** | Bot API + Mini Apps (TWA) |
| **Languages** | EN, SQ, IT, DE |
| **Currency** | EUR (default) |

---

# 4. USER ROLES & PERMISSIONS

## 4.1 Roles

| Role | Description |
|------|-------------|
| **admin** | Full platform access: approve/reject, manage payouts, fraud, KYC, settings |
| **advertiser** | Create campaigns, upload creatives, manage budgets, audiences, DCO |
| **publisher** | Register sites, create zones, view earnings, request payouts |
| **manager** | Support tickets, limited admin for client management |

## 4.2 Permission Matrix

| Resource | Admin | Advertiser | Publisher | Manager |
|----------|-------|-----------|-----------|---------|
| Users | CRUD all | Self only | Self only | Assigned |
| Campaigns | Approve all | CRUD own | — | View |
| Ads | Approve all | CRUD own | — | — |
| Sites/Zones | Approve all | — | CRUD own | — |
| Analytics | Platform-wide | Own campaigns | Own sites | — |
| Payouts | Process all | View own | Request own | — |
| Wallet | Adjustments | Add funds | View | — |
| KYC | Review all | Submit own | Submit own | — |
| Fraud | Full CRUD | — | — | — |
| Audiences | — | CRUD own | — | — |
| Settings | Full CRUD | — | — | — |

## 4.3 Auth Flow

```
Register → Verify Email → Login (JWT) → [2FA if enabled] → Access API
```

Session stored in `aq_sessions` with token, IP, UA, browser/OS/device, expiry.

---

# 5. DATABASE SCHEMA REFERENCE

**99 tables** across **15 domains:**

## 5.1 Users & Auth (8 tables)

| Table | Purpose |
|-------|---------|
| `aq_users` | Core accounts: email, role, status, 2FA, KYC status, Telegram link, referral code |
| `aq_user_profiles` | Extended: name, company, address, balance, currency, payment details |
| `aq_sessions` | Active login sessions: token, IP, UA, browser, device, expiry |
| `aq_two_factor_backup_codes` | 2FA recovery codes (bcrypt hashed, one-time use) |
| `aq_two_factor_challenges` | 2FA attempt log with lockout tracking |
| `aq_kyc_verifications` | KYC submissions: level, personal/business info, risk scoring, AML checks |
| `aq_kyc_documents` | Document uploads: ID, selfie, proof of address (encrypted storage) |
| `aq_account_deactivations` | Status change audit: deactivation, suspension, reactivation, GDPR erasure |

## 5.2 Ad Formats & Tags (4 tables)

| Table | Purpose |
|-------|---------|
| `aq_ad_formats` | 16 formats: popunder, native, interstitial, push, video, rich_media, motion, carousel, app_promotion, clip, ctw, 3× adshqipAI |
| `aq_ad_sizes` | IAB dimensions (width × height, responsive flag) |
| `aq_tags` | MultiTag system: 10 system tags (High CPM, Mobile First, GDPR Safe, etc.) |
| `aq_ad_format_tags` | Many-to-many: formats ↔ tags |

## 5.3 Campaigns & Ads (10 tables)

| Table | Purpose |
|-------|---------|
| `aq_campaigns` | RTB campaigns: pricing (CPM/CPC/CPA/CPV), targeting, budget, MSN, DCO, audience, OEM |
| `aq_ads` | Ads: 15 types, DKI, branding, CTW, end cards, clip settings, adshqipAI fields |
| `aq_ad_creatives` | Creative files (image/video/HTML5/GIF) |
| `aq_direct_campaigns` | Self-serve: adds A/B testing, optimization tools, traffic estimator, bulk create |
| `aq_direct_campaign_creatives` | A/B variants with winner tracking |
| `aq_direct_campaign_targeting` | 19 targeting types with include/exclude |
| `aq_direct_campaign_zones` | Zone placement with floor price override |
| `aq_direct_campaign_stats` | Daily stats by date/campaign/creative/zone/country/device |
| `aq_campaign_optimization` | Optimization log: In-Line, SpendGuard, Perf Stimulator, Pacing Health |
| `aq_campaign_category` / `aq_site_categories` | Category associations |

## 5.4 Publisher (5 tables)

| Table | Purpose |
|-------|---------|
| `aq_sites` | Publisher websites: domain, language, pageviews, approval status |
| `aq_zones` | Ad slots: format, size, placement, floor price, embed code |
| `aq_zone_ad_assoc` | Admin-managed zone ↔ ad assignments |
| `aq_categories` | Hierarchical IAB content categories |
| `aq_site_categories` | Site ↔ category associations |

## 5.5 Tracking (6 tables)

| Table | Purpose |
|-------|---------|
| `aq_impressions` | Every impression: full visitor context, cost, viewability |
| `aq_clicks` | Every click: impression linkage, uniqueness, cost |
| `aq_conversions` | Post-click: sale/lead/signup/install, revenue, payout |
| `aq_vast_events` | Registry of VAST + CTW + Clip trackable events |
| `aq_video_tracking` | Video event log: quartiles, completion, skip |
| `aq_clip_interactions` | Clip engagement: 18 interaction types, watch metrics |

## 5.6 Statistics (3 tables)

| Table | Purpose |
|-------|---------|
| `aq_stats_daily` | Primary analytics: multi-dimensional daily aggregates |
| `aq_stats_browser` | Browser-level aggregates |
| `aq_stats_geo` | Geographic aggregates |

## 5.7 Fraud (4 tables)

| Table | Purpose |
|-------|---------|
| `aq_fraud_events` | Detected fraud: type, reason, severity, blocked flag |
| `aq_antifraud_rules` | Configurable rules: caps, blacklists, geo blocks |
| `aq_publisher_fraud_records` | Publisher fraud history + actions |
| `aq_fraud_notifications` | Fraud alert delivery |

## 5.8 RTB (3 tables)

| Table | Purpose |
|-------|---------|
| `aq_ad_exchanges` | Exchange integrations: DSP/SSP, OpenRTB, auth, auction type |
| `aq_rtb_bid_requests` | Outgoing bid requests |
| `aq_rtb_bid_responses` | Incoming responses with win tracking |

## 5.9 Billing & Wallet (6 tables)

| Table | Purpose |
|-------|---------|
| `aq_payouts` | Publisher payout requests |
| `aq_invoices` | Charges & payout invoices with PDF |
| `aq_pricing_plans` | Starter (free), Growth (€49/mo), Enterprise (custom) |
| `aq_user_subscriptions` | Plan subscriptions |
| `aq_saved_payment_methods` | Tokenized cards (Stripe/PayPal/Coinbase) |
| `aq_transactions` | Full ledger: deposit, withdrawal, ad_spend, refund, adjustment, bonus, referral |

## 5.10 Referral (3 tables)

| Table | Purpose |
|-------|---------|
| `aq_referral_links` | Referral links: codes, commission structure, UTM tracking |
| `aq_referral_conversions` | Each referral signup with attribution + fraud flags |
| `aq_referral_payouts` | Commission disbursements |

## 5.11 Distribution & MSN (5 tables)

| Table | Purpose |
|-------|---------|
| `aq_distribution_networks` | Network registry: Adshqip Native, MSN, Bing, Partners |
| `aq_campaign_network_assoc` | Campaign ↔ network with exclusive flag, bid adjustment |
| `aq_msn_properties` | 12 MSN placements (Homepage, News, Finance, Edge, Bing, etc.) |
| `aq_msn_campaign_settings` | MSN-specific: property targeting, LinkedIn targeting, brand safety |
| `aq_msn_performance_stats` | Daily MSN metrics per property |

## 5.12 DCO & Dynamics (9 tables)

| Table | Purpose |
|-------|---------|
| `aq_dynamic_content_tokens` | 14 system tokens ({city}, {device}, {weather}, {countdown}, etc.) |
| `aq_dynamic_creative_assets` | Asset library: headlines, images, CTAs with performance tracking |
| `aq_dynamic_creative_rules` | Rule engine: conditions → asset selections |
| `aq_dynamic_product_feeds` | Catalog feeds (ecommerce, travel, auto, etc.) |
| `aq_dynamic_product_feed_items` | Individual products in feeds |
| `aq_dynamic_budget_rules` | Automated bid/budget rules on performance triggers |
| `aq_dynamic_landing_pages` | Per-segment URL rules |
| `aq_dynamic_countdown_timers` | Urgency countdown elements |
| `aq_dynamic_rule_log` | Audit trail for all rule firings |

## 5.13 Custom Audiences (8 tables)

| Table | Purpose |
|-------|---------|
| `aq_audience_pixels` | Tracking pixels: JS/image/S2S, domain auth, event config |
| `aq_custom_audiences` | Audience definitions: 7 types, membership duration, GDPR |
| `aq_audience_members` | Individual members (hashed identifiers, consent tracking) |
| `aq_audience_rules` | Rule-based building: event matching, URL patterns, conditions |
| `aq_audience_syncs` | Upload/sync history (CSV, CRM, API) |
| `aq_campaign_audience_assoc` | Campaign ↔ audience (include/exclude, bid adjustment) |
| `aq_audience_lookalikes` | Lookalike config: expansion ratio, model type, quality score |
| `aq_pixel_events` | Raw pixel fire log (high-volume) |

## 5.14 Clip & Video (8 tables)

| Table | Purpose |
|-------|---------|
| `aq_video_end_cards` | Reusable end card templates (image, HTML, CTA, product feed) |
| `aq_ad_end_card_assoc` | Ad ↔ end card (many-to-many with A/B weight) |
| `aq_video_branding_overlays` | Brand presets: logo watermark, intro/outro bumpers |
| `aq_ad_branding_overlay_assoc` | Ad ↔ overlay linking |
| `aq_clip_campaigns` | Campaign-level clip settings: duration, sound, CTA, polls, shoppable |
| `aq_clip_templates` | 10 pre-built templates (product showcase, testimonial, unboxing, etc.) |
| `aq_clip_music_library` | Licensed audio: 16 genres, 9 moods, BPM, licensing info |
| `aq_clip_interactions` | Engagement tracking (see 5.5) |

## 5.15 OEM (6 tables)

| Table | Purpose |
|-------|---------|
| `aq_oem_manufacturers` | 11 partners: Samsung, Xiaomi, Huawei, Oppo, Vivo, etc. |
| `aq_oem_placements` | 15 placement types: setup wizard, app store, lockscreen, etc. |
| `aq_oem_apps` | Pre-installed apps with ad inventory |
| `aq_oem_device_models` | Device catalog: series, tier, specs, ad capabilities |
| `aq_campaign_oem_assoc` | Campaign ↔ OEM with targeting, creative overrides, approval |
| `aq_oem_performance_stats` | Daily metrics: installs, retention D1/D7/D30, fraud rate |

## 5.16 Platform (14+ tables)

| Table | Purpose |
|-------|---------|
| `aq_platform_settings` | Key-value config (14 seeded settings) |
| `aq_activity_log` | User activity audit trail |
| `aq_log_settings` | Logging configuration |
| `aq_notifications` | In-app notifications (7 types) |
| `aq_notification_settings` | Per-user preferences |
| `aq_support_tickets` | Support system (6 categories, 4 priorities) |
| `aq_support_messages` | Ticket messages + internal notes |
| `aq_newsletters` | Newsletter subscriptions |
| `aq_faq` | Multi-language FAQ |
| `aq_testimonials` / `aq_case_studies` | Social proof |
| `aq_trusted_publishers` | Landing page marquee |
| `aq_cookie_consents` | GDPR consent tracking |
| `aq_languages` | EN, SQ, IT, DE |
| `aq_api_keys` | Developer keys with permissions + rate limits |
| `aq_mobile_devices` | FCM/APNs push tokens |
| `aq_careers` | Job postings |
| `aq_telegram_mini_apps` | Telegram bot mini apps |
| `aq_telegram_mini_app_sessions` | Mini app sessions |
| `aq_telegram_mini_app_events` | In-app events |

---

# 6. API ROUTES REFERENCE

**48 routers, ~430 endpoints** — full listing in `API_ENDPOINTS_PLAN.md`.

## 6.1 Summary

| Category | Routers | Endpoints | Auth |
|----------|---------|-----------|------|
| Auth & 2FA | 1 | 18 | Public/User |
| Users & KYC | 3 | 34 | User |
| Campaigns (RTB) | 1 | 51 | Advertiser |
| Ads & Creatives | 1 | 16 | Advertiser |
| Direct Campaigns | 1 | 28 | Advertiser |
| Sites & Zones | 2 | 14 | Publisher |
| Video/Clip/Dynamic/Audiences/OEM | 5 | 60 | Advertiser |
| Analytics | 1 | 13 | User |
| Billing (Wallet/Payouts/Invoices) | 3 | 14 | User |
| Referrals/Notifications/Support | 3 | 21 | User |
| Public Content/Pricing/API Keys | 4 | 43 | Public/User |
| Tracking/Pixels/Serving/Webhooks/RTB | 6 | 16 | Mixed |
| Telegram | 1 | 9 | User |
| **Admin (18 routers)** | 18 | 119 | Admin |
| **TOTAL** | **48** | **~430** | |

## 6.2 Standard Response

```json
{
  "success": true,
  "data": { ... },
  "meta": { "page": 1, "per_page": 25, "total": 150 },
  "errors": null
}
```

## 6.3 HTTP Codes

200 OK, 201 Created, 204 Deleted, 400 Validation, 401 Unauthed, 403 Forbidden, 404 Not Found, 409 Conflict, 429 Rate Limited, 500 Server Error

---

# 7. BUSINESS LOGIC & WORKFLOWS

## 7.1 Campaign Lifecycle

```
DRAFT → (submit) → PENDING_REVIEW → (approve) → ACTIVE ⇄ PAUSED → COMPLETED
                                   → (reject) → REJECTED
```

## 7.2 Ad Serving Flow

1. Publisher page loads → `GET /serve/zone/:zoneId`
2. Resolve visitor: GeoIP → country/region, UA → device/browser/OS
3. Call `aq_get_eligible_ads`: filter by status, approval, budget, dates, balance
4. Apply targeting: geo, device, browser, OS, language, schedule, frequency cap
5. Apply audience targeting (include/exclude)
6. Apply DCO rules: resolve tokens, select assets
7. Check distribution network (MSN exclusive)
8. Select winner (auction/priority), record impression, return ad payload

## 7.3 Add Funds (Atomic via Stored Procedure)

1. `POST /wallet/add-funds` → charge via Stripe/PayPal/Coinbase
2. Gateway webhook confirms → calls `aq_add_funds` procedure
3. Procedure: validate > START TRANSACTION > lock row > update balance > insert transaction > COMMIT
4. Returns new balance + transaction ID

## 7.4 Publisher Payout

1. `POST /payouts/request` → validate: balance ≥ €50, KYC approved, no pending
2. Admin reviews → process payment → deduct balance → log transaction → generate invoice

## 7.5 KYC Verification

1. Submit KYC → upload documents → status: pending → in_review
2. Admin verifies documents, runs risk scoring (AML/sanctions)
3. Approve (set level) or reject (with reason, count++)

## 7.6 Referral Flow

1. Create link → referred user clicks → registers (attributed)
2. Reaches qualification threshold → commission tracking begins
3. Referrer requests payout → admin processes

## 7.7 Audience Building

1. Create pixel → place JS tag → pixel fires events
2. Create audience + rules → rule engine adds matching visitors
3. Link audience to campaign (include/exclude) → ad server filters

## 7.8 A/B Testing (Direct)

1. Enable A/B → add variants (A, B, C…) → traffic splits
2. Each variant accumulates metrics → auto-pick winner → shift traffic

---

# 8. AD FORMATS

| # | Format | Category | eCPM | Key Feature |
|---|--------|----------|------|-------------|
| 1 | Popunder | high_impact | €8.50 | 95%+ fill rate |
| 2 | Native Feed | user_friendly | €5.20 | 2.80% CTR, AMP |
| 3 | Interstitial | high_impact | €12.30 | Fullscreen modal |
| 4 | In-Page Push | user_friendly | €3.80 | Notification-style |
| 5 | Text & Smart Banners | user_friendly | — | AMP, tiny payloads |
| 6 | Native Video | premium | — | Muted autoplay |
| 7 | Rich Media | premium | — | 3D, mini-games |
| 8 | Motion Ads | premium | €6.50 | Animated display |
| 9 | Motion Studio | premium | €7.20 | Timeline editor |
| 10 | Carousel | user_friendly | €5.80 | Swipeable cards |
| 11 | App Promotion | high_impact | €9.00 | Deep links, SKAN |
| 12 | adshqipAI Ad Maker | premium | €10.00 | AI static creative |
| 13 | adshqipAI Motion | premium | €11.50 | AI animated |
| 14 | adshqipAI Motion+Prompt | premium | €13.00 | AI from text prompt |
| 15 | Click-to-Watch | premium | €14.00 | CPV, end cards |
| 16 | Clip Ads | high_impact | €15.00 | TikTok-style vertical |

---

# 9. CAMPAIGN SYSTEM

## 9.1 RTB Campaigns

- **Pricing**: CPM, CPC, CPA, CPV, CPV-CTW
- **10 Objectives**: brand_awareness → store_visits
- **Targeting**: geo, device, browser, OS, language, schedule, retargeting, audiences, MSN, OEM
- **Budget**: daily + total + remaining, frequency caps

## 9.2 Direct Campaigns (Self-Serve)

Everything in RTB plus:
- **Flat-rate** pricing, **accelerated** delivery
- **A/B Testing**: auto-optimization by CTR/conversions/eCPM/viewability
- **4 Optimization Tools**: In-Line (real-time bid), SpendGuard (overspend protection), Performance Stimulator (bid boost), Pacing Health (budget pacing score 0-100)
- **Traffic Estimator**: estimated impressions/clicks/reach
- **Bulk Create**: parent_campaign_id grouping

---

# 10. AD SERVING PIPELINE

**Stored Procedure: `aq_get_eligible_ads`**

```sql
-- Inputs: zone_id, country_code, device_type, blocked_domains
-- Filters:
--   ad: active, not deleted, admin approved
--   campaign: active, approved, budget > 0, within dates
--   advertiser: active, not deleted/denied, balance > 0
-- Sort: bid_amount DESC, weight DESC
-- Limit: 10 candidates
```

After procedure returns candidates, the application layer applies:
- Targeting filters (geo/device/browser/OS/language/schedule)
- Frequency capping
- Audience membership checks
- DCO rule evaluation + token resolution
- Network distribution checks
- Final auction/selection

---

# 11. FEATURE MODULES

## 11.1 Dynamic Creative Optimization (DCO)

**14 Dynamic Tokens**: {city}, {region}, {country}, {country_code}, {device}, {browser}, {os}, {day_of_week}, {time_of_day}, {current_month}, {current_year}, {weather}, {temperature}, {countdown}, {keyword}

**Rule Engine**: 12 condition types (geo, device, browser, OS, language, day, time, weather, audience, custom) → select headline/image/CTA assets + override URLs/bids

**Product Feeds**: ecommerce, travel, auto, real_estate, jobs, events — auto-refresh from CSV/XML/JSON/Google Merchant/Facebook Catalog

**Budget Rules**: trigger on CTR/CVR/CPC/CPM/CPA/ROAS/spend% → bid/budget increase/decrease, pause/resume, alert

**Countdown Timers**: live countdowns in ads with Albanian locale support ("Oferta mbaron në 2d 14h 30m")

## 11.2 Custom Audiences

**Pixel Types**: JavaScript, Image, Server-to-Server
**Audience Types**: website_visitors, customer_list, app_activity, engagement, conversion, lookalike, combined
**Member Identifiers**: cookie_id, device_id, hashed_email, hashed_phone, IDFA, GAID, ip_hash
**Rule Logic**: OR within groups, AND between groups
**GDPR**: consent tracking per member, configurable data retention, SHA-256 hashing
**Lookalikes**: expansion ratio 1-10, behavioral/demographic/interest/combined models

## 11.3 MSN Distribution

**"Run on MSN exclusively"**: `msn_exclusive=1` restricts delivery to Microsoft-owned properties only.

**12 MSN Properties**: Homepage (500M imp/day), News, Finance, Sports, Entertainment, Lifestyle, Weather, Outlook.com, Edge Start, Edge New Tab, Bing Sidebar, MSN Mobile App

**MSN Settings**: property targeting, content categories, LinkedIn profile targeting, brand safety (standard/strict/custom), viewability threshold, above-the-fold

## 11.4 OEM Advertising

**11 Partners**: Samsung, Xiaomi, Huawei, Oppo, Vivo, Realme, OnePlus, Transsion, Motorola, Nokia/HMD, Google, Honor

**15 Placement Types**: setup_wizard, app_store, lockscreen, notification_tray, smart_folder, browser_default, preinstall, game_center, theme_store, and more

**Targeting**: by manufacturer, placement, device tier (flagship/mid/budget), model, OS version, geo
**Metrics**: installs, retention D1/D7/D30, CPI, ROAS, fraud rate

## 11.5 Clip Campaigns

**Format**: 5-60 second vertical video (9:16), sound-on, autoplay, loop
**Features**: swipe-up CTA (pill/gradient/animated), interactive polls, shoppable product pins, sticker/text overlays, branded hashtag challenges
**Music Library**: 16 genres (pop, electronic, balkan, cinematic, etc.), 9 moods, BPM search, royalty-free licensing
**10 Templates**: Product Showcase, Testimonial, Unboxing, Tutorial, Flash Sale Countdown, Brand Story, Hashtag Challenge, Behind the Scenes, Announcement, Square Product Card

## 11.6 Click-to-Watch Video

**Model**: User clicks thumbnail → video plays → charged per completed view (CPV)
**CTW Settings**: min watch seconds, skip controls, autoplay option
**Branding**: logo overlay (position, opacity), intro/outro bumper videos
**End Cards**: static image, HTML, CTA button, product feed, custom — with A/B weight rotation
**Tracking**: ctw_click_to_play, ctw_view_counted, ctw_skipped, end_card_shown/clicked/dismissed

## 11.7 adshqipAI

**3 AI Formats**:
1. **Ad Maker** (€10 eCPM): AI generates static creatives from product description
2. **Motion Ads** (€11.50 eCPM): AI selects template + animates brand assets
3. **Motion + Prompt** (€13 eCPM): Full prompt-driven animated ad from scratch

**Fields**: prompt, style preset (cinematic/minimal/bold/playful), template, generated asset URL, generation ID, model version, is_edited flag

## 11.8 Telegram Mini Apps

**Registration**: bot username, token, app URL, webhook
**Sessions**: Telegram initData validation, user profile capture, session duration
**Events**: page_view, ad_impression, ad_click, purchase, custom — with revenue attribution
**Admin**: approve/reject/suspend workflow

## 11.9 Fraud Detection

**Fraud Reasons**: duplicate, bot, datacenter_ip, click_flood, impression_stacking, geo_mismatch
**Severity**: low, medium, high, critical
**Rules**: impression_cap, click_cap, ip_blacklist, ua_blacklist, geo_block, fingerprint
**Actions**: block, flag, throttle
**Publisher Records**: warning → suspended → banned

---

# 12. STORED PROCEDURES & VIEWS

## 12.1 Stored Procedures (5)

| Procedure | Purpose | Key Parameters |
|-----------|---------|----------------|
| `aq_get_eligible_ads` | Fetch ads for zone serving | zone_id, country_code, device_type, blocked_domains |
| `aq_search_countries` | Country search (GeoIP) | query string |
| `aq_search_regions` | Region search by country | country_code |
| `aq_get_earnings_summary` | Publisher earnings sparkline | user_id, days |
| `aq_add_funds` | Atomic balance deposit | user_id, amount, currency, payment_method, gateway |

## 12.2 Views (4)

| View | Purpose |
|------|---------|
| `aq_view_campaign_performance` | RTB campaign metrics (impressions, clicks, conversions, spend, CTR, eCPM) |
| `aq_view_publisher_earnings` | Publisher totals (earnings, impressions, sites, zones) |
| `aq_view_direct_campaign_performance` | Direct campaign metrics (same structure as RTB view) |
| `aq_view_referral_summary` | Referral dashboard (links, clicks, signups, commission) |

---

# 13. ENUM REFERENCE

## User Enums
- **role**: admin, advertiser, publisher, manager
- **status**: active, inactive, suspended, pending_verification, closed
- **theme**: light, dark, system
- **kyc_status**: not_started, pending, in_review, approved, rejected, expired
- **kyc_level**: none, basic, standard, enhanced

## Campaign Enums
- **campaign_type**: cpm, cpc, cpa, cpv, cpv_ctw
- **pricing_model** (direct): cpm, cpc, cpa, cpv, cpv_ctw, flat_rate
- **marketing_objective**: brand_awareness, reach, traffic, engagement, app_installs, video_views, lead_generation, conversions, catalog_sales, store_visits
- **status**: draft, pending_review, active, paused, completed, rejected, archived
- **delivery_mode**: standard, accelerated
- **distribution_mode**: all_networks, selected_networks, msn_exclusive
- **audience_targeting_mode**: none, include, exclude, both
- **oem_targeting_mode**: none, all_oems, selected_oems

## Ad Enums
- **ad_type**: image, html, video, text, rich_media, native, vast, motion, motion_studio, carousel, app_promotion, adshqipai_ad_maker, adshqipai_motion, adshqipai_motion_prompt, clip
- **adshqipai_type**: none, ad_maker, motion, motion_prompt
- **clip_aspect_ratio**: 9:16, 4:5, 1:1
- **end_card_type**: static_image, html, cta_button, product_feed, custom

## Targeting Enums
- **targeting_type** (direct): geo_country, geo_region, geo_city, device, browser, os, language, carrier, connection_type, domain_whitelist, domain_blacklist, category, keyword, mail_domain, audience_segment, ip_range, retargeting, distribution_network, oem
- **match_mode**: include, exclude

## Billing Enums
- **transaction_type**: deposit, withdrawal, ad_spend, refund, adjustment, welcome_bonus, referral_credit
- **payment_gateway**: stripe, paypal, coinbase, wire_transfer, manual
- **payout_method**: paypal, wire_transfer, crypto, payoneer

## Fraud Enums
- **fraud_reason**: duplicate, bot, datacenter_ip, click_flood, impression_stacking, geo_mismatch, other
- **severity**: low, medium, high, critical
- **rule_type**: impression_cap, click_cap, ip_blacklist, ua_blacklist, geo_block, fingerprint
- **action**: block, flag, throttle

---

# 14. SECURITY & COMPLIANCE

## 14.1 Authentication Security
- **Passwords**: bcrypt/argon2 hashed, never stored plain
- **JWT**: Short-lived access tokens + refresh tokens
- **2FA**: TOTP (Google Authenticator), backup codes (bcrypt hashed), lockout after failures
- **Sessions**: IP + UA fingerprinting, revocation support

## 14.2 Data Security
- **PII Hashing**: SHA-256 for audience member identifiers
- **KYC Documents**: Encrypted storage bucket, file hash integrity
- **Payment Methods**: Tokenized via Stripe/PayPal — never raw card data
- **API Keys**: Secret hashed, IP allowlists, rate limiting
- **Bot Tokens**: Encrypted storage (Telegram)
- **Credentials**: Exchange/OEM API credentials stored encrypted in JSON

## 14.3 GDPR Compliance
- **Cookie Consent**: `aq_cookie_consents` — accept_all/reject_non_essential/custom with per-category toggles
- **Data Retention**: Configurable per audience (`data_retention_days`)
- **Consent Tracking**: Per-audience-member consent flag + timestamp
- **Right to Erasure**: `aq_account_deactivations.data_deletion_requested` → `data_deleted_at`
- **PII Minimization**: Hashed identifiers, no raw PII in audience tables
- **GDPR-Ready Flag**: On all ad formats

## 14.4 Fraud Protection
- Multi-layer: impression/click caps, IP blacklists, UA blacklists, geo blocks, fingerprinting
- Publisher fraud records with escalation: warning → suspended → banned
- Real-time blocking with configurable thresholds and reset periods
- OEM fraud detection level per manufacturer

---

# 15. THIRD-PARTY INTEGRATIONS

| Integration | Purpose | Tables |
|------------|---------|--------|
| **Stripe** | Card payments, tokenization | `aq_saved_payment_methods`, `aq_transactions` |
| **PayPal** | Payments, payouts | Same as Stripe |
| **Coinbase** | Crypto payments | Same as Stripe |
| **MaxMind GeoIP2** | Country/region/city resolution | `aq_geo_countries`, `aq_geo_regions` |
| **OpenRTB** | Programmatic bidding | `aq_ad_exchanges`, `aq_rtb_bid_requests/responses` |
| **VAST** | Video ad serving standard | `aq_vast_events`, `aq_video_tracking` |
| **Telegram Bot API** | Mini apps, authentication | `aq_telegram_mini_apps/sessions/events` |
| **Google Merchant** | Product feed ingestion | `aq_dynamic_product_feeds` |
| **Facebook Catalog** | Product feed ingestion | `aq_dynamic_product_feeds` |
| **Microsoft Graph** | LinkedIn profile targeting | `aq_msn_campaign_settings` |
| **Weather API** | Dynamic {weather}/{temperature} tokens | `aq_dynamic_content_tokens` |
| **FCM/APNs** | Push notifications | `aq_mobile_devices` |
| **Samsung/Xiaomi/Huawei/etc.** | OEM ad placements | `aq_oem_*` tables |

---

# 16. GLOSSARY

| Term | Definition |
|------|-----------|
| **eCPM** | Effective Cost Per Mille (1,000 impressions) |
| **CTR** | Click-Through Rate (clicks / impressions × 100) |
| **CPM/CPC/CPA/CPV** | Cost Per Mille / Click / Action / View |
| **RTB** | Real-Time Bidding — programmatic auction |
| **VAST** | Video Ad Serving Template — IAB standard |
| **DCO** | Dynamic Creative Optimization |
| **DKI** | Dynamic Keyword Insertion |
| **CTW** | Click-to-Watch (user-initiated video) |
| **OEM** | Original Equipment Manufacturer |
| **KYC** | Know Your Customer verification |
| **AML** | Anti-Money Laundering |
| **GDPR** | General Data Protection Regulation |
| **IAB** | Interactive Advertising Bureau |
| **SKAN** | StoreKit Ad Network (Apple attribution) |
| **IDFA/GAID** | iOS/Google Advertising ID |
| **TWA** | Trusted Web Activity (Telegram Mini Apps) |
| **TOTP** | Time-based One-Time Password (2FA) |
| **MRC** | Media Rating Council (viewability standard) |

---

> **Related Files:**
> - `adshqip_schema.sql` — Full database schema (3,854 lines)
> - `API_ENDPOINTS_PLAN.md` — Complete endpoint plan + all 48 router files
