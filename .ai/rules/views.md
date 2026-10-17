---
paths:
  - 'resources/views/**'
---

# Views

## Design system lives in resources/css/app.css (Vite), vendor libs stay on CDN
All three layouts load Bootstrap 5, icons, Leaflet, SweetAlert2, jQuery etc. as classic CDN tags, then `{{ Vite::fonts() }}` + `@vite(['resources/css/app.css','resources/js/app.js'])`. Tenant brand colours are injected inline as --rrep-primary/-rgb/-hover, --rrep-secondary, --rrep-accent (via brand_rgb()/brand_shade() helpers) and override the token fallbacks in app.css. Do not add new `<style>` blocks to layouts or Tailwind classes: put shared styles in app.css and use its utilities (line-clamp-*, max-w-*, half-step spacing, kpi-card, page-header, filter-bar, bulk-bar, empty-state, skeleton, table-stack with stack-primary/stack-actions cells, mobile-action-bar, btn-whatsapp, btn-icon, scroll-x). Keep jQuery/Bootstrap usage in inline view scripts inside DOMContentLoaded/ready handlers; app.js is a deferred module (window.RREP.toast, stackTables, tanzanianPhoneInputs for input[data-tz-phone]). Run `npm run build` after CSS/JS edits; public/build is gitignored.

## Tanzania-market conventions and bilingual copy in views
Money: format_currency() for exact TZS, format_money_short() for compact badges (TSh 450M). Phones: format_phone_tz() for display, inputs type="tel" data-tz-phone (app.js normalises to +255). Regions: tz_regions(); payment channels: tz_payment_methods() (M-Pesa, Mixx by Yas/Tigo Pesa, Airtel Money, HaloPesa, bank, cash, cheque, card). Land vocabulary: Title Deed/Hati Miliki, Right of Occupancy, Plot/Kiwanja, Block/Kitalu, Ward/District/Region; identity: NIDA number, TIN. All user-facing copy goes through __() with both lang/en and lang/sw entries; never inline `app()->getLocale() === 'sw' ? ... : ...` ternaries. Lang namespaces by area: app (shared/nav), marketplace (public listings/home), pages (public detail/content pages), admin (dashboard/properties/projects/services/documents), ops (CRM/finance/survey/reports/users…), settings, portal (client/owner portals + login).
