---
name: Lengkapi Flow Clipper System - Comprehensive
overview: Plan comprehensive untuk melengkapi semua flow clipper yang kurang, termasuk navigation menu, missing frontend pages, backend methods, auto transfer, dan audit profesional untuk memastikan sistem clipper berjalan sempurna.
todos:
  - id: todo-1767284902747-cvi209czm
    content: Semua task di atas udah selesai
    status: completed
---

# Pl

an Lengkapi Flow Clipper System - Comprehensive

## Overview

Analisis menyeluruh menunjukkan beberapa gap penting dalam flow clipper yang perlu dilengkapi untuk memastikan sistem profesional dan berfungsi end-to-end. Plan ini mencakup semua missing pieces, navigation improvements, dan comprehensive audit.

## Current State Analysis

### Yang Sudah Ada:

- ✅ Backend routes dan controllers untuk major features
- ✅ Brand registration flow
- ✅ Campaign creation dan management
- ✅ Clip submission
- ✅ View tracking infrastructure (jobs, services)
- ✅ Auto transfer service
- ✅ Wallet system (Creator & Clipper)

### Critical Missing Pieces:

1. **Top Up Payment Page** - File tidak ada padahal controller sudah render
2. **Clip Edit Methods** - Route ada tapi controller methods tidak ada
3. **Navigation Menu** - Hanya 1 link "Clipper" tanpa submenu/navigasi internal
4. **Auto Transfer Trigger** - Tidak langsung setelah approval
5. **Campaign Resume** - Pause ada, resume tidak ada

## Implementation Plan

### Phase 1: Critical Missing Pages & Methods (PRIORITY: CRITICAL)

#### 1.1 Top Up Payment Page

**Problem:** Controller `TopUpController@store` render `Clipper/TopUps/Payment.vue` tapi file tidak ada**Solution:**

- **File:** `resources/js/Pages/Clipper/TopUps/Payment.vue` (NEW)
- **Features:**
- Render Midtrans Snap popup dengan `snapToken` dari props
- Handle payment success callback
- Handle payment failure/error
- Loading state saat initialize Midtrans
- Redirect ke `clipper.top-ups.index` setelah success dengan flash message
- Error display untuk failed payment

**Backend Check:**

- Verify `TopUpController@store` return correct data
- Verify `TopUpController@webhook` handle success correctly
- Ensure `TopUpService@processTopUpSuccess` update wallet balance

#### 1.2 Clip Edit Methods & Frontend

**Problem:** Routes ada (`clipper.clips.edit`, `clipper.clips.update`) tapi controller methods tidak ada**Backend:**

- **File:** `app/Http/Controllers/Clipper/ClipController.php` (UPDATE)
- Add `edit($id)` method:
  ```php
                    public function edit($id)
                    {
                        $clip = auth()->user()->clips()
                            ->where('status', 'pending')
                            ->findOrFail($id);
                        
                        return Inertia::render('Clipper/Clips/Edit', [
                            'clip' => $clip->load('campaign'),
                        ]);
                    }
  ```




- Add `update(Request $request, $id)` method:
  ```php
                    public function update(Request $request, $id)
                    {
                        $clip = auth()->user()->clips()
                            ->where('status', 'pending')
                            ->findOrFail($id);
                        
                        $validated = $request->validate([
                            'content_url' => 'required|url',
                            'platform' => 'required|in:tiktok,instagram,youtube,other',
                            'platform_content_id' => 'nullable|string|max:255',
                        ]);
                        
                        $clip->update($validated);
                        
                        return redirect()->route('clipper.clips.show', $clip)
                            ->with('success', 'Clip updated successfully.');
                    }
  ```


**Frontend:**

- **File:** `resources/js/Pages/Clipper/Clips/Edit.vue` (NEW)
- Form fields: content_url, platform, platform_content_id
- Validation
- Disable edit jika status bukan 'pending'
- Cancel dan Submit buttons

### Phase 2: Navigation & UX Improvements (PRIORITY: HIGH)

#### 2.1 Clipper Navigation Menu Enhancement

**Problem:** Menu "Clipper" hanya 1 link, tidak ada submenu untuk navigate ke features berbeda**Solution:** Add submenu atau internal navigation dalam halaman clipper**Option A: Expandable Submenu in Sidebar**

