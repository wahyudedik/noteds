---
name: Real-Time Tracking & Transparency Implementation
overview: Implementasi fitur Real-Time View Tracking dan View Validation Transparency untuk Campaign dan Clip, memungkinkan brand dan clipper melihat views secara real-time dengan transparansi penuh tentang validasi views.
todos:
  - id: backend-live-endpoints
    content: Create/enhance backend endpoints untuk real-time views (campaign live, clip live, validation details)
    status: completed
  - id: realtime-counter-component
    content: Create RealTimeViewCounter component dengan polling mechanism
    status: completed
  - id: view-history-chart
    content: Create/enhance ViewHistoryChart component dengan interactive features
    status: completed
  - id: polling-composable
    content: Create useRealTimePolling composable untuk reusable polling logic
    status: completed
  - id: enhance-validation-status
    content: Enhance ViewValidationStatus component dengan real-time updates dan more details
    status: completed
  - id: validation-timeline
    content: Create ValidationTimeline component untuk display validation history
    status: completed
  - id: fraud-detection-details
    content: Create FraudDetectionDetails component untuk display fraud detection info
    status: completed
  - id: update-campaign-pages
    content: Update Campaign Show dan Analytics pages dengan real-time components
    status: completed
    dependencies:
      - realtime-counter-component
      - view-history-chart
      - enhance-validation-status
  - id: update-clip-page
    content: Update Clip Show page dengan real-time components dan enhanced validation
    status: completed
    dependencies:
      - realtime-counter-component
      - view-history-chart
      - enhance-validation-status
  - id: performance-optimization
    content: Optimize queries, add caching, dan implement performance best practices
    status: completed
    dependencies:
      - backend-live-endpoints
  - id: testing
    content: Test real-time updates, polling, error handling, dan performance
    status: completed
    dependencies:
      - update-campaign-pages
      - update-clip-page
---

# Real-Time Tracking & Transparency Implementation

Plan untuk implementasi fitur Real-Time View Tracking dan View Validation Transparency untuk Campaign dan Clip.

## Overview

Sistem saat ini sudah memiliki:

- ✅ ViewValidationService dengan fraud detection dan stability checking
- ✅ ClipViewTracking model untuk menyimpan tracking history
- ✅ ViewValidationStatus component (basic)
- ✅ Analytics endpoints (non-real-time)

Yang perlu ditambahkan:

- ⚠️ Real-time view counter dengan auto-update
- ⚠️ Enhanced view validation transparency display
- ⚠️ Interactive view history charts
- ⚠️ Real-time polling endpoints

## 1. Backend - Real-Time Endpoints

### 1.1 Campaign Live Views Endpoint

**File**: `app/Http/Controllers/Clipper/CampaignAnalyticsController.php`

- [ ] Tambahkan method `getLiveViews()` (sudah ada, perlu enhancement)
- [ ] Return data:
- Total views (real-time)
- Valid views count
- Invalid views count
- Views per clip breakdown
- Last updated timestamp
- Growth rate (vs previous check)
- Fraud detection status per clip
- [ ] Optimize query untuk performance (gunakan eager loading)
- [ ] Add caching untuk mengurangi load (optional, 5-10 detik cache)

**Route**: `GET /clipper/campaigns/{campaign}/analytics/live`

### 1.2 Clip Live Views Endpoint

**File**: `app/Http/Controllers/Clipper/ClipController.php`

- [ ] Tambahkan method `getLiveViews($id)`
- [ ] Return data:
- Current views count
- Valid views count
- View growth rate
- Last tracking timestamp
- Stability score
- Fraud detection status
- Recent tracking history (last 10 records)
- [ ] Include validation status details

**Route**: `GET /clipper/clips/{clip}/views/live`

### 1.3 Campaign Validation Details Endpoint

**File**: `app/Http/Controllers/Clipper/CampaignAnalyticsController.php`

- [ ] Tambahkan method `getValidationDetails($campaignId)`
- [ ] Return detailed validation data:
- Per-clip validation breakdown
- Validation timeline/history
- Fraud detection details
- Stability scores per clip
- Validation rate trends

**Route**: `GET /clipper/campaigns/{campaign}/analytics/validation`

### 1.4 Clip Validation History Endpoint

**File**: `app/Http/Controllers/Clipper/ClipController.php`

- [ ] Enhance method `getValidationStatus()` (sudah ada)
- [ ] Return validation history dengan timeline
- [ ] Include fraud detection reasons
- [ ] Include stability score history

**Route**: `GET /clipper/clips/{clip}/validation/history`

## 2. Frontend - Real-Time Components

### 2.1 Real-Time View Counter Component

**File**: `resources/js/Components/Clipper/RealTimeViewCounter.vue` (NEW)

