# Adshqip — Complete API Endpoints Plan

> Generated from `adshqip_schema.sql` (99 tables, 4 views, 5 stored procedures)
> Base URL: `/api/v1`

---

## 1. AUTH & SESSIONS (`aq_users`, `aq_sessions`)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/auth/register` | Register new user (advertiser/publisher) | Public |
| POST | `/auth/login` | Login, returns JWT + session | Public |
| POST | `/auth/logout` | Invalidate session/token | User |
| POST | `/auth/refresh` | Refresh access token | User |
| POST | `/auth/forgot-password` | Send password reset email | Public |
| POST | `/auth/reset-password` | Reset password with token | Public |
| POST | `/auth/verify-email` | Verify email with token | Public |
| POST | `/auth/resend-verification` | Resend email verification | Public |
| GET | `/auth/sessions` | List active sessions for current user | User |
| DELETE | `/auth/sessions/:id` | Revoke a specific session | User |
| DELETE | `/auth/sessions` | Revoke all sessions (logout everywhere) | User |

---

## 2. TWO-FACTOR AUTHENTICATION (`aq_two_factor_backup_codes`, `aq_two_factor_challenges`)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/auth/2fa/enable` | Generate TOTP secret + QR code | User |
| POST | `/auth/2fa/confirm` | Confirm TOTP setup with first code | User |
| POST | `/auth/2fa/disable` | Disable 2FA (requires current code) | User |
| POST | `/auth/2fa/verify` | Verify 2FA code during login | User |
| GET | `/auth/2fa/backup-codes` | Generate & return backup codes | User |
| POST | `/auth/2fa/backup-codes/regenerate` | Regenerate backup codes | User |
| POST | `/auth/2fa/verify-backup` | Verify login with backup code | User |

---

## 3. USER PROFILES (`aq_user_profiles`)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/users/me` | Get current user + profile | User |
| PATCH | `/users/me` | Update current user settings (language, theme, timezone) | User |
| GET | `/users/me/profile` | Get full profile (billing, address, etc.) | User |
| PATCH | `/users/me/profile` | Update profile details | User |
| PATCH | `/users/me/avatar` | Upload/update avatar | User |
| PATCH | `/users/me/password` | Change password | User |
| GET | `/users/me/balance` | Get current balance & currency | User |

### Admin User Management

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/admin/users` | List all users (paginated, filterable by role/status) | Admin |
| GET | `/admin/users/:id` | Get user detail | Admin |
| PATCH | `/admin/users/:id` | Update user role/status | Admin |
| PATCH | `/admin/users/:id/status` | Activate/suspend/close account | Admin |
| DELETE | `/admin/users/:id` | Soft-delete user | Admin |

---

## 4. ACCOUNT DEACTIVATION (`aq_account_deactivations`)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/users/me/deactivate` | Self-deactivate account (with optional feedback) | User |
| POST | `/users/me/reactivate` | Reactivate via reactivation token | Public |
| POST | `/users/me/close` | Request permanent closure + GDPR erasure | User |
| GET | `/admin/account-deactivations` | List all deactivation/reactivation events | Admin |
| POST | `/admin/users/:id/suspend` | Admin suspend account | Admin |
| POST | `/admin/users/:id/reactivate` | Admin reactivate account | Admin |

---

## 5. KYC VERIFICATION (`aq_kyc_verifications`, `aq_kyc_documents`)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/users/me/kyc` | Get current KYC status & verification details | User |
| POST | `/users/me/kyc` | Start/submit KYC verification | User |
| PATCH | `/users/me/kyc/:id` | Update KYC submission (re-submit after rejection) | User |
| POST | `/users/me/kyc/:id/documents` | Upload KYC document (ID, selfie, proof of address) | User |
| GET | `/users/me/kyc/:id/documents` | List uploaded KYC documents | User |
| DELETE | `/users/me/kyc/:id/documents/:docId` | Remove uploaded document (before review) | User |

### Admin KYC Review

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/admin/kyc` | List all KYC submissions (filterable by status/level) | Admin |
| GET | `/admin/kyc/:id` | Get KYC submission detail with documents | Admin |
| POST | `/admin/kyc/:id/approve` | Approve KYC verification | Admin |
| POST | `/admin/kyc/:id/reject` | Reject with reason | Admin |
| GET | `/admin/kyc/:id/documents/:docId` | View/download KYC document | Admin |
| POST | `/admin/kyc/:id/documents/:docId/verify` | Verify individual document | Admin |
| POST | `/admin/kyc/:id/documents/:docId/reject` | Reject individual document | Admin |

---

## 6. TELEGRAM LINKING (`aq_users` telegram fields)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/users/me/telegram/link` | Link Telegram account (validate initData) | User |
| DELETE | `/users/me/telegram/unlink` | Unlink Telegram account | User |
| GET | `/users/me/telegram` | Get linked Telegram info | User |

---

## 7. AD FORMATS (`aq_ad_formats`, `aq_ad_sizes`)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/ad-formats` | List all ad formats (public, for landing page & campaign builder) | Public |
| GET | `/ad-formats/:id` | Get ad format detail | Public |
| GET | `/ad-formats/:id/sizes` | List sizes for a format | Public |

### Admin Ad Format Management

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/admin/ad-formats` | Create ad format | Admin |
| PATCH | `/admin/ad-formats/:id` | Update ad format | Admin |
| DELETE | `/admin/ad-formats/:id` | Deactivate/delete ad format | Admin |
| GET | `/admin/ad-sizes` | List all ad sizes | Admin |
| POST | `/admin/ad-sizes` | Create ad size | Admin |
| PATCH | `/admin/ad-sizes/:id` | Update ad size | Admin |
| DELETE | `/admin/ad-sizes/:id` | Delete ad size | Admin |

---

## 8. TAGS / MULTITAG (`aq_tags`, `aq_ad_format_tags`)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/tags` | List all tags (filterable by group/status) | Public |
| GET | `/tags/:id` | Get tag detail | Public |
| GET | `/ad-formats/:id/tags` | List tags for a format | Public |

### Admin Tag Management

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/admin/tags` | Create tag | Admin |
| PATCH | `/admin/tags/:id` | Update tag | Admin |
| DELETE | `/admin/tags/:id` | Delete tag | Admin |
| POST | `/admin/ad-formats/:id/tags` | Assign tags to format | Admin |
| DELETE | `/admin/ad-formats/:id/tags/:tagId` | Remove tag from format | Admin |

---

## 9. CAMPAIGNS — RTB/PROGRAMMATIC (`aq_campaigns`)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/campaigns` | List advertiser's campaigns (paginated, filterable) | Advertiser |
| POST | `/campaigns` | Create campaign | Advertiser |
| GET | `/campaigns/:id` | Get campaign detail | Advertiser |
| PATCH | `/campaigns/:id` | Update campaign | Advertiser |
| DELETE | `/campaigns/:id` | Soft-delete campaign | Advertiser |
| POST | `/campaigns/:id/submit` | Submit for review (draft → pending_review) | Advertiser |
| POST | `/campaigns/:id/pause` | Pause campaign | Advertiser |
| POST | `/campaigns/:id/resume` | Resume campaign | Advertiser |
| GET | `/campaigns/:id/performance` | Get campaign performance (via view) | Advertiser |

### Admin Campaign Management

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/admin/campaigns` | List all campaigns | Admin |
| POST | `/admin/campaigns/:id/approve` | Approve campaign | Admin |
| POST | `/admin/campaigns/:id/reject` | Reject campaign with reason | Admin |

---

## 10. ADS & CREATIVES (`aq_ads`, `aq_ad_creatives`)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/campaigns/:id/ads` | List ads in campaign | Advertiser |
| POST | `/campaigns/:id/ads` | Create ad in campaign | Advertiser |
| GET | `/ads/:id` | Get ad detail | Advertiser |
| PATCH | `/ads/:id` | Update ad | Advertiser |
| DELETE | `/ads/:id` | Soft-delete ad | Advertiser |
| POST | `/ads/:id/submit` | Submit ad for review | Advertiser |
| POST | `/ads/:id/pause` | Pause ad | Advertiser |
| POST | `/ads/:id/resume` | Resume ad | Advertiser |

### Ad Creatives (files)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/ads/:id/creatives` | List creatives for ad | Advertiser |
| POST | `/ads/:id/creatives` | Upload creative file | Advertiser |
| PATCH | `/ads/:id/creatives/:creativeId` | Update creative (set primary, alt text) | Advertiser |
| DELETE | `/ads/:id/creatives/:creativeId` | Delete creative | Advertiser |

### Admin Ad Approval

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/admin/ads` | List all ads (filterable by status) | Admin |
| POST | `/admin/ads/:id/approve` | Approve ad | Admin |
| POST | `/admin/ads/:id/reject` | Reject ad | Admin |

---

## 11. DIRECT CAMPAIGNS — SELF-SERVE (`aq_direct_campaigns`, `aq_direct_campaign_creatives`, `aq_direct_campaign_targeting`, `aq_direct_campaign_zones`, `aq_direct_campaign_stats`)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/direct-campaigns` | List advertiser's direct campaigns | Advertiser |
| POST | `/direct-campaigns` | Create direct campaign | Advertiser |
| GET | `/direct-campaigns/:id` | Get direct campaign detail | Advertiser |
| PATCH | `/direct-campaigns/:id` | Update direct campaign | Advertiser |
| DELETE | `/direct-campaigns/:id` | Soft-delete | Advertiser |
| POST | `/direct-campaigns/:id/submit` | Submit for review | Advertiser |
| POST | `/direct-campaigns/:id/pause` | Pause | Advertiser |
| POST | `/direct-campaigns/:id/resume` | Resume | Advertiser |
| POST | `/direct-campaigns/:id/duplicate` | Duplicate campaign | Advertiser |
| POST | `/direct-campaigns/bulk` | Bulk create campaigns | Advertiser |
| GET | `/direct-campaigns/:id/performance` | Performance summary (via view) | Advertiser |
| GET | `/direct-campaigns/:id/stats` | Daily stats (filterable by date, zone, country, device) | Advertiser |