- **File:** `resources/js/Components/SidebarNav.vue` (UPDATE)
- Convert "Clipper" menu item menjadi expandable dengan submenu:
- My Campaigns (for brands)
- Available Campaigns (for clippers)
- My Clips (for clippers)
- Top Up Wallet (for brands)
- My Wallet - Creator (for brands)
- My Wallet - Clipper (for clippers)
- Withdrawals (for both)

**Option B: Internal Navigation Tabs/Submenu** (Recommended)

- Create `ClipperLayout.vue` component dengan sub-navigation
- Include submenu di semua clipper pages
- Submenu items based on user role (brand/clipper)

**Implementation:**

- **File:** `resources/js/Layouts/ClipperLayout.vue` (NEW)
- Include submenu navigation tabs
- Wrap all clipper pages dengan layout ini
- Update existing clipper pages untuk use layout

#### 2.2 Clipper Dashboard/Overview Page

**Problem:** Tidak ada landing page untuk clipper, langsung ke campaigns.index**Solution:**

- **Route:** `GET /clipper` → redirect ke dashboard atau show overview
- **File:** `app/Http/Controllers/Clipper/ClipperDashboardController.php` (NEW)
- **File:** `resources/js/Pages/Clipper/Dashboard.vue` (NEW)
- Show:
- Quick stats (for brands: campaigns, spent, active campaigns)
- Quick stats (for clippers: clips submitted, earnings, pending rewards)
- Recent activity
- Quick actions (Create Campaign, Submit Clip, Top Up, etc.)

### Phase 3: Auto Transfer & Reward Flow (PRIORITY: HIGH)

#### 3.1 Immediate Auto Transfer setelah Approval

**Problem:** Auto transfer harus tunggu scheduled job (15 menit), tidak langsung**Solution:**

- **File:** `app/Services/ClipService.php` (UPDATE)
- Update `approveClip` method:
  ```php
                    // Setelah clip approve berhasil
                    if ($result) {
                        // Immediately trigger transfer
                        $autoTransferService = app(\App\Services\AutoTransferService::class);
                        try {
                            $autoTransferService->transferRewardToClipper($clip);
                        } catch (\Exception $e) {
                            // Log error, but don't fail approval
                            Log::error('Auto transfer failed after approval', [
                                'clip_id' => $clip->id,
                                'error' => $e->getMessage(),
                            ]);
                            // Clip tetap approved, transfer akan retry via scheduled job
                        }
                    }
  ```




#### 3.2 Transfer Status Feedback

**Frontend:**

- **File:** `resources/js/Pages/Clipper/Clips/Show.vue` (UPDATE)
- Show transfer status (pending, processing, completed, failed)
- Show timestamp untuk paid_at
- Link ke wallet untuk melihat transaction

### Phase 4: Campaign Management Enhancements (PRIORITY: MEDIUM)

#### 4.1 Campaign Resume Feature

**Backend:**

- **File:** `app/Models/Campaign.php` (UPDATE)
- Add `resume()` method:
  ```php
                    public function resume(): bool
                    {
                        if ($this->status !== 'paused') {
                            return false;
                        }
                        
                        // Check if campaign masih dalam duration
                        if ($this->ended_at && $this->ended_at < now()) {
                            return false; // Already expired
                        }
                        
                        $this->status = 'active';
                        return $this->save();
                    }
  ```




- **File:** `app/Services/CampaignService.php` (UPDATE)
- Add `resumeCampaign(Campaign $campaign): bool` method
- **File:** `app/Http/Controllers/Clipper/CampaignController.php` (UPDATE)
- Add `resume($id)` method
- **Route:** Add `POST /clipper/campaigns/{campaign}/resume`

**Frontend:**

- **File:** `resources/js/Pages/Clipper/Campaigns/Show.vue` (UPDATE)
- Add "Resume" button untuk paused campaigns

#### 4.2 Campaign Budget Validation

**Frontend:**

- **File:** `resources/js/Pages/Clipper/Campaigns/Create.vue` (UPDATE)
- Show available balance sebelum submit
- Warning jika max_budget > available balance
- Disable submit jika balance tidak cukup
- **File:** `resources/js/Pages/Clipper/Campaigns/Show.vue` (UPDATE)
- Show available balance sebelum activate
- Warning jika balance tidak cukup untuk activate

**Backend:**

- **File:** `app/Http/Controllers/Clipper/CampaignController.php` (UPDATE)
- Validate balance di `create` dan `activate` methods

### Phase 5: View Tracking & Real-time Updates (PRIORITY: MEDIUM)

#### 5.1 View Tracking Status Endpoint

