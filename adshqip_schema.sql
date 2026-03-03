-- ============================================================================
-- Adshqip — Albanian Ad Network
-- Modern Database Schema
-- Generated from enterads_alam.sql (DJAX/OpenX legacy) + Landing Page Features
-- ============================================================================
-- Database: adshqip
-- Engine:   InnoDB (all tables)
-- Charset:  utf8mb4 / utf8mb4_unicode_ci
-- ============================================================================
--
-- TABLE NAME MAPPING (old → new):
-- ─────────────────────────────────────────────────────────────────────────────
--  ox_clients / djax_billing_information     → aq_users, aq_user_profiles
--  ox_banners / djax_additional_banners      → aq_ads, aq_ad_creatives
--  ox_campaigns                              → aq_campaigns
--  ox_zones / djax_app_zone_assoc            → aq_zones, aq_zone_ad_assoc
--  djax_3rd_party_ad_exchange                → aq_ad_exchanges
--  djax_activity_log                         → aq_activity_log
--  djax_ad_fraud_click/impression            → aq_fraud_events
--  djax_antifraud_impressions                → aq_antifraud_rules
--  djax_ad_zone_click / _impression          → aq_clicks, aq_impressions
--  djax_ad_zone_click_count / _imp_count     → aq_stats_daily
--  djax_browsers_stats / djax_clients_stats  → aq_stats_browser, aq_stats_geo
--  djax_conf_banner_sizes                    → aq_ad_sizes
--  djax_campaign_category_assoc              → aq_campaign_category
--  djax_campaign_traffic_source              → aq_traffic_sources
--  djax_ad_requests_rtb_buyers               → aq_rtb_bid_requests
--  djax_ad_response_rtb_buyers               → aq_rtb_bid_responses
--  djax_banner_vast_events                   → aq_vast_events
--  djax_admin_payment_details                → aq_payouts
--  djax_careers                              → aq_careers
--  djax_app_configurations                   → aq_platform_settings
--  djax_allowcrecord / djax_antifruadrecord  → aq_publisher_fraud_records
--  Geo_region / GeoIP2_Country_Locations_en  → aq_geo_countries, aq_geo_regions
--  djax_adv_pub_notification_settings        → aq_notification_settings
--  djax_activity_log_settings                → aq_log_settings
--  djax_app_category_assoc                   → aq_site_categories
--  (NEW) AdGate Self-Serve platform           → aq_direct_campaigns,
--                                              aq_direct_campaign_creatives,
--                                              aq_direct_campaign_targeting,
--                                              aq_direct_campaign_zones,
--                                              aq_direct_campaign_stats
--  (NEW) from landing page                   → aq_ad_formats, aq_pricing_plans,
--                                              aq_newsletters, aq_faq,
--                                              aq_testimonials, aq_support_tickets,
--                                              aq_cookie_consents, aq_languages,
--                                              aq_api_keys, aq_mobile_devices,
--                                              aq_trusted_publishers,
--                                              aq_notifications, aq_sessions
--  (NEW) Telegram Mini Apps                   → aq_telegram_mini_apps,
--                                              aq_telegram_mini_app_sessions,
--                                              aq_telegram_mini_app_events
--  (NEW) KYC Verification                     → aq_kyc_verifications,
--                                              aq_kyc_documents
--  (NEW) MultiTag (ad format tagging)         → aq_tags, aq_ad_format_tags
--  (NEW) Referral Program                     → aq_referral_links,
--                                              aq_referral_conversions,
--                                              aq_referral_payouts
--  (NEW) Affiliates (admin-created)           → aq_affiliates,
--                                              aq_affiliate_images,
--                                              aq_affiliate_countries,
--                                              aq_affiliate_ad_formats
-- ============================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES utf8mb4;

-- ============================================================================
-- 1. USERS & AUTHENTICATION
-- ============================================================================

CREATE TABLE IF NOT EXISTS `aq_users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(255) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('admin','advertiser','publisher','manager') NOT NULL DEFAULT 'advertiser',
  `status` ENUM('active','inactive','suspended','pending_verification','closed') NOT NULL DEFAULT 'pending_verification',
  `email_verified_at` DATETIME DEFAULT NULL,
  `two_factor_enabled` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Landing page: 2FA security',
  `two_factor_secret` VARCHAR(255) DEFAULT NULL,
  `preferred_language` VARCHAR(5) NOT NULL DEFAULT 'en' COMMENT 'EN, SQ, IT, DE from language selector',
  `theme_preference` ENUM('light','dark','system') NOT NULL DEFAULT 'system',
  `timezone` VARCHAR(50) NOT NULL DEFAULT 'Europe/Tirane',
  `last_login_at` DATETIME DEFAULT NULL,
  `last_login_ip` VARCHAR(45) DEFAULT NULL,
  `kyc_status` ENUM('not_started','pending','in_review','approved','rejected','expired') NOT NULL DEFAULT 'not_started' COMMENT 'Denormalized from aq_kyc_verifications',
  `kyc_level` ENUM('none','basic','standard','enhanced') NOT NULL DEFAULT 'none' COMMENT 'Current verified KYC tier',
  `kyc_verified_at` DATETIME DEFAULT NULL COMMENT 'When KYC was last approved',
  `telegram_user_id` BIGINT DEFAULT NULL COMMENT 'Linked Telegram numeric user ID',
  `telegram_username` VARCHAR(100) DEFAULT NULL COMMENT 'Linked Telegram @username',
  `telegram_linked_at` DATETIME DEFAULT NULL COMMENT 'When Telegram account was linked',
  `referral_code` VARCHAR(32) DEFAULT NULL COMMENT 'This user''s own unique referral code',
  `referred_by` BIGINT UNSIGNED DEFAULT NULL COMMENT 'FK → aq_users: who referred this user',
  `referred_at` DATETIME DEFAULT NULL COMMENT 'When the referral signup occurred',
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_email` (`email`),
  KEY `idx_role` (`role`),
  KEY `idx_status` (`status`),
  KEY `idx_kyc_status` (`kyc_status`),
  UNIQUE KEY `uk_telegram_user_id` (`telegram_user_id`),
  UNIQUE KEY `uk_referral_code` (`referral_code`),
  KEY `idx_referred_by` (`referred_by`),
  CONSTRAINT `fk_user_referred_by` FOREIGN KEY (`referred_by`) REFERENCES `aq_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- old: ox_clients + djax_billing_information merged
CREATE TABLE IF NOT EXISTS `aq_user_profiles` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `first_name` VARCHAR(100) NOT NULL DEFAULT '',
  `last_name` VARCHAR(100) NOT NULL DEFAULT '',
  `company_name` VARCHAR(255) DEFAULT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `address_line1` VARCHAR(255) DEFAULT NULL,
  `address_line2` VARCHAR(255) DEFAULT NULL,
  `city` VARCHAR(100) DEFAULT NULL,
  `state_region` VARCHAR(100) DEFAULT NULL,
  `postal_code` VARCHAR(20) DEFAULT NULL,
  `country_code` CHAR(2) DEFAULT 'AL',
  `vat_number` VARCHAR(50) DEFAULT NULL,
  `website_url` VARCHAR(500) DEFAULT NULL,
  `avatar_url` VARCHAR(500) DEFAULT NULL,
  `bio` TEXT DEFAULT NULL,
  `balance` DECIMAL(12,4) NOT NULL DEFAULT 0.0000 COMMENT 'old: dj_cur_balance',
  `currency` VARCHAR(3) NOT NULL DEFAULT 'EUR',
  `payment_method` ENUM('paypal','wire_transfer','crypto','payoneer') DEFAULT NULL,
  `payment_details` JSON DEFAULT NULL,
  `is_default` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'old: dj_is_default',
  `is_denied` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'old: dj_is_denied',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_id` (`user_id`),
  KEY `idx_country` (`country_code`),
  CONSTRAINT `fk_profile_user` FOREIGN KEY (`user_id`) REFERENCES `aq_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `aq_sessions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` VARCHAR(500) DEFAULT NULL,
  `browser` VARCHAR(50) DEFAULT NULL,
  `os` VARCHAR(50) DEFAULT NULL,
  `device_type` ENUM('desktop','mobile','tablet') DEFAULT NULL,
  `expires_at` DATETIME NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_token` (`token`),
  KEY `idx_user` (`user_id`),
  KEY `idx_expires` (`expires_at`),
  CONSTRAINT `fk_session_user` FOREIGN KEY (`user_id`) REFERENCES `aq_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 2. AD FORMATS (from landing page: Popunder, Native, Interstitial, Push, etc.)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `aq_ad_formats` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(50) NOT NULL COMMENT 'popunder, native_feed, interstitial, in_page_push, text_banner, native_video, rich_media',
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `category` ENUM('high_impact','user_friendly','premium') NOT NULL DEFAULT 'user_friendly',
  `ecpm_avg` DECIMAL(8,2) DEFAULT NULL COMMENT 'Average eCPM shown on landing page',
  `fill_rate_avg` DECIMAL(5,2) DEFAULT NULL COMMENT 'Average fill rate %',
  `ctr_avg` DECIMAL(5,2) DEFAULT NULL,
  `supports_mobile` TINYINT(1) NOT NULL DEFAULT 1,
  `supports_desktop` TINYINT(1) NOT NULL DEFAULT 1,
  `supports_amp` TINYINT(1) NOT NULL DEFAULT 0,
  `gdpr_ready` TINYINT(1) NOT NULL DEFAULT 1,
  `performance_rating` DECIMAL(2,1) DEFAULT NULL COMMENT '1.0-5.0 star rating',
  `is_new` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Rich Media has NEW badge',
  `status` ENUM('active','beta','deprecated') NOT NULL DEFAULT 'active',
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed the 14 ad formats from the landing page (incl. 3 adshqipAI formats)
INSERT INTO `aq_ad_formats` (`slug`, `name`, `description`, `category`, `ecpm_avg`, `fill_rate_avg`, `ctr_avg`, `supports_mobile`, `supports_desktop`, `supports_amp`, `gdpr_ready`, `performance_rating`, `is_new`, `status`, `sort_order`) VALUES
('popunder',       'Popunder',              'High CPM, 95%+ fill rate. Monetize every visit with compliant, on-click windows.',                                     'high_impact',    8.50,  98.00, NULL, 1, 1, 0, 1, 4.0, 0, 'active', 1),
('native_feed',    'Native Feed',           'Content-like cards (à la Taboola/Outbrain). High CTR, brand-safe.',                                                     'user_friendly',  5.20,  NULL,  2.80, 1, 1, 1, 1, 5.0, 0, 'active', 2),
('interstitial',   'Layer / Interstitial',  'Fullscreen or modal layer between views with smart frequency capping.',                                                  'high_impact',   12.30,  92.00, NULL, 1, 1, 0, 1, 4.5, 0, 'active', 3),
('in_page_push',   'In-Page Push',          'Notification-style creatives inside the page. Lightweight, non-intrusive.',                                              'user_friendly',  3.80,  96.00, NULL, 1, 1, 1, 1, 4.0, 0, 'active', 4),
('text_banner',    'Text & Smart Banners',  'Contextual text units and adaptive banners with tiny payloads. AMP compatible.',                                         'user_friendly',  NULL,  NULL,  NULL, 1, 1, 1, 1, 4.0, 0, 'active', 5),
('native_video',   'Native Video',          'Muted autoplay on view, with captions and click-through CTA. High engagement.',                                          'premium',        NULL,  NULL,  NULL, 1, 1, 0, 1, 4.5, 0, 'active', 6),
('rich_media',     'Rich Media',            'Interactive ad units with advanced animations, 3D elements, mini-games, and product galleries.',                          'premium',        NULL,  NULL,  NULL, 1, 1, 0, 1, 5.0, 1, 'active', 7),
('motion',         'Motion Ads',            'Animated display ads with eye-catching motion effects. Higher engagement than static banners.',                                    'premium',        6.50,  NULL,  NULL, 1, 1, 0, 1, 4.5, 1, 'active', 8),
('motion_studio',  'Motion Ads Studio',     'Advanced motion ad builder with timeline editor, transitions, and custom animations. Create pro-level animated creatives.',          'premium',        7.20,  NULL,  NULL, 1, 1, 0, 1, 5.0, 1, 'active', 9),
('carousel',       'Carousel Ads',          'Multi-card swipeable format showcasing multiple products, images, or messages in a single ad unit. Ideal for e-commerce.',           'user_friendly',  5.80,  NULL,  3.20, 1, 1, 0, 1, 4.5, 1, 'active', 10),
('app_promotion',       'App Promotion',              'Dedicated format for mobile app installs and re-engagement. Deep-links to app stores with install tracking and SKAN support.',        'high_impact',    9.00,  NULL,  NULL, 1, 0, 0, 1, 4.5, 1, 'active', 11),
('adshqipai_ad_maker',  'adshqipAI Ad Maker',         'Generate complete static ad creatives instantly using adshqipAI. Describe your product and our AI builds the headline, copy, and visual.',  'premium',       10.00,  NULL,  NULL, 1, 1, 0, 1, 5.0, 1, 'active', 12),
('adshqipai_motion',    'adshqipAI Motion Ads',       'AI-generated animated motion ads. adshqipAI selects the best motion template and animates your brand assets automatically.',              'premium',       11.50,  NULL,  NULL, 1, 1, 0, 1, 5.0, 1, 'active', 13),
('adshqipai_motion_prompt', 'adshqipAI Motion + Prompt', 'Full prompt-driven motion ad creation. Describe the scene, tone, and message — adshqipAI generates a custom animated ad from scratch.', 'premium',       13.00,  NULL,  NULL, 1, 1, 0, 1, 5.0, 1, 'active', 14),
('click_to_watch',         'Click-to-Watch Video',      'User-initiated video ads with branding overlays and end cards. Charged per completed view (CPV). High engagement, brand-safe.', 'premium',       14.00,  NULL,  NULL, 1, 1, 0, 1, 5.0, 1, 'active', 15),
('clip',                   'Clip Ads',                  'Short-form vertical video ads (5-60s, 9:16). TikTok/Reels/Shorts-style immersive, swipeable, sound-on experience with swipe-up CTA.', 'high_impact',   15.00,  NULL,  4.50, 1, 0, 0, 1, 5.0, 1, 'active', 16);

-- old: djax_conf_banner_sizes
CREATE TABLE IF NOT EXISTS `aq_ad_sizes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL COMMENT 'e.g. Leaderboard, Medium Rectangle',
  `width` INT UNSIGNED NOT NULL,
  `height` INT UNSIGNED NOT NULL,
  `format_id` INT UNSIGNED DEFAULT NULL,
  `is_responsive` TINYINT(1) NOT NULL DEFAULT 0,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_dimensions` (`width`, `height`),
  KEY `idx_format` (`format_id`),
  CONSTRAINT `fk_size_format` FOREIGN KEY (`format_id`) REFERENCES `aq_ad_formats` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 3. CAMPAIGNS & ADS
-- ============================================================================

-- old: ox_campaigns
CREATE TABLE IF NOT EXISTS `aq_campaigns` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `advertiser_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `format_id` INT UNSIGNED DEFAULT NULL,
  `marketing_objective` ENUM(
    'brand_awareness',
    'reach',
    'traffic',
    'engagement',
    'app_installs',
    'video_views',
    'lead_generation',
    'conversions',
    'catalog_sales',
    'store_visits'
  ) NOT NULL DEFAULT 'traffic' COMMENT 'Campaign marketing objective — drives optimization strategy and recommended bid type',
  `campaign_type` ENUM('cpm','cpc','cpa','cpv','cpv_ctw') NOT NULL DEFAULT 'cpm' COMMENT 'Performance Marketing: CPA, CPC, CPM, CPV Click-to-Watch',
  `status` ENUM('draft','pending_review','active','paused','completed','rejected') NOT NULL DEFAULT 'draft',
  `revenue_type` TINYINT NOT NULL DEFAULT 1 COMMENT 'old: revenue_type',
  `bid_amount` DECIMAL(10,4) NOT NULL DEFAULT 0.0000 COMMENT 'old: revenue',
  `daily_budget` DECIMAL(12,4) DEFAULT NULL COMMENT 'old: dj_daily_budget',
  `total_budget` DECIMAL(12,4) DEFAULT NULL COMMENT 'old: dj_campaign_budget',
  `remaining_budget` DECIMAL(12,4) DEFAULT NULL COMMENT 'old: dj_campaign_remain_budget',
  `currency` VARCHAR(3) NOT NULL DEFAULT 'EUR',
  `start_date` DATETIME DEFAULT NULL,
  `end_date` DATETIME DEFAULT NULL,
  `frequency_cap` INT DEFAULT NULL COMMENT 'Max impressions per user per day',
  `frequency_cap_period` ENUM('hour','day','week','month','lifetime') DEFAULT 'day',
  `targeting_geo` JSON DEFAULT NULL COMMENT 'Country/region targeting, Balkans focus',
  `targeting_device` JSON DEFAULT NULL COMMENT 'desktop, mobile, tablet',
  `targeting_browser` JSON DEFAULT NULL,
  `targeting_os` JSON DEFAULT NULL,
  `targeting_language` JSON DEFAULT NULL,
  `targeting_schedule` JSON DEFAULT NULL COMMENT 'Day-parting schedule',
  `targeting_retargeting` VARCHAR(50) DEFAULT NULL COMMENT 'old: dj_is_retargeted (MOBILE etc.)',
  `blocked_domains` TEXT DEFAULT NULL COMMENT 'old: bdomain_value',
  `blocked_categories` VARCHAR(500) DEFAULT NULL COMMENT 'old: bcat_value',
  -- Distribution network & MSN exclusive
  `distribution_mode` ENUM('all_networks','selected_networks','msn_exclusive') NOT NULL DEFAULT 'all_networks' COMMENT 'Where ads are delivered: all, selected, or MSN-only',
  `msn_exclusive` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Quick toggle: Run on MSN exclusively (overrides distribution_mode)',
  `msn_enabled` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Include MSN network in distribution (when distribution_mode=selected_networks)',
  `msn_bid_adjustment` DECIMAL(5,2) DEFAULT NULL COMMENT 'Bid modifier % for MSN placements (e.g. +20.00 for 20% higher bid on MSN)',
  -- Campaign Dynamics
  `dynamic_creative_enabled` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Enable Dynamic Creative Optimization — assemble ads from asset library based on rules',
  `dynamic_tokens_enabled` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Enable dynamic token replacement ({city}, {device}, {weather}, {countdown}, etc.)',
  `dynamic_product_feed_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'FK → aq_dynamic_product_feeds for catalog-driven dynamic ads',
  `dynamic_landing_page_enabled` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Enable per-segment dynamic landing page URL rules',
  `dynamic_budget_rules_enabled` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Enable automated budget/bid rules based on performance triggers',
  -- Custom Audience targeting
  `audience_targeting_mode` ENUM('none','include','exclude','both') NOT NULL DEFAULT 'none' COMMENT 'How custom audiences are applied to this campaign',
  `audience_expansion_enabled` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Allow platform to expand reach via lookalike modeling',
  `audience_expansion_ratio` DECIMAL(3,2) DEFAULT 1.00 COMMENT 'Lookalike expansion ratio 1-10 (1=most similar, 10=broadest)',
  -- OEM (Original Equipment Manufacturer) targeting
  `oem_enabled` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Enable OEM distribution — deliver ads via device manufacturer placements (preinstalls, setup wizards, app stores)',
  `oem_targeting_mode` ENUM('none','all_oems','selected_oems') NOT NULL DEFAULT 'none' COMMENT 'Target all OEM partners or select specific manufacturers',
  `oem_bid_adjustment` DECIMAL(5,2) DEFAULT NULL COMMENT 'Bid modifier % for OEM placements (e.g. +15.00 for 15% higher bid on OEM inventory)',
  `oem_placement_types` JSON DEFAULT NULL COMMENT 'Preferred OEM placements: ["setup_wizard","app_store","lockscreen","notification_tray","preinstall"]',
  `weight` INT NOT NULL DEFAULT 1 COMMENT 'old: campaign_weight',
  `admin_approved` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'old: dj_admin_approve',
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_advertiser` (`advertiser_id`),
  KEY `idx_status` (`status`),
  KEY `idx_format` (`format_id`),
  KEY `idx_dates` (`start_date`, `end_date`),
  CONSTRAINT `fk_campaign_advertiser` FOREIGN KEY (`advertiser_id`) REFERENCES `aq_users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_campaign_format` FOREIGN KEY (`format_id`) REFERENCES `aq_ad_formats` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- old: ox_banners + djax_additional_banners merged
CREATE TABLE IF NOT EXISTS `aq_ads` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `campaign_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL COMMENT 'old: description',
  `ad_type` ENUM('image','html','video','text','rich_media','native','vast','motion','motion_studio','carousel','app_promotion','adshqipai_ad_maker','adshqipai_motion','adshqipai_motion_prompt','clip') NOT NULL DEFAULT 'image' COMMENT 'old: storagetype',
  `status` ENUM('active','paused','pending_review','rejected','archived') NOT NULL DEFAULT 'pending_review',
  `destination_url` VARCHAR(2000) NOT NULL COMMENT 'old: url',
  `display_url` VARCHAR(500) DEFAULT NULL,
  `headline` VARCHAR(255) DEFAULT NULL COMMENT 'For native/text ads',
  `headline_dki` VARCHAR(255) DEFAULT NULL COMMENT 'DKI template e.g. "Buy {keyword} Today" — {keyword} replaced at serve time',
  `headline_dki_default` VARCHAR(255) DEFAULT NULL COMMENT 'Fallback text when no keyword matches, e.g. "Buy Now"',
  `body_text` TEXT DEFAULT NULL COMMENT 'old: bannertext',
  `body_text_dki` TEXT DEFAULT NULL COMMENT 'DKI-enabled body copy with {keyword} placeholders',
  `call_to_action` VARCHAR(50) DEFAULT NULL COMMENT 'e.g. Shiko Tani, Mëso më shumë',
  `sponsored_label` VARCHAR(50) DEFAULT 'sponsored' COMMENT 'From native feed demo',
  -- Campaign branding
  `brand_name` VARCHAR(100) DEFAULT NULL COMMENT 'Advertiser brand name shown on ad',
  `brand_logo_url` VARCHAR(500) DEFAULT NULL COMMENT 'Brand logo image URL',
  `brand_tagline` VARCHAR(255) DEFAULT NULL COMMENT 'Short brand slogan shown below headline',
  `brand_color_primary` CHAR(7) DEFAULT NULL COMMENT 'Hex color e.g. #FF5733 for brand theming',
  `brand_color_secondary` CHAR(7) DEFAULT NULL COMMENT 'Secondary hex color for gradient/border',
  -- Click-to-Watch (CTW) video settings
  `ctw_enabled` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Enable Click-to-Watch: user clicks to start video, charged on view',
  `ctw_thumbnail_url` VARCHAR(500) DEFAULT NULL COMMENT 'Thumbnail image shown before user clicks to watch',
  `ctw_min_watch_seconds` INT UNSIGNED DEFAULT 5 COMMENT 'Min seconds user must watch before counted as paid view',
  `ctw_skip_after_seconds` INT UNSIGNED DEFAULT NULL COMMENT 'Allow skip after N seconds (NULL = no skip)',
  `ctw_autoplay` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0 = user must click, 1 = autoplay muted with CTA to unmute',
  `ctw_muted_autoplay` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Start muted when autoplaying',
  -- Video branding overlay
  `video_brand_logo_url` VARCHAR(500) DEFAULT NULL COMMENT 'Logo watermark overlaid on video player',
  `video_brand_logo_position` ENUM('top_left','top_right','bottom_left','bottom_right') DEFAULT 'bottom_right' COMMENT 'Position of logo overlay on video',
  `video_brand_logo_opacity` DECIMAL(3,2) DEFAULT 0.80 COMMENT 'Logo opacity 0.00-1.00',
  `video_brand_intro_url` VARCHAR(500) DEFAULT NULL COMMENT 'Short brand intro bumper video URL (3-5s)',
  `video_brand_intro_duration` INT UNSIGNED DEFAULT NULL COMMENT 'Intro bumper duration in seconds',
  -- End Card settings
  `end_card_enabled` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Show end card after video completes',
  `end_card_type` ENUM('static_image','html','cta_button','product_feed','custom') DEFAULT 'cta_button' COMMENT 'Type of end card displayed',
  `end_card_image_url` VARCHAR(500) DEFAULT NULL COMMENT 'End card static image URL',
  `end_card_html` TEXT DEFAULT NULL COMMENT 'Custom HTML for end card (sanitized)',
  `end_card_headline` VARCHAR(255) DEFAULT NULL COMMENT 'End card headline text',
  `end_card_body` TEXT DEFAULT NULL COMMENT 'End card description text',
  `end_card_cta_text` VARCHAR(50) DEFAULT NULL COMMENT 'End card CTA button text e.g. Shiko Më Shumë',
  `end_card_cta_url` VARCHAR(2000) DEFAULT NULL COMMENT 'End card CTA click-through URL (defaults to destination_url)',
  `end_card_cta_color` CHAR(7) DEFAULT NULL COMMENT 'CTA button hex color',
  `end_card_display_seconds` INT UNSIGNED DEFAULT 10 COMMENT 'How long end card stays visible (0 = until dismissed)',
  `end_card_logo_url` VARCHAR(500) DEFAULT NULL COMMENT 'Logo shown on end card (defaults to brand_logo_url)',
  -- Clip Ad settings (short-form vertical video 9:16)
  `clip_enabled` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'This ad is a Clip (short-form vertical video)',
  `clip_video_url` VARCHAR(500) DEFAULT NULL COMMENT 'Clip video URL (9:16, 5-60s, MP4/WebM)',
  `clip_thumbnail_url` VARCHAR(500) DEFAULT NULL COMMENT 'Cover frame / thumbnail shown before playback',
  `clip_duration_seconds` INT UNSIGNED DEFAULT NULL COMMENT 'Clip length in seconds (5-60)',
  `clip_aspect_ratio` ENUM('9:16','4:5','1:1') NOT NULL DEFAULT '9:16' COMMENT 'Vertical, near-square, or square',
  `clip_sound_default` ENUM('on','off') NOT NULL DEFAULT 'on' COMMENT 'Sound-on by default (unlike native video)',
  `clip_autoplay` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Autoplay when visible in feed',
  `clip_loop` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Loop clip continuously',
  `clip_swipe_up_enabled` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Enable swipe-up / tap CTA',
  `clip_swipe_up_text` VARCHAR(100) DEFAULT 'Shiko Më Shumë' COMMENT 'Swipe-up CTA text',
  `clip_swipe_up_url` VARCHAR(2000) DEFAULT NULL COMMENT 'Swipe-up destination URL (defaults to destination_url)',
  `clip_caption` TEXT DEFAULT NULL COMMENT 'Short caption text overlaid on clip (max 150 chars recommended)',
  `clip_hashtags` JSON DEFAULT NULL COMMENT 'Hashtags for discovery e.g. ["ofertë","verë2026","shqipëri"]',
  `clip_music_track_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'FK → aq_clip_music_library for licensed background audio',
  `clip_sticker_overlays` JSON DEFAULT NULL COMMENT 'Array of sticker/GIF overlays with position and timing',
  `clip_text_overlays` JSON DEFAULT NULL COMMENT 'Array of text overlays: [{"text":"50% OFF","start_s":2,"end_s":5,"position":"center","style":"bold_white"}]',
  `clip_interactive_poll` JSON DEFAULT NULL COMMENT 'Optional interactive poll: {"question":"Cila ngjyrë?","options":["Kuqe","Blu","Gjelbër"]}',
  `clip_shoppable_products` JSON DEFAULT NULL COMMENT 'Product pins on clip: [{"product_id":"SKU123","x":0.3,"y":0.6,"start_s":5,"end_s":10}]',
  -- adshqipAI — AI-powered ad creation
  `adshqipai_type` ENUM('none','ad_maker','motion','motion_prompt') NOT NULL DEFAULT 'none' COMMENT 'Which adshqipAI tool generated this ad',
  `adshqipai_prompt` TEXT DEFAULT NULL COMMENT 'User prompt used to generate the ad via adshqipAI',
  `adshqipai_style` VARCHAR(100) DEFAULT NULL COMMENT 'AI style preset e.g. cinematic, minimal, bold, playful',
  `adshqipai_motion_template` VARCHAR(100) DEFAULT NULL COMMENT 'Motion Ads template ID used by adshqipAI',
  `adshqipai_generated_asset_url` VARCHAR(500) DEFAULT NULL COMMENT 'URL of the AI-generated creative asset',
  `adshqipai_generation_id` VARCHAR(128) DEFAULT NULL COMMENT 'Unique ID from adshqipAI generation job for traceability',
  `adshqipai_model_version` VARCHAR(50) DEFAULT NULL COMMENT 'adshqipAI model version used e.g. v1.2',
  `adshqipai_is_edited` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 if user manually edited the AI-generated creative after generation',
  `admin_approved` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'old: dj_admin_approve',
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_campaign` (`campaign_id`),
  KEY `idx_status` (`status`),
  KEY `idx_type` (`ad_type`),
  CONSTRAINT `fk_ad_campaign` FOREIGN KEY (`campaign_id`) REFERENCES `aq_campaigns` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- old: djax_additional_banners (creative files)
CREATE TABLE IF NOT EXISTS `aq_ad_creatives` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ad_id` BIGINT UNSIGNED NOT NULL,
  `file_path` VARCHAR(500) NOT NULL COMMENT 'old: file_path',
  `file_type` ENUM('image','video','html5','gif') NOT NULL DEFAULT 'image',
  `mime_type` VARCHAR(100) DEFAULT NULL,
  `file_size_bytes` INT UNSIGNED DEFAULT NULL,
  `width` INT UNSIGNED DEFAULT NULL,
  `height` INT UNSIGNED DEFAULT NULL,
  `duration_seconds` INT DEFAULT NULL COMMENT 'For video ads',
  `alt_text` VARCHAR(255) DEFAULT NULL,
  `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ad` (`ad_id`),
  CONSTRAINT `fk_creative_ad` FOREIGN KEY (`ad_id`) REFERENCES `aq_ads` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 3B. DIRECT CAMPAIGNS (Self-Serve Platform)
