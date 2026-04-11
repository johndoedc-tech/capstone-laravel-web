## Plan: Interactive Map Weather Integration

Integrate Google Weather data into the municipality details panel on the interactive map using a backend proxy with caching, while preserving existing crop analytics flow. The preferred UI is stacked weather cards (Current, Hourly 24h, Daily 7d), and this plan uses the 13 municipality coordinates already documented in benguet-weather-currentconditions.md.

**Steps**
1. Phase 0 - Planning artifact update (documentation-only, no app logic changes): update benguet-weather-currentconditions.md structure to include municipality coordinate registry, three Weather API endpoint templates (currentConditions, forecast/hours, forecast/days), integration notes for map panel behavior, and request parameter defaults (hours=24, days=7). This creates a single source reference for the 13 municipalities. *depends on none*
2. Phase 1 - Backend contract design: define weather response contract to serve map UI in one call (recommended endpoint: /api/map/weather/{municipality}) containing current, hourly, and daily sections; include normalized fields needed by UI (condition text/icon, temperature, precipitation chance, wind, humidity, timestamps). *depends on 1*
3. Phase 1 - Coordinate source centralization: move municipality lat/lng usage from markdown-only reference into runtime code source (prefer app config file or controller-level constant) keyed by uppercase municipality names used by existing map filters and preferred_municipality. *depends on 2*
4. Phase 1 - Weather service implementation plan: add service class in app/Services mirroring external API call patterns used by CropPredictionService (timeouts, retries, defensive parsing), with methods for current, hourly(24), and daily(7). *depends on 3*
5. Phase 1 - API route/controller plan: add weather API route(s) in routes/api.php and controller under app/Http/Controllers/Api that validates municipality input, maps to coordinates, fetches/caches data, and returns structured JSON for map panel rendering. *depends on 4*
6. Phase 1 - Caching and resilience plan: use backend cache/proxy strategy with separate cache keys per municipality and data type; TTL targets current=15-30m, hourly=30m, daily=6h; include stale-while-error fallback and explicit error payload shape for UI. *parallel with 5 after 4*
7. Phase 2 - Farmer map UI integration: in resources/views/farmers/map/index.blade.php, extend municipality details panel with stacked weather cards and loading/error skeletons; keep existing charts intact. Trigger weather fetch whenever loadMunicipalityDetails runs. *depends on 5 and 6*
8. Phase 2 - Admin map UI parity: apply same weather card pattern in resources/views/admin/map/index.blade.php so both role-based map views behave consistently. *parallel with 7 once contract is final*
9. Phase 2 - Frontend data flow update: keep crop endpoint and weather endpoint independent; load in parallel on municipality click/filter change; render whichever data succeeds first; avoid blocking crop charts when weather API is slow. *depends on 7 and 8*
10. Phase 3 - Optional warm-cache scheduling (if needed after quota/perf checks): add scheduled command/job to prefetch weather for 13 municipalities and smooth first-request latency. *depends on 6 and 9*
11. Phase 3 - Documentation finalization: update benguet-weather-currentconditions.md with implementation status section, endpoint ownership (Google direct vs backend proxy), and maintenance checklist for municipality coordinate edits. *depends on 9*

**Relevant files**
- c:/xampp/htdocs/capstone-laravel-web/benguet-weather-currentconditions.md - planning artifact and municipality/weather endpoint source data
- c:/xampp/htdocs/capstone-laravel-web/resources/views/farmers/map/index.blade.php - current municipality details panel and map JS flow (loadMunicipalityDetails)
- c:/xampp/htdocs/capstone-laravel-web/resources/views/admin/map/index.blade.php - admin map panel parity for weather sections
- c:/xampp/htdocs/capstone-laravel-web/routes/api.php - add weather endpoint route definitions
- c:/xampp/htdocs/capstone-laravel-web/app/Http/Controllers/Api/MapDataController.php - reference existing API style and municipality filtering patterns
- c:/xampp/htdocs/capstone-laravel-web/app/Services/CropPredictionService.php - reference outbound API integration style for WeatherService
- c:/xampp/htdocs/capstone-laravel-web/config/services.php - optional provider config location for Weather API settings
- c:/xampp/htdocs/capstone-laravel-web/app/Http/Controllers/FarmerDashboardController.php - reference canonical 13 municipality naming
- c:/xampp/htdocs/capstone-laravel-web/public/data/benguet.geojson - municipality geometry source used by map layer

**Verification**
1. Contract verification: call planned backend weather endpoint for each of 13 municipalities and confirm presence of current/hourly/daily sections with expected default ranges (24h/7d).
2. UI behavior verification: on both map views, clicking any municipality shows weather cards without breaking existing crop charts or panel interactions.
3. Filter-refresh verification: changing crop/year/farm type while a municipality panel is open refreshes crop data and weather in the same interaction cycle.
4. Failure-mode verification: simulate weather provider timeout/invalid municipality and confirm UI shows non-blocking fallback state while crop details still load.
5. Performance verification: confirm weather is served from cache on repeat municipality clicks and median panel load remains acceptable.

**Decisions**
- Weather UI layout: stacked vertical cards (Current, Hourly, Daily).
- Default forecast range: hourly 24h and daily 7d.
- Data strategy: backend cache/proxy approach.
- API key handling for now: keep key in markdown as requested for planning phase; security hardening (move to env/config) is deferred and should be revisited before production.
- Included scope: interactive map municipality details integration for both farmer/admin map pages.
- Excluded scope (this plan): dashboard widgets, calendar auto-population, and historical weather analytics persistence tables unless later requested.

**Further Considerations**
1. Endpoint shape choice recommendation: one aggregated endpoint for panel simplicity; keep provider-specific calls internal to service layer.
2. Coordinate authority recommendation: maintain a single runtime mapping source and treat markdown as documentation mirror to prevent drift.
3. Security recommendation: before production rollout, migrate API key out of markdown and apply key restrictions in Google Cloud.

**Refinement (Current Request)**
- Target artifact: restructure benguet-weather-currentconditions.md itself into a Gemini-ready UI-planning document while preserving all 13 municipality URLs.
- Keep original current-conditions links intact (reorganized only), then add sections for hourly and daily endpoint templates, UI requirements, component hierarchy, interaction states, and acceptance checklist.
- This refinement remains planning/documentation-first; no main project logic edits in this phase.