**Backend:**

- **File:** `app/Http/Controllers/Clipper/ClipController.php` (UPDATE)
- Improve `trackViews` method atau create new endpoint:
  ```php
                    public function status($id)
                    {
                        $clip = auth()->user()->clips()
                            ->with(['campaign', 'viewTrackings' => function($q) {
                                $q->latest('tracked_at')->limit(10);
                            }])
                            ->findOrFail($id);
                        
                        // Calculate current reward estimate
                        $rewardService = app(\App\Services\RewardCalculationService::class);
                        $estimatedReward = $rewardService->estimateReward(
                            $clip, 
                            $clip->valid_views ?? 0
                        );
                        
                        return response()->json([
                            'clip' => $clip,
                            'valid_views' => $clip->valid_views,
                            'pending_reward' => $clip->pending_reward,
                            'approved_reward' => $clip->approved_reward,
                            'estimated_reward' => $estimatedReward,
                            'tracking' => $clip->viewTrackings,
                            'status' => $clip->status,
                        ]);
                    }
  ```




- **Route:** Add `GET /clipper/clips/{clip}/status`

#### 5.2 Real-time View Updates

**Frontend:**

- **File:** `resources/js/Pages/Clipper/Clips/Show.vue` (UPDATE)
- Add polling mechanism untuk pending/approved clips:
  ```javascript
                    const pollInterval = ref(null);
                    
                    onMounted(() => {
                        if (['pending', 'approved'].includes(clip.value.status)) {
                            pollInterval.value = setInterval(() => {
                                router.reload({ only: ['clip'] });
                            }, 30000); // Every 30 seconds
                        }
                    });
                    
                    onUnmounted(() => {
                        if (pollInterval.value) {
                            clearInterval(pollInterval.value);
                        }
                    });
  ```




- Update reward calculation display secara real-time
- Show "Last updated" timestamp

### Phase 6: Platform API Integration Placeholder (PRIORITY: LOW)

#### 6.1 Platform API Service

**File:** `app/Services/PlatformApiService.php` (NEW)

- Methods: `fetchTikTokViews()`, `fetchInstagramViews()`, `fetchYouTubeViews()`
- Untuk sekarang return placeholder/dummy data dengan clear TODO comments
- Structure untuk future implementation

**File:** `app/Jobs/TrackClipViews.php` (UPDATE)

- Use `PlatformApiService` instead of placeholder method
- Better error handling dan logging

**Config:** `config/clipper.php` (UPDATE)

- Add placeholder config untuk API keys (commented out dengan instructions)

### Phase 7: Notification & User Feedback (PRIORITY: MEDIUM)

#### 7.1 Top Up Success Feedback

**Frontend:**

- **File:** `resources/js/Pages/Clipper/TopUps/Index.vue` (UPDATE)
- Handle success flash message
- Auto-refresh setelah success redirect
- Success toast notification

**Backend:**

- Ensure `TopUpService@processTopUpSuccess` trigger notification
- Send email notification (optional)

#### 7.2 Clip Status Notifications

**Backend Audit:**

- Verify notifications sent untuk:
- Clip approved
- Clip rejected  
- Reward transferred
- View validated

**Frontend:**

- Ensure notification bell shows clipper notifications
- Click notification → redirect ke relevant page (clip detail, campaign detail, etc.)

### Phase 8: Professional Polish & Error Handling (PRIORITY: MEDIUM)

#### 8.1 Error Handling Improvements

**Backend:**

- Add try-catch di semua critical operations
- Better error messages untuk user
- Logging untuk debugging

**Frontend:**

- Error boundaries untuk clipper pages
- Better error messages
- Retry mechanisms untuk failed operations

#### 8.2 Loading States & UX

**Frontend:**

- Loading states untuk semua async operations
- Skeleton loaders untuk data fetching
- Optimistic UI updates dimana memungkinkan

#### 8.3 Data Validation & Edge Cases

**Backend:**

- Validate duplicate clip submissions (same clipper, same campaign)
- Validate campaign budget sebelum operations
- Handle edge cases (expired campaigns, deleted campaigns, etc.)

## Comprehensive Audit Checklist

### Navigation & Access

- [ ] Clipper menu visible untuk brand/clipper users
- [ ] Submenu atau internal navigation accessible
- [ ] All clipper routes accessible dari navigation
- [ ] Role-based menu items (brand vs clipper)

### Brand Flow (End-to-End)