### Direct Campaign Creatives (A/B Testing)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/direct-campaigns/:id/creatives` | List creatives/variants | Advertiser |
| POST | `/direct-campaigns/:id/creatives` | Add creative variant | Advertiser |
| PATCH | `/direct-campaigns/:id/creatives/:cId` | Update creative | Advertiser |
| DELETE | `/direct-campaigns/:id/creatives/:cId` | Remove creative | Advertiser |
| POST | `/direct-campaigns/:id/creatives/:cId/set-winner` | Mark A/B winner | Advertiser |

### Direct Campaign Targeting

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/direct-campaigns/:id/targeting` | List targeting rules | Advertiser |
| POST | `/direct-campaigns/:id/targeting` | Add targeting rule | Advertiser |
| PATCH | `/direct-campaigns/:id/targeting/:rId` | Update targeting rule | Advertiser |
| DELETE | `/direct-campaigns/:id/targeting/:rId` | Remove targeting rule | Advertiser |

### Direct Campaign Zones

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/direct-campaigns/:id/zones` | List linked zones | Advertiser |
| POST | `/direct-campaigns/:id/zones` | Link zone to campaign | Advertiser |
| PATCH | `/direct-campaigns/:id/zones/:zId` | Update zone association | Advertiser |
| DELETE | `/direct-campaigns/:id/zones/:zId` | Unlink zone | Advertiser |

---

## 12. CAMPAIGN OPTIMIZATION TOOLS (`aq_campaign_optimization`)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/direct-campaigns/:id/optimization` | Get optimization settings (inline, spendguard, perf stimulator, pacing) | Advertiser |
| PATCH | `/direct-campaigns/:id/optimization` | Update optimization settings | Advertiser |
| GET | `/direct-campaigns/:id/optimization/log` | List optimization events/actions log | Advertiser |

---

## 13. PUBLISHER SITES (`aq_sites`)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/sites` | List publisher's sites | Publisher |
| POST | `/sites` | Register a new site | Publisher |
| GET | `/sites/:id` | Get site detail | Publisher |
| PATCH | `/sites/:id` | Update site | Publisher |
| DELETE | `/sites/:id` | Soft-delete site | Publisher |

### Admin Site Management

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/admin/sites` | List all sites | Admin |
| POST | `/admin/sites/:id/approve` | Approve site | Admin |
| POST | `/admin/sites/:id/reject` | Reject site | Admin |
| POST | `/admin/sites/:id/suspend` | Suspend site | Admin |

---

## 14. ZONES (`aq_zones`, `aq_zone_ad_assoc`)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/sites/:id/zones` | List zones for site | Publisher |
| POST | `/sites/:id/zones` | Create zone | Publisher |
| GET | `/zones/:id` | Get zone detail (with embed code) | Publisher |
| PATCH | `/zones/:id` | Update zone | Publisher |
| DELETE | `/zones/:id` | Soft-delete zone | Publisher |
| GET | `/zones/:id/ad-code` | Get generated JS/HTML ad code | Publisher |

### Zone-Ad Associations (admin/system)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/admin/zones/:id/ads` | List ads assigned to zone | Admin |
| POST | `/admin/zones/:id/ads` | Assign ad to zone | Admin |
| DELETE | `/admin/zones/:id/ads/:adId` | Remove ad from zone | Admin |

---

## 15. CATEGORIES (`aq_categories`, `aq_campaign_category`, `aq_site_categories`)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/categories` | List categories (tree structure) | Public |
| GET | `/categories/:id` | Get category detail | Public |

### Admin Category Management

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/admin/categories` | Create category | Admin |
| PATCH | `/admin/categories/:id` | Update category | Admin |
| DELETE | `/admin/categories/:id` | Delete category | Admin |

### Campaign/Site Category Linking

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/campaigns/:id/categories` | List campaign categories | Advertiser |
| POST | `/campaigns/:id/categories` | Assign categories to campaign | Advertiser |
| DELETE | `/campaigns/:id/categories/:catId` | Remove category from campaign | Advertiser |
| GET | `/sites/:id/categories` | List site categories | Publisher |
| POST | `/sites/:id/categories` | Assign categories to site | Publisher |
| DELETE | `/sites/:id/categories/:catId` | Remove category from site | Publisher |

---

## 16. GEO TARGETING (`aq_geo_countries`, `aq_geo_regions`)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/geo/countries` | List countries (filter: `is_balkan`, search by name) | Public |
| GET | `/geo/countries/:code` | Get country detail | Public |
| GET | `/geo/countries/:code/regions` | List regions for country | Public |
| GET | `/geo/regions` | Search regions | Public |

### Admin Geo Management

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/admin/geo/countries` | Add country | Admin |
| PATCH | `/admin/geo/countries/:code` | Update country | Admin |
| POST | `/admin/geo/regions` | Add region | Admin |
| PATCH | `/admin/geo/regions/:id` | Update region | Admin |

---

## 17. TRACKING: IMPRESSIONS, CLICKS, CONVERSIONS (`aq_impressions`, `aq_clicks`, `aq_conversions`)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/tracking/impression` | Record impression (ad serving pixel) | Public |
| GET | `/tracking/click/:adId` | Record click & redirect | Public |
| POST | `/tracking/conversion` | Record conversion (S2S postback) | API Key |

---

## 18. STATISTICS & ANALYTICS (`aq_stats_daily`, `aq_stats_browser`, `aq_stats_geo`)

### Advertiser Analytics

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/analytics/advertiser/overview` | Dashboard summary (spend, impressions, clicks, CTR, eCPM) | Advertiser |
| GET | `/analytics/advertiser/daily` | Daily stats (filterable by campaign, date range, country, device) | Advertiser |
| GET | `/analytics/advertiser/geo` | Geo breakdown | Advertiser |
| GET | `/analytics/advertiser/browser` | Browser breakdown | Advertiser |
| GET | `/analytics/advertiser/device` | Device breakdown | Advertiser |

### Publisher Analytics

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/analytics/publisher/overview` | Dashboard summary (earnings, impressions, fill rate) | Publisher |
| GET | `/analytics/publisher/daily` | Daily earnings stats | Publisher |
| GET | `/analytics/publisher/geo` | Geo breakdown | Publisher |
| GET | `/analytics/publisher/sites` | Per-site performance | Publisher |
| GET | `/analytics/publisher/zones` | Per-zone performance | Publisher |
| GET | `/analytics/publisher/earnings-summary` | Sparkline data (calls `aq_get_earnings_summary`) | Publisher |

### Admin Analytics

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/admin/analytics/overview` | Platform-wide summary | Admin |
| GET | `/admin/analytics/daily` | Platform daily stats | Admin |
| GET | `/admin/analytics/revenue` | Revenue & earnings breakdown | Admin |
| GET | `/admin/analytics/top-campaigns` | Top performing campaigns | Admin |
| GET | `/admin/analytics/top-publishers` | Top earning publishers | Admin |

---

## 19. FRAUD DETECTION (`aq_fraud_events`, `aq_antifraud_rules`, `aq_publisher_fraud_records`, `aq_fraud_notifications`)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/admin/fraud/events` | List fraud events (filterable by type, severity, date) | Admin |
| GET | `/admin/fraud/events/:id` | Get fraud event detail | Admin |
| GET | `/admin/fraud/rules` | List anti-fraud rules | Admin |
| POST | `/admin/fraud/rules` | Create anti-fraud rule | Admin |
| PATCH | `/admin/fraud/rules/:id` | Update rule | Admin |
| DELETE | `/admin/fraud/rules/:id` | Delete rule | Admin |
| GET | `/admin/fraud/publisher-records` | List publisher fraud records | Admin |
| POST | `/admin/fraud/publisher-records` | Create fraud record for publisher | Admin |
| PATCH | `/admin/fraud/publisher-records/:id` | Update/resolve record | Admin |
| GET | `/admin/fraud/notifications` | List fraud notifications | Admin |

---

## 20. RTB / AD EXCHANGES (`aq_ad_exchanges`, `aq_rtb_bid_requests`, `aq_rtb_bid_responses`)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/admin/exchanges` | List ad exchanges | Admin |
| POST | `/admin/exchanges` | Create exchange integration | Admin |
| GET | `/admin/exchanges/:id` | Get exchange detail | Admin |
| PATCH | `/admin/exchanges/:id` | Update exchange | Admin |
| DELETE | `/admin/exchanges/:id` | Remove exchange | Admin |
| GET | `/admin/exchanges/:id/stats` | Bid request/response stats | Admin |
| POST | `/rtb/bid-request` | Incoming OpenRTB bid request (SSP endpoint) | Exchange |
| POST | `/rtb/bid-response` | Process bid response | Exchange |

---

## 21. VIDEO / VAST (`aq_vast_events`, `aq_video_tracking`)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/vast-events` | List VAST event types | Public |
| POST | `/tracking/video` | Record video tracking event | Public |
| GET | `/analytics/video/:adId` | Video performance (quartile completion, VCR) | Advertiser |

---

## 22. CLICK-TO-WATCH VIDEO (`aq_video_end_cards`, `aq_ad_end_card_assoc`, `aq_video_branding_overlays`, `aq_ad_branding_overlay_assoc`)

### End Card Templates

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/video/end-cards` | List advertiser's end card templates | Advertiser |
| POST | `/video/end-cards` | Create end card template | Advertiser |
| GET | `/video/end-cards/:id` | Get end card detail | Advertiser |
| PATCH | `/video/end-cards/:id` | Update end card | Advertiser |
| DELETE | `/video/end-cards/:id` | Delete end card | Advertiser |
| POST | `/ads/:adId/end-cards` | Assign end card to ad | Advertiser |
| DELETE | `/ads/:adId/end-cards/:endCardId` | Remove end card from ad | Advertiser |

### Branding Overlay Presets

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/video/branding-overlays` | List advertiser's branding overlays | Advertiser |
| POST | `/video/branding-overlays` | Create branding overlay preset | Advertiser |
| GET | `/video/branding-overlays/:id` | Get overlay detail | Advertiser |
| PATCH | `/video/branding-overlays/:id` | Update overlay | Advertiser |
| DELETE | `/video/branding-overlays/:id` | Delete overlay | Advertiser |
| POST | `/ads/:adId/branding-overlays` | Assign overlay to ad | Advertiser |
| DELETE | `/ads/:adId/branding-overlays/:overlayId` | Remove overlay from ad | Advertiser |

