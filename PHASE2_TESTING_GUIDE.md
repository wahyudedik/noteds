# Quick Test Guide - Phase 2 UI Features

## Prerequisites
- Laravel application running (`php artisan serve`)
- Database seeded with test data
- User account created and logged in
- At least a few notes in the marketplace

---

## Test 1: Buyer Dashboard Enhancements

### Steps:
1. Log in as a buyer user
2. Navigate to `/dashboard` or click "Dashboard" in navigation
3. **Expected Results**:
   - ✅ See streak widget in top-right with orange-red gradient
   - ✅ Current streak shows (e.g., "3 days")
   - ✅ Level badge displays (Bronze/Silver/Gold/Platinum)
   - ✅ Progress bar shows percentage to next level
   - ✅ "Recommended For You" section appears below referral stats
   - ✅ 8 personalized notes displayed in 2x4 grid
   - ✅ Each note shows thumbnail, title, price
   - ✅ Clicking note navigates to detail page

### Troubleshooting:
- If recommendations don't appear: Ensure user has some activity (purchases, views)
- If streak is 0: Log in on consecutive days to build streak
- If service errors: Check `php artisan route:list` includes recommendations routes

---

## Test 2: Marketplace Recommendations

### Steps:
1. Log in as any user
2. Navigate to `/marketplace`
3. Scroll past the featured notes section
4. **Expected Results**:
   - ✅ "Recommended For You ✨" section appears
   - ✅ Horizontal scrollable carousel with 6 notes
   - ✅ Cards are fixed width (256px)
   - ✅ Rating badges overlay on thumbnails (if notes have ratings)
   - ✅ Placeholder gradient appears for notes without thumbnails
   - ✅ Can scroll horizontally through recommendations

### When Not Logged In:
- ⚠️ Recommendations section should NOT appear
- Only featured notes and main listing shown

### Troubleshooting:
- If not showing: Check `@auth` directive in blade file
- If empty: ContentRecommendationEngine may need more user data
- Check browser console for JavaScript errors

---

## Test 3: Share-to-Unlock Discount

### Steps:
1. Log in as a buyer
2. Navigate to any paid note you **don't own** (e.g., `/marketplace/{note-id}`)
3. Scroll to share-to-unlock widget (after summary, before content)
4. **Expected Results**:
   - ✅ Orange-red gradient widget appears with "Share to Unlock 10% Discount"
   - ✅ Progress shows "0 / 3 shares"
   - ✅ Three buttons: WhatsApp (green), Twitter (blue), Facebook (dark blue)
5. Click WhatsApp button
6. **Expected Results**:
   - ✅ WhatsApp share dialog opens in new window
   - ✅ Progress updates to "1 / 3 shares"
   - ✅ WhatsApp button becomes disabled with checkmark
   - ✅ Progress bar advances to 33%
7. Share on Twitter and Facebook
8. **Expected Results**:
   - ✅ After 3rd share: Progress shows "3 / 3 shares"
   - ✅ Progress bar at 100%
   - ✅ Red "-10%" badge appears
   - ✅ Green "Discount Unlocked!" message appears
   - ✅ All three buttons disabled

### Widget Should NOT Appear When:
- User not logged in
- User already owns the note
- Note is free (price = 0)

### Troubleshooting:
- Check browser console for API errors
- Verify `/api/growth/share-discount/{note}` endpoint works
- Verify `/api/growth/track-share` endpoint works
- Test with `curl -X POST http://localhost:8000/api/growth/track-share -d '{"note_id":1,"platform":"whatsapp"}' -H "Content-Type: application/json"`

---

## Test 4: Referral Dashboard

### Steps:
1. Log in as any user
2. Navigate to `/growth/referrals`
3. **Expected Results**:
   - ✅ Page loads successfully
   - ✅ Green gradient banner shows "Earn with Referrals"
   - ✅ Three stats cards display: Total Referrals, This Month, Conversion Rate
   - ✅ Total Earnings shows "Rp 0" initially
   - ✅ Referral code displays in large monospace font
   - ✅ "Copy Code" button works (copies to clipboard)
   - ✅ Referral link shows full URL with code
   - ✅ Copy link button works
   - ✅ Three social share buttons (WhatsApp, Twitter, Facebook)
4. Click "Copy Code" button
5. **Expected Results**:
   - ✅ Alert shows "Referral code copied to clipboard!"
   - ✅ Code is in clipboard (paste to verify)
6. Click WhatsApp share button
7. **Expected Results**:
   - ✅ WhatsApp web opens in new tab
   - ✅ Message pre-filled with referral code and link