-- ============================================================================
-- Mirrors AdGate self-serve: campaign builder, targeting, budgeting,
-- scheduling, A/B testing, traffic estimator, bulk creator, reporting.
-- Separated from aq_campaigns (which handles RTB/programmatic) to give
-- direct-buy advertisers their own streamlined workflow.
--
-- TABLE NAME MAPPING (old → new):
--  AdGate Self-Serve campaign builder        → aq_direct_campaigns
--  AdGate Self-Serve ad creatives/A-B tests  → aq_direct_campaign_creatives
--  AdGate Self-Serve targeting rules         → aq_direct_campaign_targeting
--  AdGate Self-Serve zone/placement linking  → aq_direct_campaign_zones
--  AdGate Self-Serve performance stats       → aq_direct_campaign_stats
-- ============================================================================

-- Core direct campaign record — one row per self-serve campaign
CREATE TABLE IF NOT EXISTS `aq_direct_campaigns` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `advertiser_id` BIGINT UNSIGNED NOT NULL COMMENT 'FK → aq_users (role=advertiser)',
  `name` VARCHAR(255) NOT NULL COMMENT 'AdGate: campaign name in builder',
  `description` TEXT DEFAULT NULL,
  `format_id` INT UNSIGNED DEFAULT NULL COMMENT 'FK → aq_ad_formats (popunder, native, etc.)',
  `marketing_objective` ENUM(
    'brand_awareness',
    'reach',
    'traffic',
    'engagement',
    'app_installs',
    'video_views',
    'lead_generation',
    'conversions',
    'catalog_sales',
    'store_visits'
  ) NOT NULL DEFAULT 'traffic' COMMENT 'Campaign marketing objective — drives optimization strategy and recommended bid type',

  -- Pricing model (AdGate: CPA, CPC, CPM pricing models page)
  `pricing_model` ENUM('cpm','cpc','cpa','cpv','cpv_ctw','flat_rate') NOT NULL DEFAULT 'cpm',
  `bid_amount` DECIMAL(10,4) NOT NULL DEFAULT 0.0000 COMMENT 'Bid / rate per unit',
  `daily_budget` DECIMAL(12,4) DEFAULT NULL COMMENT 'AdGate: daily budget control',
  `total_budget` DECIMAL(12,4) DEFAULT NULL COMMENT 'AdGate: total campaign budget',
  `remaining_budget` DECIMAL(12,4) DEFAULT NULL COMMENT 'Decremented on spend',
  `currency` VARCHAR(3) NOT NULL DEFAULT 'EUR',

  -- Scheduling (AdGate: campaign scheduling step)
  `start_date` DATETIME DEFAULT NULL,
  `end_date` DATETIME DEFAULT NULL,
  `schedule_timezone` VARCHAR(50) NOT NULL DEFAULT 'Europe/Tirane',
  `dayparting` JSON DEFAULT NULL COMMENT 'Hour-of-day / day-of-week schedule grid',

  -- Frequency & delivery
  `frequency_cap` INT DEFAULT NULL COMMENT 'Max impressions per user',
  `frequency_cap_period` ENUM('hour','day','week','month','lifetime') DEFAULT 'day',
  `delivery_mode` ENUM('standard','accelerated') NOT NULL DEFAULT 'standard' COMMENT 'Pacing strategy',
  `priority` INT NOT NULL DEFAULT 5 COMMENT '1-10, direct campaigns typically higher than RTB',
  `weight` INT NOT NULL DEFAULT 1 COMMENT 'Rotation weight among same-priority campaigns',

  -- Destination
  `destination_url` VARCHAR(2000) NOT NULL COMMENT 'Click-through URL',
  `display_url` VARCHAR(500) DEFAULT NULL COMMENT 'Visible URL shown to user',
  `tracking_url` VARCHAR(2000) DEFAULT NULL COMMENT 'S2S postback / impression pixel',
  `click_tracking_url` VARCHAR(2000) DEFAULT NULL COMMENT 'Third-party click tracker',

  -- Ad content (inline for simple campaigns; creatives table for multi-variant)
  `headline` VARCHAR(255) DEFAULT NULL COMMENT 'For native/text ads',
  `headline_dki` VARCHAR(255) DEFAULT NULL COMMENT 'DKI template e.g. "Buy {keyword} Today" — {keyword} replaced at serve time',
  `headline_dki_default` VARCHAR(255) DEFAULT NULL COMMENT 'Fallback text when no keyword matches, e.g. "Buy Now"',
  `body_text` TEXT DEFAULT NULL COMMENT 'Ad copy / description',
  `body_text_dki` TEXT DEFAULT NULL COMMENT 'DKI-enabled body copy with {keyword} placeholders',
  `call_to_action` VARCHAR(50) DEFAULT NULL COMMENT 'e.g. Shiko Tani, Mëso më shumë',
  `sponsored_label` VARCHAR(50) DEFAULT 'sponsored',
  -- Campaign branding
  `brand_name` VARCHAR(100) DEFAULT NULL COMMENT 'Advertiser brand name shown on ad',
  `brand_logo_url` VARCHAR(500) DEFAULT NULL COMMENT 'Brand logo image URL',
  `brand_tagline` VARCHAR(255) DEFAULT NULL COMMENT 'Short brand slogan shown below headline',
  `brand_color_primary` CHAR(7) DEFAULT NULL COMMENT 'Hex color e.g. #FF5733 for brand theming',
  `brand_color_secondary` CHAR(7) DEFAULT NULL COMMENT 'Secondary hex color for gradient/border',
  -- Click-to-Watch (CTW) video settings
  `ctw_enabled` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Enable Click-to-Watch: user clicks to start video, charged on view',
  `ctw_thumbnail_url` VARCHAR(500) DEFAULT NULL COMMENT 'Thumbnail image shown before user clicks to watch',
  `ctw_min_watch_seconds` INT UNSIGNED DEFAULT 5 COMMENT 'Min seconds user must watch before counted as paid view',
  `ctw_skip_after_seconds` INT UNSIGNED DEFAULT NULL COMMENT 'Allow skip after N seconds (NULL = no skip)',
  `ctw_autoplay` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0 = user must click, 1 = autoplay muted with CTA to unmute',
  `ctw_muted_autoplay` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Start muted when autoplaying',
  -- Video branding overlay
  `video_brand_logo_url` VARCHAR(500) DEFAULT NULL COMMENT 'Logo watermark overlaid on video player',
  `video_brand_logo_position` ENUM('top_left','top_right','bottom_left','bottom_right') DEFAULT 'bottom_right' COMMENT 'Position of logo overlay on video',
  `video_brand_logo_opacity` DECIMAL(3,2) DEFAULT 0.80 COMMENT 'Logo opacity 0.00-1.00',
  `video_brand_intro_url` VARCHAR(500) DEFAULT NULL COMMENT 'Short brand intro bumper video URL (3-5s)',
  `video_brand_intro_duration` INT UNSIGNED DEFAULT NULL COMMENT 'Intro bumper duration in seconds',
  -- End Card settings
  `end_card_enabled` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Show end card after video completes',
  `end_card_type` ENUM('static_image','html','cta_button','product_feed','custom') DEFAULT 'cta_button' COMMENT 'Type of end card displayed',
  `end_card_image_url` VARCHAR(500) DEFAULT NULL COMMENT 'End card static image URL',
  `end_card_html` TEXT DEFAULT NULL COMMENT 'Custom HTML for end card (sanitized)',
  `end_card_headline` VARCHAR(255) DEFAULT NULL COMMENT 'End card headline text',
  `end_card_body` TEXT DEFAULT NULL COMMENT 'End card description text',
  `end_card_cta_text` VARCHAR(50) DEFAULT NULL COMMENT 'End card CTA button text e.g. Shiko Më Shumë',
  `end_card_cta_url` VARCHAR(2000) DEFAULT NULL COMMENT 'End card CTA click-through URL (defaults to destination_url)',
  `end_card_cta_color` CHAR(7) DEFAULT NULL COMMENT 'CTA button hex color',
  `end_card_display_seconds` INT UNSIGNED DEFAULT 10 COMMENT 'How long end card stays visible (0 = until dismissed)',
  `end_card_logo_url` VARCHAR(500) DEFAULT NULL COMMENT 'Logo shown on end card (defaults to brand_logo_url)',
  -- Clip Ad settings (short-form vertical video 9:16)
  `clip_enabled` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'This ad is a Clip (short-form vertical video)',
  `clip_video_url` VARCHAR(500) DEFAULT NULL COMMENT 'Clip video URL (9:16, 5-60s, MP4/WebM)',
  `clip_thumbnail_url` VARCHAR(500) DEFAULT NULL COMMENT 'Cover frame / thumbnail shown before playback',
  `clip_duration_seconds` INT UNSIGNED DEFAULT NULL COMMENT 'Clip length in seconds (5-60)',
  `clip_aspect_ratio` ENUM('9:16','4:5','1:1') NOT NULL DEFAULT '9:16' COMMENT 'Vertical, near-square, or square',
  `clip_sound_default` ENUM('on','off') NOT NULL DEFAULT 'on' COMMENT 'Sound-on by default (unlike native video)',
  `clip_autoplay` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Autoplay when visible in feed',
  `clip_loop` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Loop clip continuously',
  `clip_swipe_up_enabled` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Enable swipe-up / tap CTA',
  `clip_swipe_up_text` VARCHAR(100) DEFAULT 'Shiko Më Shumë' COMMENT 'Swipe-up CTA text',
  `clip_swipe_up_url` VARCHAR(2000) DEFAULT NULL COMMENT 'Swipe-up destination URL (defaults to destination_url)',
  `clip_caption` TEXT DEFAULT NULL COMMENT 'Short caption text overlaid on clip (max 150 chars recommended)',
  `clip_hashtags` JSON DEFAULT NULL COMMENT 'Hashtags for discovery e.g. ["ofertë","verë2026","shqipëri"]',
  `clip_music_track_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'FK → aq_clip_music_library for licensed background audio',
  `clip_sticker_overlays` JSON DEFAULT NULL COMMENT 'Array of sticker/GIF overlays with position and timing',
  `clip_text_overlays` JSON DEFAULT NULL COMMENT 'Array of text overlays: [{"text":"50% OFF","start_s":2,"end_s":5,"position":"center","style":"bold_white"}]',
  `clip_interactive_poll` JSON DEFAULT NULL COMMENT 'Optional interactive poll: {"question":"Cila ngjyrë?","options":["Kuqe","Blu","Gjelbër"]}',
  `clip_shoppable_products` JSON DEFAULT NULL COMMENT 'Product pins on clip: [{"product_id":"SKU123","x":0.3,"y":0.6,"start_s":5,"end_s":10}]',
  -- adshqipAI — AI-powered ad creation
  `adshqipai_type` ENUM('none','ad_maker','motion','motion_prompt') NOT NULL DEFAULT 'none' COMMENT 'Which adshqipAI tool generated this ad',
  `adshqipai_prompt` TEXT DEFAULT NULL COMMENT 'User prompt used to generate the ad via adshqipAI',
  `adshqipai_style` VARCHAR(100) DEFAULT NULL COMMENT 'AI style preset e.g. cinematic, minimal, bold, playful',
  `adshqipai_motion_template` VARCHAR(100) DEFAULT NULL COMMENT 'Motion Ads template ID used by adshqipAI',
  `adshqipai_generated_asset_url` VARCHAR(500) DEFAULT NULL COMMENT 'URL of the AI-generated creative asset',
  `adshqipai_generation_id` VARCHAR(128) DEFAULT NULL COMMENT 'Unique ID from adshqipAI generation job for traceability',
  `adshqipai_model_version` VARCHAR(50) DEFAULT NULL COMMENT 'adshqipAI model version used e.g. v1.2',
  `adshqipai_is_edited` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 if user manually edited the AI-generated creative after generation',

  -- Traffic estimator (AdGate: real-time traffic estimator)
  `estimated_daily_impressions` BIGINT UNSIGNED DEFAULT NULL COMMENT 'Calculated by traffic estimator',
  `estimated_daily_clicks` BIGINT UNSIGNED DEFAULT NULL,
  `estimated_reach` BIGINT UNSIGNED DEFAULT NULL,

  -- A/B testing (AdGate: automated A/B testing)
  `ab_test_enabled` TINYINT(1) NOT NULL DEFAULT 0,
  `ab_test_split_percent` TINYINT UNSIGNED DEFAULT 50 COMMENT 'Traffic split % for variant A',
  `ab_winner_metric` ENUM('ctr','conversions','ecpm','viewability') DEFAULT 'ctr',
  `ab_auto_optimize` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Auto-pick winner after threshold',

  -- Optimization Tools
  `inline_optimization` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'In-Line: auto-adjusts bid in real time based on zone performance',
  `inline_optimization_mode` ENUM('conservative','balanced','aggressive') DEFAULT 'balanced' COMMENT 'In-Line bid adjustment aggressiveness',
  `spendguard_enabled` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'SpendGuard: prevents overspend by capping daily budget with safety buffer',
  `spendguard_buffer_pct` DECIMAL(5,2) DEFAULT 5.00 COMMENT 'SpendGuard safety buffer % above daily budget before hard stop',
  `perf_stimulator_enabled` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Performance Stimulator: auto-boosts bid on high-converting zones',
  `perf_stimulator_target_metric` ENUM('ctr','conversions','roas','ecpm') DEFAULT 'conversions' COMMENT 'Metric Performance Stimulator optimizes toward',
  `perf_stimulator_boost_pct` DECIMAL(5,2) DEFAULT 20.00 COMMENT 'Max % bid boost Performance Stimulator can apply',
  `pacing_health_score` DECIMAL(5,2) DEFAULT NULL COMMENT 'Pacing Health Score 0-100: measures how evenly budget is being spent throughout the day',
  `pacing_health_status` ENUM('healthy','under_pacing','over_pacing','critical') DEFAULT NULL COMMENT 'Derived from pacing_health_score',

  -- Distribution network & MSN exclusive
  `distribution_mode` ENUM('all_networks','selected_networks','msn_exclusive') NOT NULL DEFAULT 'all_networks' COMMENT 'Where ads are delivered: all, selected, or MSN-only',
  `msn_exclusive` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Quick toggle: Run on MSN exclusively (overrides distribution_mode)',
  `msn_enabled` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Include MSN network in distribution (when distribution_mode=selected_networks)',
  `msn_bid_adjustment` DECIMAL(5,2) DEFAULT NULL COMMENT 'Bid modifier % for MSN placements (e.g. +20.00 for 20% higher bid on MSN)',

  -- Campaign Dynamics
  `dynamic_creative_enabled` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Enable Dynamic Creative Optimization — assemble ads from asset library based on rules',
  `dynamic_tokens_enabled` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Enable dynamic token replacement ({city}, {device}, {weather}, {countdown}, etc.)',
  `dynamic_product_feed_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'FK → aq_dynamic_product_feeds for catalog-driven dynamic ads',
  `dynamic_landing_page_enabled` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Enable per-segment dynamic landing page URL rules',
  `dynamic_budget_rules_enabled` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Enable automated budget/bid rules based on performance triggers',

  -- Custom Audience targeting
  `audience_targeting_mode` ENUM('none','include','exclude','both') NOT NULL DEFAULT 'none' COMMENT 'How custom audiences are applied to this campaign',
  `audience_expansion_enabled` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Allow platform to expand reach via lookalike modeling',
  `audience_expansion_ratio` DECIMAL(3,2) DEFAULT 1.00 COMMENT 'Lookalike expansion ratio 1-10 (1=most similar, 10=broadest)',

  -- OEM (Original Equipment Manufacturer) targeting
  `oem_enabled` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Enable OEM distribution — deliver ads via device manufacturer placements (preinstalls, setup wizards, app stores)',
  `oem_targeting_mode` ENUM('none','all_oems','selected_oems') NOT NULL DEFAULT 'none' COMMENT 'Target all OEM partners or select specific manufacturers',
  `oem_bid_adjustment` DECIMAL(5,2) DEFAULT NULL COMMENT 'Bid modifier % for OEM placements (e.g. +15.00 for 15% higher bid on OEM inventory)',
  `oem_placement_types` JSON DEFAULT NULL COMMENT 'Preferred OEM placements: ["setup_wizard","app_store","lockscreen","notification_tray","preinstall"]',

  -- Status & approval
  `status` ENUM('draft','pending_review','active','paused','completed','rejected','archived') NOT NULL DEFAULT 'draft',
  `admin_approved` TINYINT(1) NOT NULL DEFAULT 0,
  `rejection_reason` TEXT DEFAULT NULL,

  -- Bulk campaign support (AdGate: bulk campaign creator)
  `parent_campaign_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'For bulk-created campaign groups',
  `campaign_group_name` VARCHAR(255) DEFAULT NULL COMMENT 'Bulk group label',

  -- Metadata
  `notes` TEXT DEFAULT NULL COMMENT 'Internal advertiser notes',
  `tags` JSON DEFAULT NULL COMMENT 'Freeform tags for organization',
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_advertiser` (`advertiser_id`),
  KEY `idx_status` (`status`),
  KEY `idx_format` (`format_id`),
  KEY `idx_dates` (`start_date`, `end_date`),
  KEY `idx_pricing` (`pricing_model`),
  KEY `idx_parent` (`parent_campaign_id`),
  CONSTRAINT `fk_dc_advertiser` FOREIGN KEY (`advertiser_id`) REFERENCES `aq_users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_dc_format` FOREIGN KEY (`format_id`) REFERENCES `aq_ad_formats` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_dc_parent` FOREIGN KEY (`parent_campaign_id`) REFERENCES `aq_direct_campaigns` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Creative variants for direct campaigns (supports A/B testing)
CREATE TABLE IF NOT EXISTS `aq_direct_campaign_creatives` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `campaign_id` BIGINT UNSIGNED NOT NULL,
  `variant_label` VARCHAR(50) DEFAULT 'A' COMMENT 'A, B, C… for A/B testing',
  `creative_type` ENUM('image','html','video','text','rich_media','native','vast','clip') NOT NULL DEFAULT 'image',
  `file_path` VARCHAR(500) DEFAULT NULL,
  `file_type` ENUM('image','video','html5','gif') DEFAULT 'image',
  `mime_type` VARCHAR(100) DEFAULT NULL,
  `file_size_bytes` INT UNSIGNED DEFAULT NULL,
  `width` INT UNSIGNED DEFAULT NULL,
  `height` INT UNSIGNED DEFAULT NULL,
  `duration_seconds` INT DEFAULT NULL COMMENT 'For video creatives',
  `alt_text` VARCHAR(255) DEFAULT NULL,
  `headline` VARCHAR(255) DEFAULT NULL COMMENT 'Override campaign-level headline',
  `body_text` TEXT DEFAULT NULL COMMENT 'Override campaign-level body',
  `call_to_action` VARCHAR(50) DEFAULT NULL,
  `destination_url` VARCHAR(2000) DEFAULT NULL COMMENT 'Override campaign-level URL',
  `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
  `is_winner` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'A/B test winner flag',
  `status` ENUM('active','paused','pending_review','rejected','archived') NOT NULL DEFAULT 'pending_review',
  `admin_approved` TINYINT(1) NOT NULL DEFAULT 0,
  `impressions` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Running count for A/B split',
  `clicks` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `conversions` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_campaign` (`campaign_id`),
  KEY `idx_variant` (`variant_label`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_dcc_campaign` FOREIGN KEY (`campaign_id`) REFERENCES `aq_direct_campaigns` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Granular targeting rules for direct campaigns
-- (AdGate: target selection step — geo, device, browser, OS, schedule, audience)
CREATE TABLE IF NOT EXISTS `aq_direct_campaign_targeting` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `campaign_id` BIGINT UNSIGNED NOT NULL,
  `targeting_type` ENUM('geo_country','geo_region','geo_city','device','browser','os','language','carrier','connection_type','domain_whitelist','domain_blacklist','category','keyword','mail_domain','audience_segment','ip_range','retargeting','distribution_network','oem') NOT NULL,
  `match_mode` ENUM('include','exclude') NOT NULL DEFAULT 'include',
  `target_values` JSON NOT NULL COMMENT 'Array of values, e.g. ["AL","XK","MK"] or ["mobile","tablet"]',
  `priority` INT NOT NULL DEFAULT 0 COMMENT 'Evaluation order',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_campaign` (`campaign_id`),
  KEY `idx_type` (`targeting_type`),
  CONSTRAINT `fk_dct_campaign` FOREIGN KEY (`campaign_id`) REFERENCES `aq_direct_campaigns` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 4. PUBLISHER SITES & ZONES
-- ============================================================================

CREATE TABLE IF NOT EXISTS `aq_sites` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `publisher_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `domain` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `category` VARCHAR(100) DEFAULT NULL,
  `language` VARCHAR(5) NOT NULL DEFAULT 'sq',
  `monthly_pageviews` BIGINT UNSIGNED DEFAULT NULL,
  `status` ENUM('active','pending_review','rejected','suspended') NOT NULL DEFAULT 'pending_review',
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_publisher` (`publisher_id`),
  KEY `idx_domain` (`domain`),
  CONSTRAINT `fk_site_publisher` FOREIGN KEY (`publisher_id`) REFERENCES `aq_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- old: ox_zones
CREATE TABLE IF NOT EXISTS `aq_zones` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `site_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `format_id` INT UNSIGNED DEFAULT NULL,
  `size_id` INT UNSIGNED DEFAULT NULL,
  `placement` ENUM('header','sidebar','content','footer','overlay','interstitial','push') DEFAULT 'content',
  `floor_price` DECIMAL(10,4) DEFAULT NULL COMMENT 'Minimum CPM',
  `status` ENUM('active','paused','archived') NOT NULL DEFAULT 'active',
  `ad_code` TEXT DEFAULT NULL COMMENT 'Generated JS/HTML embed code',
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_site` (`site_id`),
  KEY `idx_format` (`format_id`),
  CONSTRAINT `fk_zone_site` FOREIGN KEY (`site_id`) REFERENCES `aq_sites` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_zone_format` FOREIGN KEY (`format_id`) REFERENCES `aq_ad_formats` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_zone_size` FOREIGN KEY (`size_id`) REFERENCES `aq_ad_sizes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- old: djax_app_zone_assoc
CREATE TABLE IF NOT EXISTS `aq_zone_ad_assoc` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `zone_id` BIGINT UNSIGNED NOT NULL,
  `ad_id` BIGINT UNSIGNED NOT NULL,
  `priority` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_zone_ad` (`zone_id`, `ad_id`),
  CONSTRAINT `fk_assoc_zone` FOREIGN KEY (`zone_id`) REFERENCES `aq_zones` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_assoc_ad` FOREIGN KEY (`ad_id`) REFERENCES `aq_ads` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Direct campaign ↔ zone association (ad placement step)
-- Moved here so aq_zones exists before FK reference
CREATE TABLE IF NOT EXISTS `aq_direct_campaign_zones` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `campaign_id` BIGINT UNSIGNED NOT NULL,
  `zone_id` BIGINT UNSIGNED NOT NULL,
  `priority` INT NOT NULL DEFAULT 0,
  `floor_price_override` DECIMAL(10,4) DEFAULT NULL COMMENT 'Override zone floor for this deal',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_campaign_zone` (`campaign_id`, `zone_id`),
  KEY `idx_zone` (`zone_id`),
  CONSTRAINT `fk_dcz_campaign` FOREIGN KEY (`campaign_id`) REFERENCES `aq_direct_campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_dcz_zone` FOREIGN KEY (`zone_id`) REFERENCES `aq_zones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Aggregated daily stats for direct campaigns
-- (AdGate: real-time reports, performance analysis, measurable results)
-- Moved here so aq_zones exists before FK reference
CREATE TABLE IF NOT EXISTS `aq_direct_campaign_stats` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `date` DATE NOT NULL,
  `campaign_id` BIGINT UNSIGNED NOT NULL,
  `creative_id` BIGINT UNSIGNED DEFAULT NULL,
  `zone_id` BIGINT UNSIGNED DEFAULT NULL,
  `country_code` CHAR(2) DEFAULT NULL,
  `device_type` ENUM('desktop','mobile','tablet') DEFAULT NULL,
  `impressions` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `viewable_impressions` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `clicks` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `unique_clicks` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `conversions` INT UNSIGNED NOT NULL DEFAULT 0,
  `revenue` DECIMAL(12,4) NOT NULL DEFAULT 0.0000 COMMENT 'Advertiser spend',
  `publisher_earnings` DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
  `ecpm` DECIMAL(8,4) DEFAULT NULL,
  `ctr` DECIMAL(8,4) DEFAULT NULL,
  `conversion_rate` DECIMAL(8,4) DEFAULT NULL,
  `fill_rate` DECIMAL(5,2) DEFAULT NULL,
  `avg_cpc` DECIMAL(10,4) DEFAULT NULL,
  `avg_cpa` DECIMAL(10,4) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_daily_dc_combo` (`date`, `campaign_id`, `creative_id`, `zone_id`, `country_code`, `device_type`),
  KEY `idx_date` (`date`),
  KEY `idx_campaign` (`campaign_id`),
  KEY `idx_creative` (`creative_id`),
  KEY `idx_zone` (`zone_id`),
  KEY `idx_country` (`country_code`),
  CONSTRAINT `fk_dcs_campaign` FOREIGN KEY (`campaign_id`) REFERENCES `aq_direct_campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_dcs_creative` FOREIGN KEY (`creative_id`) REFERENCES `aq_direct_campaign_creatives` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_dcs_zone` FOREIGN KEY (`zone_id`) REFERENCES `aq_zones` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- View: Direct campaign performance summary
CREATE OR REPLACE VIEW `aq_view_direct_campaign_performance` AS
SELECT
  dc.id AS campaign_id,
  dc.name AS campaign_name,
  dc.pricing_model,
  dc.status,
  dc.bid_amount,
  dc.daily_budget,
  dc.total_budget,
  dc.remaining_budget,
  u.email AS advertiser_email,
  af.name AS format_name,
  COALESCE(SUM(s.impressions), 0) AS total_impressions,
  COALESCE(SUM(s.clicks), 0) AS total_clicks,
  COALESCE(SUM(s.conversions), 0) AS total_conversions,
  COALESCE(SUM(s.revenue), 0) AS total_spend,
  CASE WHEN SUM(s.impressions) > 0
    THEN ROUND(SUM(s.clicks) / SUM(s.impressions) * 100, 4)
    ELSE 0
  END AS ctr,
  CASE WHEN SUM(s.impressions) > 0
    THEN ROUND(SUM(s.revenue) / SUM(s.impressions) * 1000, 4)
    ELSE 0
  END AS ecpm,
  CASE WHEN SUM(s.clicks) > 0
    THEN ROUND(SUM(s.revenue) / SUM(s.clicks), 4)
    ELSE 0
  END AS avg_cpc
FROM aq_direct_campaigns dc
JOIN aq_users u ON dc.advertiser_id = u.id
LEFT JOIN aq_ad_formats af ON dc.format_id = af.id
LEFT JOIN aq_direct_campaign_stats s ON dc.id = s.campaign_id
WHERE dc.is_deleted = 0
GROUP BY dc.id, dc.name, dc.pricing_model, dc.status,
         dc.bid_amount, dc.daily_budget, dc.total_budget, dc.remaining_budget,
         u.email, af.name;

-- ============================================================================
-- 5. CATEGORIES & TARGETING
-- ============================================================================

CREATE TABLE IF NOT EXISTS `aq_categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `parent_id` INT UNSIGNED DEFAULT NULL,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL,
  `iab_code` VARCHAR(20) DEFAULT NULL COMMENT 'IAB category code for RTB',
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_slug` (`slug`),
  KEY `idx_parent` (`parent_id`),
  CONSTRAINT `fk_cat_parent` FOREIGN KEY (`parent_id`) REFERENCES `aq_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- old: djax_campaign_category_assoc