---

## 23. MSN DISTRIBUTION NETWORK (`aq_distribution_networks`, `aq_campaign_network_assoc`, `aq_msn_properties`, `aq_msn_campaign_settings`, `aq_msn_performance_stats`)

### Distribution Networks

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/distribution-networks` | List available networks | Advertiser |
| GET | `/distribution-networks/:id` | Get network detail | Advertiser |

### Campaign ↔ Network Association

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/campaigns/:id/networks` | List networks linked to campaign | Advertiser |
| POST | `/campaigns/:id/networks` | Link network (with exclusive flag, bid adjustment) | Advertiser |
| PATCH | `/campaigns/:id/networks/:nId` | Update network association | Advertiser |
| DELETE | `/campaigns/:id/networks/:nId` | Unlink network | Advertiser |
| POST | `/campaigns/:id/msn-exclusive` | Toggle "Run on MSN exclusively" | Advertiser |

### MSN Properties

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/msn/properties` | List MSN properties (homepage, news, finance, etc.) | Advertiser |
| GET | `/msn/properties/:id` | Get MSN property detail | Advertiser |

### MSN Campaign Settings

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/campaigns/:id/msn-settings` | Get MSN-specific settings | Advertiser |
| PUT | `/campaigns/:id/msn-settings` | Create/update MSN settings | Advertiser |
| DELETE | `/campaigns/:id/msn-settings` | Remove MSN settings | Advertiser |

### MSN Performance Stats

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/campaigns/:id/msn-stats` | MSN performance by property & date | Advertiser |

### Admin Distribution Network Management

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/admin/distribution-networks` | Create network | Admin |
| PATCH | `/admin/distribution-networks/:id` | Update network | Admin |
| DELETE | `/admin/distribution-networks/:id` | Delete network | Admin |
| POST | `/admin/msn/properties` | Create MSN property | Admin |
| PATCH | `/admin/msn/properties/:id` | Update MSN property | Admin |
| DELETE | `/admin/msn/properties/:id` | Delete MSN property | Admin |

---

## 24. CAMPAIGN DYNAMICS — DCO (`aq_dynamic_content_tokens`, `aq_dynamic_creative_assets`, `aq_dynamic_creative_rules`, `aq_dynamic_product_feeds`, `aq_dynamic_product_feed_items`, `aq_dynamic_budget_rules`, `aq_dynamic_landing_pages`, `aq_dynamic_countdown_timers`, `aq_dynamic_rule_log`)

### Dynamic Content Tokens

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/dynamic/tokens` | List available tokens ({city}, {device}, etc.) | Advertiser |
| GET | `/dynamic/tokens/:id` | Get token detail | Advertiser |
| POST | `/admin/dynamic/tokens` | Create custom token | Admin |
| PATCH | `/admin/dynamic/tokens/:id` | Update token | Admin |

### Dynamic Creative Assets

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/dynamic/assets` | List advertiser's creative assets | Advertiser |
| POST | `/dynamic/assets` | Upload asset (headline, image, CTA, etc.) | Advertiser |
| GET | `/dynamic/assets/:id` | Get asset detail | Advertiser |
| PATCH | `/dynamic/assets/:id` | Update asset | Advertiser |
| DELETE | `/dynamic/assets/:id` | Delete asset | Advertiser |
| GET | `/dynamic/assets/:id/performance` | Asset performance (impressions, CTR) | Advertiser |

### Dynamic Creative Rules

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/campaigns/:id/dynamic-rules` | List creative rules for campaign | Advertiser |
| POST | `/campaigns/:id/dynamic-rules` | Create rule (conditions → asset selections) | Advertiser |
| PATCH | `/campaigns/:id/dynamic-rules/:rId` | Update rule | Advertiser |
| DELETE | `/campaigns/:id/dynamic-rules/:rId` | Delete rule | Advertiser |
| GET | `/campaigns/:id/dynamic-rules/log` | Rule firing log | Advertiser |

### Dynamic Product Feeds

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/dynamic/product-feeds` | List advertiser's product feeds | Advertiser |
| POST | `/dynamic/product-feeds` | Create product feed | Advertiser |
| GET | `/dynamic/product-feeds/:id` | Get feed detail | Advertiser |
| PATCH | `/dynamic/product-feeds/:id` | Update feed (URL, mapping, filters) | Advertiser |
| DELETE | `/dynamic/product-feeds/:id` | Delete feed | Advertiser |
| POST | `/dynamic/product-feeds/:id/refresh` | Trigger manual feed refresh | Advertiser |
| GET | `/dynamic/product-feeds/:id/items` | List items in feed (paginated, searchable) | Advertiser |
| GET | `/dynamic/product-feeds/:id/items/:itemId` | Get item detail | Advertiser |
| POST | `/dynamic/product-feeds/:id/items` | Manually add item | Advertiser |
| PATCH | `/dynamic/product-feeds/:id/items/:itemId` | Update item | Advertiser |
| DELETE | `/dynamic/product-feeds/:id/items/:itemId` | Remove item | Advertiser |

### Dynamic Budget Rules

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/campaigns/:id/budget-rules` | List budget rules | Advertiser |
| POST | `/campaigns/:id/budget-rules` | Create budget rule | Advertiser |
| PATCH | `/campaigns/:id/budget-rules/:rId` | Update budget rule | Advertiser |
| DELETE | `/campaigns/:id/budget-rules/:rId` | Delete budget rule | Advertiser |

### Dynamic Landing Pages

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/campaigns/:id/landing-pages` | List landing page rules | Advertiser |
| POST | `/campaigns/:id/landing-pages` | Create landing page rule | Advertiser |
| PATCH | `/campaigns/:id/landing-pages/:rId` | Update rule | Advertiser |
| DELETE | `/campaigns/:id/landing-pages/:rId` | Delete rule | Advertiser |

### Dynamic Countdown Timers

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/campaigns/:id/countdowns` | List countdown timers | Advertiser |
| POST | `/campaigns/:id/countdowns` | Create countdown timer | Advertiser |
| PATCH | `/campaigns/:id/countdowns/:tId` | Update timer | Advertiser |
| DELETE | `/campaigns/:id/countdowns/:tId` | Delete timer | Advertiser |

---

## 25. CUSTOM AUDIENCES (`aq_audience_pixels`, `aq_custom_audiences`, `aq_audience_members`, `aq_audience_rules`, `aq_audience_syncs`, `aq_campaign_audience_assoc`, `aq_audience_lookalikes`, `aq_pixel_events`)

### Audience Pixels

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/audiences/pixels` | List advertiser's tracking pixels | Advertiser |
| POST | `/audiences/pixels` | Create pixel | Advertiser |
| GET | `/audiences/pixels/:id` | Get pixel detail + snippet | Advertiser |
| PATCH | `/audiences/pixels/:id` | Update pixel | Advertiser |
| DELETE | `/audiences/pixels/:id` | Delete pixel | Advertiser |
| POST | `/audiences/pixels/:id/verify` | Verify pixel is firing | Advertiser |

### Pixel Event Collection (public, called by JS tag)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/p/:pixelUuid/event` | Record pixel event (PageView, AddToCart, etc.) | Public |
| GET | `/p/:pixelUuid/pixel.gif` | Image pixel fallback | Public |

### Custom Audiences

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/audiences` | List advertiser's audiences | Advertiser |
| POST | `/audiences` | Create audience | Advertiser |
| GET | `/audiences/:id` | Get audience detail (size, status, rules) | Advertiser |
| PATCH | `/audiences/:id` | Update audience | Advertiser |
| DELETE | `/audiences/:id` | Archive audience | Advertiser |
| GET | `/audiences/:id/size` | Get estimated audience size | Advertiser |

### Audience Rules

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/audiences/:id/rules` | List audience rules | Advertiser |
| POST | `/audiences/:id/rules` | Add rule | Advertiser |
| PATCH | `/audiences/:id/rules/:rId` | Update rule | Advertiser |
| DELETE | `/audiences/:id/rules/:rId` | Delete rule | Advertiser |

### Audience Data Upload / Sync

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/audiences/:id/syncs` | List sync history | Advertiser |
| POST | `/audiences/:id/syncs/upload` | Upload CSV customer list (hashed emails/phones) | Advertiser |
| GET | `/audiences/:id/syncs/:syncId` | Get sync status | Advertiser |

### Lookalike Audiences

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/audiences/:id/lookalike` | Create lookalike audience from seed | Advertiser |
| GET | `/audiences/lookalikes/:id` | Get lookalike config & status | Advertiser |

### Campaign ↔ Audience Association

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/campaigns/:id/audiences` | List audiences linked to campaign | Advertiser |
| POST | `/campaigns/:id/audiences` | Link audience (include/exclude, bid adjustment) | Advertiser |
| PATCH | `/campaigns/:id/audiences/:aId` | Update association | Advertiser |
| DELETE | `/campaigns/:id/audiences/:aId` | Unlink audience | Advertiser |

---

## 26. CLIP CAMPAIGNS (`aq_clip_campaigns`, `aq_clip_templates`, `aq_clip_music_library`, `aq_clip_interactions`)

### Clip Campaign Settings

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/campaigns/:id/clip-settings` | Get clip campaign settings | Advertiser |
| PUT | `/campaigns/:id/clip-settings` | Create/update clip settings | Advertiser |