- [ ] Create component dengan props:
- `initialViews`: number
- `endpoint`: string (URL untuk polling)
- `pollInterval`: number (default: 30000ms = 30 detik)
- `showGrowthRate`: boolean
- [ ] Implement polling mechanism:
- Auto-fetch setiap X detik
- Show loading state saat fetching
- Animate number changes
- Display "Last updated" timestamp
- [ ] Display growth rate indicator (up/down arrow dengan percentage)
- [ ] Handle errors gracefully (retry dengan exponential backoff)
- [ ] Cleanup polling on component unmount

### 2.2 Enhanced View History Chart Component

**File**: `resources/js/Components/Clipper/ViewHistoryChart.vue` (NEW atau enhance existing)

- [ ] Create interactive chart component:
- Use Chart.js atau similar library
- Display views over time (line chart)
- Show valid vs invalid views (stacked area atau dual axis)
- Interactive tooltips dengan detail per point
- Time range selector (last 24h, 7d, 30d, all)
- Zoom functionality
- [ ] Real-time updates (append new data points)
- [ ] Color coding:
- Valid views: green
- Invalid views: red
- Fraud detected periods: highlighted in red

### 2.3 Enhanced View Validation Status Component

**File**: `resources/js/Components/Clipper/ViewValidationStatus.vue` (ENHANCE)

- [ ] Enhance existing component dengan:
- Real-time updates (polling)
- More detailed fraud detection info
- Validation timeline visualization
- Per-clip breakdown (untuk campaign)
- Export validation report (optional)
- [ ] Add sections:
- Fraud Detection Details (jika terdeteksi)
- Validation Timeline
- Stability Score History
- View Source Breakdown (jika data tersedia)

### 2.4 View Growth Indicator Component

**File**: `resources/js/Components/Clipper/ViewGrowthIndicator.vue` (NEW)

- [ ] Create component untuk menampilkan:
- Growth rate (percentage)
- Growth trend (up/down/stable)
- Comparison dengan previous period
- Visual indicator (arrow, color coding)

## 3. Frontend - Page Updates

### 3.1 Campaign Detail Page

**File**: `resources/js/Pages/Clipper/Campaigns/Show.vue`

- [ ] Add Real-Time View Counter di header section
- [ ] Replace static view display dengan RealTimeViewCounter component
- [ ] Add View History Chart section
- [ ] Enhance ViewValidationStatus dengan real-time updates
- [ ] Add "Last Updated" indicator
- [ ] Add refresh button (manual refresh)

### 3.2 Campaign Analytics Page

**File**: `resources/js/Pages/Clipper/Campaigns/Analytics.vue`

- [ ] Add real-time view counter di top
- [ ] Enhance analytics charts dengan real-time data
- [ ] Add view history chart dengan time range selector
- [ ] Add validation details section dengan per-clip breakdown
- [ ] Add fraud detection alerts section

### 3.3 Clip Detail Page

**File**: `resources/js/Pages/Clipper/Clips/Show.vue`

- [ ] Enhance existing polling mechanism (sudah ada di line 41-50)
- [ ] Add Real-Time View Counter component
- [ ] Enhance View History Chart dengan real-time updates
- [ ] Add View Validation Timeline section
- [ ] Add Stability Score visualization
- [ ] Add Fraud Detection Alert (jika terdeteksi)

## 4. Real-Time Polling Implementation

### 4.1 Polling Strategy

- [ ] Use polling (setInterval) untuk real-time updates
- [ ] Default interval: 30 detik
- [ ] Configurable interval (user bisa set: 15s, 30s, 60s)
- [ ] Pause polling saat tab tidak aktif (Page Visibility API)
- [ ] Resume polling saat tab aktif kembali
- [ ] Exponential backoff pada error

### 4.2 Polling Service/Composable

**File**: `resources/js/Composables/useRealTimePolling.js` (NEW)

- [ ] Create composable untuk reusable polling logic:
  ```javascript
      useRealTimePolling(endpoint, options)
  ```




- [ ] Features:
- Auto-start/stop polling
- Error handling
- Loading states
- Data transformation
- Cleanup on unmount

## 5. View Validation Transparency Enhancements

### 5.1 Validation Timeline Component

**File**: `resources/js/Components/Clipper/ValidationTimeline.vue` (NEW)

- [ ] Display validation history sebagai timeline
- [ ] Show validation events:
- View tracking events
- Validation runs
- Fraud detection events
- Manual overrides (jika ada)
- [ ] Color coding untuk event types
- [ ] Tooltips dengan detail per event

### 5.2 Fraud Detection Details Component

**File**: `resources/js/Components/Clipper/FraudDetectionDetails.vue` (NEW)

- [ ] Display fraud detection results:
- Fraud status (detected/not detected)
- Detection reasons (sudden spike, instability, etc.)
- Affected periods
- Recommended actions
- [ ] Visual indicators untuk fraud patterns
- [ ] Link ke admin untuk manual review (jika clipper)

### 5.3 Stability Score Visualization

**File**: `resources/js/Components/Clipper/StabilityScoreChart.vue` (NEW)

- [ ] Display stability score over time
- [ ] Color coding (green/yellow/red)
- [ ] Threshold indicators
- [ ] Trend analysis