### With Referrals:
If user has made referrals:
- ✅ Recent Referrals table shows entries
- ✅ Each row shows: User name, Date, Status badge, Earnings
- ✅ Status badges colored (green for completed, yellow for pending)

### Without Referrals:
- ✅ Empty state shows with user-friends icon
- ✅ Message: "No referrals yet"

### Troubleshooting:
- If page doesn't load: Check route is registered (`php artisan route:list --name=growth.referrals`)
- If data doesn't load: Check `/api/growth/referrals` endpoint
- Test API: `curl http://localhost:8000/api/growth/referrals -H "Authorization: Bearer {token}"`
- Check browser console for errors

---

## Test 5: Challenges Page

### Steps:
1. Log in as any user
2. Navigate to `/growth/challenges`
3. **Expected Results**:
   - ✅ Page loads successfully
   - ✅ Purple-pink gradient banner shows "Complete Challenges 🏆"
   - ✅ Completed challenges counter shows "0" initially
   - ✅ Active challenges grid displays (2 columns)
   - ✅ Each challenge card shows:
     - Challenge name (e.g., "Upload 10 Notes")
     - Description
     - Challenge emoji icon (📝, 💰, ⭐, etc.)
     - Progress bar (0/10)
     - Reward description
     - "Join Challenge" button (purple)
4. Click "Join Challenge" button
5. **Expected Results**:
   - ✅ API call to `/api/growth/challenges/{id}/join`
   - ✅ Success alert: "Successfully joined challenge!"
   - ✅ Button changes to "Joined" (gray, disabled)
   - ✅ Challenge stays in Active section with updated progress

### With Completed Challenges:
- ✅ Completed challenges appear in History section at bottom
- ✅ Each shows completion date
- ✅ Green "Completed ✓" badge
- ✅ Challenge icon

### Empty States:
- **No Active Challenges**: Trophy icon with "No active challenges"
- **No History**: Medal icon with "No completed challenges yet"

### Challenge Types to Test:
- `upload_notes` → 📝 Upload Notes
- `make_sales` → 💰 Make Sales
- `get_reviews` → ⭐ Get Reviews
- `reach_followers` → 👥 Reach Followers
- `streak_days` → 🔥 Maintain Streak
- `complete_profile` → ✅ Complete Profile
- `share_notes` → 📤 Share Notes

### Troubleshooting:
- If page doesn't load: Check route registered
- If challenges don't load: Check `/api/growth/challenges` endpoint
- If join doesn't work: Verify POST endpoint `/api/growth/challenges/{id}/join`
- Test API: `curl http://localhost:8000/api/growth/challenges`

---

## Test 6: API Endpoints Verification

### Test All Endpoints:

```bash
# 1. Get personalized recommendations
curl http://localhost:8000/api/recommendations?user_id=1

# 2. Get streak info
curl http://localhost:8000/api/growth/streak

# 3. Get referral stats
curl http://localhost:8000/api/growth/referrals

# 4. Get challenges
curl http://localhost:8000/api/growth/challenges

# 5. Get share discount status for note
curl http://localhost:8000/api/growth/share/discount/1

# 6. Track a share (POST)
curl -X POST http://localhost:8000/api/growth/share/track \
  -H "Content-Type: application/json" \
  -d '{"note_id":1,"platform":"whatsapp"}'

# 7. Join a challenge (POST)
curl -X POST http://localhost:8000/api/growth/challenges/1/join \
  -H "Content-Type: application/json"
```

**Expected Responses**:
- All should return 200 status
- JSON responses with appropriate data
- No 500 errors

---

## Test 7: Responsive Design

### Mobile Testing (< 768px):
1. Open Chrome DevTools
2. Toggle device toolbar (Ctrl+Shift+M)
3. Select "iPhone 12 Pro"
4. Navigate through all pages

**Expected Results**:
- ✅ Dashboard grid adjusts to 2 columns
- ✅ Marketplace carousel is swipeable
- ✅ Share buttons stack vertically or wrap
- ✅ Referral dashboard cards stack
- ✅ Challenge cards stack to 1 column
- ✅ All text remains readable
- ✅ Buttons remain tappable (min 44px height)

### Tablet Testing (768px - 1024px):
- ✅ Dashboard shows 3-4 columns
- ✅ Marketplace carousel shows 3-4 cards
- ✅ Challenge grid shows 2 columns

---

## Test 8: Error Scenarios

### Test Network Failures:
1. Open browser DevTools
2. Go to Network tab
3. Set throttling to "Offline"
4. Reload pages

**Expected Results**:
- ✅ Loading spinners show
- ✅ Error messages display (not blank pages)
- ✅ Graceful degradation (page structure intact)

### Test with No Data:
1. Create fresh user account
2. Don't make any purchases or views
3. Visit dashboard

