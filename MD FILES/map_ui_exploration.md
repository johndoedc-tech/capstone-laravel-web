# Interactive Map UI Exploration - Summary

## Key Files Identified
1. **Routes**: 
   - `routes/web.php` - lines 160, 215
   - `routes/api.php` - API endpoints

2. **Controllers**:
   - `app/Http/Controllers/MapController.php` - view resolution
   - `app/Http/Controllers/Api/MapDataController.php` - 6 main endpoints

3. **Blade Templates**:
   - `resources/views/admin/map/index.blade.php` - admin version with contribution chart
   - `resources/views/farmers/map/index.blade.php` - farmer version with user preferences

4. **Data Files**:
   - `public/data/benguet.geojson` - municipality boundaries

5. **Weather Resources**:
   - `benguet-weather-currentconditions.md` - API endpoints document (not integrated)

## Data Flow Summary
1. User clicks map → municipality selected → `loadMunicipalityDetails()` → API call
2. API responds with 6 data types (monthly, crops, farm types, summary, etc.)
3. Charts updated (Chart.js), panel slides in from right

## Current Panel Sections (municipality details)
- Overview stats (production, area, productivity, records)
- Farm type breakdown (progress bars)
- Monthly production chart
- Contribution chart (municipality vs others)
- Crop distribution chart

## Challenge for Weather Integration
- No existing weather widget/service
- Need to decide insertion point (as separate section? merge into overview?)
- API key exposed in markdown (benguet-weather-currentconditions.md)
- Would need backend proxy or frontend call modification