### Clip Templates

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/clip/templates` | List clip templates (filterable by category) | Advertiser |
| GET | `/clip/templates/:id` | Get template detail + preview | Advertiser |

### Clip Music Library

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/clip/music` | Browse music library (filter by genre, mood, BPM, trending) | Advertiser |
| GET | `/clip/music/:id` | Get track detail + preview URL | Advertiser |
| GET | `/clip/music/:id/preview` | Stream music preview | Advertiser |

### Clip Interaction Analytics

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/analytics/clip/:campaignId` | Clip engagement analytics (views, loops, swipes, polls) | Advertiser |
| POST | `/tracking/clip-interaction` | Record clip interaction event | Public |

### Admin Clip Management

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/admin/clip/templates` | Create template | Admin |
| PATCH | `/admin/clip/templates/:id` | Update template | Admin |
| DELETE | `/admin/clip/templates/:id` | Delete template | Admin |
| POST | `/admin/clip/music` | Add music track | Admin |
| PATCH | `/admin/clip/music/:id` | Update track | Admin |
| DELETE | `/admin/clip/music/:id` | Remove track | Admin |

---

## 27. OEM — ORIGINAL EQUIPMENT MANUFACTURERS (`aq_oem_manufacturers`, `aq_oem_placements`, `aq_oem_apps`, `aq_oem_device_models`, `aq_campaign_oem_assoc`, `aq_oem_performance_stats`)

### OEM Browsing (for campaign builder)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/oem/manufacturers` | List OEM partners | Advertiser |
| GET | `/oem/manufacturers/:id` | Get OEM detail | Advertiser |
| GET | `/oem/manufacturers/:id/placements` | List placements for OEM | Advertiser |
| GET | `/oem/manufacturers/:id/device-models` | List device models | Advertiser |
| GET | `/oem/manufacturers/:id/apps` | List preinstalled apps | Advertiser |

### Campaign ↔ OEM Association

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/campaigns/:id/oem` | List OEM associations for campaign | Advertiser |
| POST | `/campaigns/:id/oem` | Link OEM manufacturer/placement to campaign | Advertiser |
| PATCH | `/campaigns/:id/oem/:assocId` | Update OEM targeting (device tier, geo, bid) | Advertiser |
| DELETE | `/campaigns/:id/oem/:assocId` | Remove OEM targeting | Advertiser |

### OEM Performance Stats

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/campaigns/:id/oem-stats` | OEM performance by manufacturer, placement, date | Advertiser |

### Admin OEM Management

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/admin/oem/manufacturers` | Add OEM partner | Admin |
| PATCH | `/admin/oem/manufacturers/:id` | Update OEM | Admin |
| DELETE | `/admin/oem/manufacturers/:id` | Remove OEM | Admin |
| POST | `/admin/oem/placements` | Add placement | Admin |
| PATCH | `/admin/oem/placements/:id` | Update placement | Admin |
| DELETE | `/admin/oem/placements/:id` | Remove placement | Admin |
| POST | `/admin/oem/apps` | Add OEM app | Admin |
| PATCH | `/admin/oem/apps/:id` | Update OEM app | Admin |
| POST | `/admin/oem/device-models` | Add device model | Admin |
| PATCH | `/admin/oem/device-models/:id` | Update device model | Admin |

---

## 28. PAYOUTS & BILLING (`aq_payouts`, `aq_invoices`)

### Publisher Payouts

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/payouts` | List user's payouts | User |
| POST | `/payouts/request` | Request payout (requires min threshold + KYC) | Publisher |
| GET | `/payouts/:id` | Get payout detail | User |

### Invoices

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/invoices` | List user's invoices | User |
| GET | `/invoices/:id` | Get invoice detail | User |
| GET | `/invoices/:id/pdf` | Download invoice PDF | User |

### Admin Payouts & Invoices

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/admin/payouts` | List all payouts | Admin |
| PATCH | `/admin/payouts/:id` | Update payout status (process, complete, fail) | Admin |
| POST | `/admin/invoices` | Generate invoice | Admin |
| PATCH | `/admin/invoices/:id` | Update invoice status | Admin |

---

## 29. WALLET & TRANSACTIONS (`aq_saved_payment_methods`, `aq_transactions`)

### Saved Payment Methods

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/wallet/payment-methods` | List saved payment methods | User |
| POST | `/wallet/payment-methods` | Add payment method (tokenized via Stripe/PayPal) | User |
| PATCH | `/wallet/payment-methods/:id` | Update (set default, rename) | User |
| DELETE | `/wallet/payment-methods/:id` | Remove payment method | User |

### Add Funds / Transactions

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/wallet/add-funds` | Add funds to balance (calls `aq_add_funds` procedure) | Advertiser |
| GET | `/wallet/transactions` | List transaction history (deposits, spend, refunds) | User |
| GET | `/wallet/transactions/:id` | Get transaction detail | User |
| GET | `/wallet/balance` | Get current balance | User |

### Webhooks (from payment gateways)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/webhooks/stripe` | Stripe payment webhook | Webhook |
| POST | `/webhooks/paypal` | PayPal payment webhook | Webhook |
| POST | `/webhooks/coinbase` | Coinbase payment webhook | Webhook |

---

## 30. PRICING PLANS & SUBSCRIPTIONS (`aq_pricing_plans`, `aq_user_subscriptions`)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/pricing-plans` | List plans (Starter, Growth, Enterprise) | Public |
| GET | `/pricing-plans/:id` | Get plan detail | Public |
| GET | `/users/me/subscription` | Get current subscription | User |
| POST | `/users/me/subscription` | Subscribe to plan | User |
| PATCH | `/users/me/subscription` | Change plan or billing cycle | User |
| POST | `/users/me/subscription/cancel` | Cancel subscription | User |

### Admin Plan Management

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/admin/pricing-plans` | Create plan | Admin |
| PATCH | `/admin/pricing-plans/:id` | Update plan | Admin |
| DELETE | `/admin/pricing-plans/:id` | Deactivate plan | Admin |

---

## 31. TRAFFIC SOURCES (`aq_traffic_sources`, `aq_campaign_traffic_source`)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/traffic-sources` | List traffic sources | Public |
| GET | `/campaigns/:id/traffic-sources` | List traffic sources for campaign | Advertiser |
| POST | `/campaigns/:id/traffic-sources` | Add traffic source (whitelist/blacklist) | Advertiser |
| DELETE | `/campaigns/:id/traffic-sources/:tsId` | Remove traffic source | Advertiser |

### Admin Traffic Source Management

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/admin/traffic-sources` | Create traffic source | Admin |
| PATCH | `/admin/traffic-sources/:id` | Update traffic source | Admin |
| DELETE | `/admin/traffic-sources/:id` | Delete traffic source | Admin |

---

## 32. REFERRAL PROGRAM (`aq_referral_links`, `aq_referral_conversions`, `aq_referral_payouts`)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/referrals/summary` | Referral dashboard summary (via view) | User |
| GET | `/referrals/links` | List my referral links | User |
| POST | `/referrals/links` | Create referral link | User |
| GET | `/referrals/links/:id` | Get link detail + stats | User |
| PATCH | `/referrals/links/:id` | Update link (pause, customize) | User |
| DELETE | `/referrals/links/:id` | Soft-delete link | User |
| GET | `/referrals/conversions` | List referral conversions | User |
| GET | `/referrals/payouts` | List referral payouts | User |
| POST | `/referrals/payouts/request` | Request referral payout | User |

### Public Referral Tracking

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/ref/:code` | Track referral click & redirect to signup | Public |

### Admin Referral Management

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/admin/referrals` | List all referrals (with fraud flags) | Admin |
| PATCH | `/admin/referrals/conversions/:id` | Update conversion status (flag as fraudulent) | Admin |
| GET | `/admin/referrals/payouts` | List all referral payouts | Admin |
| PATCH | `/admin/referrals/payouts/:id` | Process referral payout | Admin |

---

## 33. ACTIVITY LOG (`aq_activity_log`, `aq_log_settings`)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/users/me/activity-log` | User's own activity log | User |
| GET | `/admin/activity-log` | Platform activity log (all users) | Admin |
| GET | `/admin/activity-log/settings` | Get log settings | Admin |
| PATCH | `/admin/activity-log/settings` | Update log settings | Admin |

---

## 34. NOTIFICATIONS (`aq_notifications`, `aq_notification_settings`)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/notifications` | List user's notifications (paginated, unread count) | User |
| GET | `/notifications/unread-count` | Get unread notification count | User |
| PATCH | `/notifications/:id/read` | Mark as read | User |
| POST | `/notifications/mark-all-read` | Mark all as read | User |
| DELETE | `/notifications/:id` | Delete notification | User |
| GET | `/notifications/settings` | Get notification preferences | User |
| PATCH | `/notifications/settings` | Update notification preferences | User |

---

## 35. SUPPORT TICKETS (`aq_support_tickets`, `aq_support_messages`)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/support/tickets` | List user's tickets | User |
| POST | `/support/tickets` | Create support ticket | User |
| GET | `/support/tickets/:id` | Get ticket detail with messages | User |
| PATCH | `/support/tickets/:id` | Update ticket (close, reopen) | User |
| POST | `/support/tickets/:id/messages` | Add message to ticket | User |

### Admin Support

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/admin/support/tickets` | List all tickets (filterable) | Admin |
| GET | `/admin/support/tickets/:id` | Get ticket detail | Admin |
| PATCH | `/admin/support/tickets/:id` | Update status, assign, set priority | Admin |
| POST | `/admin/support/tickets/:id/messages` | Reply to ticket (supports internal notes) | Admin |

---

## 36. NEWSLETTER (`aq_newsletters`)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/newsletter/subscribe` | Subscribe to newsletter | Public |
| POST | `/newsletter/unsubscribe` | Unsubscribe (with token) | Public |

### Admin Newsletter

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/admin/newsletter/subscribers` | List subscribers | Admin |
| DELETE | `/admin/newsletter/subscribers/:id` | Remove subscriber | Admin |

---

## 37. FAQ (`aq_faq`)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/faq` | List published FAQs (filterable by category, language) | Public |
| GET | `/faq/:id` | Get FAQ item | Public |