**Expected Results**:
- ✅ Recommendations section shows empty state or doesn't appear
- ✅ No JavaScript errors
- ✅ Other sections still work

---

## Test 9: Cross-Browser Testing

Test in:
- ✅ Chrome (latest)
- ✅ Firefox (latest)
- ✅ Safari (if on Mac)
- ✅ Edge (latest)

**Check**:
- CSS gradients render correctly
- JavaScript functions work
- Social share buttons open
- Clipboard API works (may need HTTPS)

---

## Test 10: Performance

### Check Load Times:
1. Open DevTools → Network tab
2. Hard refresh (Ctrl+Shift+R)
3. Check:
   - ✅ Page load < 2 seconds
   - ✅ API calls < 500ms
   - ✅ No unnecessary duplicate requests
   - ✅ Images optimized

### Check Memory:
1. Open DevTools → Performance tab
2. Record while navigating
3. Check:
   - ✅ No memory leaks
   - ✅ Smooth animations
   - ✅ No layout thrashing

---

## Common Issues & Fixes

### Issue: Recommendations Not Showing
**Fix**: 
```php
php artisan cache:clear
php artisan route:clear
php artisan config:clear
```

### Issue: API Returns 401 Unauthorized
**Fix**: Ensure user is logged in, check auth middleware on routes

### Issue: Share Buttons Not Opening
**Fix**: Check pop-up blocker settings, verify JavaScript console for errors

### Issue: Streak Shows 0
**Fix**: Log in on consecutive days, or manually set in database for testing:
```sql
UPDATE users SET current_streak = 5, last_login_at = NOW() WHERE id = 1;
```

### Issue: Challenges Not Loading
**Fix**: Seed challenge data:
```php
php artisan db:seed --class=EventChallengeSeeder
```

### Issue: CSS Not Applied
**Fix**: 
```bash
npm run build
php artisan view:clear
```

---

## Success Criteria

All tests pass when:
- ✅ 6/6 UI features display correctly
- ✅ All API endpoints return 200 status
- ✅ No JavaScript console errors
- ✅ No PHP errors in logs
- ✅ Responsive design works on mobile/tablet/desktop
- ✅ Social share buttons open correct dialogs
- ✅ Copy to clipboard works
- ✅ Progress tracking updates in real-time
- ✅ Empty states display appropriately
- ✅ Loading states show while fetching data

---

## Test Checklist

Copy and check off as you test:

**Buyer Dashboard**:
- [ ] Streak widget displays
- [ ] Recommendations section displays
- [ ] Both sections have correct data
- [ ] Links work

**Marketplace**:
- [ ] Recommendations carousel displays for auth users
- [ ] Carousel is scrollable
- [ ] Cards have correct layout
- [ ] Links work

**Note Detail**:
- [ ] Share-to-unlock widget displays correctly
- [ ] Share buttons work
- [ ] Progress updates
- [ ] Discount unlocks at 3 shares

**Referral Dashboard**:
- [ ] Page loads at /growth/referrals
- [ ] Data fetches from API
- [ ] Copy buttons work
- [ ] Social share buttons work

**Challenges Page**:
- [ ] Page loads at /growth/challenges
- [ ] Challenges display
- [ ] Join button works
- [ ] Progress tracking works

**API Endpoints**:
- [ ] All 7 endpoints return data
- [ ] No 500 errors
- [ ] Response times acceptable

**Cross-Device**:
- [ ] Works on desktop
- [ ] Works on tablet
- [ ] Works on mobile
- [ ] No layout breaks

**Edge Cases**:
- [ ] Empty states display
- [ ] Error states handle gracefully
- [ ] Loading states show
- [ ] Auth required pages redirect correctly

---

## Next Steps After Testing

1. **If tests pass**: 
   - Mark all features as complete ✅
   - Deploy to staging environment
   - Run tests again in staging
   - Deploy to production

2. **If tests fail**:
   - Document failures
   - Check error logs: `tail -f storage/logs/laravel.log`
   - Fix issues
   - Re-test
   - Repeat until all pass

3. **Post-Deployment**:
   - Monitor error logs
   - Track user engagement with new features
   - Gather feedback
   - Plan Phase 3 enhancements

---

## Contact for Issues

If you encounter issues during testing:
1. Check `storage/logs/laravel.log` for errors
2. Check browser console for JavaScript errors
3. Verify database migrations ran: `php artisan migrate:status`
4. Verify routes loaded: `php artisan route:list`
5. Clear all caches: `php artisan optimize:clear`

---

## Testing Complete! 🎉

When all tests pass, you can confidently say:
**Phase 2 UI Integration is 100% Complete and Production-Ready**
