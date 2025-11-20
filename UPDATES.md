# System Updates Log

## Update 2025-11-20B - Map Coordinates & Address Autocomplete Fix

### Issues Fixed

1. **Map Coordinates Issue**: Previous coordinates were too broad, allowing pins outside San Miguel
2. **Address Autocomplete Location**: Address autocomplete was in the wrong section (Complainant instead of Incident Location)

### Changes Made

#### 1. Updated Barangay San Miguel Map Boundaries
**Files Modified**: `blotter.php`, `blotteradmin.php`

**Old Coordinates** (Approximate):
- 6-point polygon with broad boundaries
- Center: [14.5690, 121.0850]
- Zoom: 15

**New Coordinates** (Accurate):
- 8-point polygon with precise boundaries
```javascript
[14.575421, 121.083611], // Northwest
[14.573889, 121.089167], // Northeast
[14.569444, 121.090833], // East
[14.566944, 121.088889], // Southeast
[14.564167, 121.084722], // South
[14.565556, 121.081389], // Southwest
[14.569167, 121.080278], // West
[14.572500, 121.081944]  // Northwest connection
```
- Center: [14.5695, 121.0855]
- Zoom: 16
- Enhanced styling (darker blue, thicker borders, better visibility)

#### 2. Simplified Complainant Address Input
**Files Modified**: `blotter.php`, `blotteradmin.php`

**Old Design**:
- 4 separate fields: Region, City/Municipality, Barangay, Street
- 3 datalists with autocomplete (cities, barangays)
- JavaScript concatenation on submit

**New Design**:
- Single text field
- Placeholder: "Halimbawa: 123 Main St, San Miguel, Pasig City"
- Direct input, no concatenation needed

#### 3. Enhanced Incident Location with Autocomplete
**Files Modified**: `blotter.php`, `blotteradmin.php`

**Added**:
- Datalist with 14 common San Miguel locations:
  - San Miguel Elementary School
  - San Miguel Public Market
  - San Miguel Barangay Hall
  - San Miguel Chapel
  - Kanlaon Street
  - Sierra Madre Street
  - Mayon Street
  - Taal Street
  - Pinatubo Street
  - Apo Street
  - Banahaw Street
  - Makiling Street
  - San Miguel Industrial Area
  - San Miguel Riverside

**Benefits**:
- Faster location input for common places
- Consistent location naming
- Better for analytics
- Still allows custom input or map pinning

### Testing Recommendations

1. **Test Map Boundaries**:
   - Try pinning locations inside San Miguel → Should work
   - Try pinning outside San Miguel → Should show alert
   - Drag marker outside → Should reset to center

2. **Test Incident Location Autocomplete**:
   - Type partial location name → Should show suggestions
   - Select from dropdown → Should fill input
   - Map pin still works → Verify

3. **Test Complainant Address**:
   - Enter free-text address → Should save correctly
   - No validation errors → Verify submission works

### Rollback Instructions

If needed, revert to previous version:
```bash
git checkout HEAD~1 blotter.php blotteradmin.php
```

---

## Previous Updates

See [IMPLEMENTATION_SUMMARY.md](IMPLEMENTATION_SUMMARY.md) for full feature list from v2.0 release.

---

**Update Date**: November 20, 2025
**Modified By**: AI Development Assistant
**Files Changed**: 2 (blotter.php, blotteradmin.php)
**Lines Changed**: ~100 per file
**Status**: ✅ Complete - Ready for Testing