### Admin FAQ Management

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/admin/faq` | Create FAQ | Admin |
| PATCH | `/admin/faq/:id` | Update FAQ | Admin |
| DELETE | `/admin/faq/:id` | Delete FAQ | Admin |

---

## 38. TESTIMONIALS & CASE STUDIES (`aq_testimonials`, `aq_case_studies`)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/testimonials` | List published testimonials | Public |
| GET | `/case-studies` | List published case studies | Public |
| GET | `/case-studies/:slug` | Get case study by slug | Public |

### Admin Management

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/admin/testimonials` | Create testimonial | Admin |
| PATCH | `/admin/testimonials/:id` | Update testimonial | Admin |
| DELETE | `/admin/testimonials/:id` | Delete testimonial | Admin |
| POST | `/admin/case-studies` | Create case study | Admin |
| PATCH | `/admin/case-studies/:id` | Update case study | Admin |
| DELETE | `/admin/case-studies/:id` | Delete case study | Admin |

---

## 39. TRUSTED PUBLISHERS (`aq_trusted_publishers`)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/trusted-publishers` | List featured publishers (landing page marquee) | Public |

### Admin Management

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/admin/trusted-publishers` | Add trusted publisher | Admin |
| PATCH | `/admin/trusted-publishers/:id` | Update | Admin |
| DELETE | `/admin/trusted-publishers/:id` | Remove | Admin |

---

## 40. COOKIE CONSENT / GDPR (`aq_cookie_consents`)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/cookie-consent` | Record cookie consent choice | Public |
| GET | `/cookie-consent/:visitorId` | Get consent status for visitor | Public |
| PATCH | `/cookie-consent/:visitorId` | Update consent preferences | Public |

---

## 41. LANGUAGES (`aq_languages`)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/languages` | List available languages (EN, SQ, IT, DE) | Public |

### Admin Language Management

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/admin/languages` | Add language | Admin |
| PATCH | `/admin/languages/:id` | Update language | Admin |
| DELETE | `/admin/languages/:id` | Deactivate language | Admin |

---

## 42. API KEYS / SDK (`aq_api_keys`)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api-keys` | List user's API keys | User |
| POST | `/api-keys` | Generate new API key | User |
| GET | `/api-keys/:id` | Get API key detail (never shows secret after creation) | User |
| PATCH | `/api-keys/:id` | Update key (name, permissions, rate limit, allowed IPs) | User |
| DELETE | `/api-keys/:id` | Revoke API key | User |

---

## 43. MOBILE DEVICES (`aq_mobile_devices`)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/users/me/devices` | List registered mobile devices | User |
| POST | `/users/me/devices` | Register mobile device (push token) | User |
| PATCH | `/users/me/devices/:id` | Update device (new token, app version) | User |
| DELETE | `/users/me/devices/:id` | Unregister device | User |

---

## 44. CAREERS (`aq_careers`)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/careers` | List published job openings | Public |
| GET | `/careers/:id` | Get job detail | Public |

### Admin Career Management

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/admin/careers` | Create job posting | Admin |
| PATCH | `/admin/careers/:id` | Update job posting | Admin |
| DELETE | `/admin/careers/:id` | Delete job posting | Admin |

---

## 45. PLATFORM SETTINGS (`aq_platform_settings`)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/admin/settings` | List all platform settings | Admin |
| GET | `/admin/settings/:key` | Get setting by key | Admin |
| PATCH | `/admin/settings/:key` | Update setting value | Admin |
| POST | `/admin/settings` | Create new setting | Admin |

---

## 46. TELEGRAM MINI APPS (`aq_telegram_mini_apps`, `aq_telegram_mini_app_sessions`, `aq_telegram_mini_app_events`)

### Mini App Management

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/telegram/mini-apps` | List user's mini apps | User |
| POST | `/telegram/mini-apps` | Register mini app | User |
| GET | `/telegram/mini-apps/:id` | Get mini app detail | User |
| PATCH | `/telegram/mini-apps/:id` | Update mini app | User |
| DELETE | `/telegram/mini-apps/:id` | Soft-delete mini app | User |
| GET | `/telegram/mini-apps/:id/analytics` | Mini app analytics (sessions, events) | User |

### Mini App Session Tracking

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/telegram/mini-apps/:id/sessions` | Record session start (validate initData) | Public |
| PATCH | `/telegram/mini-apps/:id/sessions/:sId` | Update session (end, duration) | Public |
| POST | `/telegram/mini-apps/:id/events` | Record in-app event | Public |

### Admin Mini App Management

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/admin/telegram/mini-apps` | List all mini apps | Admin |
| POST | `/admin/telegram/mini-apps/:id/approve` | Approve mini app | Admin |
| POST | `/admin/telegram/mini-apps/:id/reject` | Reject mini app | Admin |
| POST | `/admin/telegram/mini-apps/:id/suspend` | Suspend mini app | Admin |

---

## 47. AD SERVING (internal, uses `aq_get_eligible_ads` procedure)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/serve/zone/:zoneId` | Get ad for zone (ad decision endpoint) | Public |
| GET | `/serve/vast/:adId` | Generate VAST XML for video ad | Public |
| GET | `/serve/native/:zoneId` | Get native ad JSON payload | Public |

---

## Summary

| Section | Endpoint Count |
|---------|---------------|
| Auth & Sessions | 11 |
| Two-Factor Authentication | 7 |
| User Profiles | 12 |
| Account Deactivation | 6 |
| KYC Verification | 13 |
| Telegram Linking | 3 |
| Ad Formats & Sizes | 11 |
| Tags / MultiTag | 8 |
| RTB Campaigns | 12 |
| Ads & Creatives | 14 |
| Direct Campaigns (Self-Serve) | 27 |
| Campaign Optimization | 3 |
| Publisher Sites | 8 |
| Zones | 9 |
| Categories | 11 |
| Geo Targeting | 8 |
| Tracking (Impressions/Clicks/Conversions) | 3 |
| Statistics & Analytics | 13 |
| Fraud Detection | 10 |
| RTB / Ad Exchanges | 8 |
| Video / VAST | 3 |
| Click-to-Watch Video | 14 |
| MSN Distribution Network | 16 |
| Campaign Dynamics (DCO) | 29 |
| Custom Audiences | 24 |
| Clip Campaigns | 12 |
| OEM | 17 |
| Payouts & Billing | 8 |
| Wallet & Transactions | 9 |
| Pricing Plans & Subscriptions | 9 |
| Traffic Sources | 6 |
| Referral Program | 13 |
| Activity Log | 4 |
| Notifications | 7 |
| Support Tickets | 9 |
| Newsletter | 4 |
| FAQ | 5 |
| Testimonials & Case Studies | 8 |
| Trusted Publishers | 4 |
| Cookie Consent / GDPR | 3 |
| Languages | 4 |
| API Keys / SDK | 5 |
| Mobile Devices | 4 |
| Careers | 5 |
| Platform Settings | 4 |
| Telegram Mini Apps | 10 |
| Ad Serving | 3 |
| **TOTAL** | **~430 endpoints** |

---
---

# ALL ROUTES (Router Files)

> Each router file groups related endpoints under a single prefix.
> **48 router files** — **~430 endpoints**

---

## Route 1: `auth.router.ts`
**Prefix:** `/api/v1/auth`

```
POST   /register                          → Register new user
POST   /login                             → Login (JWT + session)
POST   /logout                            → Invalidate session
POST   /refresh                           → Refresh access token
POST   /forgot-password                   → Send reset email
POST   /reset-password                    → Reset password with token
POST   /verify-email                      → Verify email
POST   /resend-verification               → Resend verification email
GET    /sessions                          → List active sessions
DELETE /sessions/:id                      → Revoke session
DELETE /sessions                          → Revoke all sessions
POST   /2fa/enable                        → Generate TOTP secret + QR
POST   /2fa/confirm                       → Confirm TOTP setup
POST   /2fa/disable                       → Disable 2FA
POST   /2fa/verify                        → Verify 2FA during login
GET    /2fa/backup-codes                  → Get backup codes
POST   /2fa/backup-codes/regenerate       → Regenerate backup codes
POST   /2fa/verify-backup                 → Verify with backup code
```
**18 endpoints**

---

## Route 2: `users.router.ts`
**Prefix:** `/api/v1/users`

```
GET    /me                                → Get current user + profile
PATCH  /me                                → Update user settings
GET    /me/profile                        → Get full profile
PATCH  /me/profile                        → Update profile
PATCH  /me/avatar                         → Upload avatar
PATCH  /me/password                       → Change password
GET    /me/balance                        → Get balance
POST   /me/deactivate                     → Self-deactivate
POST   /me/reactivate                     → Reactivate via token
POST   /me/close                          → Request permanent closure
GET    /me/activity-log                   → User activity log
GET    /me/devices                        → List mobile devices
POST   /me/devices                        → Register device
PATCH  /me/devices/:id                    → Update device
DELETE /me/devices/:id                    → Unregister device
POST   /me/telegram/link                  → Link Telegram
DELETE /me/telegram/unlink                → Unlink Telegram
GET    /me/telegram                       → Get Telegram info
GET    /me/subscription                   → Get subscription
POST   /me/subscription                   → Subscribe to plan
PATCH  /me/subscription                   → Change plan
POST   /me/subscription/cancel            → Cancel subscription
```
**22 endpoints**

---

## Route 3: `kyc.router.ts`
**Prefix:** `/api/v1/users/me/kyc`

```
GET    /                                  → Get KYC status
POST   /                                  → Submit KYC verification
PATCH  /:id                               → Update KYC submission
POST   /:id/documents                     → Upload document
GET    /:id/documents                     → List documents
DELETE /:id/documents/:docId              → Remove document
```
**6 endpoints**

---

## Route 4: `campaigns.router.ts`
**Prefix:** `/api/v1/campaigns`