## 6. Performance Optimizations

### 6.1 Backend Optimizations

- [ ] Add database indexes untuk view tracking queries:
- `clip_view_tracking.clip_id`
- `clip_view_tracking.tracked_at`
- Composite index: `(clip_id, tracked_at)`
- [ ] Implement query caching untuk live views (5-10 detik)
- [ ] Use eager loading untuk relationships
- [ ] Limit data returned (last N records, not all)

### 6.2 Frontend Optimizations

- [ ] Debounce polling requests
- [ ] Only poll saat page visible
- [ ] Cache previous data untuk smooth transitions
- [ ] Lazy load charts (load saat user scroll ke section)

## 7. User Experience Enhancements

### 7.1 Loading States

- [ ] Skeleton loaders untuk initial load
- [ ] Subtle loading indicator untuk polling updates
- [ ] Smooth number animations saat update

### 7.2 Error Handling

- [ ] Display user-friendly error messages
- [ ] Retry mechanism dengan exponential backoff
- [ ] Fallback ke cached data jika polling fails
- [ ] Notification untuk connection issues

### 7.3 Accessibility

- [ ] ARIA labels untuk screen readers
- [ ] Keyboard navigation support
- [ ] High contrast mode support
- [ ] Screen reader announcements untuk updates

## 8. Testing & Validation

### 8.1 Backend Testing

- [ ] Test live views endpoints dengan various scenarios
- [ ] Test dengan large datasets (performance)
- [ ] Test error handling
- [ ] Test caching behavior

### 8.2 Frontend Testing

- [ ] Test polling mechanism
- [ ] Test component updates
- [ ] Test error states
- [ ] Test performance dengan multiple components polling

### 8.3 Integration Testing

- [ ] Test end-to-end real-time updates
- [ ] Test dengan multiple users viewing same campaign/clip
- [ ] Test polling cleanup
- [ ] Test page visibility API behavior

## 9. Documentation

### 9.1 API Documentation

- [ ] Document new endpoints
- [ ] Document polling intervals
- [ ] Document response formats
- [ ] Document error responses

### 9.2 User Documentation

- [ ] Document real-time features untuk users
- [ ] Explain validation transparency
- [ ] Guide untuk interpreting stability scores
- [ ] Guide untuk understanding fraud detection

## Implementation Priority

### Phase 1: Core Real-Time Features (High Priority)

1. Real-Time View Counter Component
2. Live Views Endpoints (enhance existing)
3. Polling mechanism
4. Update Campaign Detail Page
5. Update Clip Detail Page

### Phase 2: Enhanced Transparency (Medium Priority)

6. Enhanced ViewValidationStatus component
7. Validation Timeline Component
8. Fraud Detection Details Component
9. View History Chart enhancements

### Phase 3: Advanced Features (Low Priority)

10. Stability Score Visualization
11. View Source Tracking
12. Export functionality
13. Advanced analytics

## Technical Decisions

### Polling vs WebSocket

- **Decision**: Use polling (simpler, no additional infrastructure)
- **Interval**: 30 seconds (configurable)
- **Rationale**: 
- No need for WebSocket server
- Easier to implement and maintain
- Sufficient for view tracking use case
- Can upgrade to WebSocket later if needed

### Chart Library

- **Decision**: Use Chart.js (already in use or add if needed)
- **Alternative**: ApexCharts, Recharts
- **Rationale**: Popular, well-documented, good performance

### Data Caching

- **Backend**: 5-10 second cache untuk live views
- **Frontend**: Cache previous data untuk smooth transitions
- **Rationale**: Balance between real-time feel and server load

## Files to Create/Modify

### New Files

- `resources/js/Components/Clipper/RealTimeViewCounter.vue`
- `resources/js/Components/Clipper/ViewHistoryChart.vue`
- `resources/js/Components/Clipper/ValidationTimeline.vue`
- `resources/js/Components/Clipper/FraudDetectionDetails.vue`
- `resources/js/Components/Clipper/StabilityScoreChart.vue`
- `resources/js/Components/Clipper/ViewGrowthIndicator.vue`
- `resources/js/Composables/useRealTimePolling.js`

### Files to Modify

- `app/Http/Controllers/Clipper/CampaignAnalyticsController.php`
- `app/Http/Controllers/Clipper/ClipController.php`
- `resources/js/Pages/Clipper/Campaigns/Show.vue`
- `resources/js/Pages/Clipper/Campaigns/Analytics.vue`
- `resources/js/Pages/Clipper/Clips/Show.vue`
- `resources/js/Components/Clipper/ViewValidationStatus.vue`
- `routes/web.php` (add new routes)

## Success Criteria

- [ ] Users can see view counts update in real-time (within 30 seconds)
- [ ] View validation status is transparent and understandable
- [ ] Fraud detection alerts are visible and actionable
- [ ] View history charts are interactive and informative
- [ ] Performance is acceptable (no lag, smooth updates)