CREATE TABLE IF NOT EXISTS `aq_campaign_category` (
  `campaign_id` BIGINT UNSIGNED NOT NULL,
  `category_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`campaign_id`, `category_id`),
  CONSTRAINT `fk_cc_campaign` FOREIGN KEY (`campaign_id`) REFERENCES `aq_campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cc_category` FOREIGN KEY (`category_id`) REFERENCES `aq_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- old: djax_app_category_assoc
CREATE TABLE IF NOT EXISTS `aq_site_categories` (
  `site_id` BIGINT UNSIGNED NOT NULL,
  `category_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`site_id`, `category_id`),
  CONSTRAINT `fk_sc_site` FOREIGN KEY (`site_id`) REFERENCES `aq_sites` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sc_category` FOREIGN KEY (`category_id`) REFERENCES `aq_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 6. GEO TARGETING (Balkans focus from landing page)
-- ============================================================================

-- old: GeoIP2_Country_Locations_en
CREATE TABLE IF NOT EXISTS `aq_geo_countries` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `iso_code` CHAR(2) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `name_sq` VARCHAR(100) DEFAULT NULL COMMENT 'Albanian name',
  `continent` VARCHAR(50) DEFAULT NULL,
  `is_balkan` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Albania, Kosovo, North Macedonia, etc.',
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_iso` (`iso_code`),
  KEY `idx_balkan` (`is_balkan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed Balkan countries from landing page
INSERT INTO `aq_geo_countries` (`iso_code`, `name`, `name_sq`, `continent`, `is_balkan`) VALUES
('AL', 'Albania',           'Shqipëria',          'Europe', 1),
('XK', 'Kosovo',            'Kosova',             'Europe', 1),
('MK', 'North Macedonia',   'Maqedonia e Veriut', 'Europe', 1),
('ME', 'Montenegro',        'Mali i Zi',          'Europe', 1),
('RS', 'Serbia',            'Serbia',             'Europe', 1),
('BA', 'Bosnia & Herzegovina','Bosnja dhe Hercegovina','Europe', 1),
('HR', 'Croatia',           'Kroacia',            'Europe', 1),
('SI', 'Slovenia',          'Sllovenia',          'Europe', 1),
('BG', 'Bulgaria',          'Bullgaria',          'Europe', 1),
('RO', 'Romania',           'Rumania',            'Europe', 1),
('GR', 'Greece',            'Greqia',             'Europe', 1);

-- old: Geo_region
CREATE TABLE IF NOT EXISTS `aq_geo_regions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `country_code` CHAR(2) NOT NULL,
  `region_code` VARCHAR(10) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_country` (`country_code`),
  CONSTRAINT `fk_region_country` FOREIGN KEY (`country_code`) REFERENCES `aq_geo_countries` (`iso_code`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 7. TRACKING: IMPRESSIONS, CLICKS, CONVERSIONS
-- ============================================================================

-- old: djax_ad_zone_impression
CREATE TABLE IF NOT EXISTS `aq_impressions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ad_id` BIGINT UNSIGNED NOT NULL,
  `zone_id` BIGINT UNSIGNED DEFAULT NULL,
  `campaign_id` BIGINT UNSIGNED DEFAULT NULL,
  `viewer_id` VARCHAR(64) NOT NULL COMMENT 'old: viewer_id (cookie/fingerprint)',
  `fingerprint_id` VARCHAR(64) DEFAULT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` VARCHAR(500) DEFAULT NULL,
  `country_code` CHAR(2) DEFAULT NULL,
  `region` VARCHAR(100) DEFAULT NULL,
  `device_type` ENUM('desktop','mobile','tablet') DEFAULT NULL,
  `browser` VARCHAR(50) DEFAULT NULL,
  `os` VARCHAR(50) DEFAULT NULL,
  `referer_url` VARCHAR(2000) DEFAULT NULL,
  `cost` DECIMAL(10,6) DEFAULT NULL COMMENT 'CPM cost for this impression',
  `is_viewable` TINYINT(1) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ad` (`ad_id`),
  KEY `idx_zone` (`zone_id`),
  KEY `idx_campaign` (`campaign_id`),
  KEY `idx_viewer` (`viewer_id`),
  KEY `idx_created` (`created_at`),
  KEY `idx_country` (`country_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- old: djax_ad_zone_click
CREATE TABLE IF NOT EXISTS `aq_clicks` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ad_id` BIGINT UNSIGNED NOT NULL,
  `zone_id` BIGINT UNSIGNED DEFAULT NULL,
  `campaign_id` BIGINT UNSIGNED DEFAULT NULL,
  `impression_id` BIGINT UNSIGNED DEFAULT NULL,
  `viewer_id` VARCHAR(64) NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` VARCHAR(500) DEFAULT NULL,
  `country_code` CHAR(2) DEFAULT NULL,
  `device_type` ENUM('desktop','mobile','tablet') DEFAULT NULL,
  `browser` VARCHAR(50) DEFAULT NULL,
  `os` VARCHAR(50) DEFAULT NULL,
  `cost` DECIMAL(10,6) DEFAULT NULL COMMENT 'CPC cost for this click',
  `is_unique` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ad` (`ad_id`),
  KEY `idx_campaign` (`campaign_id`),
  KEY `idx_viewer` (`viewer_id`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `aq_conversions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ad_id` BIGINT UNSIGNED NOT NULL,
  `campaign_id` BIGINT UNSIGNED NOT NULL,
  `click_id` BIGINT UNSIGNED DEFAULT NULL,
  `viewer_id` VARCHAR(64) NOT NULL,
  `conversion_type` ENUM('sale','lead','signup','install','custom') NOT NULL DEFAULT 'sale',
  `revenue` DECIMAL(12,4) DEFAULT NULL,
  `payout` DECIMAL(12,4) DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `country_code` CHAR(2) DEFAULT NULL,
  `metadata` JSON DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_campaign` (`campaign_id`),
  KEY `idx_click` (`click_id`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 8. AGGREGATED STATISTICS (Analytics Dashboard from landing page)
-- ============================================================================

-- old: djax_ad_zone_click_count + djax_ad_zone_imp_count merged
CREATE TABLE IF NOT EXISTS `aq_stats_daily` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `date` DATE NOT NULL,
  `ad_id` BIGINT UNSIGNED DEFAULT NULL,
  `campaign_id` BIGINT UNSIGNED DEFAULT NULL,
  `zone_id` BIGINT UNSIGNED DEFAULT NULL,
  `site_id` BIGINT UNSIGNED DEFAULT NULL,
  `advertiser_id` BIGINT UNSIGNED DEFAULT NULL,
  `publisher_id` BIGINT UNSIGNED DEFAULT NULL,
  `format_id` INT UNSIGNED DEFAULT NULL,
  `country_code` CHAR(2) DEFAULT NULL,
  `device_type` ENUM('desktop','mobile','tablet') DEFAULT NULL,
  `impressions` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `clicks` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `conversions` INT UNSIGNED NOT NULL DEFAULT 0,
  `viewable_impressions` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `revenue` DECIMAL(12,4) NOT NULL DEFAULT 0.0000 COMMENT 'Advertiser spend',
  `publisher_earnings` DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
  `ecpm` DECIMAL(8,4) DEFAULT NULL,
  `ctr` DECIMAL(8,4) DEFAULT NULL,
  `fill_rate` DECIMAL(5,2) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_daily_combo` (`date`, `ad_id`, `zone_id`, `country_code`, `device_type`),
  KEY `idx_date` (`date`),
  KEY `idx_campaign` (`campaign_id`),
  KEY `idx_advertiser` (`advertiser_id`),
  KEY `idx_publisher` (`publisher_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- old: djax_browsers_stats
CREATE TABLE IF NOT EXISTS `aq_stats_browser` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `date` DATE NOT NULL,
  `browser` VARCHAR(50) NOT NULL,
  `browser_version` VARCHAR(20) DEFAULT NULL,
  `impressions` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `clicks` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_date_browser` (`date`, `browser`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- old: djax_clients_stats (geo breakdown)
CREATE TABLE IF NOT EXISTS `aq_stats_geo` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `date` DATE NOT NULL,
  `country_code` CHAR(2) NOT NULL,
  `region` VARCHAR(100) DEFAULT NULL,
  `impressions` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `clicks` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `revenue` DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
  PRIMARY KEY (`id`),
  KEY `idx_date_country` (`date`, `country_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 9. FRAUD DETECTION & ANTI-FRAUD
-- ============================================================================

-- old: djax_ad_fraud_click + djax_ad_fraud_impression merged
CREATE TABLE IF NOT EXISTS `aq_fraud_events` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_type` ENUM('click','impression') NOT NULL,
  `ad_id` BIGINT UNSIGNED DEFAULT NULL,
  `zone_id` BIGINT UNSIGNED DEFAULT NULL,
  `viewer_id` VARCHAR(64) NOT NULL,
  `fingerprint_id` VARCHAR(64) DEFAULT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` VARCHAR(500) DEFAULT NULL,
  `fraud_reason` ENUM('duplicate','bot','datacenter_ip','click_flood','impression_stacking','geo_mismatch','other') NOT NULL,
  `severity` ENUM('low','medium','high','critical') NOT NULL DEFAULT 'medium',
  `blocked` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_type` (`event_type`),
  KEY `idx_viewer` (`viewer_id`),
  KEY `idx_ip` (`ip_address`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- old: djax_antifraud_impressions
CREATE TABLE IF NOT EXISTS `aq_antifraud_rules` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `rule_name` VARCHAR(100) NOT NULL,
  `rule_type` ENUM('impression_cap','click_cap','ip_blacklist','ua_blacklist','geo_block','fingerprint') NOT NULL,
  `threshold_value` INT DEFAULT NULL COMMENT 'old: imp_num',
  `reset_period_seconds` INT DEFAULT NULL COMMENT 'old: resettime',
  `action` ENUM('block','flag','throttle') NOT NULL DEFAULT 'block',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- old: djax_allowcrecord + djax_antifruadrecord merged
CREATE TABLE IF NOT EXISTS `aq_publisher_fraud_records` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `publisher_id` BIGINT UNSIGNED NOT NULL,
  `record_type` ENUM('allow','fraud') NOT NULL,
  `reason` TEXT DEFAULT NULL,
  `flagged_impressions` BIGINT UNSIGNED DEFAULT 0,
  `flagged_clicks` BIGINT UNSIGNED DEFAULT 0,
  `action_taken` ENUM('none','warning','suspended','banned') NOT NULL DEFAULT 'none',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `resolved_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_publisher` (`publisher_id`),
  CONSTRAINT `fk_fraud_publisher` FOREIGN KEY (`publisher_id`) REFERENCES `aq_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- old: djax_anti_fraud_mail
CREATE TABLE IF NOT EXISTS `aq_fraud_notifications` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `publisher_id` BIGINT UNSIGNED NOT NULL,
  `fraud_record_id` BIGINT UNSIGNED DEFAULT NULL,
  `notification_type` ENUM('email','in_app','sms') NOT NULL DEFAULT 'email',
  `subject` VARCHAR(255) DEFAULT NULL,
  `message` TEXT DEFAULT NULL,
  `sent_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_publisher` (`publisher_id`),
  KEY `idx_fraud_record` (`fraud_record_id`),
  CONSTRAINT `fk_fraudnotif_record` FOREIGN KEY (`fraud_record_id`) REFERENCES `aq_publisher_fraud_records` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_fraudnotif_publisher` FOREIGN KEY (`publisher_id`) REFERENCES `aq_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 10. RTB / AD EXCHANGE (old: djax_3rd_party_ad_exchange)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `aq_ad_exchanges` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL COMMENT 'old: exchange_name',
  `type` ENUM('DSP','SSP','ad_network') NOT NULL DEFAULT 'DSP' COMMENT 'old: type',
  `endpoint_url` VARCHAR(500) NOT NULL COMMENT 'old: ping_url',
  `auth_type` ENUM('api_key','oauth2','basic','none') NOT NULL DEFAULT 'api_key',
  `credentials` JSON DEFAULT NULL COMMENT 'Encrypted; old: username/password/authentiction_key',
  `seller_id` VARCHAR(50) DEFAULT NULL,
  `auction_currency` VARCHAR(3) NOT NULL DEFAULT 'EUR' COMMENT 'old: auction_currency (was USD)',
  `auction_type` TINYINT NOT NULL DEFAULT 2 COMMENT '1=first-price, 2=second-price',
  `is_strict_openrtb` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'old: is_stirct_open_rtb_standard',
  `status` ENUM('active','inactive','testing') NOT NULL DEFAULT 'active',
  `agency_id` BIGINT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- old: djax_ad_requests_rtb_buyers
CREATE TABLE IF NOT EXISTS `aq_rtb_bid_requests` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `request_id` VARCHAR(64) NOT NULL COMMENT 'Unique OpenRTB request id',
  `exchange_id` INT UNSIGNED DEFAULT NULL,
  `zone_id` BIGINT UNSIGNED DEFAULT NULL,
  `bid_floor` DECIMAL(10,4) NOT NULL DEFAULT 0.0000,
  `ad_format` VARCHAR(50) DEFAULT NULL,
  `width` INT UNSIGNED DEFAULT NULL,
  `height` INT UNSIGNED DEFAULT NULL,
  `country_code` CHAR(2) DEFAULT NULL,
  `device_type` VARCHAR(20) DEFAULT NULL,
  `user_agent` VARCHAR(500) DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `response_time_ms` INT DEFAULT NULL,
  `status` ENUM('sent','responded','timeout','error') NOT NULL DEFAULT 'sent',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_request` (`request_id`),
  KEY `idx_exchange` (`exchange_id`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- old: djax_ad_response_rtb_buyers
CREATE TABLE IF NOT EXISTS `aq_rtb_bid_responses` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `request_id` VARCHAR(64) NOT NULL,
  `exchange_id` INT UNSIGNED DEFAULT NULL,
  `bid_price` DECIMAL(10,4) DEFAULT NULL,
  `ad_markup` TEXT DEFAULT NULL,
  `creative_id` VARCHAR(100) DEFAULT NULL,
  `advertiser_domain` VARCHAR(255) DEFAULT NULL,
  `win` TINYINT(1) NOT NULL DEFAULT 0,
  `win_price` DECIMAL(10,4) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_request` (`request_id`),
  KEY `idx_exchange` (`exchange_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 11. VIDEO / VAST (old: djax_banner_vast_events)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `aq_vast_events` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_name` VARCHAR(50) NOT NULL COMMENT 'start, firstQuartile, midpoint, thirdQuartile, complete, skip, mute, unmute, pause, resume',
  `description` VARCHAR(255) DEFAULT NULL,
  `is_trackable` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_event` (`event_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `aq_video_tracking` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ad_id` BIGINT UNSIGNED NOT NULL,
  `impression_id` BIGINT UNSIGNED DEFAULT NULL,
  `event_id` INT UNSIGNED NOT NULL,
  `viewer_id` VARCHAR(64) NOT NULL,
  `progress_percent` TINYINT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ad` (`ad_id`),
  KEY `idx_event` (`event_id`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 12. PAYOUTS & BILLING (Landing page: Payouts button, $1,254 payment notification)
-- ============================================================================

-- old: djax_admin_payment_details
CREATE TABLE IF NOT EXISTS `aq_payouts` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `amount` DECIMAL(12,4) NOT NULL,
  `currency` VARCHAR(3) NOT NULL DEFAULT 'EUR',
  `payment_method` ENUM('paypal','wire_transfer','crypto','payoneer') NOT NULL,
  `payment_reference` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('pending','processing','completed','failed','cancelled') NOT NULL DEFAULT 'pending',
  `period_start` DATE DEFAULT NULL,
  `period_end` DATE DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `processed_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `fk_payout_user` FOREIGN KEY (`user_id`) REFERENCES `aq_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `aq_invoices` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `invoice_number` VARCHAR(50) NOT NULL,
  `type` ENUM('advertiser_charge','publisher_payout') NOT NULL,
  `amount` DECIMAL(12,4) NOT NULL,
  `tax_amount` DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
  `total_amount` DECIMAL(12,4) NOT NULL,
  `currency` VARCHAR(3) NOT NULL DEFAULT 'EUR',
  `status` ENUM('draft','sent','paid','overdue','cancelled') NOT NULL DEFAULT 'draft',
  `due_date` DATE DEFAULT NULL,
  `paid_at` DATETIME DEFAULT NULL,
  `pdf_url` VARCHAR(500) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_invoice_number` (`invoice_number`),
  KEY `idx_user` (`user_id`),
  CONSTRAINT `fk_invoice_user` FOREIGN KEY (`user_id`) REFERENCES `aq_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 13. PRICING PLANS (from landing page: Starter, Growth, Enterprise)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `aq_pricing_plans` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(50) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `target_audience` ENUM('advertiser','publisher','both') NOT NULL DEFAULT 'both',
  `price_monthly` DECIMAL(10,2) DEFAULT NULL COMMENT 'NULL = custom/contact us',
  `price_yearly` DECIMAL(10,2) DEFAULT NULL,
  `currency` VARCHAR(3) NOT NULL DEFAULT 'EUR',
  `features` JSON NOT NULL COMMENT 'List of included features',
  `impressions_limit` BIGINT UNSIGNED DEFAULT NULL,
  `is_popular` TINYINT(1) NOT NULL DEFAULT 0,
  `is_enterprise` TINYINT(1) NOT NULL DEFAULT 0,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `aq_pricing_plans` (`slug`, `name`, `target_audience`, `price_monthly`, `features`, `is_popular`, `is_enterprise`, `sort_order`) VALUES
('starter',    'Starter',    'both', 0.00,   '["Basic ad formats","Standard support","1 site","Basic analytics","Email reports"]', 0, 0, 1),
('growth',     'Growth',     'both', 49.00,  '["All ad formats","Priority support","10 sites","Advanced analytics","API access","Custom targeting"]', 1, 0, 2),
('enterprise', 'Enterprise', 'both', NULL,   '["All formats + Rich Media","Dedicated account manager","Unlimited sites","Real-time analytics","Full API + SDK","Custom integrations","SLA guarantee"]', 0, 1, 3);

CREATE TABLE IF NOT EXISTS `aq_user_subscriptions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `plan_id` INT UNSIGNED NOT NULL,
  `billing_cycle` ENUM('monthly','yearly') NOT NULL DEFAULT 'monthly',
  `status` ENUM('active','cancelled','expired','trial') NOT NULL DEFAULT 'active',
  `trial_ends_at` DATETIME DEFAULT NULL,
  `current_period_start` DATE NOT NULL,
  `current_period_end` DATE NOT NULL,
  `cancelled_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_plan` (`plan_id`),
  CONSTRAINT `fk_sub_user` FOREIGN KEY (`user_id`) REFERENCES `aq_users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sub_plan` FOREIGN KEY (`plan_id`) REFERENCES `aq_pricing_plans` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 14. TRAFFIC SOURCES (old: djax_campaign_traffic_source)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `aq_traffic_sources` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(50) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `aq_campaign_traffic_source` (
  `campaign_id` BIGINT UNSIGNED NOT NULL,
  `traffic_source_id` INT UNSIGNED NOT NULL,
  `is_allowed` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=whitelist, 0=blacklist',
  PRIMARY KEY (`campaign_id`, `traffic_source_id`),
  CONSTRAINT `fk_cts_campaign` FOREIGN KEY (`campaign_id`) REFERENCES `aq_campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cts_source` FOREIGN KEY (`traffic_source_id`) REFERENCES `aq_traffic_sources` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 15. ACTIVITY LOG (old: djax_activity_log)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `aq_activity_log` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED DEFAULT NULL,
  `action` VARCHAR(100) NOT NULL COMMENT 'e.g. campaign.created, ad.approved, payout.processed',
  `entity_type` VARCHAR(50) DEFAULT NULL COMMENT 'campaign, ad, zone, payout, etc.',
  `entity_id` BIGINT UNSIGNED DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` VARCHAR(500) DEFAULT NULL,
  `browser` VARCHAR(50) DEFAULT NULL,
  `os` VARCHAR(50) DEFAULT NULL,
  `metadata` JSON DEFAULT NULL COMMENT 'Additional context',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_entity` (`entity_type`, `entity_id`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- old: djax_activity_log_settings
CREATE TABLE IF NOT EXISTS `aq_log_settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `log_enabled` TINYINT(1) NOT NULL DEFAULT 1,
  `log_account_activity` TINYINT(1) NOT NULL DEFAULT 1,
  `log_campaign_activity` TINYINT(1) NOT NULL DEFAULT 1,
  `log_ad_activity` TINYINT(1) NOT NULL DEFAULT 1,
  `log_payment_activity` TINYINT(1) NOT NULL DEFAULT 1,
  `retention_days` INT NOT NULL DEFAULT 90,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `aq_log_settings` (`log_enabled`, `retention_days`) VALUES (1, 90);

-- ============================================================================
-- 16. NOTIFICATIONS (Landing page: notification center, instant notifications)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `aq_notifications` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `type` ENUM('success','warning','error','info','payment','campaign','system') NOT NULL DEFAULT 'info',
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT DEFAULT NULL,
  `action_url` VARCHAR(500) DEFAULT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `read_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_read` (`user_id`, `is_read`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `aq_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- old: djax_adv_pub_notification_settings
CREATE TABLE IF NOT EXISTS `aq_notification_settings` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `email_campaign_updates` TINYINT(1) NOT NULL DEFAULT 1,
  `email_payment_alerts` TINYINT(1) NOT NULL DEFAULT 1,
  `email_fraud_alerts` TINYINT(1) NOT NULL DEFAULT 1,
  `email_newsletter` TINYINT(1) NOT NULL DEFAULT 1,
  `push_enabled` TINYINT(1) NOT NULL DEFAULT 1,
  `push_earnings` TINYINT(1) NOT NULL DEFAULT 1,
  `push_campaign_status` TINYINT(1) NOT NULL DEFAULT 1,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user` (`user_id`),
  CONSTRAINT `fk_notifsettings_user` FOREIGN KEY (`user_id`) REFERENCES `aq_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 17. SUPPORT & LIVE CHAT (Landing page: support button, live support)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `aq_support_tickets` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `assigned_to` BIGINT UNSIGNED DEFAULT NULL COMMENT 'Admin/manager user_id',
  `subject` VARCHAR(255) NOT NULL,
  `category` ENUM('billing','technical','campaign','account','fraud','other') NOT NULL DEFAULT 'other',
  `priority` ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  `status` ENUM('open','in_progress','waiting_reply','resolved','closed') NOT NULL DEFAULT 'open',
  `resolved_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_assigned` (`assigned_to`),
  CONSTRAINT `fk_ticket_user` FOREIGN KEY (`user_id`) REFERENCES `aq_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `aq_support_messages` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_id` BIGINT UNSIGNED NOT NULL,
  `sender_id` BIGINT UNSIGNED NOT NULL,
  `message` TEXT NOT NULL,
  `attachment_url` VARCHAR(500) DEFAULT NULL,
  `is_internal_note` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ticket` (`ticket_id`),
  CONSTRAINT `fk_msg_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `aq_support_tickets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 18. NEWSLETTER (Landing page: newsletter section)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `aq_newsletters` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(255) NOT NULL,
  `user_id` BIGINT UNSIGNED DEFAULT NULL,
  `source` ENUM('landing_page','dashboard','api','import') NOT NULL DEFAULT 'landing_page',
  `status` ENUM('subscribed','unsubscribed','bounced') NOT NULL DEFAULT 'subscribed',
  `subscribed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `unsubscribed_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_email` (`email`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 19. FAQ (Landing page: FAQ section with accordion)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `aq_faq` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category` VARCHAR(100) DEFAULT 'General',
  `question` TEXT NOT NULL,
  `answer` TEXT NOT NULL,
  `language` VARCHAR(5) NOT NULL DEFAULT 'en',
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_published` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_lang_published` (`language`, `is_published`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 20. TESTIMONIALS & CASE STUDIES (Landing page: Cases section)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `aq_testimonials` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `author_name` VARCHAR(100) NOT NULL,
  `author_title` VARCHAR(100) DEFAULT NULL,
  `author_company` VARCHAR(100) DEFAULT NULL,
  `author_avatar_url` VARCHAR(500) DEFAULT NULL,
  `quote` TEXT NOT NULL,
  `rating` TINYINT UNSIGNED DEFAULT NULL COMMENT '1-5 stars',
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `is_published` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `aq_case_studies` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `client_name` VARCHAR(100) NOT NULL,
  `client_logo_url` VARCHAR(500) DEFAULT NULL,
  `summary` TEXT DEFAULT NULL,
  `content` LONGTEXT DEFAULT NULL,
  `metric_impressions` VARCHAR(50) DEFAULT NULL COMMENT 'e.g. 1.8B+',
  `metric_revenue_increase` VARCHAR(50) DEFAULT NULL COMMENT 'e.g. +340%',
  `metric_ctr` VARCHAR(50) DEFAULT NULL,
  `metric_custom_label` VARCHAR(100) DEFAULT NULL,
  `metric_custom_value` VARCHAR(50) DEFAULT NULL,
  `is_published` TINYINT(1) NOT NULL DEFAULT 0,
  `published_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 21. TRUSTED PUBLISHERS (Landing page: marquee logos)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `aq_trusted_publishers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `logo_url` VARCHAR(500) DEFAULT NULL,
  `website_url` VARCHAR(500) DEFAULT NULL,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed from landing page marquee
INSERT INTO `aq_trusted_publishers` (`name`, `logo_url`, `is_featured`, `sort_order`) VALUES
('TVSH+',          './TVSH+.png',          1, 1),
('ShqipMedia',     './Shqipmedia.png',     1, 2),
('KosovaNet',      './KosovaNet.png',      1, 3),
('AlbaTech',       './AlbaTech.png',       1, 4),
('Prishtina Post', './PrishtinaPost.png',  1, 5),
('BalkanWeb',      './BalkanWeb.png',      1, 6),
('Top Channel',    NULL,                   1, 7);

-- ============================================================================
-- 22. COOKIE CONSENT & GDPR (Landing page: cookie consent banner)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `aq_cookie_consents` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `visitor_id` VARCHAR(64) NOT NULL COMMENT 'Anonymous visitor fingerprint',
  `user_id` BIGINT UNSIGNED DEFAULT NULL,
  `consent_type` ENUM('accept_all','reject_non_essential','custom') NOT NULL,
  `essential_cookies` TINYINT(1) NOT NULL DEFAULT 1,
  `analytics_cookies` TINYINT(1) NOT NULL DEFAULT 0,
  `marketing_cookies` TINYINT(1) NOT NULL DEFAULT 0,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` VARCHAR(500) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_visitor` (`visitor_id`),
  KEY `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 23. LANGUAGES (Landing page: EN, SQ, IT, DE selector)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `aq_languages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(5) NOT NULL,
  `name` VARCHAR(50) NOT NULL,
  `native_name` VARCHAR(50) NOT NULL,
  `is_default` TINYINT(1) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `aq_languages` (`code`, `name`, `native_name`, `is_default`, `sort_order`) VALUES
('en', 'English',  'English',  1, 1),
('sq', 'Albanian', 'Shqip',    0, 2),
('it', 'Italian',  'Italiano', 0, 3),
('de', 'German',   'Deutsch',  0, 4);

-- ============================================================================
-- 24. API KEYS & SDK (Landing page: SDK & API section)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `aq_api_keys` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(100) NOT NULL DEFAULT 'Default',
  `api_key` VARCHAR(64) NOT NULL,
  `api_secret_hash` VARCHAR(255) NOT NULL,
  `permissions` JSON DEFAULT NULL COMMENT '["read","write","admin"]',
  `rate_limit_per_minute` INT NOT NULL DEFAULT 60,
  `allowed_ips` JSON DEFAULT NULL,
  `last_used_at` DATETIME DEFAULT NULL,
  `expires_at` DATETIME DEFAULT NULL,
  `status` ENUM('active','revoked') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_api_key` (`api_key`),
  KEY `idx_user` (`user_id`),
  CONSTRAINT `fk_apikey_user` FOREIGN KEY (`user_id`) REFERENCES `aq_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 25. MOBILE APP (Landing page: Mobile App beta, iOS/Android)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `aq_mobile_devices` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `device_token` VARCHAR(500) NOT NULL COMMENT 'FCM/APNs push token',
  `platform` ENUM('ios','android') NOT NULL,
  `device_model` VARCHAR(100) DEFAULT NULL,
  `os_version` VARCHAR(20) DEFAULT NULL,
  `app_version` VARCHAR(20) DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `last_active_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_platform` (`platform`),
  CONSTRAINT `fk_device_user` FOREIGN KEY (`user_id`) REFERENCES `aq_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 26. CAREERS (old: djax_careers)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `aq_careers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL COMMENT 'old: job_category',
  `department` VARCHAR(100) DEFAULT NULL,
  `location` VARCHAR(100) DEFAULT 'Tirana, Albania',
  `employment_type` ENUM('full_time','part_time','contract','remote') NOT NULL DEFAULT 'full_time',
  `summary` TEXT DEFAULT NULL COMMENT 'old: job_summary',
  `description` LONGTEXT DEFAULT NULL COMMENT 'old: job_description',
  `requirements` TEXT DEFAULT NULL,
  `salary_range` VARCHAR(100) DEFAULT NULL,
  `is_published` TINYINT(1) NOT NULL DEFAULT 0,
  `published_at` DATETIME DEFAULT NULL,
  `expires_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_published` (`is_published`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 27. PLATFORM SETTINGS (old: djax_app_configurations)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `aq_platform_settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(100) NOT NULL,
  `setting_value` TEXT DEFAULT NULL,
  `setting_type` ENUM('string','integer','boolean','json','decimal') NOT NULL DEFAULT 'string',
  `category` VARCHAR(50) NOT NULL DEFAULT 'general',
  `description` VARCHAR(255) DEFAULT NULL,
  `updated_by` BIGINT UNSIGNED DEFAULT NULL,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed essential platform settings
INSERT INTO `aq_platform_settings` (`setting_key`, `setting_value`, `setting_type`, `category`, `description`) VALUES
('platform_name',              'Adshqip',                'string',  'general',   'Platform display name'),
('platform_url',               'https://adshqip.com',    'string',  'general',   'Platform URL'),
('default_currency',           'EUR',                    'string',  'billing',   'Default currency'),
('min_payout_amount',          '50.00',                  'decimal', 'billing',   'Minimum payout threshold'),
('publisher_revenue_share',    '70',                     'integer', 'billing',   'Publisher revenue share %'),
('min_impressions_per_day',    '1000',                   'integer', 'serving',   'old: min_impressions_day_banner'),
('default_frequency_cap',      '3',                      'integer', 'serving',   'Default frequency cap per user per day'),
('fraud_detection_enabled',    '1',                      'boolean', 'security',  'Enable anti-fraud system'),
('gdpr_enabled',               '1',                      'boolean', 'compliance','GDPR compliance mode'),
('cookie_consent_required',    '1',                      'boolean', 'compliance','Require cookie consent'),
('maintenance_mode',           '0',                      'boolean', 'general',   'Maintenance mode toggle'),
('api_rate_limit',             '60',                     'integer', 'api',       'API requests per minute'),
('mobile_app_version_ios',     '1.0.0-beta',             'string',  'mobile',    'Current iOS app version'),
('mobile_app_version_android', '1.0.0-beta',             'string',  'mobile',    'Current Android app version');

-- ============================================================================
-- 28. STORED PROCEDURES (modernized from old getAd, sp_fetch_country, etc.)
-- ============================================================================

DELIMITER $$

-- Replaces old: getAd procedure
-- Fetches eligible ads for a given zone with fraud/budget/status checks
CREATE PROCEDURE `aq_get_eligible_ads`(
  IN p_zone_id BIGINT UNSIGNED,
  IN p_country_code CHAR(2),
  IN p_device_type VARCHAR(10),
  IN p_blocked_domains TEXT
)
BEGIN
  SELECT
    a.id AS ad_id,
    a.name AS ad_name,
    a.ad_type,
    a.destination_url,
    a.headline,
    a.body_text,
    a.call_to_action,
    c.id AS campaign_id,
    c.campaign_type,
    c.bid_amount,
    c.daily_budget,
    c.remaining_budget,
    c.weight,
    ac.file_path AS creative_url,
    ac.width,
    ac.height,
    af.slug AS format_slug
  FROM aq_ads a
  JOIN aq_campaigns c ON a.campaign_id = c.id
  JOIN aq_users u ON c.advertiser_id = u.id
  JOIN aq_user_profiles up ON u.id = up.user_id
  LEFT JOIN aq_ad_creatives ac ON a.id = ac.ad_id AND ac.is_primary = 1
  LEFT JOIN aq_ad_formats af ON c.format_id = af.id
  WHERE
    a.status = 'active'
    AND a.is_deleted = 0
    AND a.admin_approved = 1
    AND c.status = 'active'
    AND c.is_deleted = 0
    AND c.admin_approved = 1
    AND (c.remaining_budget IS NULL OR c.remaining_budget > 0)
    AND (c.start_date IS NULL OR c.start_date <= NOW())
    AND (c.end_date IS NULL OR c.end_date >= NOW())
    AND u.status = 'active'
    AND u.is_deleted = 0
    AND up.is_denied = 0
    AND up.balance > 0
    AND (p_blocked_domains IS NULL OR a.destination_url NOT IN (p_blocked_domains))
  ORDER BY c.bid_amount DESC, c.weight DESC
  LIMIT 10;
END$$

-- Replaces old: sp_fetch_country
CREATE PROCEDURE `aq_search_countries`(
  IN p_query VARCHAR(100)
)
BEGIN
  SELECT iso_code AS id, name AS text, name_sq, is_balkan
  FROM aq_geo_countries
  WHERE name LIKE CONCAT(p_query, '%')
     OR name_sq LIKE CONCAT(p_query, '%')
     OR iso_code = p_query
  ORDER BY is_balkan DESC, name ASC;
END$$

-- Replaces old: sp_fetch_regions
CREATE PROCEDURE `aq_search_regions`(
  IN p_country_code CHAR(2)
)
BEGIN
  SELECT id, region_code, name
  FROM aq_geo_regions
  WHERE country_code = p_country_code
  ORDER BY name ASC;
END$$

-- New: Dashboard earnings summary (for mobile app sparkline chart)
CREATE PROCEDURE `aq_get_earnings_summary`(
  IN p_user_id BIGINT UNSIGNED,
  IN p_days INT
)
BEGIN
  SELECT
    date,
    SUM(publisher_earnings) AS earnings,
    SUM(impressions) AS impressions,
    SUM(clicks) AS clicks,
    CASE WHEN SUM(impressions) > 0
      THEN ROUND(SUM(clicks) / SUM(impressions) * 100, 2)
      ELSE 0
    END AS ctr
  FROM aq_stats_daily
  WHERE publisher_id = p_user_id
    AND date >= DATE_SUB(CURDATE(), INTERVAL p_days DAY)
  GROUP BY date
  ORDER BY date ASC;
END$$

DELIMITER ;

-- ============================================================================
-- 29. VIEWS (for Analytics Dashboard)
-- ============================================================================

CREATE OR REPLACE VIEW `aq_view_campaign_performance` AS
SELECT
  c.id AS campaign_id,
  c.name AS campaign_name,
  c.campaign_type,
  c.status,
  u.email AS advertiser_email,
  af.name AS format_name,
  COALESCE(SUM(s.impressions), 0) AS total_impressions,
  COALESCE(SUM(s.clicks), 0) AS total_clicks,
  COALESCE(SUM(s.conversions), 0) AS total_conversions,
  COALESCE(SUM(s.revenue), 0) AS total_spend,
  CASE WHEN SUM(s.impressions) > 0
    THEN ROUND(SUM(s.clicks) / SUM(s.impressions) * 100, 4)
    ELSE 0
  END AS ctr,
  CASE WHEN SUM(s.impressions) > 0
    THEN ROUND(SUM(s.revenue) / SUM(s.impressions) * 1000, 4)
    ELSE 0
  END AS ecpm
FROM aq_campaigns c
JOIN aq_users u ON c.advertiser_id = u.id
LEFT JOIN aq_ad_formats af ON c.format_id = af.id
LEFT JOIN aq_stats_daily s ON c.id = s.campaign_id
WHERE c.is_deleted = 0
GROUP BY c.id, c.name, c.campaign_type, c.status, u.email, af.name;

CREATE OR REPLACE VIEW `aq_view_publisher_earnings` AS
SELECT
  u.id AS publisher_id,
  u.email,
  up.first_name,
  up.last_name,
  up.company_name,
  up.balance,
  COALESCE(SUM(s.publisher_earnings), 0) AS total_earnings,
  COALESCE(SUM(s.impressions), 0) AS total_impressions,
  COALESCE(SUM(s.clicks), 0) AS total_clicks,
  COUNT(DISTINCT si.id) AS total_sites,
  COUNT(DISTINCT z.id) AS total_zones
FROM aq_users u
JOIN aq_user_profiles up ON u.id = up.user_id
LEFT JOIN aq_stats_daily s ON u.id = s.publisher_id
LEFT JOIN aq_sites si ON u.id = si.publisher_id AND si.is_deleted = 0
LEFT JOIN aq_zones z ON si.id = z.site_id AND z.is_deleted = 0
WHERE u.role = 'publisher' AND u.is_deleted = 0
GROUP BY u.id, u.email, up.first_name, up.last_name, up.company_name, up.balance;

-- ============================================================================
-- 30. TELEGRAM MINI APPS
-- ============================================================================
-- Telegram Mini Apps (TWA) allow publishers and advertisers to interact with
-- the Adshqip platform directly inside Telegram. Each mini app is registered
-- here with its bot token, webhook, and configuration. Sessions track
-- Telegram-authenticated users, and events capture in-app analytics.
-- ============================================================================

CREATE TABLE IF NOT EXISTS `aq_telegram_mini_apps` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'Owner (publisher or advertiser)',
  `app_name` VARCHAR(255) NOT NULL COMMENT 'Display name inside Telegram',
  `app_short_name` VARCHAR(64) NOT NULL COMMENT 'Telegram bot menu button short_name',
  `bot_username` VARCHAR(100) NOT NULL COMMENT '@BotFather bot username',
  `bot_token_hash` VARCHAR(255) NOT NULL COMMENT 'Encrypted bot token',
  `app_url` VARCHAR(500) NOT NULL COMMENT 'HTTPS URL served as the mini app',
  `icon_url` VARCHAR(500) DEFAULT NULL COMMENT 'Mini app icon / thumbnail',
  `description` TEXT DEFAULT NULL,
  `category` ENUM('monetization','analytics','campaign_manager','ad_preview','custom') NOT NULL DEFAULT 'custom',
  `webhook_url` VARCHAR(500) DEFAULT NULL COMMENT 'Incoming update webhook',
  `webhook_secret_hash` VARCHAR(255) DEFAULT NULL,
  `allowed_origins` JSON DEFAULT NULL COMMENT 'Allowed web_app_data origins',
  `theme_params` JSON DEFAULT NULL COMMENT 'Telegram theme color overrides',
  `inline_mode_enabled` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Allow inline query integration',
  `payment_enabled` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Telegram Payments API enabled',
  `payment_provider_token_hash` VARCHAR(255) DEFAULT NULL COMMENT 'Encrypted Stripe/etc. provider token',
  `menu_button_enabled` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Show as bot menu button',
  `expand_on_open` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Open in expanded mode',
  `status` ENUM('draft','pending_review','active','suspended','archived') NOT NULL DEFAULT 'draft',
  `admin_approved` TINYINT(1) NOT NULL DEFAULT 0,
  `rejection_reason` TEXT DEFAULT NULL,
  `total_sessions` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Denormalized session counter',
  `total_events` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Denormalized event counter',
  `last_active_at` DATETIME DEFAULT NULL,
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_bot_username` (`bot_username`),
  UNIQUE KEY `uk_app_short_name` (`app_short_name`),
  KEY `idx_user` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_category` (`category`),
  CONSTRAINT `fk_tma_user` FOREIGN KEY (`user_id`) REFERENCES `aq_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tracks each time a Telegram user opens / authenticates with a mini app
CREATE TABLE IF NOT EXISTS `aq_telegram_mini_app_sessions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `mini_app_id` BIGINT UNSIGNED NOT NULL,
  `telegram_user_id` BIGINT NOT NULL COMMENT 'Telegram numeric user ID',
  `telegram_username` VARCHAR(100) DEFAULT NULL,
  `telegram_first_name` VARCHAR(100) DEFAULT NULL,
  `telegram_last_name` VARCHAR(100) DEFAULT NULL,
  `telegram_language_code` VARCHAR(10) DEFAULT NULL,
  `telegram_is_premium` TINYINT(1) NOT NULL DEFAULT 0,
  `auth_date` DATETIME NOT NULL COMMENT 'initData auth_date',
  `init_data_hash` VARCHAR(255) NOT NULL COMMENT 'Validated initData hash',
  `query_id` VARCHAR(64) DEFAULT NULL COMMENT 'web_app_query_id for answerWebAppQuery',
  `platform` VARCHAR(30) DEFAULT NULL COMMENT 'tdesktop, android, ios, web, etc.',
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` VARCHAR(500) DEFAULT NULL,
  `start_param` VARCHAR(255) DEFAULT NULL COMMENT 'Deep-link start parameter',
  `referrer_mini_app_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'If opened via another mini app',
  `duration_seconds` INT UNSIGNED DEFAULT NULL COMMENT 'Session duration (updated on close)',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ended_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_mini_app` (`mini_app_id`),
  KEY `idx_tg_user` (`telegram_user_id`),
  KEY `idx_created` (`created_at`),
  KEY `idx_referrer` (`referrer_mini_app_id`),
  CONSTRAINT `fk_tmas_app` FOREIGN KEY (`mini_app_id`) REFERENCES `aq_telegram_mini_apps` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tmas_referrer` FOREIGN KEY (`referrer_mini_app_id`) REFERENCES `aq_telegram_mini_apps` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Granular in-app events (ad views, clicks, purchases, custom)
CREATE TABLE IF NOT EXISTS `aq_telegram_mini_app_events` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `mini_app_id` BIGINT UNSIGNED NOT NULL,
  `session_id` BIGINT UNSIGNED DEFAULT NULL,
  `telegram_user_id` BIGINT NOT NULL,
  `event_type` ENUM('page_view','ad_impression','ad_click','purchase','custom') NOT NULL DEFAULT 'custom',
  `event_name` VARCHAR(100) NOT NULL COMMENT 'e.g. banner_viewed, cta_clicked, checkout_complete',
  `event_data` JSON DEFAULT NULL COMMENT 'Arbitrary payload',
  `revenue` DECIMAL(12,4) DEFAULT NULL COMMENT 'Revenue attributed to this event',
  `currency` VARCHAR(3) DEFAULT 'EUR',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_mini_app` (`mini_app_id`),
  KEY `idx_session` (`session_id`),
  KEY `idx_tg_user` (`telegram_user_id`),
  KEY `idx_event_type` (`event_type`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `fk_tmae_app` FOREIGN KEY (`mini_app_id`) REFERENCES `aq_telegram_mini_apps` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tmae_session` FOREIGN KEY (`session_id`) REFERENCES `aq_telegram_mini_app_sessions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 31. KYC — KNOW YOUR CUSTOMER VERIFICATION
-- ============================================================================
-- Publishers and advertisers must pass KYC before withdrawing funds or
-- launching high-budget campaigns. Supports multi-document upload, manual
-- admin review, and automated status transitions.
-- ============================================================================

CREATE TABLE IF NOT EXISTS `aq_kyc_verifications` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `verification_level` ENUM('basic','standard','enhanced') NOT NULL DEFAULT 'basic' COMMENT 'Tiered KYC levels',
  `status` ENUM('not_started','pending','in_review','approved','rejected','expired') NOT NULL DEFAULT 'not_started',

  -- Personal / business info snapshot (may differ from profile)
  `legal_first_name` VARCHAR(100) DEFAULT NULL,
  `legal_last_name` VARCHAR(100) DEFAULT NULL,
  `date_of_birth` DATE DEFAULT NULL,
  `nationality` CHAR(2) DEFAULT NULL COMMENT 'ISO 3166-1 alpha-2',
  `id_number` VARCHAR(100) DEFAULT NULL COMMENT 'National ID / passport number (encrypted at app layer)',
  `id_type` ENUM('passport','national_id','drivers_license','residence_permit') DEFAULT NULL,
  `id_issuing_country` CHAR(2) DEFAULT NULL,
  `id_expiry_date` DATE DEFAULT NULL,

  -- Business KYC (for companies)
  `business_name` VARCHAR(255) DEFAULT NULL,
  `business_registration_number` VARCHAR(100) DEFAULT NULL,
  `business_type` ENUM('individual','sole_proprietor','llc','corporation','partnership','non_profit') DEFAULT NULL,
  `business_country` CHAR(2) DEFAULT NULL,
  `business_address` TEXT DEFAULT NULL,
  `vat_number` VARCHAR(50) DEFAULT NULL,

  -- Review
  `reviewer_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'Admin who reviewed',
  `reviewed_at` DATETIME DEFAULT NULL,
  `rejection_reason` TEXT DEFAULT NULL,
  `rejection_count` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'How many times rejected',
  `notes` TEXT DEFAULT NULL COMMENT 'Internal admin notes',

  -- Risk scoring
  `risk_score` DECIMAL(5,2) DEFAULT NULL COMMENT '0-100, higher = riskier',
  `risk_flags` JSON DEFAULT NULL COMMENT 'e.g. ["pep","sanctions_match","high_risk_country"]',
  `aml_check_passed` TINYINT(1) DEFAULT NULL COMMENT 'Anti-money-laundering check result',
  `sanctions_check_passed` TINYINT(1) DEFAULT NULL,

  -- Timestamps
  `submitted_at` DATETIME DEFAULT NULL,
  `approved_at` DATETIME DEFAULT NULL,
  `expires_at` DATETIME DEFAULT NULL COMMENT 'KYC validity period',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_level` (`verification_level`),
  KEY `idx_reviewer` (`reviewer_id`),
  CONSTRAINT `fk_kyc_user` FOREIGN KEY (`user_id`) REFERENCES `aq_users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_kyc_reviewer` FOREIGN KEY (`reviewer_id`) REFERENCES `aq_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Supporting documents uploaded for KYC (ID scans, selfies, utility bills, etc.)
CREATE TABLE IF NOT EXISTS `aq_kyc_documents` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kyc_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `document_type` ENUM('id_front','id_back','passport','selfie','selfie_with_id','proof_of_address','business_registration','tax_certificate','bank_statement','other') NOT NULL,
  `file_path` VARCHAR(500) NOT NULL COMMENT 'Stored in secure / encrypted bucket',
  `file_name` VARCHAR(255) DEFAULT NULL,
  `mime_type` VARCHAR(100) DEFAULT NULL,
  `file_size_bytes` INT UNSIGNED DEFAULT NULL,
  `file_hash` VARCHAR(128) DEFAULT NULL COMMENT 'SHA-256 for integrity',
  `status` ENUM('uploaded','verified','rejected','expired') NOT NULL DEFAULT 'uploaded',
  `rejection_reason` VARCHAR(500) DEFAULT NULL,
  `verified_by` BIGINT UNSIGNED DEFAULT NULL COMMENT 'Admin who verified this doc',
  `verified_at` DATETIME DEFAULT NULL,
  `expires_at` DATETIME DEFAULT NULL COMMENT 'Document expiry date',
  `metadata` JSON DEFAULT NULL COMMENT 'OCR results, MRZ data, etc.',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_kyc` (`kyc_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_type` (`document_type`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_kycdoc_kyc` FOREIGN KEY (`kyc_id`) REFERENCES `aq_kyc_verifications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_kycdoc_user` FOREIGN KEY (`user_id`) REFERENCES `aq_users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_kycdoc_verifier` FOREIGN KEY (`verified_by`) REFERENCES `aq_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 32. MULTITAG — AD FORMAT TAGGING SYSTEM
-- ============================================================================
-- Allows assigning multiple tags to ad formats (e.g. "high-cpm",
-- "mobile-first", "gdpr-safe", "video-ready"). Tags are reusable across
-- formats and enable flexible filtering in the campaign builder and
-- publisher zone setup.
-- ============================================================================

CREATE TABLE IF NOT EXISTS `aq_tags` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL,
  `color` VARCHAR(7) DEFAULT '#6366f1' COMMENT 'Hex color for UI badge',
  `description` VARCHAR(500) DEFAULT NULL,
  `tag_group` VARCHAR(50) DEFAULT NULL COMMENT 'e.g. performance, compliance, device, content',
  `is_system` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'System-managed vs user-created',
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_slug` (`slug`),
  KEY `idx_group` (`tag_group`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default system tags
INSERT INTO `aq_tags` (`name`, `slug`, `color`, `tag_group`, `is_system`) VALUES
('High CPM',       'high-cpm',       '#ef4444', 'performance', 1),
('High CTR',       'high-ctr',       '#f97316', 'performance', 1),
('Mobile First',   'mobile-first',   '#8b5cf6', 'device',      1),
('Desktop Only',   'desktop-only',   '#6366f1', 'device',      1),
('AMP Compatible', 'amp-compatible', '#14b8a6', 'compliance',  1),
('GDPR Safe',      'gdpr-safe',      '#22c55e', 'compliance',  1),
('Video Ready',    'video-ready',    '#3b82f6', 'content',     1),
('Interactive',    'interactive',    '#ec4899', 'content',     1),
('Non-Intrusive',  'non-intrusive',  '#10b981', 'content',     1),
('Brand Safe',     'brand-safe',     '#0ea5e9', 'compliance',  1);

-- Many-to-many: ad formats ↔ tags
CREATE TABLE IF NOT EXISTS `aq_ad_format_tags` (
  `format_id` INT UNSIGNED NOT NULL,
  `tag_id` INT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`format_id`, `tag_id`),
  KEY `idx_tag` (`tag_id`),
  CONSTRAINT `fk_aft_format` FOREIGN KEY (`format_id`) REFERENCES `aq_ad_formats` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_aft_tag` FOREIGN KEY (`tag_id`) REFERENCES `aq_tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed format ↔ tag associations for the 7 existing ad formats
-- (format IDs 1-7 from the aq_ad_formats seed data)
INSERT INTO `aq_ad_format_tags` (`format_id`, `tag_id`) VALUES
-- Popunder: High CPM, GDPR Safe
(1, 1), (1, 6),
-- Native Feed: High CTR, Mobile First, AMP Compatible, Brand Safe
(2, 2), (2, 3), (2, 5), (2, 10),
-- Interstitial: High CPM, Mobile First
(3, 1), (3, 3),
-- In-Page Push: Mobile First, AMP Compatible, Non-Intrusive
(4, 3), (4, 5), (4, 9),
-- Text & Smart Banners: AMP Compatible, Non-Intrusive, GDPR Safe
(5, 5), (5, 9), (5, 6),
-- Native Video: Video Ready, Mobile First
(6, 7), (6, 3),
-- Rich Media: Interactive, Video Ready
(7, 8), (7, 7);

-- ============================================================================
-- 33. REFERRAL PROGRAM
-- ============================================================================
-- Users can generate referral links to invite new publishers or advertisers.
-- Referrers earn a commission on the referred user's spend or earnings for
-- a configurable period. Supports multi-tier tracking and fraud checks.
-- ============================================================================

CREATE TABLE IF NOT EXISTS `aq_referral_links` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `referrer_id` BIGINT UNSIGNED NOT NULL COMMENT 'User who created the link',
  `code` VARCHAR(32) NOT NULL COMMENT 'Unique referral code (URL-safe)',
  `slug` VARCHAR(100) DEFAULT NULL COMMENT 'Optional vanity slug, e.g. /ref/john',
  `target_role` ENUM('advertiser','publisher','any') NOT NULL DEFAULT 'any' COMMENT 'Which role the link targets',
  `campaign_name` VARCHAR(255) DEFAULT NULL COMMENT 'Internal label for tracking campaigns',
  `landing_url` VARCHAR(500) DEFAULT NULL COMMENT 'Custom landing page override',
  `utm_source` VARCHAR(100) DEFAULT NULL,
  `utm_medium` VARCHAR(100) DEFAULT NULL,
  `utm_campaign` VARCHAR(100) DEFAULT NULL,

  -- Commission structure
  `commission_type` ENUM('percentage','flat') NOT NULL DEFAULT 'percentage',
  `commission_rate` DECIMAL(8,4) NOT NULL DEFAULT 5.0000 COMMENT '% of referred user spend/earnings or flat EUR amount',
  `commission_duration_days` INT UNSIGNED DEFAULT 365 COMMENT 'How long referrer earns commission (NULL = lifetime)',
  `max_commission_per_referral` DECIMAL(12,4) DEFAULT NULL COMMENT 'Cap per referred user',

  -- Counters (denormalized for dashboard)
  `total_clicks` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `total_signups` INT UNSIGNED NOT NULL DEFAULT 0,
  `total_qualified` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Signups that passed min threshold',
  `total_earned` DECIMAL(12,4) NOT NULL DEFAULT 0.0000,

  `status` ENUM('active','paused','expired','revoked') NOT NULL DEFAULT 'active',
  `expires_at` DATETIME DEFAULT NULL,
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_code` (`code`),
  UNIQUE KEY `uk_slug` (`slug`),
  KEY `idx_referrer` (`referrer_id`),
  KEY `idx_status` (`status`),
  KEY `idx_target_role` (`target_role`),
  CONSTRAINT `fk_reflink_user` FOREIGN KEY (`referrer_id`) REFERENCES `aq_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tracks each successful referral conversion (signup + qualification)
CREATE TABLE IF NOT EXISTS `aq_referral_conversions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `link_id` BIGINT UNSIGNED NOT NULL,
  `referrer_id` BIGINT UNSIGNED NOT NULL,
  `referred_user_id` BIGINT UNSIGNED NOT NULL COMMENT 'The new user who signed up',
  `referred_role` ENUM('advertiser','publisher') NOT NULL,

  -- Attribution
  `click_ip` VARCHAR(45) DEFAULT NULL,
  `click_user_agent` VARCHAR(500) DEFAULT NULL,
  `click_referer` VARCHAR(2000) DEFAULT NULL,
  `signup_ip` VARCHAR(45) DEFAULT NULL,
  `cookie_id` VARCHAR(64) DEFAULT NULL COMMENT 'First-party attribution cookie',

  -- Qualification
  `is_qualified` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Met minimum spend/earnings threshold',
  `qualified_at` DATETIME DEFAULT NULL,
  `qualification_threshold` DECIMAL(12,4) DEFAULT NULL COMMENT 'Threshold amount that was met',

  -- Commission tracking
  `commission_earned` DECIMAL(12,4) NOT NULL DEFAULT 0.0000 COMMENT 'Running total earned from this referral',
  `commission_currency` VARCHAR(3) NOT NULL DEFAULT 'EUR',
  `commission_ends_at` DATETIME DEFAULT NULL COMMENT 'When commission period expires',

  `status` ENUM('pending','active','qualified','expired','fraudulent') NOT NULL DEFAULT 'pending',
  `fraud_flags` JSON DEFAULT NULL COMMENT 'e.g. ["same_ip","rapid_signup"]',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_referred_user` (`referred_user_id`) COMMENT 'A user can only be referred once',
  KEY `idx_link` (`link_id`),
  KEY `idx_referrer` (`referrer_id`),
  KEY `idx_status` (`status`),
  KEY `idx_qualified` (`is_qualified`),
  CONSTRAINT `fk_refconv_link` FOREIGN KEY (`link_id`) REFERENCES `aq_referral_links` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_refconv_referrer` FOREIGN KEY (`referrer_id`) REFERENCES `aq_users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_refconv_referred` FOREIGN KEY (`referred_user_id`) REFERENCES `aq_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Referral commission payouts (separate from regular aq_payouts)
CREATE TABLE IF NOT EXISTS `aq_referral_payouts` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `referrer_id` BIGINT UNSIGNED NOT NULL,
  `amount` DECIMAL(12,4) NOT NULL,
  `currency` VARCHAR(3) NOT NULL DEFAULT 'EUR',
  `payment_method` ENUM('balance_credit','paypal','wire_transfer','crypto','payoneer') NOT NULL DEFAULT 'balance_credit',
  `payment_reference` VARCHAR(255) DEFAULT NULL,
  `period_start` DATE NOT NULL,
  `period_end` DATE NOT NULL,
  `conversions_count` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Number of referrals included',
  `status` ENUM('pending','processing','completed','failed','cancelled') NOT NULL DEFAULT 'pending',
  `processed_at` DATETIME DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_referrer` (`referrer_id`),
  KEY `idx_status` (`status`),
  KEY `idx_period` (`period_start`, `period_end`),
  CONSTRAINT `fk_refpayout_user` FOREIGN KEY (`referrer_id`) REFERENCES `aq_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- View: Referral program dashboard summary
CREATE OR REPLACE VIEW `aq_view_referral_summary` AS
SELECT
  rl.referrer_id,
  u.email AS referrer_email,
  COUNT(DISTINCT rl.id) AS total_links,
  COALESCE(SUM(rl.total_clicks), 0) AS total_clicks,
  COALESCE(SUM(rl.total_signups), 0) AS total_signups,
  COALESCE(SUM(rl.total_qualified), 0) AS total_qualified,
  COALESCE(SUM(rl.total_earned), 0) AS total_earned,
  COUNT(DISTINCT rc.id) AS total_conversions,
  COALESCE(SUM(rc.commission_earned), 0) AS total_commission
FROM aq_referral_links rl
JOIN aq_users u ON rl.referrer_id = u.id
LEFT JOIN aq_referral_conversions rc ON rl.id = rc.link_id
WHERE rl.is_deleted = 0
GROUP BY rl.referrer_id, u.email;

-- ============================================================================
-- 35. WALLET & ADD FUNDS
-- ============================================================================
-- Advertisers must add funds to their balance before creating/running campaigns.
-- The aq_get_eligible_ads procedure already checks up.balance > 0.
-- This section provides the full transaction ledger and payment method storage.
-- ============================================================================

-- Saved payment methods for adding funds (tokenized, never store raw card data)
CREATE TABLE IF NOT EXISTS `aq_saved_payment_methods` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `label` VARCHAR(100) NOT NULL DEFAULT 'My Card' COMMENT 'User-friendly name, e.g. "Visa ending 4242"',
  `type` ENUM('credit_card','debit_card','paypal','crypto_wallet','wire_transfer') NOT NULL,
  `gateway` ENUM('stripe','paypal','coinbase','manual') NOT NULL DEFAULT 'stripe',
  `gateway_customer_id` VARCHAR(255) DEFAULT NULL COMMENT 'Stripe customer ID / PayPal payer ID',
  `gateway_payment_method_id` VARCHAR(255) NOT NULL COMMENT 'Tokenized payment method ID from gateway',
  `card_brand` VARCHAR(20) DEFAULT NULL COMMENT 'visa, mastercard, amex, etc.',
  `card_last4` CHAR(4) DEFAULT NULL,
  `card_exp_month` TINYINT UNSIGNED DEFAULT NULL,
  `card_exp_year` SMALLINT UNSIGNED DEFAULT NULL,
  `billing_country` CHAR(2) DEFAULT NULL,
  `is_default` TINYINT(1) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_gateway` (`gateway`),
  CONSTRAINT `fk_spm_user` FOREIGN KEY (`user_id`) REFERENCES `aq_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Full transaction ledger — every balance change is recorded here
CREATE TABLE IF NOT EXISTS `aq_transactions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `type` ENUM('deposit','withdrawal','ad_spend','refund','adjustment','welcome_bonus','referral_credit') NOT NULL,
  `amount` DECIMAL(12,4) NOT NULL COMMENT 'Positive = credit to balance, negative = debit',
  `currency` VARCHAR(3) NOT NULL DEFAULT 'EUR',
  `balance_before` DECIMAL(12,4) NOT NULL,
  `balance_after` DECIMAL(12,4) NOT NULL,

  -- Payment gateway details (for deposits/withdrawals)
  `payment_method_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'FK → aq_saved_payment_methods',
  `payment_gateway` ENUM('stripe','paypal','coinbase','wire_transfer','manual') DEFAULT NULL,
  `gateway_txn_id` VARCHAR(255) DEFAULT NULL COMMENT 'External gateway transaction/charge ID',
  `gateway_status` ENUM('pending','processing','confirmed','failed','refunded','cancelled') DEFAULT NULL,
  `gateway_response` JSON DEFAULT NULL COMMENT 'Raw gateway webhook/response payload',

  -- Metadata
  `campaign_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'For ad_spend transactions',
  `invoice_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'Linked invoice if generated',
  `description` VARCHAR(500) DEFAULT NULL COMMENT 'Human-readable description',
  `admin_note` VARCHAR(500) DEFAULT NULL COMMENT 'Internal note for manual adjustments',
  `initiated_by` BIGINT UNSIGNED DEFAULT NULL COMMENT 'Admin user_id for manual adjustments',
  `ip_address` VARCHAR(45) DEFAULT NULL,

  `status` ENUM('pending','completed','failed','reversed') NOT NULL DEFAULT 'pending',
  `completed_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_type` (`type`),
  KEY `idx_status` (`status`),
  KEY `idx_gateway_txn` (`gateway_txn_id`),
  KEY `idx_campaign` (`campaign_id`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `fk_txn_user` FOREIGN KEY (`user_id`) REFERENCES `aq_users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_txn_payment_method` FOREIGN KEY (`payment_method_id`) REFERENCES `aq_saved_payment_methods` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_txn_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `aq_invoices` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Safely add funds to an advertiser's balance (atomic: updates balance + logs transaction)
-- Moved here so aq_transactions table exists before procedure references it
DELIMITER $$

CREATE PROCEDURE `aq_add_funds`(
  IN p_user_id BIGINT UNSIGNED,
  IN p_amount DECIMAL(12,4),
  IN p_currency VARCHAR(3),
  IN p_payment_method_id BIGINT UNSIGNED,
  IN p_gateway ENUM('stripe','paypal','coinbase','wire_transfer','manual'),
  IN p_gateway_txn_id VARCHAR(255),
  IN p_description VARCHAR(500),
  IN p_ip_address VARCHAR(45)
)
BEGIN
  DECLARE v_balance_before DECIMAL(12,4);
  DECLARE v_balance_after DECIMAL(12,4);

  -- Validate amount
  IF p_amount <= 0 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Deposit amount must be greater than zero';
  END IF;

  START TRANSACTION;

  -- Lock the row to prevent race conditions
  SELECT balance INTO v_balance_before
  FROM aq_user_profiles
  WHERE user_id = p_user_id
  FOR UPDATE;

  IF v_balance_before IS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'User profile not found';
  END IF;

  SET v_balance_after = v_balance_before + p_amount;

  -- Update balance
  UPDATE aq_user_profiles
  SET balance = v_balance_after
  WHERE user_id = p_user_id;

  -- Record the transaction
  INSERT INTO aq_transactions (
    user_id, `type`, amount, currency,
    balance_before, balance_after,
    payment_method_id, payment_gateway, gateway_txn_id,
    gateway_status, description, ip_address,
    status, completed_at
  ) VALUES (
    p_user_id, 'deposit', p_amount, p_currency,
    v_balance_before, v_balance_after,
    p_payment_method_id, p_gateway, p_gateway_txn_id,
    'confirmed', p_description, p_ip_address,
    'completed', NOW()
  );

  COMMIT;

  -- Return the new balance and transaction ID
  SELECT LAST_INSERT_ID() AS transaction_id, v_balance_after AS new_balance;
END$$

DELIMITER ;

-- ============================================================================
-- 36. CAMPAIGN OPTIMIZATION TOOLS
-- ============================================================================
-- Logs every action taken by the 4 optimization tools:
--   In-Line          : real-time bid adjustments per zone
--   SpendGuard       : budget overspend protection events
--   Performance Stimulator : bid boosts on high-converting zones
--   Pacing Health Score    : periodic pacing snapshots
-- ============================================================================

CREATE TABLE IF NOT EXISTS `aq_campaign_optimization` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `campaign_id` BIGINT UNSIGNED NOT NULL,
  `tool` ENUM('inline','spendguard','perf_stimulator','pacing_health') NOT NULL COMMENT 'Which optimization tool triggered this event',

  -- In-Line: real-time bid adjustment per zone
  `inline_zone_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'Zone that triggered the bid adjustment',
  `inline_bid_before` DECIMAL(10,4) DEFAULT NULL COMMENT 'Bid amount before In-Line adjustment',
  `inline_bid_after` DECIMAL(10,4) DEFAULT NULL COMMENT 'Bid amount after In-Line adjustment',
  `inline_adjustment_reason` VARCHAR(255) DEFAULT NULL COMMENT 'e.g. low CTR on zone, high conversion rate',

  -- SpendGuard: overspend protection
  `spendguard_daily_budget` DECIMAL(12,4) DEFAULT NULL COMMENT 'Daily budget at time of SpendGuard trigger',
  `spendguard_spent_so_far` DECIMAL(12,4) DEFAULT NULL COMMENT 'Amount spent when SpendGuard fired',
  `spendguard_action` ENUM('warning','soft_cap','hard_stop') DEFAULT NULL COMMENT 'Action SpendGuard took',

  -- Performance Stimulator: bid boost on high-converting zones
  `perf_zone_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'Zone that received the boost',
  `perf_metric` ENUM('ctr','conversions','roas','ecpm') DEFAULT NULL COMMENT 'Metric that triggered the boost',
  `perf_metric_value` DECIMAL(10,4) DEFAULT NULL COMMENT 'Metric value at time of boost',
  `perf_bid_boost_pct` DECIMAL(5,2) DEFAULT NULL COMMENT 'Actual % boost applied',

  -- Pacing Health Score: periodic snapshot
  `pacing_score` DECIMAL(5,2) DEFAULT NULL COMMENT '0-100 pacing health score at snapshot time',
  `pacing_status` ENUM('healthy','under_pacing','over_pacing','critical') DEFAULT NULL,
  `pacing_budget_consumed_pct` DECIMAL(5,2) DEFAULT NULL COMMENT '% of daily budget consumed at snapshot time',
  `pacing_time_elapsed_pct` DECIMAL(5,2) DEFAULT NULL COMMENT '% of day elapsed at snapshot time',

  -- Common
  `note` VARCHAR(500) DEFAULT NULL COMMENT 'Human-readable summary of the event',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_campaign` (`campaign_id`),
  KEY `idx_tool` (`tool`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `fk_opt_campaign` FOREIGN KEY (`campaign_id`) REFERENCES `aq_direct_campaigns` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 37. ACCOUNT DEACTIVATION
-- ============================================================================
-- Full audit log for every account status change (deactivation, suspension,
-- reactivation, closure). aq_users.status is the live flag; this table
-- stores the complete history of who changed it, why, and when.
-- ============================================================================

CREATE TABLE IF NOT EXISTS `aq_account_deactivations` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'Account that was affected',

  -- What happened
  `action` ENUM('deactivated','suspended','reactivated','closed','pending_review') NOT NULL COMMENT 'Status transition applied to the account',
  `previous_status` ENUM('active','inactive','suspended','pending_verification','closed') NOT NULL COMMENT 'aq_users.status before this action',
  `new_status` ENUM('active','inactive','suspended','pending_verification','closed') NOT NULL COMMENT 'aq_users.status after this action',

  -- Why it happened
  `reason_code` ENUM(
    'user_request',
    'inactivity',
    'payment_failure',
    'fraud_detected',
    'policy_violation',
    'kyc_failed',
    'duplicate_account',
    'admin_manual',
    'gdpr_erasure',
    'other'
  ) NOT NULL DEFAULT 'user_request' COMMENT 'Standardised reason category',
  `reason_detail` TEXT DEFAULT NULL COMMENT 'Free-text explanation or admin note',

  -- Who triggered it
  `triggered_by` ENUM('user','admin','system') NOT NULL DEFAULT 'user' COMMENT 'Who initiated the status change',
  `admin_user_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'Admin user_id if triggered_by = admin',

  -- Self-deactivation details (user-initiated)
  `user_feedback` TEXT DEFAULT NULL COMMENT 'Optional feedback the user provided on deactivation',
  `reactivation_token` VARCHAR(128) DEFAULT NULL COMMENT 'Secure token e-mailed to user to allow self-reactivation',
  `reactivation_token_expires_at` DATETIME DEFAULT NULL,
  `reactivated_at` DATETIME DEFAULT NULL COMMENT 'Timestamp when account was reactivated (NULL if still inactive)',

  -- Scheduled closure
  `scheduled_closure_at` DATETIME DEFAULT NULL COMMENT 'If set, account will be permanently closed at this time',
  `data_deletion_requested` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'GDPR erasure requested alongside deactivation',
  `data_deleted_at` DATETIME DEFAULT NULL COMMENT 'Timestamp when personal data was purged',

  -- Metadata
  `ip_address` VARCHAR(45) DEFAULT NULL COMMENT 'IP of the user or admin at time of action',
  `user_agent` VARCHAR(500) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created` (`created_at`),
  KEY `idx_reactivation_token` (`reactivation_token`),
  CONSTRAINT `fk_deact_user` FOREIGN KEY (`user_id`) REFERENCES `aq_users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_deact_admin` FOREIGN KEY (`admin_user_id`) REFERENCES `aq_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 38. TWO-FACTOR AUTHENTICATION
-- ============================================================================
-- aq_users already stores:
--   two_factor_enabled  TINYINT(1)   -- live ON/OFF flag
--   two_factor_secret   VARCHAR(255) -- TOTP shared secret (base32, encrypted)
-- These two tables add:
--   backup codes  : one-time recovery codes issued when 2FA is enabled
--   challenge log : every 2FA attempt with result and lockout tracking
-- ============================================================================

CREATE TABLE IF NOT EXISTS `aq_two_factor_backup_codes` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `code_hash` VARCHAR(255) NOT NULL COMMENT 'bcrypt hash of the 8-digit backup code',
  `used` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 once the code has been consumed',
  `used_at` DATETIME DEFAULT NULL COMMENT 'Timestamp the code was consumed',
  `used_ip` VARCHAR(45) DEFAULT NULL COMMENT 'IP address that consumed the code',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_used` (`used`),
  CONSTRAINT `fk_2fa_backup_user` FOREIGN KEY (`user_id`) REFERENCES `aq_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `aq_two_factor_challenges` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `method` ENUM('totp','backup_code','sms','email_otp') NOT NULL DEFAULT 'totp' COMMENT 'Which 2FA method was used',
  `result` ENUM('success','failed','expired','locked_out') NOT NULL COMMENT 'Outcome of the challenge attempt',
  `code_used` VARCHAR(10) DEFAULT NULL COMMENT 'The raw OTP submitted (stored briefly for audit, cleared after 24h)',
  `failure_count` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Consecutive failures at time of this attempt',
  `locked_until` DATETIME DEFAULT NULL COMMENT 'Account 2FA locked until this time after too many failures',
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` VARCHAR(500) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_result` (`result`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `fk_2fa_challenge_user` FOREIGN KEY (`user_id`) REFERENCES `aq_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 39. CLICK-TO-WATCH VIDEO — END CARDS & BRANDING OVERLAYS
-- ============================================================================
-- Supports the Click-to-Watch (CTW) video campaign type:
--   • Reusable end card templates advertisers can attach to any video ad
--   • Reusable branding overlay presets (logo watermark, intro bumper)
--   • CTW-specific VAST tracking events
-- Inline CTW / end-card / branding fields live on aq_ads & aq_direct_campaigns;
-- these tables allow library-style reuse across multiple ads.
-- ============================================================================

-- Reusable end card templates — advertisers build once, attach to many video ads
CREATE TABLE IF NOT EXISTS `aq_video_end_cards` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `advertiser_id` BIGINT UNSIGNED NOT NULL COMMENT 'FK → aq_users (owner)',
  `name` VARCHAR(255) NOT NULL COMMENT 'Template name e.g. "Summer Sale End Card"',
  `end_card_type` ENUM('static_image','html','cta_button','product_feed','custom') NOT NULL DEFAULT 'cta_button',
  `image_url` VARCHAR(500) DEFAULT NULL COMMENT 'Background / hero image',
  `html_content` TEXT DEFAULT NULL COMMENT 'Custom HTML (sanitized server-side)',
  `headline` VARCHAR(255) DEFAULT NULL,
  `body_text` TEXT DEFAULT NULL,
  `cta_text` VARCHAR(50) DEFAULT NULL COMMENT 'e.g. Shiko Tani, Bli Tani',
  `cta_url` VARCHAR(2000) DEFAULT NULL COMMENT 'Click-through URL',
  `cta_color` CHAR(7) DEFAULT NULL COMMENT 'CTA button hex color',
  `logo_url` VARCHAR(500) DEFAULT NULL COMMENT 'Logo shown on end card',
  `background_color` CHAR(7) DEFAULT '#FFFFFF',
  `display_seconds` INT UNSIGNED DEFAULT 10 COMMENT 'Duration end card stays visible',
  `is_default` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Default end card for this advertiser',
  `status` ENUM('active','inactive','archived') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_advertiser` (`advertiser_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_endcard_advertiser` FOREIGN KEY (`advertiser_id`) REFERENCES `aq_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Link end cards to ads (many-to-many: one ad can A/B test end cards)
CREATE TABLE IF NOT EXISTS `aq_ad_end_card_assoc` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ad_id` BIGINT UNSIGNED NOT NULL COMMENT 'FK → aq_ads',
  `end_card_id` BIGINT UNSIGNED NOT NULL COMMENT 'FK → aq_video_end_cards',
  `weight` INT NOT NULL DEFAULT 1 COMMENT 'Rotation weight for A/B testing end cards',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_ad_endcard` (`ad_id`, `end_card_id`),
  KEY `idx_endcard` (`end_card_id`),
  CONSTRAINT `fk_aec_ad` FOREIGN KEY (`ad_id`) REFERENCES `aq_ads` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_aec_endcard` FOREIGN KEY (`end_card_id`) REFERENCES `aq_video_end_cards` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reusable video branding overlay presets
CREATE TABLE IF NOT EXISTS `aq_video_branding_overlays` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `advertiser_id` BIGINT UNSIGNED NOT NULL COMMENT 'FK → aq_users (owner)',
  `name` VARCHAR(255) NOT NULL COMMENT 'Preset name e.g. "Main Brand Overlay"',
  `logo_url` VARCHAR(500) NOT NULL COMMENT 'Logo watermark image URL',
  `logo_position` ENUM('top_left','top_right','bottom_left','bottom_right') NOT NULL DEFAULT 'bottom_right',
  `logo_opacity` DECIMAL(3,2) NOT NULL DEFAULT 0.80 COMMENT '0.00-1.00',
  `logo_size_percent` TINYINT UNSIGNED DEFAULT 15 COMMENT 'Logo size as % of player width',
  `intro_video_url` VARCHAR(500) DEFAULT NULL COMMENT 'Brand intro bumper video (3-5s)',
  `intro_duration_seconds` INT UNSIGNED DEFAULT NULL,
  `outro_video_url` VARCHAR(500) DEFAULT NULL COMMENT 'Brand outro bumper video',
  `outro_duration_seconds` INT UNSIGNED DEFAULT NULL,
  `color_border` CHAR(7) DEFAULT NULL COMMENT 'Brand color border around player',
  `is_default` TINYINT(1) NOT NULL DEFAULT 0,
  `status` ENUM('active','inactive','archived') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_advertiser` (`advertiser_id`),
  CONSTRAINT `fk_branding_advertiser` FOREIGN KEY (`advertiser_id`) REFERENCES `aq_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Link branding overlays to ads
CREATE TABLE IF NOT EXISTS `aq_ad_branding_overlay_assoc` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ad_id` BIGINT UNSIGNED NOT NULL,
  `overlay_id` BIGINT UNSIGNED NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_ad_overlay` (`ad_id`, `overlay_id`),
  KEY `idx_overlay` (`overlay_id`),
  CONSTRAINT `fk_abo_ad` FOREIGN KEY (`ad_id`) REFERENCES `aq_ads` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_abo_overlay` FOREIGN KEY (`overlay_id`) REFERENCES `aq_video_branding_overlays` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- CTW-specific video tracking events (supplement aq_vast_events)
INSERT IGNORE INTO `aq_vast_events` (`event_name`, `description`, `is_trackable`) VALUES
('ctw_click_to_play',  'User clicked thumbnail to start video (CTW)',       1),
('ctw_view_counted',   'Min watch threshold reached — billable view (CTW)', 1),
('ctw_skipped',        'User skipped video before completion (CTW)',         1),
('end_card_shown',     'End card displayed after video completion',          1),
('end_card_clicked',   'User clicked CTA on end card',                      1),
('end_card_dismissed', 'User dismissed end card without clicking',           1),
('branding_intro_shown',  'Brand intro bumper started playing',             1),
('branding_intro_completed', 'Brand intro bumper completed',                1);

-- Add Click-to-Watch to MultiTag associations
-- (assuming click_to_watch format id = 15)
INSERT IGNORE INTO `aq_ad_format_tags` (`format_id`, `tag_id`) VALUES
-- Click-to-Watch Video: Video Ready, Mobile First, Brand Safe
(15, 7), (15, 3), (15, 10);

-- ============================================================================
-- 40. MSN DISTRIBUTION NETWORK — "RUN ON MSN EXCLUSIVELY"
-- ============================================================================
-- Supports the "Run on MSN exclusively" feature:
--   • Distribution network registry (MSN, Adshqip native, partner sites, etc.)
--   • Campaign ↔ network linking with exclusive mode
--   • MSN property catalog (msn.com, Outlook.com, Edge Start, Bing sidebar, etc.)
--   • MSN-specific campaign settings (content categories, brand safety, audience)
--   • MSN performance stats for reporting
--
-- How "Run on MSN exclusively" works:
--   1. Advertiser sets msn_exclusive=1 on campaign (or distribution_mode='msn_exclusive')
--   2. Ad server restricts delivery ONLY to MSN-owned properties
--   3. Premium brand-safe placements on Microsoft properties
--   4. MSN-specific bid adjustments, audience targeting, content categories
-- ============================================================================

-- Registry of all distribution networks the platform can serve ads to
CREATE TABLE IF NOT EXISTS `aq_distribution_networks` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(50) NOT NULL COMMENT 'Unique identifier e.g. msn, adshqip_native, google_adsense',
  `name` VARCHAR(150) NOT NULL COMMENT 'Display name e.g. Microsoft MSN Network',
  `description` TEXT DEFAULT NULL,
  `network_type` ENUM('owned','partner','exchange','msn','social') NOT NULL DEFAULT 'partner' COMMENT 'Network classification',
  `logo_url` VARCHAR(500) DEFAULT NULL,
  `website_url` VARCHAR(500) DEFAULT NULL,
  `api_endpoint` VARCHAR(500) DEFAULT NULL COMMENT 'API endpoint for programmatic delivery',
  `api_key_encrypted` VARCHAR(500) DEFAULT NULL COMMENT 'Encrypted API credentials',
  `supported_formats` JSON DEFAULT NULL COMMENT 'Array of supported ad format slugs e.g. ["native_feed","native_video","click_to_watch"]',
  `supported_countries` JSON DEFAULT NULL COMMENT 'Array of ISO country codes this network covers',
  `min_bid_cpm` DECIMAL(10,4) DEFAULT NULL COMMENT 'Minimum CPM required by this network',
  `revenue_share_pct` DECIMAL(5,2) DEFAULT NULL COMMENT 'Platform revenue share % from this network',
  `is_premium` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Premium network with higher quality placements',
  `brand_safety_level` ENUM('basic','standard','strict') NOT NULL DEFAULT 'standard',
  `requires_approval` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Campaigns need approval before running on this network',
  `status` ENUM('active','inactive','testing','deprecated') NOT NULL DEFAULT 'active',
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_slug` (`slug`),
  KEY `idx_type` (`network_type`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed distribution networks
INSERT INTO `aq_distribution_networks` (`slug`, `name`, `description`, `network_type`, `website_url`, `supported_formats`, `supported_countries`, `min_bid_cpm`, `is_premium`, `brand_safety_level`, `requires_approval`, `status`, `sort_order`) VALUES
('adshqip_native',  'Adshqip Native Network',    'Adshqip owned publisher network across Balkans.',                                                    'owned',   'https://adshqip.com',     '["popunder","native_feed","interstitial","in_page_push","text_banner","native_video","rich_media","motion","click_to_watch"]', '["AL","XK","MK","ME","BA","RS"]', 0.50,  0, 'standard', 0, 'active', 1),
('msn',             'Microsoft MSN Network',      'Premium placements on MSN.com, Outlook.com, Microsoft Edge Start page, Bing sidebar, and Microsoft News. "Run on MSN exclusively" delivers ads only here.', 'msn', 'https://www.msn.com',     '["native_feed","native_video","click_to_watch","text_banner","rich_media"]', NULL, 2.00, 1, 'strict', 1, 'active', 2),
('bing_ads',        'Microsoft Bing Search Ads',  'Search and native ads served alongside Bing search results.',                                       'partner', 'https://ads.microsoft.com','["text_banner","native_feed"]',                                                                     NULL,                              1.50,  1, 'strict',   1, 'active', 3),
('partner_sites',   'Partner Publisher Network',  'Third-party publisher sites approved by Adshqip.',                                                   'partner', NULL,                      '["popunder","native_feed","interstitial","in_page_push","text_banner","native_video"]',                                        '["AL","XK","MK","ME","BA","RS"]', 0.30,  0, 'standard', 0, 'active', 4);

-- Campaign ↔ distribution network association
-- Controls which networks each campaign is allowed to serve on
CREATE TABLE IF NOT EXISTS `aq_campaign_network_assoc` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `campaign_id` BIGINT UNSIGNED NOT NULL COMMENT 'FK → aq_campaigns or aq_direct_campaigns',
  `campaign_source` ENUM('rtb','direct') NOT NULL DEFAULT 'rtb' COMMENT 'Which campaign table this references',
  `network_id` INT UNSIGNED NOT NULL COMMENT 'FK → aq_distribution_networks',
  `is_exclusive` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = deliver ONLY on this network (Run on MSN exclusively)',
  `bid_adjustment_pct` DECIMAL(5,2) DEFAULT NULL COMMENT 'Bid modifier for this network e.g. +15.00',
  `daily_budget_cap` DECIMAL(12,4) DEFAULT NULL COMMENT 'Optional per-network daily budget cap',
  `frequency_cap` INT DEFAULT NULL COMMENT 'Per-network frequency cap (overrides campaign-level)',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_campaign_network` (`campaign_id`, `campaign_source`, `network_id`),
  KEY `idx_network` (`network_id`),
  KEY `idx_exclusive` (`is_exclusive`),
  CONSTRAINT `fk_cna_network` FOREIGN KEY (`network_id`) REFERENCES `aq_distribution_networks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Catalog of individual MSN properties / placements
CREATE TABLE IF NOT EXISTS `aq_msn_properties` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(80) NOT NULL COMMENT 'Unique property identifier',
  `name` VARCHAR(150) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `property_type` ENUM('homepage','news','finance','sports','entertainment','lifestyle','weather','email','search','edge_start','edge_newtab','app') NOT NULL,
  `url` VARCHAR(500) DEFAULT NULL COMMENT 'Property URL',
  `supported_formats` JSON DEFAULT NULL COMMENT 'Ad formats this placement supports',
  `supported_sizes` JSON DEFAULT NULL COMMENT 'Ad sizes e.g. [{"w":300,"h":250},{"w":728,"h":90}]',
  `avg_daily_impressions` BIGINT UNSIGNED DEFAULT NULL COMMENT 'Estimated daily inventory',
  `avg_cpm` DECIMAL(10,4) DEFAULT NULL COMMENT 'Average CPM for this property',
  `audience_demographics` JSON DEFAULT NULL COMMENT '{"age_groups":["25-34","35-44"],"gender_split":{"m":48,"f":52},"interests":["news","tech"]}',
  `brand_safety_tier` ENUM('tier_1','tier_2','tier_3') NOT NULL DEFAULT 'tier_1' COMMENT 'Tier 1 = safest (homepage), Tier 3 = user-generated',
  `geo_availability` JSON DEFAULT NULL COMMENT 'Countries where this property has significant traffic',
  `is_premium` TINYINT(1) NOT NULL DEFAULT 1,
  `status` ENUM('active','inactive','coming_soon') NOT NULL DEFAULT 'active',
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_slug` (`slug`),
  KEY `idx_type` (`property_type`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed MSN properties
INSERT INTO `aq_msn_properties` (`slug`, `name`, `description`, `property_type`, `url`, `supported_formats`, `avg_daily_impressions`, `avg_cpm`, `brand_safety_tier`, `is_premium`, `status`, `sort_order`) VALUES
('msn_homepage',       'MSN Homepage',              'Main MSN.com homepage — high visibility, brand-safe.',                          'homepage',      'https://www.msn.com',              '["native_feed","native_video","click_to_watch","rich_media"]',  500000000, 4.50, 'tier_1', 1, 'active', 1),
('msn_news',           'MSN News',                  'MSN News articles and feed — contextual native ads.',                           'news',          'https://www.msn.com/news',         '["native_feed","native_video","click_to_watch","text_banner"]', 200000000, 3.80, 'tier_1', 1, 'active', 2),
('msn_finance',        'MSN Money / Finance',       'Financial news and market data pages.',                                         'finance',       'https://www.msn.com/money',        '["native_feed","text_banner"]',                                  80000000, 5.20, 'tier_1', 1, 'active', 3),
('msn_sports',         'MSN Sports',                'Sports news, scores, and live event coverage.',                                 'sports',        'https://www.msn.com/sports',       '["native_feed","native_video","click_to_watch"]',               150000000, 3.50, 'tier_1', 1, 'active', 4),
('msn_entertainment',  'MSN Entertainment',         'Celebrity news, movies, TV, and pop culture.',                                  'entertainment', 'https://www.msn.com/entertainment', '["native_feed","native_video","click_to_watch","rich_media"]', 120000000, 3.20, 'tier_1', 1, 'active', 5),
('msn_lifestyle',      'MSN Lifestyle',             'Health, food, travel, and lifestyle content.',                                  'lifestyle',     'https://www.msn.com/lifestyle',    '["native_feed","native_video","click_to_watch"]',               100000000, 3.00, 'tier_2', 1, 'active', 6),
('msn_weather',        'MSN Weather',               'Weather forecasts — high-frequency, location-aware placements.',                'weather',       'https://www.msn.com/weather',      '["native_feed","text_banner"]',                                 300000000, 2.50, 'tier_1', 1, 'active', 7),
('outlook_com',        'Outlook.com Mail',          'Outlook.com webmail — native ads in inbox and reading pane.',                   'email',         'https://outlook.com',              '["native_feed","text_banner"]',                                 400000000, 3.80, 'tier_1', 1, 'active', 8),
('edge_start',         'Microsoft Edge Start Page', 'Default start page in Microsoft Edge browser — high daily reach.',              'edge_start',    NULL,                               '["native_feed","native_video","click_to_watch","rich_media"]',  350000000, 4.00, 'tier_1', 1, 'active', 9),
('edge_newtab',        'Microsoft Edge New Tab',    'New tab page in Edge — feed-style native ads.',                                 'edge_newtab',   NULL,                               '["native_feed","native_video","click_to_watch"]',               250000000, 3.50, 'tier_1', 1, 'active', 10),
('bing_sidebar',       'Bing Search Sidebar',       'Native placements in the Bing search results sidebar.',                         'search',        'https://www.bing.com',             '["native_feed","text_banner"]',                                 600000000, 5.00, 'tier_1', 1, 'active', 11),
('msn_app_mobile',     'MSN Mobile App',            'Microsoft Start / MSN app on iOS and Android.',                                 'app',           NULL,                               '["native_feed","native_video","click_to_watch","in_page_push"]',180000000, 3.20, 'tier_1', 1, 'active', 12);

-- MSN-specific campaign settings (extends campaign with MSN-only config)
CREATE TABLE IF NOT EXISTS `aq_msn_campaign_settings` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `campaign_id` BIGINT UNSIGNED NOT NULL COMMENT 'FK → aq_campaigns or aq_direct_campaigns',
  `campaign_source` ENUM('rtb','direct') NOT NULL DEFAULT 'rtb',
  -- Property targeting
  `target_properties` JSON DEFAULT NULL COMMENT 'Array of aq_msn_properties.slug to target, NULL = all MSN properties',
  `exclude_properties` JSON DEFAULT NULL COMMENT 'Array of aq_msn_properties.slug to exclude',
  -- Content category targeting (MSN content verticals)
  `content_categories` JSON DEFAULT NULL COMMENT '["news","finance","sports","entertainment","lifestyle","weather","technology","politics","health"]',
  `content_category_mode` ENUM('include','exclude') NOT NULL DEFAULT 'include',
  -- MSN audience targeting
  `msn_audience_segments` JSON DEFAULT NULL COMMENT 'Microsoft Audience Network segments e.g. ["in_market_auto","in_market_travel","affinity_tech"]',
  `linkedin_profile_targeting` JSON DEFAULT NULL COMMENT 'LinkedIn-based targeting via Microsoft Graph: {"industries":["tech","finance"],"job_functions":["marketing"],"company_sizes":["51-200"]}',
  -- Brand safety
  `brand_safety_level` ENUM('standard','strict','custom') NOT NULL DEFAULT 'strict' COMMENT 'MSN brand safety tier',
  `blocked_content_types` JSON DEFAULT NULL COMMENT '["user_generated","opinion","political","sensitive_news"]',
  -- Viewability & quality
  `viewability_threshold_pct` TINYINT UNSIGNED DEFAULT 50 COMMENT 'Min viewability % required (MRC standard = 50)',
  `above_the_fold_only` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Only serve in above-the-fold placements',
  -- MSN native ad customization
  `msn_headline_override` VARCHAR(255) DEFAULT NULL COMMENT 'MSN-specific headline (overrides campaign headline)',
  `msn_body_override` TEXT DEFAULT NULL COMMENT 'MSN-specific body text',
  `msn_thumbnail_url` VARCHAR(500) DEFAULT NULL COMMENT 'MSN-specific thumbnail image',
  `msn_sponsored_label` VARCHAR(50) DEFAULT 'Ad' COMMENT 'Sponsor label shown on MSN e.g. "Ad", "Sponsored", "Promoted"',
  -- Performance settings
  `auto_optimize_placements` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Auto-shift budget toward best-performing MSN properties',
  `max_cpm_override` DECIMAL(10,4) DEFAULT NULL COMMENT 'Max CPM willing to pay on MSN (overrides campaign bid)',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_campaign_source` (`campaign_id`, `campaign_source`),
  KEY `idx_brand_safety` (`brand_safety_level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- MSN performance stats — daily aggregated per campaign per MSN property
CREATE TABLE IF NOT EXISTS `aq_msn_performance_stats` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `date` DATE NOT NULL,
  `campaign_id` BIGINT UNSIGNED NOT NULL,
  `campaign_source` ENUM('rtb','direct') NOT NULL DEFAULT 'rtb',
  `property_id` INT UNSIGNED NOT NULL COMMENT 'FK → aq_msn_properties',
  `impressions` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `clicks` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `views` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Video/CTW completed views',
  `conversions` INT UNSIGNED NOT NULL DEFAULT 0,
  `spend` DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
  `revenue` DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
  `ctr` DECIMAL(8,4) DEFAULT NULL COMMENT 'Click-through rate %',
  `vcr` DECIMAL(8,4) DEFAULT NULL COMMENT 'Video completion rate %',
  `viewability_rate` DECIMAL(8,4) DEFAULT NULL COMMENT 'Viewability %',
  `avg_cpm` DECIMAL(10,4) DEFAULT NULL,
  `avg_cpc` DECIMAL(10,4) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_daily_campaign_property` (`date`, `campaign_id`, `campaign_source`, `property_id`),
  KEY `idx_campaign` (`campaign_id`),
  KEY `idx_property` (`property_id`),
  KEY `idx_date` (`date`),
  CONSTRAINT `fk_mps_property` FOREIGN KEY (`property_id`) REFERENCES `aq_msn_properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 41. CAMPAIGN DYNAMICS — DYNAMIC CREATIVE OPTIMIZATION (DCO)
-- ============================================================================
-- Full dynamic campaign system:
--   • Dynamic Content Tokens  — {city}, {device}, {weather}, {countdown} macros
--   • Dynamic Creative Assets — reusable asset library (headlines, images, CTAs)
--   • Dynamic Creative Rules  — rule engine: swap assets based on conditions
--   • Dynamic Product Feeds   — catalog/data feeds for e-commerce dynamic ads
--   • Dynamic Product Items   — individual products in a feed
--   • Dynamic Budget Rules    — automated bid/budget adjustments on triggers
--   • Dynamic Landing Pages   — per-segment URL customization
--   • Dynamic Countdown Timers— urgency-driven countdown elements
-- ============================================================================

-- Registry of dynamic tokens (macros) that can be embedded in ad copy
-- At serve time the ad server replaces {token_key} with the resolved value
CREATE TABLE IF NOT EXISTS `aq_dynamic_content_tokens` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `token_key` VARCHAR(50) NOT NULL COMMENT 'Token placeholder key e.g. city, device, weather, countdown, day_of_week',
  `name` VARCHAR(100) NOT NULL COMMENT 'Display name e.g. "Visitor City"',
  `description` TEXT DEFAULT NULL,
  `category` ENUM('geo','device','time','weather','audience','custom','feed') NOT NULL DEFAULT 'custom',
  `resolver_type` ENUM('geoip','user_agent','server_time','weather_api','audience_data','product_feed','custom_api','static_map') NOT NULL COMMENT 'How the token value is resolved at serve time',
  `resolver_config` JSON DEFAULT NULL COMMENT 'Config for the resolver, e.g. {"api_url":"...","field":"city_name"}',
  `default_value` VARCHAR(255) DEFAULT NULL COMMENT 'Fallback value if resolution fails',
  `example_output` VARCHAR(255) DEFAULT NULL COMMENT 'Example resolved value for UI preview',
  `is_system` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = platform-provided, 0 = advertiser-custom',
  `status` ENUM('active','inactive','deprecated') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_token_key` (`token_key`),
  KEY `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed system tokens
INSERT INTO `aq_dynamic_content_tokens` (`token_key`, `name`, `description`, `category`, `resolver_type`, `default_value`, `example_output`, `is_system`, `status`) VALUES
('city',          'Visitor City',         'Resolved via GeoIP to the visitor''s city name.',                           'geo',      'geoip',       'your city',    'Tirana',          1, 'active'),
('region',        'Visitor Region',       'State/region from GeoIP.',                                                  'geo',      'geoip',       'your region',  'Tirana County',   1, 'active'),
('country',       'Visitor Country',      'Country name from GeoIP.',                                                  'geo',      'geoip',       'your country', 'Albania',         1, 'active'),
('country_code',  'Country Code',         'ISO 2-letter country code from GeoIP.',                                     'geo',      'geoip',       'XX',           'AL',              1, 'active'),
('device',        'Device Type',          'mobile, desktop, or tablet.',                                               'device',   'user_agent',  'device',       'mobile',          1, 'active'),
('browser',       'Browser Name',         'Visitor browser name.',                                                     'device',   'user_agent',  'browser',      'Chrome',          1, 'active'),
('os',            'Operating System',     'Visitor OS name.',                                                          'device',   'user_agent',  'your device',  'Android',         1, 'active'),
('day_of_week',   'Day of Week',          'Current day name in visitor timezone.',                                     'time',     'server_time', 'today',        'Monday',          1, 'active'),
('time_of_day',   'Time of Day',          'morning, afternoon, evening, night based on visitor timezone.',             'time',     'server_time', 'today',        'afternoon',       1, 'active'),
('current_month', 'Current Month',        'Full month name.',                                                          'time',     'server_time', 'this month',   'March',           1, 'active'),
('current_year',  'Current Year',         'Four-digit year.',                                                          'time',     'server_time', '2025',         '2026',            1, 'active'),
('weather',       'Current Weather',      'Weather condition at visitor location (requires weather API).',             'weather',  'weather_api', 'nice weather', 'Sunny',           1, 'active'),
('temperature',   'Temperature',          'Temperature in °C at visitor location.',                                    'weather',  'weather_api', '20°C',         '28°C',            1, 'active'),
('countdown',     'Countdown Timer',      'Dynamic countdown to a specified end date/time. Configured per campaign.',  'time',     'server_time', 'soon',         '2d 14h 30m',     1, 'active'),
('keyword',       'Search Keyword',       'DKI keyword from referring search query or campaign keyword list.',         'audience', 'audience_data','our product',  'cheap flights',   1, 'active');

-- Reusable creative asset library for Dynamic Creative Optimization
-- Advertisers upload multiple headlines, images, CTAs, etc. and the DCO
-- engine assembles winning combinations based on rules and performance
CREATE TABLE IF NOT EXISTS `aq_dynamic_creative_assets` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `advertiser_id` BIGINT UNSIGNED NOT NULL COMMENT 'FK → aq_users',
  `campaign_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'NULL = account-level reusable, or FK → specific campaign',
  `campaign_source` ENUM('rtb','direct') DEFAULT NULL,
  `asset_type` ENUM('headline','body_text','image','video','cta_text','cta_url','logo','description','display_url','sponsored_label') NOT NULL,
  `content` TEXT NOT NULL COMMENT 'The asset value: text string, or URL for images/videos',
  `language` VARCHAR(5) DEFAULT 'sq' COMMENT 'ISO language code (sq, en, it, de)',
  `character_count` INT UNSIGNED DEFAULT NULL COMMENT 'Auto-calculated for text assets',
  `file_size_bytes` INT UNSIGNED DEFAULT NULL COMMENT 'For image/video assets',
  `width` INT UNSIGNED DEFAULT NULL,
  `height` INT UNSIGNED DEFAULT NULL,
  `performance_score` DECIMAL(5,2) DEFAULT NULL COMMENT 'Auto-calculated DCO performance score 0-100',
  `impressions` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Times this asset was served',
  `clicks` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `conversions` INT UNSIGNED NOT NULL DEFAULT 0,
  `ctr` DECIMAL(8,4) DEFAULT NULL,
  `tags` JSON DEFAULT NULL COMMENT 'Freeform tags for filtering e.g. ["promo","summer","albanian"]',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_advertiser` (`advertiser_id`),
  KEY `idx_campaign` (`campaign_id`),
  KEY `idx_type` (`asset_type`),
  KEY `idx_performance` (`performance_score`),
  CONSTRAINT `fk_dca_advertiser` FOREIGN KEY (`advertiser_id`) REFERENCES `aq_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Rule engine: defines conditions under which specific creative assets are selected
-- Example: IF geo=AL AND device=mobile THEN use headline asset #12 + image asset #34
CREATE TABLE IF NOT EXISTS `aq_dynamic_creative_rules` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `campaign_id` BIGINT UNSIGNED NOT NULL,
  `campaign_source` ENUM('rtb','direct') NOT NULL DEFAULT 'rtb',
  `rule_name` VARCHAR(255) NOT NULL COMMENT 'Descriptive name e.g. "Albania Mobile — Summer Promo"',
  `priority` INT NOT NULL DEFAULT 0 COMMENT 'Higher priority rules evaluated first',
  -- Conditions (all non-NULL conditions must match — AND logic)
  `condition_geo_countries` JSON DEFAULT NULL COMMENT '["AL","XK","MK"]',
  `condition_geo_cities` JSON DEFAULT NULL COMMENT '["Tirana","Prishtina"]',
  `condition_devices` JSON DEFAULT NULL COMMENT '["mobile","tablet"]',
  `condition_browsers` JSON DEFAULT NULL COMMENT '["Chrome","Firefox"]',
  `condition_os` JSON DEFAULT NULL COMMENT '["Android","iOS"]',
  `condition_languages` JSON DEFAULT NULL COMMENT '["sq","en"]',
  `condition_day_of_week` JSON DEFAULT NULL COMMENT '["Mon","Tue","Wed"]',
  `condition_time_range` JSON DEFAULT NULL COMMENT '{"start":"08:00","end":"18:00"}',
  `condition_weather` JSON DEFAULT NULL COMMENT '["Sunny","Clear","Warm"]',
  `condition_audience_segments` JSON DEFAULT NULL COMMENT '["returning_visitor","high_intent"]',
  `condition_custom` JSON DEFAULT NULL COMMENT 'Arbitrary key-value conditions for extensibility',
  -- Asset selections (array of aq_dynamic_creative_assets.id)
  `selected_headlines` JSON DEFAULT NULL COMMENT 'Asset IDs to use for headlines when conditions match',
  `selected_body_texts` JSON DEFAULT NULL COMMENT 'Asset IDs for body text',
  `selected_images` JSON DEFAULT NULL COMMENT 'Asset IDs for images',
  `selected_videos` JSON DEFAULT NULL COMMENT 'Asset IDs for videos',
  `selected_ctas` JSON DEFAULT NULL COMMENT 'Asset IDs for CTA text',
  `selected_cta_urls` JSON DEFAULT NULL COMMENT 'Asset IDs for CTA URLs',
  `selected_logos` JSON DEFAULT NULL COMMENT 'Asset IDs for logos',
  -- Override fields (direct value override instead of asset selection)
  `override_destination_url` VARCHAR(2000) DEFAULT NULL COMMENT 'Override click-through URL for this rule',
  `override_display_url` VARCHAR(500) DEFAULT NULL,
  `override_bid_adjustment_pct` DECIMAL(5,2) DEFAULT NULL COMMENT 'Bid modifier when this rule matches',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_campaign` (`campaign_id`, `campaign_source`),
  KEY `idx_priority` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product / data feeds for catalog-driven dynamic ads
-- (e-commerce, travel, auto, real estate, job listings, etc.)
CREATE TABLE IF NOT EXISTS `aq_dynamic_product_feeds` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `advertiser_id` BIGINT UNSIGNED NOT NULL COMMENT 'FK → aq_users',
  `name` VARCHAR(255) NOT NULL COMMENT 'Feed name e.g. "Summer Collection 2026"',
  `feed_type` ENUM('ecommerce','travel','auto','real_estate','jobs','events','custom') NOT NULL DEFAULT 'ecommerce',
  `source_type` ENUM('manual','csv_upload','xml_url','json_url','google_merchant','facebook_catalog','api') NOT NULL DEFAULT 'manual',
  `source_url` VARCHAR(2000) DEFAULT NULL COMMENT 'URL for automatic feed ingestion',
  `source_credentials` JSON DEFAULT NULL COMMENT 'Encrypted auth for feed source',
  `refresh_interval_minutes` INT UNSIGNED DEFAULT 360 COMMENT 'How often to re-fetch the feed (default 6h)',
  `last_fetched_at` DATETIME DEFAULT NULL,
  `last_fetch_status` ENUM('success','failed','partial','pending') DEFAULT NULL,
  `last_fetch_error` TEXT DEFAULT NULL,
  `item_count` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Number of active items in feed',
  `field_mapping` JSON DEFAULT NULL COMMENT 'Maps feed columns to standard fields: {"title":"product_name","price":"sale_price","image":"main_image_url"}',
  `default_currency` VARCHAR(3) NOT NULL DEFAULT 'EUR',
  `default_language` VARCHAR(5) NOT NULL DEFAULT 'sq',
  `filter_rules` JSON DEFAULT NULL COMMENT 'Auto-filter items: {"min_price":5,"in_stock":true,"category_include":["electronics"]}',
  `status` ENUM('active','paused','error','archived') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_advertiser` (`advertiser_id`),
  KEY `idx_type` (`feed_type`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_dpf_advertiser` FOREIGN KEY (`advertiser_id`) REFERENCES `aq_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Individual items in a product feed
CREATE TABLE IF NOT EXISTS `aq_dynamic_product_feed_items` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `feed_id` BIGINT UNSIGNED NOT NULL COMMENT 'FK → aq_dynamic_product_feeds',
  `external_id` VARCHAR(255) NOT NULL COMMENT 'Product ID from the merchant feed (SKU, GTIN, etc.)',
  `title` VARCHAR(500) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `url` VARCHAR(2000) NOT NULL COMMENT 'Product landing page URL',
  `image_url` VARCHAR(500) DEFAULT NULL,
  `additional_image_urls` JSON DEFAULT NULL COMMENT 'Array of extra image URLs',
  `price` DECIMAL(12,2) NOT NULL,
  `sale_price` DECIMAL(12,2) DEFAULT NULL,
  `currency` VARCHAR(3) NOT NULL DEFAULT 'EUR',
  `category` VARCHAR(500) DEFAULT NULL COMMENT 'Product category path e.g. "Electronics > Phones > Smartphones"',
  `brand` VARCHAR(255) DEFAULT NULL,
  `availability` ENUM('in_stock','out_of_stock','preorder','backorder') NOT NULL DEFAULT 'in_stock',
  `condition_status` ENUM('new','refurbished','used') DEFAULT 'new',
  `rating` DECIMAL(3,2) DEFAULT NULL COMMENT 'Product rating 0.00-5.00',
  `review_count` INT UNSIGNED DEFAULT NULL,
  `custom_labels` JSON DEFAULT NULL COMMENT 'Up to 5 custom labels for filtering {"label_0":"sale","label_1":"bestseller"}',
  `custom_attributes` JSON DEFAULT NULL COMMENT 'Arbitrary extra attributes from feed',
  `geo_availability` JSON DEFAULT NULL COMMENT 'Countries where product is available',
  `expiry_date` DATE DEFAULT NULL COMMENT 'Product offer expiration',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `last_synced_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_feed_external` (`feed_id`, `external_id`),
  KEY `idx_category` (`category`(100)),
  KEY `idx_brand` (`brand`),
  KEY `idx_availability` (`availability`),
  KEY `idx_price` (`price`),
  CONSTRAINT `fk_dpfi_feed` FOREIGN KEY (`feed_id`) REFERENCES `aq_dynamic_product_feeds` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Automated budget and bid rules triggered by performance conditions
-- Example: "If CTR > 2% for 3 consecutive hours, increase bid by 15%"
-- Example: "If daily spend > 80% of budget before 2 PM, reduce bid by 20%"
CREATE TABLE IF NOT EXISTS `aq_dynamic_budget_rules` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `campaign_id` BIGINT UNSIGNED NOT NULL,
  `campaign_source` ENUM('rtb','direct') NOT NULL DEFAULT 'rtb',
  `rule_name` VARCHAR(255) NOT NULL,
  `rule_type` ENUM('bid_increase','bid_decrease','budget_increase','budget_decrease','pause_campaign','resume_campaign','alert_only') NOT NULL,
  -- Trigger conditions
  `trigger_metric` ENUM('ctr','cvr','cpc','cpm','cpa','roas','spend_pct','impressions','clicks','conversions','viewability','frequency') NOT NULL COMMENT 'Which metric to evaluate',
  `trigger_operator` ENUM('greater_than','less_than','equal_to','between','not_between') NOT NULL DEFAULT 'greater_than',
  `trigger_value` DECIMAL(12,4) NOT NULL COMMENT 'Threshold value',
  `trigger_value_upper` DECIMAL(12,4) DEFAULT NULL COMMENT 'Upper bound for between/not_between operators',
  `trigger_window_minutes` INT UNSIGNED NOT NULL DEFAULT 60 COMMENT 'Evaluation window in minutes (e.g. 60 = last 1 hour)',
  `trigger_min_samples` INT UNSIGNED DEFAULT 100 COMMENT 'Minimum impressions/events in window before rule fires',
  -- Action
  `action_value` DECIMAL(10,4) NOT NULL COMMENT 'Amount or percentage for the action',
  `action_is_percentage` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = action_value is %, 0 = absolute value',
  `action_cap_min` DECIMAL(10,4) DEFAULT NULL COMMENT 'Floor: never adjust below this bid/budget',
  `action_cap_max` DECIMAL(10,4) DEFAULT NULL COMMENT 'Ceiling: never adjust above this bid/budget',
  `cooldown_minutes` INT UNSIGNED NOT NULL DEFAULT 60 COMMENT 'Min minutes between consecutive firings of this rule',
  `max_fires_per_day` INT UNSIGNED DEFAULT 10 COMMENT 'Max times this rule can fire per day',
  -- Notification
  `notify_on_fire` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Send notification when rule fires',
  `notify_email` VARCHAR(255) DEFAULT NULL,
  -- Metadata
  `last_fired_at` DATETIME DEFAULT NULL,
  `total_fires` INT UNSIGNED NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_campaign` (`campaign_id`, `campaign_source`),
  KEY `idx_type` (`rule_type`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Per-segment dynamic landing page URL rules
-- Allows different landing pages based on visitor attributes
CREATE TABLE IF NOT EXISTS `aq_dynamic_landing_pages` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `campaign_id` BIGINT UNSIGNED NOT NULL,
  `campaign_source` ENUM('rtb','direct') NOT NULL DEFAULT 'rtb',
  `rule_name` VARCHAR(255) NOT NULL COMMENT 'e.g. "Albania Mobile Landing Page"',
  `priority` INT NOT NULL DEFAULT 0,
  -- Conditions (AND logic — all non-NULL conditions must match)
  `condition_geo_countries` JSON DEFAULT NULL,
  `condition_geo_cities` JSON DEFAULT NULL,
  `condition_devices` JSON DEFAULT NULL,
  `condition_os` JSON DEFAULT NULL,
  `condition_languages` JSON DEFAULT NULL,
  `condition_audience_segments` JSON DEFAULT NULL,
  `condition_referrer_domains` JSON DEFAULT NULL COMMENT 'Match specific referrer domains',
  `condition_utm_params` JSON DEFAULT NULL COMMENT '{"utm_source":"google","utm_medium":"cpc"}',
  `condition_custom` JSON DEFAULT NULL,
  -- Landing page
  `destination_url` VARCHAR(2000) NOT NULL COMMENT 'Landing page URL for this segment',
  `append_params` JSON DEFAULT NULL COMMENT 'Additional URL params to append: {"lang":"sq","promo":"summer"}',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_campaign` (`campaign_id`, `campaign_source`),
  KEY `idx_priority` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Countdown timer configurations for urgency-driven dynamic ads
-- Embeds a live countdown in the ad creative via {countdown} token
CREATE TABLE IF NOT EXISTS `aq_dynamic_countdown_timers` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `campaign_id` BIGINT UNSIGNED NOT NULL,
  `campaign_source` ENUM('rtb','direct') NOT NULL DEFAULT 'rtb',
  `timer_name` VARCHAR(255) NOT NULL COMMENT 'e.g. "Black Friday Countdown"',
  `end_datetime` DATETIME NOT NULL COMMENT 'Target date/time the countdown counts down to',
  `timezone` VARCHAR(50) NOT NULL DEFAULT 'Europe/Tirane',
  `display_format` ENUM('dhms','hms','days_only','custom') NOT NULL DEFAULT 'dhms' COMMENT 'd=days h=hours m=minutes s=seconds',
  `custom_format` VARCHAR(255) DEFAULT NULL COMMENT 'Custom format string e.g. "{d} ditë {h} orë {m} min"',
  `before_text` VARCHAR(255) DEFAULT NULL COMMENT 'Text shown before countdown e.g. "Oferta mbaron në"',
  `after_text` VARCHAR(255) DEFAULT NULL COMMENT 'Text shown after countdown expires e.g. "Oferta ka përfunduar!"',
  `expired_action` ENUM('show_after_text','hide_ad','pause_campaign','redirect') NOT NULL DEFAULT 'show_after_text',
  `expired_redirect_url` VARCHAR(2000) DEFAULT NULL COMMENT 'Redirect URL when expired_action=redirect',
  `style_config` JSON DEFAULT NULL COMMENT '{"font_size":"24px","color":"#FF0000","bg_color":"#000","bold":true}',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_campaign` (`campaign_id`, `campaign_source`),
  KEY `idx_end` (`end_datetime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Log of dynamic creative rule firings for auditing and analytics
CREATE TABLE IF NOT EXISTS `aq_dynamic_rule_log` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `rule_id` BIGINT UNSIGNED NOT NULL COMMENT 'FK → aq_dynamic_creative_rules or aq_dynamic_budget_rules',
  `rule_source` ENUM('creative','budget','landing_page') NOT NULL,
  `campaign_id` BIGINT UNSIGNED NOT NULL,
  `campaign_source` ENUM('rtb','direct') NOT NULL DEFAULT 'rtb',
  `impression_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'FK → aq_impressions (for creative rules)',
  `matched_conditions` JSON DEFAULT NULL COMMENT 'Snapshot of conditions that matched',
  `action_taken` VARCHAR(255) DEFAULT NULL COMMENT 'Description of action e.g. "Used headline asset #12"',
  `old_value` VARCHAR(500) DEFAULT NULL COMMENT 'Previous value (for budget rules)',
  `new_value` VARCHAR(500) DEFAULT NULL COMMENT 'New value after rule applied',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_rule` (`rule_id`, `rule_source`),
  KEY `idx_campaign` (`campaign_id`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 42. CUSTOM AUDIENCES
-- ============================================================================
-- Full audience management system:
--   • Audience Pixels       — tracking pixel/tag for website visitor collection
--   • Custom Audiences      — master audience definitions (website, customer_list,
--                             app_activity, engagement, conversion, lookalike)
--   • Audience Members      — individual users in an audience (hashed PII, cookie/device IDs)
--   • Audience Rules        — rule-based audience building (URL visited, event fired, etc.)
--   • Audience Syncs        — upload/sync history (CSV, CRM, API)
--   • Campaign ↔ Audience   — link campaigns to include/exclude specific audiences
--   • Lookalike Audiences   — seed audience expansion via similarity modeling
--   • Pixel Events          — raw pixel fire log for real-time audience population
--
-- How "Create a Custom Audience" works:
--   1. Advertiser creates a pixel (aq_audience_pixels) and places it on their site
--   2. Pixel fires on visitor actions → logged in aq_pixel_events
--   3. Rules (aq_audience_rules) evaluate events and add matching visitors to audiences
--   4. Advertiser can also upload customer lists (CSV of hashed emails/phones)
--   5. Audiences are linked to campaigns via aq_campaign_audience_assoc (include/exclude)
--   6. Lookalike audiences expand a seed audience to find similar users
-- ============================================================================

-- Tracking pixel / tag management
-- Each advertiser gets one or more pixels to place on their website
CREATE TABLE IF NOT EXISTS `aq_audience_pixels` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `advertiser_id` BIGINT UNSIGNED NOT NULL COMMENT 'FK → aq_users',
  `pixel_name` VARCHAR(255) NOT NULL COMMENT 'e.g. "Main Website Pixel"',
  `pixel_uuid` CHAR(36) NOT NULL COMMENT 'Unique pixel identifier embedded in the tag snippet',
  `pixel_type` ENUM('javascript','image','server_to_server') NOT NULL DEFAULT 'javascript',
  `domain` VARCHAR(255) DEFAULT NULL COMMENT 'Authorized domain for this pixel',
  `allowed_domains` JSON DEFAULT NULL COMMENT 'Additional authorized domains ["shop.example.com","blog.example.com"]',
  `tag_snippet` TEXT DEFAULT NULL COMMENT 'Auto-generated JavaScript/image tag snippet for the advertiser to paste',
  `events_tracked` JSON DEFAULT NULL COMMENT 'Standard events this pixel captures: ["PageView","AddToCart","Purchase","Lead","ViewContent"]',
  `custom_events` JSON DEFAULT NULL COMMENT 'Advertiser-defined custom events: ["signup_start","trial_activated"]',
  `is_verified` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Has the pixel been detected firing from the authorized domain',
  `last_fired_at` DATETIME DEFAULT NULL COMMENT 'Last time this pixel recorded an event',
  `total_events` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Lifetime event count',
  `status` ENUM('active','paused','deleted') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_pixel_uuid` (`pixel_uuid`),
  KEY `idx_advertiser` (`advertiser_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_ap_advertiser` FOREIGN KEY (`advertiser_id`) REFERENCES `aq_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Master audience definitions
CREATE TABLE IF NOT EXISTS `aq_custom_audiences` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `advertiser_id` BIGINT UNSIGNED NOT NULL COMMENT 'FK → aq_users',
  `name` VARCHAR(255) NOT NULL COMMENT 'e.g. "Cart Abandoners - Last 30 Days"',
  `description` TEXT DEFAULT NULL,
  `audience_type` ENUM('website_visitors','customer_list','app_activity','engagement','conversion','lookalike','combined') NOT NULL,
  `source_pixel_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'FK → aq_audience_pixels (for website/conversion audiences)',
  `source_audience_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'Seed audience FK for lookalike audiences',
  -- Membership settings
  `membership_duration_days` INT UNSIGNED NOT NULL DEFAULT 30 COMMENT 'How long a user stays in this audience (1-540 days)',
  `member_count` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Current estimated audience size',
  `member_count_updated_at` DATETIME DEFAULT NULL,
  `min_size_for_delivery` INT UNSIGNED NOT NULL DEFAULT 100 COMMENT 'Minimum members required before audience can be used for targeting',
  -- Data source settings
  `data_retention_days` INT UNSIGNED NOT NULL DEFAULT 180 COMMENT 'GDPR: how long raw data is retained',
  `consent_required` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Require cookie/GDPR consent before adding to audience',
  `pii_hashing_algorithm` ENUM('sha256','md5','none') NOT NULL DEFAULT 'sha256' COMMENT 'How PII is hashed before storage',
  -- Auto-refresh
  `auto_refresh_enabled` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Automatically update membership based on rules',
  `last_refreshed_at` DATETIME DEFAULT NULL,
  `refresh_frequency_hours` INT UNSIGNED DEFAULT 24,
  -- Status
  `status` ENUM('building','ready','too_small','paused','error','archived') NOT NULL DEFAULT 'building',
  `is_shared` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Can other campaigns in the account use this audience',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_advertiser` (`advertiser_id`),
  KEY `idx_type` (`audience_type`),
  KEY `idx_pixel` (`source_pixel_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_ca_advertiser` FOREIGN KEY (`advertiser_id`) REFERENCES `aq_users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ca_pixel` FOREIGN KEY (`source_pixel_id`) REFERENCES `aq_audience_pixels` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ca_source_audience` FOREIGN KEY (`source_audience_id`) REFERENCES `aq_custom_audiences` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Individual members in a custom audience
-- Stores hashed identifiers (NEVER raw PII) for privacy compliance
CREATE TABLE IF NOT EXISTS `aq_audience_members` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `audience_id` BIGINT UNSIGNED NOT NULL COMMENT 'FK → aq_custom_audiences',
  `identifier_type` ENUM('cookie_id','device_id','hashed_email','hashed_phone','idfa','gaid','ip_hash','custom_id') NOT NULL,
  `identifier_value` VARCHAR(255) NOT NULL COMMENT 'Hashed identifier value (SHA-256 for PII)',
  `source` ENUM('pixel','upload','api','rule','lookalike','engagement') NOT NULL DEFAULT 'pixel',
  `first_seen_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_seen_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `event_count` INT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'How many qualifying events from this user',
  `expires_at` DATETIME DEFAULT NULL COMMENT 'Auto-computed from audience membership_duration_days',
  `consent_given` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'GDPR: did user consent to tracking',
  `consent_timestamp` DATETIME DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_audience_identifier` (`audience_id`, `identifier_type`, `identifier_value`),
  KEY `idx_identifier` (`identifier_type`, `identifier_value`),
  KEY `idx_expires` (`expires_at`),
  KEY `idx_source` (`source`),
  CONSTRAINT `fk_am_audience` FOREIGN KEY (`audience_id`) REFERENCES `aq_custom_audiences` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Rule-based audience building
-- Defines which events/conditions qualify a visitor for an audience
CREATE TABLE IF NOT EXISTS `aq_audience_rules` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `audience_id` BIGINT UNSIGNED NOT NULL COMMENT 'FK → aq_custom_audiences',
  `rule_name` VARCHAR(255) NOT NULL COMMENT 'e.g. "Visited product page"',
  `rule_group` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Rules in the same group use OR logic; groups use AND logic',
  -- Event matching
  `event_type` VARCHAR(100) NOT NULL COMMENT 'Pixel event name: PageView, AddToCart, Purchase, Lead, ViewContent, custom events',
  `url_match_type` ENUM('exact','contains','starts_with','regex','any') NOT NULL DEFAULT 'any',
  `url_match_value` VARCHAR(2000) DEFAULT NULL COMMENT 'URL pattern to match (NULL = any URL)',
  -- Additional conditions
  `condition_params` JSON DEFAULT NULL COMMENT 'Extra conditions: {"value_min":50,"currency":"EUR","product_category":"electronics"}',
  `condition_frequency` INT UNSIGNED DEFAULT NULL COMMENT 'Min number of times event must occur',
  `condition_recency_days` INT UNSIGNED DEFAULT NULL COMMENT 'Event must have occurred within this many days',
  -- Exclusion rules (negative matching)
  `is_exclusion` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = remove users matching this rule from audience',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_audience` (`audience_id`),
  KEY `idx_event` (`event_type`),
  CONSTRAINT `fk_ar_audience` FOREIGN KEY (`audience_id`) REFERENCES `aq_custom_audiences` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Audience data upload / sync history
-- Tracks CSV uploads, CRM syncs, API pushes
CREATE TABLE IF NOT EXISTS `aq_audience_syncs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `audience_id` BIGINT UNSIGNED NOT NULL COMMENT 'FK → aq_custom_audiences',
  `sync_type` ENUM('csv_upload','crm_sync','api_push','manual','pixel_backfill') NOT NULL,
  `file_name` VARCHAR(500) DEFAULT NULL COMMENT 'Original upload file name',
  `file_size_bytes` BIGINT UNSIGNED DEFAULT NULL,
  `identifier_type` ENUM('hashed_email','hashed_phone','device_id','cookie_id','custom_id') NOT NULL DEFAULT 'hashed_email',
  `total_records` INT UNSIGNED DEFAULT NULL COMMENT 'Total rows in the upload',
  `matched_records` INT UNSIGNED DEFAULT NULL COMMENT 'Records successfully matched/added',
  `failed_records` INT UNSIGNED DEFAULT NULL COMMENT 'Records that failed validation',
  `duplicate_records` INT UNSIGNED DEFAULT NULL COMMENT 'Records already in audience',
  `error_log` TEXT DEFAULT NULL COMMENT 'Detailed error messages for failed records',
  `status` ENUM('pending','processing','completed','failed','cancelled') NOT NULL DEFAULT 'pending',
  `started_at` DATETIME DEFAULT NULL,
  `completed_at` DATETIME DEFAULT NULL,
  `initiated_by` BIGINT UNSIGNED DEFAULT NULL COMMENT 'FK → aq_users who started the sync',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_audience` (`audience_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `fk_as_audience` FOREIGN KEY (`audience_id`) REFERENCES `aq_custom_audiences` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Campaign ↔ Audience association
-- Links campaigns to specific audiences for include or exclude targeting
CREATE TABLE IF NOT EXISTS `aq_campaign_audience_assoc` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `campaign_id` BIGINT UNSIGNED NOT NULL,
  `campaign_source` ENUM('rtb','direct') NOT NULL DEFAULT 'rtb',
  `audience_id` BIGINT UNSIGNED NOT NULL COMMENT 'FK → aq_custom_audiences',
  `match_mode` ENUM('include','exclude') NOT NULL DEFAULT 'include' COMMENT 'Include = target this audience, Exclude = suppress this audience',
  `bid_adjustment_pct` DECIMAL(5,2) DEFAULT NULL COMMENT 'Bid modifier for this audience e.g. +25.00 = bid 25% higher for this audience',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_campaign_audience` (`campaign_id`, `campaign_source`, `audience_id`),
  KEY `idx_audience` (`audience_id`),
  KEY `idx_mode` (`match_mode`),
  CONSTRAINT `fk_caa_audience` FOREIGN KEY (`audience_id`) REFERENCES `aq_custom_audiences` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Lookalike audience configuration
-- Expands a seed audience to find similar users in the platform's data
CREATE TABLE IF NOT EXISTS `aq_audience_lookalikes` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `lookalike_audience_id` BIGINT UNSIGNED NOT NULL COMMENT 'FK → aq_custom_audiences (the generated lookalike audience)',
  `seed_audience_id` BIGINT UNSIGNED NOT NULL COMMENT 'FK → aq_custom_audiences (the source seed audience)',
  `seed_size` INT UNSIGNED DEFAULT NULL COMMENT 'Seed audience size at time of creation',
  `expansion_ratio` DECIMAL(4,2) NOT NULL DEFAULT 1.00 COMMENT 'Expansion factor 1-10 (1=most similar, 10=broadest reach)',
  `similarity_threshold` DECIMAL(5,4) DEFAULT NULL COMMENT 'Minimum similarity score 0.0000-1.0000',
  `target_countries` JSON DEFAULT NULL COMMENT 'Limit lookalike to specific countries ["AL","XK","MK"]',
  `model_type` ENUM('behavioral','demographic','interest','combined') NOT NULL DEFAULT 'combined',
  `model_version` VARCHAR(50) DEFAULT NULL COMMENT 'ML model version used for generation',
  `model_quality_score` DECIMAL(5,2) DEFAULT NULL COMMENT 'Model quality metric 0-100',
  `estimated_reach` BIGINT UNSIGNED DEFAULT NULL COMMENT 'Estimated audience size after expansion',
  `generation_status` ENUM('pending','processing','ready','failed','expired') NOT NULL DEFAULT 'pending',
  `generated_at` DATETIME DEFAULT NULL,
  `expires_at` DATETIME DEFAULT NULL COMMENT 'Lookalike models need periodic refresh',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_lookalike` (`lookalike_audience_id`),
  KEY `idx_seed` (`seed_audience_id`),
  KEY `idx_status` (`generation_status`),
  CONSTRAINT `fk_al_lookalike` FOREIGN KEY (`lookalike_audience_id`) REFERENCES `aq_custom_audiences` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_al_seed` FOREIGN KEY (`seed_audience_id`) REFERENCES `aq_custom_audiences` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Raw pixel fire events — high-volume log table
-- Each pixel fire from a visitor's browser is recorded here, then processed into audience membership
CREATE TABLE IF NOT EXISTS `aq_pixel_events` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `pixel_id` BIGINT UNSIGNED NOT NULL COMMENT 'FK → aq_audience_pixels',
  `event_name` VARCHAR(100) NOT NULL COMMENT 'PageView, AddToCart, Purchase, Lead, ViewContent, or custom event',
  `event_timestamp` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  -- Visitor identification (all hashed for privacy)
  `cookie_id` VARCHAR(255) DEFAULT NULL COMMENT 'First-party cookie ID',
  `device_id` VARCHAR(255) DEFAULT NULL COMMENT 'Device advertising ID (IDFA/GAID) hashed',
  `ip_hash` VARCHAR(64) DEFAULT NULL COMMENT 'SHA-256 hashed IP for fingerprinting fallback',
  `user_agent_hash` VARCHAR(64) DEFAULT NULL COMMENT 'SHA-256 hashed User-Agent',
  -- Event context
  `page_url` VARCHAR(2000) DEFAULT NULL COMMENT 'URL where the pixel fired',
  `referrer_url` VARCHAR(2000) DEFAULT NULL,
  `event_value` DECIMAL(12,2) DEFAULT NULL COMMENT 'Monetary value (e.g. purchase amount)',
  `event_currency` VARCHAR(3) DEFAULT NULL,
  `event_params` JSON DEFAULT NULL COMMENT 'Custom event parameters {"product_id":"SKU123","category":"shoes","quantity":2}',
  -- Geo & device (resolved at ingestion)
  `country_code` CHAR(2) DEFAULT NULL,
  `city` VARCHAR(100) DEFAULT NULL,
  `device_type` ENUM('desktop','mobile','tablet','smart_tv','other') DEFAULT NULL,
  `browser` VARCHAR(50) DEFAULT NULL,
  `os` VARCHAR(50) DEFAULT NULL,
  -- Processing
  `is_processed` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Has this event been evaluated against audience rules',
  `audiences_matched` JSON DEFAULT NULL COMMENT 'Array of audience IDs this event qualified for',
  PRIMARY KEY (`id`),
  KEY `idx_pixel` (`pixel_id`),
  KEY `idx_event` (`event_name`),
  KEY `idx_timestamp` (`event_timestamp`),
  KEY `idx_cookie` (`cookie_id`),
  KEY `idx_processed` (`is_processed`),
  CONSTRAINT `fk_pe_pixel` FOREIGN KEY (`pixel_id`) REFERENCES `aq_audience_pixels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 43. CLIP CAMPAIGNS — SHORT-FORM VERTICAL VIDEO ADS
-- ============================================================================
-- TikTok / Reels / Shorts-style immersive short-form video ad system:
--   • Clip Campaign Settings  — campaign-level clip configuration
--   • Clip Templates          — reusable clip creative templates & presets
--   • Clip Music Library      — licensed audio tracks for background music
--   • Clip Interactions       — swipe, tap, share, save, poll engagement tracking
--
-- Clip ads are 5-60 second vertical (9:16) videos that play sound-on,
-- autoplay in-feed, loop continuously, and support swipe-up CTA,
-- interactive polls, shoppable product pins, text/sticker overlays,
-- and branded hashtag challenges.
--
-- Inline clip fields live on aq_ads & aq_direct_campaigns;
-- these tables provide campaign-level settings, reusable templates,
-- a licensed music library, and granular interaction analytics.
-- ============================================================================

-- Campaign-level clip settings
-- Applies to any campaign that uses clip ad format
CREATE TABLE IF NOT EXISTS `aq_clip_campaigns` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `campaign_id` BIGINT UNSIGNED NOT NULL,
  `campaign_source` ENUM('rtb','direct') NOT NULL DEFAULT 'rtb',
  -- Creative constraints
  `min_duration_seconds` INT UNSIGNED NOT NULL DEFAULT 5 COMMENT 'Minimum clip length allowed (5s)',
  `max_duration_seconds` INT UNSIGNED NOT NULL DEFAULT 60 COMMENT 'Maximum clip length allowed (60s)',
  `allowed_aspect_ratios` JSON NOT NULL DEFAULT '["9:16"]' COMMENT 'Allowed ratios: ["9:16","4:5","1:1"]',
  `require_sound` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Reject clips without audio track',
  `require_captions` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Require burn-in or auto-generated captions for accessibility',
  `auto_generate_captions` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Auto-generate captions via speech-to-text',
  `caption_language` VARCHAR(5) DEFAULT 'sq' COMMENT 'Caption language ISO code',
  -- Playback behavior
  `autoplay_mode` ENUM('always','wifi_only','never') NOT NULL DEFAULT 'always',
  `sound_default` ENUM('on','off','follow_device') NOT NULL DEFAULT 'on',
  `loop_enabled` TINYINT(1) NOT NULL DEFAULT 1,
  `max_loop_count` INT UNSIGNED DEFAULT NULL COMMENT 'NULL = infinite loops, or limit to N replays',
  -- Swipe-up / CTA
  `swipe_up_style` ENUM('text_button','pill','gradient_bar','animated_arrow','custom') NOT NULL DEFAULT 'pill',
  `swipe_up_color` CHAR(7) DEFAULT NULL COMMENT 'CTA button hex color',
  `swipe_up_animation` ENUM('none','pulse','slide_up','bounce') NOT NULL DEFAULT 'pulse',
  -- Engagement features
  `polls_enabled` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Allow interactive polls on clips',
  `shoppable_enabled` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Allow product pin overlays',
  `stickers_enabled` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Allow sticker/GIF overlays',
  `hashtag_challenge` VARCHAR(255) DEFAULT NULL COMMENT 'Branded hashtag challenge name e.g. #VerëMeAdshqip',
  `hashtag_challenge_description` TEXT DEFAULT NULL COMMENT 'Challenge description / instructions',
  -- Delivery settings
  `placement_type` ENUM('in_feed','stories','discovery','pre_roll','all') NOT NULL DEFAULT 'in_feed' COMMENT 'Where clips appear in publisher inventory',
  `frequency_cap_per_user` INT UNSIGNED DEFAULT 3 COMMENT 'Max times same user sees this clip per day',
  `viewability_threshold_pct` INT UNSIGNED DEFAULT 50 COMMENT 'Min % of clip visible to count as viewable',
  `view_counted_at_seconds` INT UNSIGNED DEFAULT 2 COMMENT 'Seconds of watch time before counting as a view (billing)',
  -- Brand safety
  `content_rating` ENUM('G','PG','PG13','R') NOT NULL DEFAULT 'PG' COMMENT 'Content rating for brand safety matching',
  `sensitive_categories_blocked` JSON DEFAULT NULL COMMENT 'Block categories: ["politics","gambling","alcohol"]',
  -- Performance
  `optimization_goal` ENUM('views','engagement','swipe_ups','conversions','reach') NOT NULL DEFAULT 'views',
  `target_completion_rate_pct` DECIMAL(5,2) DEFAULT NULL COMMENT 'Target % of viewers who watch entire clip',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_campaign` (`campaign_id`, `campaign_source`),
  KEY `idx_placement` (`placement_type`),
  KEY `idx_optimization` (`optimization_goal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reusable clip creative templates / presets
-- Pre-built layouts, transitions, and styles that advertisers can apply to their clips
CREATE TABLE IF NOT EXISTS `aq_clip_templates` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL COMMENT 'e.g. "Product Showcase — Bold", "Testimonial — Minimal"',
  `description` TEXT DEFAULT NULL,
  `category` ENUM('product_showcase','testimonial','unboxing','tutorial','behind_the_scenes','announcement','sale_promo','challenge','storytelling','custom') NOT NULL DEFAULT 'product_showcase',
  `thumbnail_url` VARCHAR(500) DEFAULT NULL COMMENT 'Template preview thumbnail',
  `preview_video_url` VARCHAR(500) DEFAULT NULL COMMENT 'Animated template preview',
  -- Template structure
  `aspect_ratio` ENUM('9:16','4:5','1:1') NOT NULL DEFAULT '9:16',
  `duration_seconds` INT UNSIGNED NOT NULL DEFAULT 15 COMMENT 'Suggested clip duration for this template',
  `scene_count` INT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Number of scenes / slides in the template',
  `scene_config` JSON DEFAULT NULL COMMENT 'Per-scene config: [{"scene":1,"duration_s":5,"transition":"slide_left","overlay":"bold_text_bottom"}]',
  `transition_style` ENUM('cut','fade','slide_left','slide_up','zoom','glitch','none') NOT NULL DEFAULT 'cut',
  -- Text & overlay presets
  `text_preset` JSON DEFAULT NULL COMMENT 'Default text overlay style: {"font":"Montserrat","size":"24px","color":"#FFF","shadow":true,"position":"bottom_center"}',
  `cta_preset` JSON DEFAULT NULL COMMENT 'Default CTA style: {"style":"pill","color":"#FF5500","text":"Shiko Tani","animation":"pulse"}',
  `sticker_presets` JSON DEFAULT NULL COMMENT 'Suggested stickers for this template',
  -- Music
  `suggested_music_genre` VARCHAR(50) DEFAULT NULL COMMENT 'Recommended music genre e.g. "upbeat", "chill", "dramatic"',
  `suggested_music_bpm_range` JSON DEFAULT NULL COMMENT '{"min":120,"max":140}',
  -- Metadata
  `is_premium` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Premium templates for paid plans',
  `usage_count` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Times this template has been used',
  `avg_completion_rate` DECIMAL(5,2) DEFAULT NULL COMMENT 'Average clip completion rate for ads using this template',
  `avg_ctr` DECIMAL(8,4) DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`),
  KEY `idx_active` (`is_active`),
  KEY `idx_usage` (`usage_count`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed clip templates
INSERT INTO `aq_clip_templates` (`name`, `description`, `category`, `aspect_ratio`, `duration_seconds`, `scene_count`, `transition_style`, `is_premium`, `is_active`) VALUES
('Product Showcase — Bold',       'Eye-catching product reveal with bold text overlays and zoom transitions.',                'product_showcase',    '9:16', 15, 3, 'zoom',       0, 1),
('Testimonial — Minimal',         'Clean customer testimonial layout with subtle text and fade transitions.',                 'testimonial',         '9:16', 20, 2, 'fade',       0, 1),
('Unboxing Experience',            'Step-by-step unboxing with slide transitions and product highlight pins.',               'unboxing',            '9:16', 30, 4, 'slide_left', 0, 1),
('Quick Tutorial',                 'Fast-paced how-to format with numbered steps and cut transitions.',                      'tutorial',            '9:16', 25, 5, 'cut',        0, 1),
('Flash Sale Countdown',           'Urgency-driven sale promo with countdown timer and glitch transitions.',                 'sale_promo',          '9:16', 10, 2, 'glitch',     0, 1),
('Brand Story — Cinematic',        'Premium cinematic storytelling template with slow fades and voiceover support.',          'storytelling',        '9:16', 45, 6, 'fade',       1, 1),
('Hashtag Challenge Starter',      'Template for branded hashtag challenges with participant instructions.',                  'challenge',           '9:16', 15, 3, 'slide_up',   0, 1),
('Behind the Scenes',              'Raw, authentic behind-the-scenes look with minimal editing and jump cuts.',              'behind_the_scenes',   '9:16', 20, 3, 'cut',        0, 1),
('Announcement — Big Reveal',      'Dramatic announcement format with suspense build-up and reveal moment.',                 'announcement',        '9:16', 12, 2, 'zoom',       0, 1),
('Square Product Card',            'Square format optimized for in-feed product cards with price overlay.',                  'product_showcase',    '1:1',  10, 1, 'none',       0, 1);

-- Licensed music / audio tracks for clip ads
CREATE TABLE IF NOT EXISTS `aq_clip_music_library` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL COMMENT 'Track title',
  `artist` VARCHAR(255) DEFAULT NULL,
  `album` VARCHAR(255) DEFAULT NULL,
  `genre` ENUM('pop','electronic','hip_hop','rock','indie','acoustic','cinematic','lofi','upbeat','chill','dramatic','ambient','folk','latin','balkan','custom') NOT NULL DEFAULT 'pop',
  `mood` ENUM('happy','energetic','calm','dramatic','inspirational','funny','romantic','dark','neutral') NOT NULL DEFAULT 'energetic',
  `bpm` INT UNSIGNED DEFAULT NULL COMMENT 'Beats per minute',
  `duration_seconds` INT UNSIGNED NOT NULL,
  `audio_url` VARCHAR(500) NOT NULL COMMENT 'CDN URL to the audio file (MP3/AAC)',
  `preview_url` VARCHAR(500) DEFAULT NULL COMMENT 'Short preview clip (15s) for browsing',
  `waveform_url` VARCHAR(500) DEFAULT NULL COMMENT 'Pre-rendered waveform image URL',
  -- Licensing
  `license_type` ENUM('royalty_free','creative_commons','licensed','original','public_domain') NOT NULL DEFAULT 'royalty_free',
  `license_holder` VARCHAR(255) DEFAULT NULL,
  `license_expires_at` DATE DEFAULT NULL,
  `usage_rights` JSON DEFAULT NULL COMMENT '{"commercial":true,"modification":true,"attribution_required":false,"territories":["worldwide"]}',
  -- Metadata
  `tags` JSON DEFAULT NULL COMMENT '["summer","dance","albanian","trending"]',
  `is_trending` TINYINT(1) NOT NULL DEFAULT 0,
  `usage_count` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Times used in clip ads',
  `is_explicit` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Contains explicit lyrics',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_genre` (`genre`),
  KEY `idx_mood` (`mood`),
  KEY `idx_trending` (`is_trending`),
  KEY `idx_bpm` (`bpm`),
  KEY `idx_license` (`license_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed some starter music tracks
INSERT INTO `aq_clip_music_library` (`title`, `artist`, `genre`, `mood`, `bpm`, `duration_seconds`, `audio_url`, `license_type`, `tags`, `is_trending`, `is_active`) VALUES
('Summer Vibes',        'Adshqip Beats',    'electronic',  'happy',         128, 30, '/audio/clip-music/summer-vibes.mp3',        'royalty_free', '["summer","upbeat","trending"]',     1, 1),
('Balkan Energy',       'Adshqip Beats',    'balkan',      'energetic',     135, 25, '/audio/clip-music/balkan-energy.mp3',       'royalty_free', '["balkan","dance","albanian"]',      1, 1),
('Chill Lo-fi',         'Adshqip Beats',    'lofi',        'calm',           85, 45, '/audio/clip-music/chill-lofi.mp3',          'royalty_free', '["lofi","chill","study"]',           0, 1),
('Product Reveal',      'Adshqip Beats',    'cinematic',   'dramatic',      100, 15, '/audio/clip-music/product-reveal.mp3',      'royalty_free', '["reveal","dramatic","launch"]',     1, 1),
('Upbeat Pop',          'Adshqip Beats',    'pop',         'energetic',     120, 30, '/audio/clip-music/upbeat-pop.mp3',          'royalty_free', '["pop","upbeat","commercial"]',      0, 1),
('Inspirational Rise',  'Adshqip Beats',    'cinematic',   'inspirational', 110, 30, '/audio/clip-music/inspirational-rise.mp3',  'royalty_free', '["inspirational","brand","story"]',  0, 1),
('Street Style',        'Adshqip Beats',    'hip_hop',     'energetic',     95,  20, '/audio/clip-music/street-style.mp3',        'royalty_free', '["hiphop","urban","fashion"]',       0, 1),
('Acoustic Morning',    'Adshqip Beats',    'acoustic',    'calm',          90,  40, '/audio/clip-music/acoustic-morning.mp3',    'royalty_free', '["acoustic","morning","soft"]',      0, 1);

-- Granular clip interaction / engagement tracking
-- Every swipe, tap, share, save, poll vote on a clip ad is logged here
CREATE TABLE IF NOT EXISTS `aq_clip_interactions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ad_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'FK → aq_ads',
  `campaign_id` BIGINT UNSIGNED NOT NULL,
  `campaign_source` ENUM('rtb','direct') NOT NULL DEFAULT 'rtb',
  `impression_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'FK → aq_impressions',
  -- Interaction type
  `interaction_type` ENUM('view_start','view_25pct','view_50pct','view_75pct','view_complete','view_loop','swipe_up','tap_cta','tap_product','share','save','like','poll_vote','sound_on','sound_off','caption_toggle','long_press','screenshot') NOT NULL,
  `interaction_value` VARCHAR(500) DEFAULT NULL COMMENT 'Context: poll answer text, product ID tapped, share platform, etc.',
  -- Watch metrics (populated for view_ events)
  `watch_duration_seconds` DECIMAL(6,2) DEFAULT NULL COMMENT 'Actual seconds watched at time of event',
  `clip_total_seconds` INT UNSIGNED DEFAULT NULL COMMENT 'Total clip length for % calculation',
  `loop_number` INT UNSIGNED DEFAULT 1 COMMENT 'Which loop iteration (1 = first play)',
  `sound_was_on` TINYINT(1) DEFAULT NULL COMMENT 'Was sound on when interaction occurred',
  -- Visitor context
  `cookie_id` VARCHAR(255) DEFAULT NULL,
  `device_type` ENUM('mobile','tablet','desktop') DEFAULT NULL,
  `os` VARCHAR(50) DEFAULT NULL,
  `country_code` CHAR(2) DEFAULT NULL,
  `placement` ENUM('in_feed','stories','discovery','pre_roll') DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ad` (`ad_id`),
  KEY `idx_campaign` (`campaign_id`, `campaign_source`),
  KEY `idx_type` (`interaction_type`),
  KEY `idx_created` (`created_at`),
  KEY `idx_impression` (`impression_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Clip-specific VAST / tracking events (supplement aq_vast_events)
INSERT IGNORE INTO `aq_vast_events` (`event_name`, `description`, `is_trackable`) VALUES
('clip_view_start',     'Clip started playing (autoplay or tap)',                     1),
('clip_view_25pct',     'Viewer reached 25% of clip duration',                       1),
('clip_view_50pct',     'Viewer reached 50% of clip duration',                       1),
('clip_view_75pct',     'Viewer reached 75% of clip duration',                       1),
('clip_view_complete',  'Viewer watched entire clip to completion',                   1),
('clip_view_loop',      'Clip looped back to start (repeat view)',                   1),
('clip_swipe_up',       'Viewer swiped up on CTA',                                   1),
('clip_tap_product',    'Viewer tapped a shoppable product pin',                     1),
('clip_share',          'Viewer shared the clip',                                     1),
('clip_save',           'Viewer saved/bookmarked the clip',                          1),
('clip_poll_vote',      'Viewer voted in interactive poll',                           1),
('clip_sound_on',       'Viewer unmuted / turned sound on',                          1),
('clip_sound_off',      'Viewer muted / turned sound off',                           1);

-- Add Clip to MultiTag associations
-- (assuming clip format id = 16)
INSERT IGNORE INTO `aq_ad_format_tags` (`format_id`, `tag_id`) VALUES
-- Clip Ads: Mobile First, Video Ready, Brand Safe, High Impact
(16, 3), (16, 7), (16, 10), (16, 1);

-- ============================================================================
-- 44. OEM — ORIGINAL EQUIPMENT MANUFACTURERS
-- ============================================================================
-- OEM advertising delivers ads through device manufacturer channels:
--   • Pre-installed apps (bloatware / recommended apps)
--   • Device setup wizard (first-boot app recommendations)
--   • OEM app stores (Galaxy Store, Huawei AppGallery, GetApps, etc.)
--   • Lockscreen / glance screen ads
--   • Notification tray recommendations
--   • Smart folder / content discovery widgets
--   • Browser default & search recommendations
--
-- OEMs are a major mobile user acquisition channel — reaching users
-- at first device setup before they even open the Play Store.
--
-- Tables:
--   aq_oem_manufacturers     — master list of OEM partners
--   aq_oem_placements        — available ad placement types per OEM
--   aq_oem_apps              — pre-installed / bundled apps carrying inventory
--   aq_oem_device_models     — supported device models per OEM
--   aq_campaign_oem_assoc    — link campaigns → OEM manufacturers/placements
--   aq_oem_performance_stats — daily OEM performance metrics
-- ============================================================================

-- Master list of OEM partners
CREATE TABLE IF NOT EXISTS `aq_oem_manufacturers` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL COMMENT 'OEM brand name e.g. Samsung, Xiaomi, Huawei',
  `slug` VARCHAR(100) NOT NULL COMMENT 'URL-safe identifier e.g. samsung, xiaomi, huawei',
  `logo_url` VARCHAR(500) DEFAULT NULL,
  `website_url` VARCHAR(500) DEFAULT NULL,
  `headquarters_country` CHAR(2) DEFAULT NULL COMMENT 'ISO 3166-1 alpha-2 country code',
  -- Market data
  `global_market_share_pct` DECIMAL(5,2) DEFAULT NULL COMMENT 'Estimated global smartphone market share %',
  `monthly_active_devices` BIGINT UNSIGNED DEFAULT NULL COMMENT 'Estimated MAD (monthly active devices)',
  `primary_regions` JSON DEFAULT NULL COMMENT 'Strongest regions: ["europe","asia","latin_america","africa","middle_east"]',
  `supported_os` JSON DEFAULT NULL COMMENT '["android","harmonyos","kaios","tizen","custom"]',
  -- Integration
  `integration_type` ENUM('api','sdk','s2s','manual') NOT NULL DEFAULT 'api' COMMENT 'How we integrate with this OEM ad platform',
  `api_endpoint` VARCHAR(500) DEFAULT NULL COMMENT 'OEM ad platform API endpoint',
  `api_version` VARCHAR(20) DEFAULT NULL,
  `sdk_version` VARCHAR(50) DEFAULT NULL COMMENT 'OEM SDK version if SDK integration',
  `attribution_method` ENUM('device_id','referrer','s2s_postback','skan','fingerprint') NOT NULL DEFAULT 'device_id' COMMENT 'How installs/events are attributed',
  `supports_retargeting` TINYINT(1) NOT NULL DEFAULT 0,
  `supports_deep_linking` TINYINT(1) NOT NULL DEFAULT 0,
  -- Commercial terms
  `pricing_model` ENUM('cpi','cpc','cpm','cpa','revenue_share','flat_fee','hybrid') NOT NULL DEFAULT 'cpi' COMMENT 'Primary pricing model with this OEM',
  `min_bid_cpi` DECIMAL(10,4) DEFAULT NULL COMMENT 'Minimum cost per install bid',
  `avg_cpi` DECIMAL(10,4) DEFAULT NULL COMMENT 'Average CPI across all campaigns',
  `revenue_share_pct` DECIMAL(5,2) DEFAULT NULL COMMENT 'Revenue share % for rev-share model',
  `payment_terms_days` INT UNSIGNED DEFAULT 30 COMMENT 'Net payment terms in days',
  `currency` VARCHAR(3) DEFAULT 'USD',
  -- Quality & compliance
  `brand_safety_tier` ENUM('premium','standard','open') NOT NULL DEFAULT 'standard',
  `fraud_detection_level` ENUM('basic','advanced','premium') NOT NULL DEFAULT 'advanced',
  `gdpr_compliant` TINYINT(1) NOT NULL DEFAULT 1,
  `coppa_compliant` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Children Online Privacy Protection Act compliance',
  -- Status
  `partnership_status` ENUM('prospect','negotiating','active','paused','terminated') NOT NULL DEFAULT 'active',
  `contract_start_date` DATE DEFAULT NULL,
  `contract_end_date` DATE DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_slug` (`slug`),
  KEY `idx_status` (`partnership_status`),
  KEY `idx_active` (`is_active`),
  KEY `idx_country` (`headquarters_country`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed OEM manufacturers
INSERT INTO `aq_oem_manufacturers` (`name`, `slug`, `headquarters_country`, `global_market_share_pct`, `primary_regions`, `supported_os`, `integration_type`, `attribution_method`, `pricing_model`, `brand_safety_tier`, `partnership_status`, `is_active`) VALUES
('Samsung',    'samsung',    'KR', 19.40, '["europe","asia","north_america","latin_america","africa"]',  '["android","tizen"]',     'api', 'device_id',    'cpi',     'premium',  'active', 1),
('Xiaomi',     'xiaomi',     'CN', 12.80, '["asia","europe","latin_america","africa","middle_east"]',    '["android"]',             'api', 'device_id',    'cpi',     'standard', 'active', 1),
('Huawei',     'huawei',     'CN',  4.20, '["asia","europe","middle_east","africa"]',                    '["android","harmonyos"]', 'api', 's2s_postback', 'cpi',     'premium',  'active', 1),
('Oppo',       'oppo',       'CN',  8.80, '["asia","europe","middle_east","africa"]',                    '["android"]',             'api', 'device_id',    'cpi',     'standard', 'active', 1),
('Vivo',       'vivo',       'CN',  8.10, '["asia","europe","middle_east"]',                             '["android"]',             'api', 'device_id',    'cpi',     'standard', 'active', 1),
('Realme',     'realme',     'CN',  3.50, '["asia","europe","latin_america"]',                           '["android"]',             'sdk', 'device_id',    'cpi',     'standard', 'active', 1),
('OnePlus',    'oneplus',    'CN',  1.80, '["asia","europe","north_america"]',                           '["android"]',             'api', 'device_id',    'cpi',     'premium',  'active', 1),
('Transsion',  'transsion',  'CN',  6.50, '["africa","asia","middle_east","latin_america"]',             '["android"]',             's2s', 's2s_postback', 'cpi',     'standard', 'active', 1),
('Motorola',   'motorola',   'US',  2.80, '["north_america","latin_america","europe"]',                  '["android"]',             'api', 'device_id',    'cpi',     'standard', 'active', 1),
('Nokia/HMD',  'nokia_hmd',  'FI',  0.90, '["europe","asia","africa"]',                                 '["android"]',             'api', 'device_id',    'cpi',     'premium',  'active', 1),
('Google',     'google',     'US',  2.10, '["north_america","europe","asia"]',                           '["android"]',             'api', 'referrer',     'cpi',     'premium',  'active', 1),
('Honor',      'honor',      'CN',  4.60, '["asia","europe","middle_east","latin_america"]',             '["android"]',             'api', 'device_id',    'cpi',     'standard', 'active', 1);

-- Available ad placement types per OEM
CREATE TABLE IF NOT EXISTS `aq_oem_placements` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `manufacturer_id` BIGINT UNSIGNED NOT NULL COMMENT 'FK → aq_oem_manufacturers',
  `placement_type` ENUM('setup_wizard','app_store','lockscreen','notification_tray','smart_folder','browser_default','search_recommendation','preinstall','firmware_update','content_widget','game_center','theme_store','weather_app','system_cleaner','file_manager') NOT NULL,
  `placement_name` VARCHAR(255) NOT NULL COMMENT 'Human-readable name e.g. "Galaxy Store Featured Apps"',
  `description` TEXT DEFAULT NULL,
  -- Placement specs
  `ad_format` ENUM('app_icon','banner','interstitial','native_card','notification','fullscreen','video','text_link','carousel') NOT NULL DEFAULT 'app_icon',
  `position` VARCHAR(100) DEFAULT NULL COMMENT 'e.g. "row_1_slot_3", "top_banner", "recommended_section"',
  `impression_type` ENUM('view','click','install','engagement') NOT NULL DEFAULT 'view' COMMENT 'When the impression is counted',
  `max_creatives` INT UNSIGNED DEFAULT 1 COMMENT 'Max number of ad creatives per placement slot',
  `dimensions` JSON DEFAULT NULL COMMENT '{"width":300,"height":250} or null for dynamic',
  -- Reach & performance
  `estimated_daily_impressions` BIGINT UNSIGNED DEFAULT NULL,
  `avg_ctr` DECIMAL(8,4) DEFAULT NULL,
  `avg_conversion_rate` DECIMAL(8,4) DEFAULT NULL COMMENT 'Avg install rate for CPI',
  -- Targeting capabilities
  `supports_geo_targeting` TINYINT(1) NOT NULL DEFAULT 1,
  `supports_device_targeting` TINYINT(1) NOT NULL DEFAULT 1,
  `supports_demographic_targeting` TINYINT(1) NOT NULL DEFAULT 0,
  `supports_interest_targeting` TINYINT(1) NOT NULL DEFAULT 0,
  `supports_frequency_capping` TINYINT(1) NOT NULL DEFAULT 1,
  -- Restrictions
  `min_os_version` VARCHAR(20) DEFAULT NULL COMMENT 'Minimum Android/OS version required',
  `restricted_categories` JSON DEFAULT NULL COMMENT 'Categories blocked on this placement: ["gambling","adult","crypto"]',
  `requires_approval` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'OEM requires creative approval before going live',
  `approval_lead_time_days` INT UNSIGNED DEFAULT 3 COMMENT 'Typical approval turnaround in business days',
  -- Status
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_manufacturer` (`manufacturer_id`),
  KEY `idx_placement_type` (`placement_type`),
  KEY `idx_format` (`ad_format`),
  KEY `idx_active` (`is_active`),
  CONSTRAINT `fk_op_manufacturer` FOREIGN KEY (`manufacturer_id`) REFERENCES `aq_oem_manufacturers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed OEM placements for major partners
INSERT INTO `aq_oem_placements` (`manufacturer_id`, `placement_type`, `placement_name`, `description`, `ad_format`, `impression_type`, `is_active`) VALUES
-- Samsung (id=1)
(1, 'setup_wizard',          'Galaxy Setup Wizard — App Recommendations',     'First-boot app recommendations during device setup. Highest intent placement.', 'app_icon',    'install', 1),
(1, 'app_store',             'Galaxy Store — Featured Apps',                   'Featured app slots in Samsung Galaxy Store browse and search results.',          'native_card', 'view',    1),
(1, 'lockscreen',            'Samsung Glance — Lockscreen Ads',               'Full-screen ads on Samsung lockscreen via Glance integration.',                  'fullscreen',  'view',    1),
(1, 'notification_tray',     'Samsung Push — Notification Recommendations',   'Notification tray app/content recommendations.',                                'notification','click',   1),
(1, 'smart_folder',          'Galaxy Smart Folder — App Discovery',           'Recommended apps in the smart folder widget on home screen.',                    'app_icon',    'click',   1),
(1, 'game_center',           'Samsung Game Launcher — Featured Games',        'Featured game slots in Samsung Game Launcher.',                                  'native_card', 'view',    1),
-- Xiaomi (id=2)
(2, 'setup_wizard',          'MIUI Setup — App Recommendations',              'App recommendations during MIUI first-boot setup.',                              'app_icon',    'install', 1),
(2, 'app_store',             'GetApps — Featured Apps',                       'Featured app placements in Xiaomi GetApps store.',                               'native_card', 'view',    1),
(2, 'lockscreen',            'MIUI Glance — Lockscreen Content',              'Lockscreen content and ad cards on MIUI devices.',                               'fullscreen',  'view',    1),
(2, 'browser_default',       'Mi Browser — Content Feed',                     'Native content cards in Xiaomi Mi Browser feed.',                                'native_card', 'view',    1),
(2, 'smart_folder',          'MIUI App Vault — Recommendations',              'App recommendations in MIUI App Vault swipe-right panel.',                       'native_card', 'click',   1),
(2, 'theme_store',           'MIUI Themes — Sponsored Themes',                'Sponsored theme and wallpaper placements in MIUI Themes.',                       'banner',      'view',    1),
-- Huawei (id=3)
(3, 'setup_wizard',          'EMUI Setup — App Recommendations',              'First-boot app suggestions on EMUI/HarmonyOS devices.',                          'app_icon',    'install', 1),
(3, 'app_store',             'AppGallery — Featured Apps',                    'Featured placements in Huawei AppGallery.',                                      'native_card', 'view',    1),
(3, 'browser_default',       'Huawei Browser — Content Discovery',            'Sponsored content cards in Huawei Browser homepage.',                            'native_card', 'view',    1),
(3, 'content_widget',        'Huawei Assistant — Smart Cards',                'Smart recommendation cards in Huawei Assistant (leftmost home panel).',           'native_card', 'click',   1),
-- Oppo (id=4)
(4, 'setup_wizard',          'ColorOS Setup — App Recommendations',           'App recommendations during ColorOS first-boot.',                                 'app_icon',    'install', 1),
(4, 'app_store',             'Oppo App Market — Featured Apps',               'Featured app slots in Oppo App Market.',                                         'native_card', 'view',    1),
(4, 'lockscreen',            'Oppo Lockscreen Magazine',                      'Magazine-style lockscreen content and ads on ColorOS.',                           'fullscreen',  'view',    1),
(4, 'smart_folder',          'ColorOS Smart Folder — App Discovery',          'Recommended apps in ColorOS smart folders.',                                     'app_icon',    'click',   1),
-- Vivo (id=5)
(5, 'setup_wizard',          'Funtouch Setup — App Recommendations',          'App recommendations during Funtouch OS first-boot.',                             'app_icon',    'install', 1),
(5, 'app_store',             'V-Appstore — Featured Apps',                    'Featured app slots in Vivo V-Appstore.',                                         'native_card', 'view',    1),
(5, 'lockscreen',            'Vivo Lockscreen — Glance Ads',                  'Lockscreen ad placements on Funtouch OS devices.',                               'fullscreen',  'view',    1);

-- Pre-installed / bundled apps carrying OEM ad inventory
CREATE TABLE IF NOT EXISTS `aq_oem_apps` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `manufacturer_id` BIGINT UNSIGNED NOT NULL COMMENT 'FK → aq_oem_manufacturers',
  `app_name` VARCHAR(255) NOT NULL,
  `package_name` VARCHAR(255) NOT NULL COMMENT 'Android package e.g. com.sec.android.app.sbrowser',
  `app_category` ENUM('browser','app_store','file_manager','weather','news','music','video','camera','gallery','cleaner','security','game_center','health','theme_store','keyboard','launcher','other') NOT NULL DEFAULT 'other',
  `preinstall_type` ENUM('system_app','partner_app','removable','non_removable','trial','optional_setup') NOT NULL DEFAULT 'partner_app',
  -- Reach
  `estimated_mau` BIGINT UNSIGNED DEFAULT NULL COMMENT 'Monthly active users',
  `estimated_dau` BIGINT UNSIGNED DEFAULT NULL COMMENT 'Daily active users',
  `available_regions` JSON DEFAULT NULL COMMENT 'Regions where this app is preinstalled: ["AL","XK","MK","RS","ME"]',
  -- Ad inventory
  `ad_placements_count` INT UNSIGNED DEFAULT 0 COMMENT 'Number of ad slots within this app',
  `supported_ad_formats` JSON DEFAULT NULL COMMENT '["banner","interstitial","native","video","rewarded_video"]',
  `avg_session_duration_s` INT UNSIGNED DEFAULT NULL COMMENT 'Average user session in seconds',
  `avg_daily_sessions` DECIMAL(5,2) DEFAULT NULL COMMENT 'Average sessions per user per day',
  -- Status
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_manufacturer` (`manufacturer_id`),
  KEY `idx_package` (`package_name`),
  KEY `idx_category` (`app_category`),
  KEY `idx_preinstall` (`preinstall_type`),
  CONSTRAINT `fk_oa_manufacturer` FOREIGN KEY (`manufacturer_id`) REFERENCES `aq_oem_manufacturers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Supported device models per OEM
CREATE TABLE IF NOT EXISTS `aq_oem_device_models` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `manufacturer_id` BIGINT UNSIGNED NOT NULL COMMENT 'FK → aq_oem_manufacturers',
  `model_name` VARCHAR(255) NOT NULL COMMENT 'e.g. Galaxy S24 Ultra, Redmi Note 13 Pro',
  `model_code` VARCHAR(100) DEFAULT NULL COMMENT 'Internal model code e.g. SM-S928B',
  `series` VARCHAR(100) DEFAULT NULL COMMENT 'Product line e.g. Galaxy S, Redmi Note, P series',
  `tier` ENUM('flagship','mid_range','budget','entry') NOT NULL DEFAULT 'mid_range',
  `release_year` SMALLINT UNSIGNED DEFAULT NULL,
  `os_version` VARCHAR(50) DEFAULT NULL COMMENT 'Shipped OS version e.g. Android 14, HarmonyOS 4',
  `screen_size_inches` DECIMAL(4,2) DEFAULT NULL,
  `screen_resolution` VARCHAR(20) DEFAULT NULL COMMENT 'e.g. 1440x3120',
  `ram_gb` DECIMAL(4,1) DEFAULT NULL,
  `estimated_active_units` BIGINT UNSIGNED DEFAULT NULL COMMENT 'Estimated units still in active use',
  -- Ad capabilities
  `supports_lockscreen_ads` TINYINT(1) NOT NULL DEFAULT 0,
  `supports_setup_wizard_ads` TINYINT(1) NOT NULL DEFAULT 1,
  `supports_notification_ads` TINYINT(1) NOT NULL DEFAULT 1,
  `supports_preinstall` TINYINT(1) NOT NULL DEFAULT 1,
  -- Targeting value
  `avg_user_income_tier` ENUM('low','medium','high','premium') DEFAULT NULL COMMENT 'Estimated user income tier based on device price',
  `primary_markets` JSON DEFAULT NULL COMMENT 'Top markets for this model: ["AL","XK","DE","TR"]',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_manufacturer` (`manufacturer_id`),
  KEY `idx_series` (`series`),
  KEY `idx_tier` (`tier`),
  KEY `idx_year` (`release_year`),
  CONSTRAINT `fk_odm_manufacturer` FOREIGN KEY (`manufacturer_id`) REFERENCES `aq_oem_manufacturers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Link campaigns to specific OEM manufacturers and placements
CREATE TABLE IF NOT EXISTS `aq_campaign_oem_assoc` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `campaign_id` BIGINT UNSIGNED NOT NULL,
  `campaign_source` ENUM('rtb','direct') NOT NULL DEFAULT 'rtb',
  `manufacturer_id` BIGINT UNSIGNED NOT NULL COMMENT 'FK → aq_oem_manufacturers',
  `placement_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'FK → aq_oem_placements (NULL = all placements for this OEM)',
  -- Targeting refinements
  `device_tier_filter` JSON DEFAULT NULL COMMENT 'Target by tier: ["flagship","mid_range"] or null for all',
  `device_model_ids` JSON DEFAULT NULL COMMENT 'Specific model IDs or null for all models',
  `min_os_version` VARCHAR(20) DEFAULT NULL COMMENT 'Minimum OS version for this OEM targeting',
  `geo_filter` JSON DEFAULT NULL COMMENT 'Override geo for this OEM: ["AL","XK","MK"] or null to inherit campaign geo',
  -- Bid & budget
  `bid_adjustment_pct` DECIMAL(5,2) DEFAULT NULL COMMENT 'Bid modifier % for this specific OEM/placement',
  `daily_budget_limit` DECIMAL(10,2) DEFAULT NULL COMMENT 'Max daily spend on this OEM (NULL = no limit, use campaign budget)',
  `total_budget_limit` DECIMAL(10,2) DEFAULT NULL COMMENT 'Max total spend on this OEM',
  -- Creative
  `custom_creative_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'Override creative for this OEM placement (e.g. different icon/banner)',
  `custom_app_name` VARCHAR(255) DEFAULT NULL COMMENT 'Override app name displayed on this OEM (localization)',
  `custom_description` TEXT DEFAULT NULL COMMENT 'Override description for this OEM listing',
  -- Tracking
  `tracking_url` VARCHAR(2000) DEFAULT NULL COMMENT 'Custom tracking/postback URL for this OEM',
  `attribution_link` VARCHAR(2000) DEFAULT NULL COMMENT 'OEM-specific attribution link',
  -- Status
  `oem_approval_status` ENUM('pending','approved','rejected','changes_requested') NOT NULL DEFAULT 'pending',
  `oem_approval_date` DATETIME DEFAULT NULL,
  `oem_rejection_reason` TEXT DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_campaign_oem_placement` (`campaign_id`, `campaign_source`, `manufacturer_id`, `placement_id`),
  KEY `idx_manufacturer` (`manufacturer_id`),
  KEY `idx_placement` (`placement_id`),
  KEY `idx_approval` (`oem_approval_status`),
  CONSTRAINT `fk_coa_manufacturer` FOREIGN KEY (`manufacturer_id`) REFERENCES `aq_oem_manufacturers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_coa_placement` FOREIGN KEY (`placement_id`) REFERENCES `aq_oem_placements` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Daily OEM performance metrics
CREATE TABLE IF NOT EXISTS `aq_oem_performance_stats` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `date` DATE NOT NULL,
  `campaign_id` BIGINT UNSIGNED NOT NULL,
  `campaign_source` ENUM('rtb','direct') NOT NULL DEFAULT 'rtb',
  `manufacturer_id` BIGINT UNSIGNED NOT NULL COMMENT 'FK → aq_oem_manufacturers',
  `placement_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'FK → aq_oem_placements',
  `device_model_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'FK → aq_oem_device_models',
  `country_code` CHAR(2) DEFAULT NULL,
  -- Volume metrics
  `impressions` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `clicks` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `installs` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `uninstalls` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Users who uninstalled within attribution window',
  `opens` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'First opens after install',
  `registrations` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'In-app registration events',
  `purchases` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'In-app purchase events',
  -- Spend & revenue
  `spend` DECIMAL(12,4) NOT NULL DEFAULT 0 COMMENT 'Total spend for this OEM on this date',
  `revenue` DECIMAL(12,4) NOT NULL DEFAULT 0 COMMENT 'Revenue attributed from OEM installs',
  -- Rates
  `ctr` DECIMAL(8,4) DEFAULT NULL,
  `conversion_rate` DECIMAL(8,4) DEFAULT NULL COMMENT 'Install rate',
  `cpi` DECIMAL(10,4) DEFAULT NULL COMMENT 'Cost per install',
  `cpa` DECIMAL(10,4) DEFAULT NULL COMMENT 'Cost per action (registration/purchase)',
  `roas` DECIMAL(8,4) DEFAULT NULL COMMENT 'Return on ad spend',
  `retention_d1_pct` DECIMAL(5,2) DEFAULT NULL COMMENT 'Day-1 retention %',
  `retention_d7_pct` DECIMAL(5,2) DEFAULT NULL COMMENT 'Day-7 retention %',
  `retention_d30_pct` DECIMAL(5,2) DEFAULT NULL COMMENT 'Day-30 retention %',
  -- Quality
  `fraud_blocked` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Installs blocked by fraud detection',
  `fraud_rate_pct` DECIMAL(5,2) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_daily_oem` (`date`, `campaign_id`, `campaign_source`, `manufacturer_id`, `placement_id`, `device_model_id`, `country_code`),
  KEY `idx_date` (`date`),
  KEY `idx_campaign` (`campaign_id`, `campaign_source`),
  KEY `idx_manufacturer` (`manufacturer_id`),
  KEY `idx_placement` (`placement_id`),
  KEY `idx_model` (`device_model_id`),
  KEY `idx_country` (`country_code`),
  CONSTRAINT `fk_ops_manufacturer` FOREIGN KEY (`manufacturer_id`) REFERENCES `aq_oem_manufacturers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ops_placement` FOREIGN KEY (`placement_id`) REFERENCES `aq_oem_placements` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ops_model` FOREIGN KEY (`device_model_id`) REFERENCES `aq_oem_device_models` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 45. AFFILIATES (Admin-Created Affiliate Programs)
-- ============================================================================
-- Admins create affiliate entries to showcase advertising opportunities.
-- Each affiliate has a domain, images, target countries, title, description,
-- and linked advertising materials (ad formats).
--
-- Tables:
--   aq_affiliates              — master affiliate record (admin-created)
--   aq_affiliate_images        — multiple images per affiliate
--   aq_affiliate_countries     — link affiliates → target countries (many-to-many)
--   aq_affiliate_ad_formats    — link affiliates → ad formats / advertising materials (many-to-many)
-- ============================================================================

-- Core affiliate — one row per admin-created affiliate program
CREATE TABLE IF NOT EXISTS `aq_affiliates` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `created_by` BIGINT UNSIGNED NOT NULL COMMENT 'FK → aq_users (role=admin) who created this affiliate',
  `title` VARCHAR(255) NOT NULL COMMENT 'Affiliate program title',
  `slug` VARCHAR(255) NOT NULL COMMENT 'URL-safe slug',
  `description` TEXT DEFAULT NULL COMMENT 'Full description of the affiliate program',
  `short_description` VARCHAR(500) DEFAULT NULL COMMENT 'Brief summary shown in listings',
  `domain` VARCHAR(255) NOT NULL COMMENT 'Affiliate domain / website',
  `cover_image_url` VARCHAR(500) DEFAULT NULL COMMENT 'Primary cover/hero image URL',
  -- Status & visibility
  `status` ENUM('draft','active','paused','expired','archived') NOT NULL DEFAULT 'draft',
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Show in featured/promoted section',
  `sort_order` INT NOT NULL DEFAULT 0 COMMENT 'Display order in listings',
  -- Metadata
  `tags` JSON DEFAULT NULL COMMENT 'Freeform tags e.g. ["premium","balkans","news"]',
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_slug` (`slug`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_status` (`status`),
  KEY `idx_domain` (`domain`),
  KEY `idx_featured` (`is_featured`, `sort_order`),
  CONSTRAINT `fk_affiliate_created_by` FOREIGN KEY (`created_by`) REFERENCES `aq_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Multiple images per affiliate (gallery)
CREATE TABLE IF NOT EXISTS `aq_affiliate_images` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `affiliate_id` BIGINT UNSIGNED NOT NULL,
  `image_url` VARCHAR(500) NOT NULL COMMENT 'Image file URL',
  `alt_text` VARCHAR(255) DEFAULT NULL COMMENT 'Alt text for accessibility',
  `is_primary` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Primary image flag',
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_affiliate` (`affiliate_id`),
  CONSTRAINT `fk_afimg_affiliate` FOREIGN KEY (`affiliate_id`) REFERENCES `aq_affiliates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Junction: affiliate ↔ target countries (many-to-many)
CREATE TABLE IF NOT EXISTS `aq_affiliate_countries` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `affiliate_id` BIGINT UNSIGNED NOT NULL,
  `country_code` CHAR(2) NOT NULL COMMENT 'ISO 3166-1 alpha-2 code, FK → aq_geo_countries',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_affiliate_country` (`affiliate_id`, `country_code`),
  KEY `idx_country` (`country_code`),
  CONSTRAINT `fk_afcountry_affiliate` FOREIGN KEY (`affiliate_id`) REFERENCES `aq_affiliates` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_afcountry_country` FOREIGN KEY (`country_code`) REFERENCES `aq_geo_countries` (`iso_code`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Junction: affiliate ↔ ad formats / advertising materials (many-to-many)
CREATE TABLE IF NOT EXISTS `aq_affiliate_ad_formats` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `affiliate_id` BIGINT UNSIGNED NOT NULL,
  `format_id` INT UNSIGNED NOT NULL COMMENT 'FK → aq_ad_formats (popunder, native, etc.)',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_affiliate_format` (`affiliate_id`, `format_id`),
  KEY `idx_format` (`format_id`),
  CONSTRAINT `fk_afformat_affiliate` FOREIGN KEY (`affiliate_id`) REFERENCES `aq_affiliates` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_afformat_format` FOREIGN KEY (`format_id`) REFERENCES `aq_ad_formats` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 46. ARTICLES (Admin-Created Blog / News Articles)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `aq_articles` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `created_by` BIGINT UNSIGNED NOT NULL COMMENT 'FK → aq_users (role=admin) who created this article',
  `title` VARCHAR(255) NOT NULL COMMENT 'Article title / headline',
  `slug` VARCHAR(255) NOT NULL COMMENT 'URL-safe slug for the article',
  `details` TEXT NOT NULL COMMENT 'Full article content / body (HTML or Markdown)',
  `image_url` VARCHAR(500) DEFAULT NULL COMMENT 'Cover / featured image URL',
  `image_alt` VARCHAR(255) DEFAULT NULL COMMENT 'Alt text for the cover image',
  `published_at` DATETIME DEFAULT NULL COMMENT 'Publication date shown to readers',
  `status` ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_slug` (`slug`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_status` (`status`),
  KEY `idx_published` (`published_at`),
  CONSTRAINT `fk_article_created_by` FOREIGN KEY (`created_by`) REFERENCES `aq_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 47. CONTACT US (Public Contact Form Submissions)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `aq_contact_requests` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `contacting_as` ENUM('advertiser','publisher') NOT NULL COMMENT 'Role the person is contacting as',
  `full_name` VARCHAR(255) NOT NULL COMMENT 'Full name of the person',
  `company` VARCHAR(255) DEFAULT NULL COMMENT 'Company / organization name',
  `email` VARCHAR(255) NOT NULL COMMENT 'Contact email address',
  `message` TEXT NOT NULL COMMENT 'Message / inquiry body',
  `status` ENUM('new','read','replied','closed') NOT NULL DEFAULT 'new' COMMENT 'Admin processing status',
  `admin_notes` TEXT DEFAULT NULL COMMENT 'Internal admin notes on this request',
  `replied_by` BIGINT UNSIGNED DEFAULT NULL COMMENT 'FK → aq_users (admin who replied)',
  `replied_at` DATETIME DEFAULT NULL COMMENT 'When the admin replied',
  `ip_address` VARCHAR(45) DEFAULT NULL COMMENT 'Submitter IP for spam prevention',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_email` (`email`),
  KEY `idx_contacting_as` (`contacting_as`),
  KEY `idx_created` (`created_at`),
  KEY `idx_replied_by` (`replied_by`),
  CONSTRAINT `fk_contact_replied_by` FOREIGN KEY (`replied_by`) REFERENCES `aq_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SCHEMA COMPLETE
-- ============================================================================
-- Total tables: 105 (+5 direct campaign, +3 telegram, +2 kyc, +2 multitag, +3 referral, +2 wallet, +1 optimization, +1 account_deactivations, +2 two_factor, +4 ctw_video, +5 msn_distribution, +9 campaign_dynamics, +8 custom_audiences, +4 clip_campaigns, +6 oem, +4 affiliates, +1 articles, +1 contact)
-- Total views:  4 (+1 direct campaign, +1 referral summary)
-- Total stored procedures: 5 (+1 aq_add_funds)
-- 
-- Naming convention: aq_ prefix (Adshqip)
-- All tables use InnoDB + utf8mb4
-- Foreign keys with proper CASCADE/SET NULL
-- JSON columns for flexible targeting & settings
-- GDPR-ready with cookie consent tracking
-- Multi-language support (EN, SQ, IT, DE)
-- Balkan geo-targeting built in
-- Modern ad formats matching the landing page
-- Telegram Mini Apps integration for in-app monetization
-- KYC verification for publisher/advertiser compliance
-- MultiTag system for flexible ad format categorization
-- Referral program with commission tracking & payouts
-- Click-to-Watch video with branding overlays & end cards
-- MSN Distribution Network with "Run on MSN exclusively" feature
-- Campaign Dynamics: DCO, tokens, product feeds, budget rules, landing pages, countdowns
-- Custom Audiences: pixels, audience builder, rules, uploads, lookalikes, campaign targeting
-- Clip Campaigns: short-form vertical video (9:16), templates, music library, interactions
-- OEM: device manufacturers, placements, apps, device models, campaign targeting, performance
-- Affiliates: admin-created programs with domain, images, countries, ad formats
-- ============================================================================