```
GET    /                                  → List campaigns
POST   /                                  → Create campaign
GET    /:id                               → Get campaign detail
PATCH  /:id                               → Update campaign
DELETE /:id                               → Soft-delete campaign
POST   /:id/submit                        → Submit for review
POST   /:id/pause                         → Pause
POST   /:id/resume                        → Resume
GET    /:id/performance                   → Performance stats
GET    /:id/categories                    → List categories
POST   /:id/categories                    → Assign categories
DELETE /:id/categories/:catId             → Remove category
GET    /:id/networks                      → List linked networks
POST   /:id/networks                      → Link network
PATCH  /:id/networks/:nId                 → Update network assoc
DELETE /:id/networks/:nId                 → Unlink network
POST   /:id/msn-exclusive                 → Toggle MSN exclusive
GET    /:id/msn-settings                  → Get MSN settings
PUT    /:id/msn-settings                  → Create/update MSN settings
DELETE /:id/msn-settings                  → Remove MSN settings
GET    /:id/msn-stats                     → MSN performance
GET    /:id/dynamic-rules                 → List DCO rules
POST   /:id/dynamic-rules                 → Create DCO rule
PATCH  /:id/dynamic-rules/:rId            → Update DCO rule
DELETE /:id/dynamic-rules/:rId            → Delete DCO rule
GET    /:id/dynamic-rules/log             → Rule firing log
GET    /:id/budget-rules                  → List budget rules
POST   /:id/budget-rules                  → Create budget rule
PATCH  /:id/budget-rules/:rId             → Update budget rule
DELETE /:id/budget-rules/:rId             → Delete budget rule
GET    /:id/landing-pages                 → List landing page rules
POST   /:id/landing-pages                 → Create landing page rule
PATCH  /:id/landing-pages/:rId            → Update rule
DELETE /:id/landing-pages/:rId            → Delete rule
GET    /:id/countdowns                    → List countdowns
POST   /:id/countdowns                    → Create countdown
PATCH  /:id/countdowns/:tId               → Update countdown
DELETE /:id/countdowns/:tId               → Delete countdown
GET    /:id/audiences                     → List linked audiences
POST   /:id/audiences                     → Link audience
PATCH  /:id/audiences/:aId                → Update audience assoc
DELETE /:id/audiences/:aId                → Unlink audience
GET    /:id/clip-settings                 → Get clip settings
PUT    /:id/clip-settings                 → Create/update clip settings
GET    /:id/oem                           → List OEM assocs
POST   /:id/oem                           → Link OEM
PATCH  /:id/oem/:assocId                  → Update OEM targeting
DELETE /:id/oem/:assocId                  → Remove OEM
GET    /:id/oem-stats                     → OEM performance
GET    /:id/traffic-sources               → List traffic sources
POST   /:id/traffic-sources               → Add traffic source
DELETE /:id/traffic-sources/:tsId         → Remove traffic source
```
**51 endpoints**

---

## Route 5: `ads.router.ts`
**Prefix:** `/api/v1`

```
GET    /campaigns/:id/ads                 → List ads in campaign
POST   /campaigns/:id/ads                 → Create ad
GET    /ads/:id                           → Get ad detail
PATCH  /ads/:id                           → Update ad
DELETE /ads/:id                           → Soft-delete ad
POST   /ads/:id/submit                    → Submit for review
POST   /ads/:id/pause                     → Pause ad
POST   /ads/:id/resume                    → Resume ad
GET    /ads/:id/creatives                 → List creatives
POST   /ads/:id/creatives                 → Upload creative
PATCH  /ads/:id/creatives/:cId            → Update creative
DELETE /ads/:id/creatives/:cId            → Delete creative
POST   /ads/:adId/end-cards               → Assign end card
DELETE /ads/:adId/end-cards/:ecId         → Remove end card
POST   /ads/:adId/branding-overlays       → Assign overlay
DELETE /ads/:adId/branding-overlays/:oId  → Remove overlay
```
**16 endpoints**

---

## Route 6: `direct-campaigns.router.ts`
**Prefix:** `/api/v1/direct-campaigns`

```
GET    /                                  → List direct campaigns
POST   /                                  → Create direct campaign
POST   /bulk                              → Bulk create
GET    /:id                               → Get detail
PATCH  /:id                               → Update
DELETE /:id                               → Soft-delete
POST   /:id/submit                        → Submit for review
POST   /:id/pause                         → Pause
POST   /:id/resume                        → Resume
POST   /:id/duplicate                     → Duplicate
GET    /:id/performance                   → Performance summary
GET    /:id/stats                         → Daily stats
GET    /:id/optimization                  → Get optimization settings
PATCH  /:id/optimization                  → Update optimization
GET    /:id/optimization/log              → Optimization log
GET    /:id/creatives                     → List creatives
POST   /:id/creatives                     → Add creative variant
PATCH  /:id/creatives/:cId                → Update creative
DELETE /:id/creatives/:cId                → Remove creative
POST   /:id/creatives/:cId/set-winner     → Mark A/B winner
GET    /:id/targeting                     → List targeting rules
POST   /:id/targeting                     → Add rule
PATCH  /:id/targeting/:rId                → Update rule
DELETE /:id/targeting/:rId                → Remove rule
GET    /:id/zones                         → List linked zones
POST   /:id/zones                         → Link zone
PATCH  /:id/zones/:zId                    → Update zone assoc
DELETE /:id/zones/:zId                    → Unlink zone
```
**28 endpoints**

---

## Route 7: `sites.router.ts`
**Prefix:** `/api/v1/sites`

```
GET    /                                  → List publisher's sites
POST   /                                  → Register site
GET    /:id                               → Get site detail
PATCH  /:id                               → Update site
DELETE /:id                               → Soft-delete site
GET    /:id/categories                    → List site categories
POST   /:id/categories                    → Assign categories
DELETE /:id/categories/:catId             → Remove category
GET    /:id/zones                         → List zones for site
POST   /:id/zones                         → Create zone
```
**10 endpoints**

---

## Route 8: `zones.router.ts`
**Prefix:** `/api/v1/zones`

```
GET    /:id                               → Get zone detail
PATCH  /:id                               → Update zone
DELETE /:id                               → Soft-delete zone
GET    /:id/ad-code                       → Get JS/HTML embed code
```
**4 endpoints**

---

## Route 9: `video.router.ts`
**Prefix:** `/api/v1/video`

```
GET    /end-cards                         → List end card templates
POST   /end-cards                         → Create end card
GET    /end-cards/:id                     → Get end card detail
PATCH  /end-cards/:id                     → Update end card
DELETE /end-cards/:id                     → Delete end card
GET    /branding-overlays                 → List overlays
POST   /branding-overlays                 → Create overlay
GET    /branding-overlays/:id             → Get overlay detail
PATCH  /branding-overlays/:id             → Update overlay
DELETE /branding-overlays/:id             → Delete overlay
```
**10 endpoints**

---

## Route 10: `dynamic.router.ts`
**Prefix:** `/api/v1/dynamic`

```
GET    /tokens                            → List dynamic tokens
GET    /tokens/:id                        → Get token detail
GET    /assets                            → List creative assets
POST   /assets                            → Upload asset
GET    /assets/:id                        → Get asset detail
PATCH  /assets/:id                        → Update asset
DELETE /assets/:id                        → Delete asset
GET    /assets/:id/performance            → Asset performance
GET    /product-feeds                     → List product feeds
POST   /product-feeds                     → Create feed
GET    /product-feeds/:id                 → Get feed detail
PATCH  /product-feeds/:id                 → Update feed
DELETE /product-feeds/:id                 → Delete feed
POST   /product-feeds/:id/refresh         → Manual refresh
GET    /product-feeds/:id/items           → List items
GET    /product-feeds/:id/items/:iId      → Get item detail
POST   /product-feeds/:id/items           → Add item
PATCH  /product-feeds/:id/items/:iId      → Update item
DELETE /product-feeds/:id/items/:iId      → Remove item
```
**19 endpoints**

---

## Route 11: `audiences.router.ts`
**Prefix:** `/api/v1/audiences`

```
GET    /                                  → List audiences
POST   /                                  → Create audience
GET    /:id                               → Get detail
PATCH  /:id                               → Update audience
DELETE /:id                               → Archive audience
GET    /:id/size                          → Estimated size
GET    /:id/rules                         → List rules
POST   /:id/rules                         → Add rule
PATCH  /:id/rules/:rId                    → Update rule
DELETE /:id/rules/:rId                    → Delete rule
GET    /:id/syncs                         → List sync history
POST   /:id/syncs/upload                  → Upload CSV list
GET    /:id/syncs/:syncId                 → Get sync status
POST   /:id/lookalike                     → Create lookalike
GET    /lookalikes/:id                    → Get lookalike config
GET    /pixels                            → List pixels
POST   /pixels                            → Create pixel
GET    /pixels/:id                        → Get pixel + snippet
PATCH  /pixels/:id                        → Update pixel
DELETE /pixels/:id                        → Delete pixel
POST   /pixels/:id/verify                 → Verify pixel
```
**21 endpoints**

---

## Route 12: `clip.router.ts`
**Prefix:** `/api/v1/clip`

```
GET    /templates                         → List clip templates
GET    /templates/:id                     → Get template detail
GET    /music                             → Browse music library
GET    /music/:id                         → Get track detail
GET    /music/:id/preview                 → Stream preview
```
**5 endpoints**

---

## Route 13: `oem.router.ts`
**Prefix:** `/api/v1/oem`

```
GET    /manufacturers                     → List OEM partners
GET    /manufacturers/:id                 → Get OEM detail
GET    /manufacturers/:id/placements      → List placements
GET    /manufacturers/:id/device-models   → List device models
GET    /manufacturers/:id/apps            → List preinstalled apps
```
**5 endpoints**

---

## Route 14: `analytics.router.ts`
**Prefix:** `/api/v1/analytics`

