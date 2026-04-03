=== Giga Schema Pro ===
Contributors: gigaverse
Tags: schema, structured data, json-ld, rich snippets, seo, woocommerce, google, schema.org
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Rich snippets that rank. Schema that validates. WooCommerce that shines.

== Description ==

Giga Schema Pro is a complete, production-ready WordPress plugin for advanced JSON-LD schema generation with zero frontend assets. It auto-generates structured data for 25+ schema types, deeply integrates with WooCommerce products, and includes built-in validation against Google Rich Results Test.

### Key Features

* **25+ Schema Types** - Article, WebPage, Product, ProductGroup, FAQ, HowTo, LocalBusiness, Event, Course, Recipe, VideoObject, and more
* **WooCommerce-First** - Deep Product schema with GTIN, MPN, brand, shippingDetails, returnPolicy, aggregateRating, and ProductGroup for variable products
* **"Works Alongside" Architecture** - Detects schema from Yoast, Rank Math, AIOSEO, and only adds what's missing. No duplicates. No conflicts.
* **Auto-Generation Rules** - Set rules like "Apply FAQ schema to all posts in category X" and they run automatically on new and existing content
* **Built-In Validation** - One-click validation against Google Rich Results Test API. See exactly which pages pass, which fail, and what fields are missing
* **Zero Frontend Assets** - No CSS or JavaScript loaded on the frontend. Zero performance impact. Only JSON-LD in the head.
* **Free on WordPress.org** - 10 schema types available for free. Pro version unlocks 25+ types and advanced features.

### Free Schema Types (10)

* Article - Blog posts, news articles
* WebPage - Standard pages
* Product (basic) - WooCommerce products with name, price, availability
* BreadcrumbList - Navigation breadcrumbs
* Organization - Company/business info
* Person - Author profiles
* WebSite - Site-level schema with SearchAction
* FAQ - FAQ sections on any page/post
* HowTo - Step-by-step instructions
* LocalBusiness - Business address, hours, phone

### Pro Schema Types (25+)

All free types plus:
* Product (deep) - Full Google fields: GTIN, MPN, brand, shippingDetails, returnPolicy, ProductGroup
* Review - Individual product/service reviews
* AggregateRating - Star ratings
* Offer - Product offers with conditions
* Event - Events with dates, locations, ticket info
* Course - Online courses
* Recipe - Recipes with ingredients, cook time, nutrition
* VideoObject - Video content with duration, thumbnails
* SoftwareApplication - Apps and software
* Book - Book schema for publishers
* JobPosting - Job listings
* Service - Service offerings
* MedicalCondition - Health content
* RealEstateListing - Property listings
* CollectionPage - Category/collection pages
* ItemList - Carousel-eligible lists
* SpeakableSpecification - Voice search optimization
* Custom JSON-LD - Manual JSON-LD for any schema type

### Pro Features

* **Deep WooCommerce Integration** - All Google-required Product fields with custom field mapping
* **Built-In Validation** - Per-page and bulk validation with Google Rich Results Test API
* **Custom JSON-LD Editor** - Add manual schema to any page/post
* **Advanced Rules** - Conditional rules based on category, tag, post type, custom fields
* **Schema Analytics** - Track validation status across all pages
* **Priority Support** - Direct support from the development team

### Why Giga Schema Pro?

* **WooCommerce Expert** - Unlike general SEO plugins, we specialize in WooCommerce Product schema with all Google-required fields
* **No Conflicts** - Designed to complement (not replace) Yoast, Rank Math, AIOSEO. Detects existing schema and only adds what's missing
* **Performance First** - Zero frontend assets. Zero CLS. Zero render-blocking resources
* **Affordable** - $49/year for a dedicated schema plugin. Schema Pro charges $79/year
* **Free Version** - Available on WordPress.org with 10 schema types at no cost

== Installation ==

1. Upload `giga-schema-pro` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Visit Giga Schema → Settings to configure your organization info
4. Visit Giga Schema → Rules to review auto-generation rules
5. Done! Schema is automatically generated for your content

== Frequently Asked Questions ==

= Does this work with other SEO plugins? =

Yes! Giga Schema Pro is designed to "work alongside" Yoast SEO, Rank Math, AIOSEO, SEOPress, and The SEO Framework. It detects which schema types your SEO plugin already generates and only adds what's missing. No duplicates. No conflicts.

= Will this slow down my site? =

No. Giga Schema Pro adds ZERO CSS and ZERO JavaScript to the frontend. The only output is JSON-LD in the `<head>` section. We also cache detection results to minimize performance impact.

= Do I need to know JSON-LD or Schema.org? =

No. The plugin auto-generates all schema based on your WordPress content and WooCommerce product data. Advanced users can add custom JSON-LD if needed (Pro feature).

= What's the difference between Free and Pro? =

Free includes 10 basic schema types. Pro includes 25+ types with deep WooCommerce integration, built-in validation, custom JSON-LD editor, advanced rules, and schema analytics.

= Can I use this without WooCommerce? =

Yes! The plugin works with any WordPress site. WooCommerce integration is optional but provides enhanced Product schema when WooCommerce is active.

= How do I validate my schema? =

Pro users can click "Validate Schema" on any page/post edit screen or run bulk validation from the Validation dashboard. Free users can use Google's Rich Results Test tool manually.

== Screenshots ==

1. Dashboard showing detected SEO plugin and schema health
2. Auto-generation rules management
3. WooCommerce settings for deep Product schema
4. Validation results showing pass/fail status
5. Per-page schema settings with custom JSON-LD editor

== Changelog ==

= 1.0.0 =
* Initial release
* 10 free schema types
* 25+ Pro schema types
* WooCommerce deep integration
* "Works alongside" architecture
* Built-in validation
* Auto-generation rules
* Zero frontend assets

== Upgrade Notice ==

Upgrade to Giga Schema Pro to unlock:
* 25+ schema types
* Deep WooCommerce Product schema with all Google fields
* Built-in validation against Google Rich Results Test
* Custom JSON-LD editor
* Advanced rule conditions
* Schema analytics dashboard

[Upgrade to Pro](https://gigaverse.com/products/giga-schema-pro/)

== Support ==

* Documentation: https://gigaverse.com/docs/giga-schema-pro/
* Support: https://gigaverse.com/support/
* GitHub: https://github.com/giga-schema-pro/giga-schema-pro

== Credits ==

Developed by the Gigaverse team.
Built following WordPress Plugin Best Practices.

== License ==

Giga Schema Pro is licensed under the GPL v2 or later.