- [ ] Brand registration → approval → access clipper features
- [ ] Top up wallet → payment → balance updated
- [ ] Create campaign → activate → budget locked
- [ ] View submitted clips → approve/reject
- [ ] View campaign analytics
- [ ] Withdraw earnings

### Clipper Flow (End-to-End)

- [ ] Clipper profile setup → verification
- [ ] Browse available campaigns
- [ ] Submit clip → view status
- [ ] Edit pending clip
- [ ] Track views & rewards
- [ ] Receive rewards → wallet updated
- [ ] Withdraw earnings

### Backend Completeness

- [ ] All routes have corresponding controller methods
- [ ] All controller methods have proper validation
- [ ] All services handle errors gracefully
- [ ] All scheduled jobs registered in `routes/console.php`
- [ ] Webhook handlers properly configured
- [ ] Database transactions used untuk critical operations

### Frontend Completeness

- [ ] All routes have corresponding pages
- [ ] All pages handle loading/error states
- [ ] Forms have proper validation
- [ ] Success/error messages displayed
- [ ] Navigation works correctly
- [ ] Responsive design

### Data Integrity

- [ ] Budget calculations correct
- [ ] Reward calculations correct
- [ ] Wallet balances accurate
- [ ] Transaction history complete
- [ ] View tracking data consistent

### Security & Permissions

- [ ] Only authorized users can access clipper features
- [ ] Brands can only see their campaigns
- [ ] Clippers can only edit their own clips
- [ ] Proper authorization checks di semua endpoints
- [ ] CSRF protection enabled
- [ ] Rate limiting applied

## Files to Create

### New Files:

1. `resources/js/Pages/Clipper/TopUps/Payment.vue` - **CRITICAL**
2. `resources/js/Pages/Clipper/Clips/Edit.vue` - **CRITICAL**
3. `resources/js/Layouts/ClipperLayout.vue` - Navigation layout
4. `app/Http/Controllers/Clipper/ClipperDashboardController.php` - Dashboard
5. `resources/js/Pages/Clipper/Dashboard.vue` - Dashboard page
6. `app/Services/PlatformApiService.php` - Platform API service (placeholder)

### Files to Modify:

1. `app/Http/Controllers/Clipper/ClipController.php` - Add edit/update methods
2. `app/Http/Controllers/Clipper/TopUpController.php` - Verify return data
3. `app/Services/ClipService.php` - Immediate auto transfer
4. `app/Models/Campaign.php` - Add resume method
5. `app/Services/CampaignService.php` - Add resumeCampaign
6. `app/Http/Controllers/Clipper/CampaignController.php` - Add resume method, budget validation
7. `resources/js/Pages/Clipper/Campaigns/Show.vue` - Resume button, budget warning
8. `resources/js/Pages/Clipper/Campaigns/Create.vue` - Budget validation
9. `resources/js/Pages/Clipper/Clips/Show.vue` - Real-time updates, transfer status
10. `resources/js/Pages/Clipper/TopUps/Index.vue` - Success feedback
11. `resources/js/Components/SidebarNav.vue` - Submenu atau reference ke clipper layout
12. `routes/web.php` - Add missing routes (resume, clip status, dashboard)
13. `app/Jobs/TrackClipViews.php` - Use PlatformApiService
14. `config/clipper.php` - Add API config placeholders

## Testing Checklist

### Critical Paths:

- [ ] Top up: Create → Payment → Success → Balance updated
- [ ] Clip submission: Submit → Edit (pending) → View status → Approved → Reward received
- [ ] Campaign: Create → Activate → Pause → Resume → Complete
- [ ] View tracking: Submit clip → Views tracked → Rewards calculated → Auto transfer

### Edge Cases:

- [ ] Top up dengan insufficient payment method
- [ ] Edit clip setelah approved (should fail)
- [ ] Activate campaign dengan insufficient balance (should fail)
- [ ] Submit duplicate clip (should validate)
- [ ] Resume expired campaign (should fail)

### Integration:

- [ ] Midtrans webhook → Top up success
- [ ] Scheduled jobs running correctly
- [ ] Auto transfer → Wallet updated
- [ ] Notifications sent untuk semua events

## Implementation Order

**Sprint 1 (Critical - Must Fix):**

1. Top Up Payment Page
2. Clip Edit Methods & Frontend
3. Navigation Menu Enhancement

**Sprint 2 (High Priority):**

4. Immediate Auto Transfer
5. Campaign Resume