```
GET    /advertiser/overview               → Advertiser dashboard
GET    /advertiser/daily                  → Daily stats
GET    /advertiser/geo                    → Geo breakdown
GET    /advertiser/browser                → Browser breakdown
GET    /advertiser/device                 → Device breakdown
GET    /publisher/overview                → Publisher dashboard
GET    /publisher/daily                   → Daily earnings
GET    /publisher/geo                     → Geo breakdown
GET    /publisher/sites                   → Per-site performance
GET    /publisher/zones                   → Per-zone performance
GET    /publisher/earnings-summary        → Earnings sparkline
GET    /video/:adId                       → Video performance
GET    /clip/:campaignId                  → Clip engagement
```
**13 endpoints**

---

## Route 15: `wallet.router.ts`
**Prefix:** `/api/v1/wallet`

```
GET    /balance                           → Get balance
GET    /payment-methods                   → List payment methods
POST   /payment-methods                   → Add payment method
PATCH  /payment-methods/:id               → Update method
DELETE /payment-methods/:id               → Remove method
POST   /add-funds                         → Add funds
GET    /transactions                      → List transactions
GET    /transactions/:id                  → Transaction detail
```
**8 endpoints**

---

## Route 16: `payouts.router.ts`
**Prefix:** `/api/v1/payouts`

```
GET    /                                  → List payouts
POST   /request                           → Request payout
GET    /:id                               → Payout detail
```
**3 endpoints**

---

## Route 17: `invoices.router.ts`
**Prefix:** `/api/v1/invoices`

```
GET    /                                  → List invoices
GET    /:id                               → Invoice detail
GET    /:id/pdf                           → Download PDF
```
**3 endpoints**

---

## Route 18: `referrals.router.ts`
**Prefix:** `/api/v1/referrals`

```
GET    /summary                           → Referral dashboard
GET    /links                             → List referral links
POST   /links                             → Create link
GET    /links/:id                         → Link detail + stats
PATCH  /links/:id                         → Update link
DELETE /links/:id                         → Soft-delete link
GET    /conversions                       → List conversions
GET    /payouts                           → List referral payouts
POST   /payouts/request                   → Request payout
```
**9 endpoints**

---

## Route 19: `notifications.router.ts`
**Prefix:** `/api/v1/notifications`

```
GET    /                                  → List notifications
GET    /unread-count                      → Unread count
PATCH  /:id/read                          → Mark as read
POST   /mark-all-read                     → Mark all read
DELETE /:id                               → Delete notification
GET    /settings                          → Get preferences
PATCH  /settings                          → Update preferences
```
**7 endpoints**

---

## Route 20: `support.router.ts`
**Prefix:** `/api/v1/support/tickets`

```
GET    /                                  → List tickets
POST   /                                  → Create ticket
GET    /:id                               → Ticket + messages
PATCH  /:id                               → Update ticket
POST   /:id/messages                      → Add message
```
**5 endpoints**

---

## Route 21: `api-keys.router.ts`
**Prefix:** `/api/v1/api-keys`

```
GET    /                                  → List API keys
POST   /                                  → Generate key
GET    /:id                               → Key detail
PATCH  /:id                               → Update key
DELETE /:id                               → Revoke key
```
**5 endpoints**

---

## Route 22: `pricing.router.ts`
**Prefix:** `/api/v1/pricing-plans`

```
GET    /                                  → List plans
GET    /:id                               → Plan detail
```
**2 endpoints**

---

## Route 23: `public.router.ts`
**Prefix:** `/api/v1`

```
GET    /ad-formats                        → List ad formats
GET    /ad-formats/:id                    → Format detail
GET    /ad-formats/:id/sizes              → Format sizes
GET    /ad-formats/:id/tags               → Tags for format
GET    /tags                              → List tags
GET    /tags/:id                          → Tag detail
GET    /categories                        → List categories
GET    /categories/:id                    → Category detail
GET    /geo/countries                     → List countries
GET    /geo/countries/:code               → Country detail
GET    /geo/countries/:code/regions       → Regions for country
GET    /geo/regions                       → Search regions
GET    /languages                         → List languages
GET    /vast-events                       → List VAST events
GET    /distribution-networks             → List networks
GET    /distribution-networks/:id         → Network detail
GET    /msn/properties                    → List MSN properties
GET    /msn/properties/:id                → MSN property detail
GET    /traffic-sources                   → List traffic sources
GET    /faq                               → List FAQs
GET    /faq/:id                           → FAQ detail
GET    /testimonials                      → List testimonials
GET    /case-studies                      → List case studies
GET    /case-studies/:slug                → Case study detail
GET    /trusted-publishers                → List trusted publishers
GET    /careers                           → List jobs
GET    /careers/:id                       → Job detail
POST   /newsletter/subscribe             → Subscribe
POST   /newsletter/unsubscribe           → Unsubscribe
POST   /cookie-consent                   → Record consent
GET    /cookie-consent/:visitorId        → Get consent
PATCH  /cookie-consent/:visitorId        → Update consent
```
**31 endpoints**

---

## Route 24: `tracking.router.ts`
**Prefix:** `/api/v1/tracking`

```
GET    /impression                        → Record impression
GET    /click/:adId                       → Record click & redirect
POST   /conversion                        → S2S conversion postback
POST   /video                             → Video tracking event
POST   /clip-interaction                  → Clip interaction event
```
**5 endpoints**

---

## Route 25: `pixel.router.ts`
**Prefix:** `/api/v1/p`

```
POST   /:pixelUuid/event                 → Record pixel event
GET    /:pixelUuid/pixel.gif             → Image pixel fallback
```
**2 endpoints**

---

## Route 26: `serve.router.ts`
**Prefix:** `/api/v1/serve`

```
GET    /zone/:zoneId                      → Ad decision for zone
GET    /vast/:adId                        → VAST XML for video
GET    /native/:zoneId                    → Native ad JSON
```
**3 endpoints**

---

## Route 27: `ref.router.ts`
**Prefix:** `/api/v1/ref`

```
GET    /:code                             → Track referral & redirect
```
**1 endpoint**

---

## Route 28: `webhooks.router.ts`
**Prefix:** `/api/v1/webhooks`

```
POST   /stripe                            → Stripe webhook
POST   /paypal                            → PayPal webhook
POST   /coinbase                          → Coinbase webhook
```
**3 endpoints**

---

## Route 29: `telegram.router.ts`
**Prefix:** `/api/v1/telegram/mini-apps`

```
GET    /                                  → List mini apps
POST   /                                  → Register mini app
GET    /:id                               → Mini app detail
PATCH  /:id                               → Update mini app
DELETE /:id                               → Soft-delete mini app
GET    /:id/analytics                     → Mini app analytics
POST   /:id/sessions                      → Record session
PATCH  /:id/sessions/:sId                 → Update session
POST   /:id/events                        → Record event
```
**9 endpoints**

---

## Route 30: `rtb.router.ts`
**Prefix:** `/api/v1/rtb`

```
POST   /bid-request                       → OpenRTB bid request
POST   /bid-response                      → Process bid response
```
**2 endpoints**

---

---

# ADMIN ROUTES

---

## Route 31: `admin.users.router.ts`
**Prefix:** `/api/v1/admin/users`

```
GET    /                                  → List all users
GET    /:id                               → User detail
PATCH  /:id                               → Update role/status
PATCH  /:id/status                        → Activate/suspend/close
DELETE /:id                               → Soft-delete
POST   /:id/suspend                       → Suspend account
POST   /:id/reactivate                    → Reactivate account
```
**7 endpoints**

---

## Route 32: `admin.kyc.router.ts`
**Prefix:** `/api/v1/admin/kyc`

```
GET    /                                  → List KYC submissions
GET    /:id                               → Submission detail
POST   /:id/approve                       → Approve KYC
POST   /:id/reject                        → Reject KYC
GET    /:id/documents/:docId              → View document
POST   /:id/documents/:docId/verify       → Verify document
POST   /:id/documents/:docId/reject       → Reject document
```
**7 endpoints**

---

## Route 33: `admin.campaigns.router.ts`
**Prefix:** `/api/v1/admin/campaigns`

```
GET    /                                  → List all campaigns
POST   /:id/approve                       → Approve campaign
POST   /:id/reject                        → Reject campaign
```
**3 endpoints**

---

## Route 34: `admin.ads.router.ts`
**Prefix:** `/api/v1/admin/ads`

```
GET    /                                  → List all ads
POST   /:id/approve                       → Approve ad
POST   /:id/reject                        → Reject ad
```
**3 endpoints**

---

## Route 35: `admin.sites.router.ts`
**Prefix:** `/api/v1/admin/sites`

```
GET    /                                  → List all sites
POST   /:id/approve                       → Approve site
POST   /:id/reject                        → Reject site
POST   /:id/suspend                       → Suspend site
```
**4 endpoints**

---

## Route 36: `admin.zones.router.ts`
**Prefix:** `/api/v1/admin/zones`

```
GET    /:id/ads                           → List ads in zone
POST   /:id/ads                           → Assign ad to zone
DELETE /:id/ads/:adId                     → Remove ad from zone
```
**3 endpoints**

---

## Route 37: `admin.fraud.router.ts`
**Prefix:** `/api/v1/admin/fraud`

```
GET    /events                            → List fraud events
GET    /events/:id                        → Event detail
GET    /rules                             → List anti-fraud rules
POST   /rules                             → Create rule
PATCH  /rules/:id                         → Update rule
DELETE /rules/:id                         → Delete rule
GET    /publisher-records                 → List publisher fraud records
POST   /publisher-records                 → Create fraud record
PATCH  /publisher-records/:id             → Update/resolve record
GET    /notifications                     → Fraud notifications
```
**10 endpoints**

---

## Route 38: `admin.exchanges.router.ts`
**Prefix:** `/api/v1/admin/exchanges`

```
GET    /                                  → List exchanges
POST   /                                  → Create exchange
GET    /:id                               → Exchange detail
PATCH  /:id                               → Update exchange
DELETE /:id                               → Remove exchange
GET    /:id/stats                         → Bid stats
```
**6 endpoints**

---

## Route 39: `admin.analytics.router.ts`
**Prefix:** `/api/v1/admin/analytics`

```
GET    /overview                          → Platform summary
GET    /daily                             → Platform daily stats
GET    /revenue                           → Revenue breakdown
GET    /top-campaigns                     → Top campaigns
GET    /top-publishers                    → Top publishers
```
**5 endpoints**

---

## Route 40: `admin.payouts.router.ts`
**Prefix:** `/api/v1/admin`

```
GET    /payouts                           → List all payouts
PATCH  /payouts/:id                       → Update payout status
POST   /invoices                          → Generate invoice
PATCH  /invoices/:id                      → Update invoice status
```
**4 endpoints**

---

## Route 41: `admin.referrals.router.ts`
**Prefix:** `/api/v1/admin/referrals`

```
GET    /                                  → List all referrals
PATCH  /conversions/:id                   → Update conversion
GET    /payouts                           → List referral payouts
PATCH  /payouts/:id                       → Process payout
```
**4 endpoints**

---

## Route 42: `admin.content.router.ts`
**Prefix:** `/api/v1/admin`

```
POST   /ad-formats                        → Create ad format
PATCH  /ad-formats/:id                    → Update ad format
DELETE /ad-formats/:id                    → Delete ad format
GET    /ad-sizes                          → List ad sizes
POST   /ad-sizes                          → Create ad size
PATCH  /ad-sizes/:id                      → Update ad size
DELETE /ad-sizes/:id                      → Delete ad size
POST   /tags                              → Create tag
PATCH  /tags/:id                          → Update tag
DELETE /tags/:id                          → Delete tag
POST   /ad-formats/:id/tags               → Assign tags to format
DELETE /ad-formats/:id/tags/:tagId        → Remove tag
POST   /categories                        → Create category
PATCH  /categories/:id                    → Update category
DELETE /categories/:id                    → Delete category
POST   /geo/countries                     → Add country
PATCH  /geo/countries/:code               → Update country
POST   /geo/regions                       → Add region
PATCH  /geo/regions/:id                   → Update region
POST   /languages                         → Add language
PATCH  /languages/:id                     → Update language
DELETE /languages/:id                     → Deactivate language
POST   /traffic-sources                   → Create traffic source
PATCH  /traffic-sources/:id               → Update traffic source
DELETE /traffic-sources/:id               → Delete traffic source
POST   /faq                               → Create FAQ
PATCH  /faq/:id                           → Update FAQ
DELETE /faq/:id                           → Delete FAQ
POST   /testimonials                      → Create testimonial
PATCH  /testimonials/:id                  → Update testimonial
DELETE /testimonials/:id                  → Delete testimonial
POST   /case-studies                      → Create case study
PATCH  /case-studies/:id                  → Update case study
DELETE /case-studies/:id                  → Delete case study
POST   /trusted-publishers                → Add trusted publisher
PATCH  /trusted-publishers/:id            → Update
DELETE /trusted-publishers/:id            → Remove
POST   /careers                           → Create job posting
PATCH  /careers/:id                       → Update posting
DELETE /careers/:id                       → Delete posting
GET    /newsletter/subscribers            → List subscribers
DELETE /newsletter/subscribers/:id        → Remove subscriber
GET    /account-deactivations             → List deactivation events
```
**43 endpoints**

---

## Route 43: `admin.dynamic.router.ts`
**Prefix:** `/api/v1/admin/dynamic`

```
POST   /tokens                            → Create custom token
PATCH  /tokens/:id                        → Update token
```
**2 endpoints**

---

## Route 44: `admin.distribution.router.ts`
**Prefix:** `/api/v1/admin`

```
POST   /distribution-networks             → Create network
PATCH  /distribution-networks/:id         → Update network
DELETE /distribution-networks/:id         → Delete network
POST   /msn/properties                    → Create MSN property
PATCH  /msn/properties/:id                → Update MSN property
DELETE /msn/properties/:id                → Delete MSN property
```
**6 endpoints**

---

## Route 45: `admin.clip.router.ts`
**Prefix:** `/api/v1/admin/clip`

```
POST   /templates                         → Create template
PATCH  /templates/:id                     → Update template
DELETE /templates/:id                     → Delete template
POST   /music                             → Add music track
PATCH  /music/:id                         → Update track
DELETE /music/:id                         → Remove track
```
**6 endpoints**

---

## Route 46: `admin.oem.router.ts`
**Prefix:** `/api/v1/admin/oem`

```
POST   /manufacturers                     → Add OEM partner
PATCH  /manufacturers/:id                 → Update OEM
DELETE /manufacturers/:id                 → Remove OEM
POST   /placements                        → Add placement
PATCH  /placements/:id                    → Update placement
DELETE /placements/:id                    → Remove placement
POST   /apps                              → Add OEM app
PATCH  /apps/:id                          → Update OEM app
POST   /device-models                     → Add device model
PATCH  /device-models/:id                 → Update device model
```
**10 endpoints**

---

## Route 47: `admin.telegram.router.ts`
**Prefix:** `/api/v1/admin/telegram/mini-apps`

```
GET    /                                  → List all mini apps
POST   /:id/approve                       → Approve mini app
POST   /:id/reject                        → Reject mini app
POST   /:id/suspend                       → Suspend mini app
```
**4 endpoints**

---

## Route 48: `admin.settings.router.ts`
**Prefix:** `/api/v1/admin`

```
GET    /settings                          → List platform settings
GET    /settings/:key                     → Get setting by key
PATCH  /settings/:key                     → Update setting
POST   /settings                          → Create setting
GET    /activity-log                      → Platform activity log
GET    /activity-log/settings             → Log settings
PATCH  /activity-log/settings             → Update log settings
```
**7 endpoints**

---

# Route Summary

| # | Router File | Prefix | Endpoints |
|---|-------------|--------|-----------|
| 1 | `auth.router.ts` | `/api/v1/auth` | 18 |
| 2 | `users.router.ts` | `/api/v1/users` | 22 |
| 3 | `kyc.router.ts` | `/api/v1/users/me/kyc` | 6 |
| 4 | `campaigns.router.ts` | `/api/v1/campaigns` | 51 |
| 5 | `ads.router.ts` | `/api/v1` | 16 |
| 6 | `direct-campaigns.router.ts` | `/api/v1/direct-campaigns` | 28 |
| 7 | `sites.router.ts` | `/api/v1/sites` | 10 |
| 8 | `zones.router.ts` | `/api/v1/zones` | 4 |
| 9 | `video.router.ts` | `/api/v1/video` | 10 |
| 10 | `dynamic.router.ts` | `/api/v1/dynamic` | 19 |
| 11 | `audiences.router.ts` | `/api/v1/audiences` | 21 |
| 12 | `clip.router.ts` | `/api/v1/clip` | 5 |
| 13 | `oem.router.ts` | `/api/v1/oem` | 5 |
| 14 | `analytics.router.ts` | `/api/v1/analytics` | 13 |
| 15 | `wallet.router.ts` | `/api/v1/wallet` | 8 |
| 16 | `payouts.router.ts` | `/api/v1/payouts` | 3 |
| 17 | `invoices.router.ts` | `/api/v1/invoices` | 3 |
| 18 | `referrals.router.ts` | `/api/v1/referrals` | 9 |
| 19 | `notifications.router.ts` | `/api/v1/notifications` | 7 |
| 20 | `support.router.ts` | `/api/v1/support/tickets` | 5 |
| 21 | `api-keys.router.ts` | `/api/v1/api-keys` | 5 |
| 22 | `pricing.router.ts` | `/api/v1/pricing-plans` | 2 |
| 23 | `public.router.ts` | `/api/v1` | 31 |
| 24 | `tracking.router.ts` | `/api/v1/tracking` | 5 |
| 25 | `pixel.router.ts` | `/api/v1/p` | 2 |
| 26 | `serve.router.ts` | `/api/v1/serve` | 3 |
| 27 | `ref.router.ts` | `/api/v1/ref` | 1 |
| 28 | `webhooks.router.ts` | `/api/v1/webhooks` | 3 |
| 29 | `telegram.router.ts` | `/api/v1/telegram/mini-apps` | 9 |
| 30 | `rtb.router.ts` | `/api/v1/rtb` | 2 |
| 31 | `admin.users.router.ts` | `/api/v1/admin/users` | 7 |
| 32 | `admin.kyc.router.ts` | `/api/v1/admin/kyc` | 7 |
| 33 | `admin.campaigns.router.ts` | `/api/v1/admin/campaigns` | 3 |
| 34 | `admin.ads.router.ts` | `/api/v1/admin/ads` | 3 |
| 35 | `admin.sites.router.ts` | `/api/v1/admin/sites` | 4 |
| 36 | `admin.zones.router.ts` | `/api/v1/admin/zones` | 3 |
| 37 | `admin.fraud.router.ts` | `/api/v1/admin/fraud` | 10 |
| 38 | `admin.exchanges.router.ts` | `/api/v1/admin/exchanges` | 6 |
| 39 | `admin.analytics.router.ts` | `/api/v1/admin/analytics` | 5 |
| 40 | `admin.payouts.router.ts` | `/api/v1/admin` | 4 |
| 41 | `admin.referrals.router.ts` | `/api/v1/admin/referrals` | 4 |
| 42 | `admin.content.router.ts` | `/api/v1/admin` | 43 |
| 43 | `admin.dynamic.router.ts` | `/api/v1/admin/dynamic` | 2 |
| 44 | `admin.distribution.router.ts` | `/api/v1/admin` | 6 |
| 45 | `admin.clip.router.ts` | `/api/v1/admin/clip` | 6 |
| 46 | `admin.oem.router.ts` | `/api/v1/admin/oem` | 10 |
| 47 | `admin.telegram.router.ts` | `/api/v1/admin/telegram/mini-apps` | 4 |
| 48 | `admin.settings.router.ts` | `/api/v1/admin` | 7 |
| | **TOTAL** | **48 routers** | **~430 endpoints** |
