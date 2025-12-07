# Quick Reference Card - Points Pricing System

**Print this page and keep it handy!**

---

## 🚀 Quick Start

### Access Points Pricing
```
Admin Dashboard → Click "Points Pricing" (pink card)
OR
Direct URL: http://noteds.test/admin/points-pricing
```

### Create New Offer (30 seconds)
```
1. Click "Add New Pricing Option"
2. Name: [offer name]
3. Type: Discount OR Premium Feature
4. Points Required: [amount]
5. Value: [discount % or amount]
6. Daily Limit: [number or blank]
7. User Limit: [number or blank]
8. Click Save
```

### Monitor Redemptions (1 minute)
```
1. Go to points-pricing page
2. Click "Redemption Monitoring"
3. View Today's Stats
4. Filter by Date Range (optional)
5. Export CSV (optional)
```

---

## 📋 Fields Explained

### Configuration Form

| Field | Required | Example | Purpose |
|-------|----------|---------|---------|
| Name | ✅ | "5% Discount" | Display name for users |
| Type | ✅ | "discount" | What user gets |
| Points Required | ✅ | 500 | Cost in points |
| Discount % | For discount | 5 | Percentage off |
| Discount Amount | For discount | 50000 | Rupiah off |
| Premium Days | For premium | 30 | Days of access |
| Daily Limit | ⭕ | 50 | Max per day |
| User Limit | ⭕ | 2 | Max per user |
| Expiration | ⭕ | 2025-12-31 | When to disable |
| Active | ✅ | ☑️ Checked | Enable/disable |
| Description | ⭕ | "Holiday offer" | Notes |

**✅ = Required | ⭕ = Optional**

---

## 🔢 Recommended Settings

### For New Offers (Conservative)
```
Daily Limit:  20-50
User Limit:   1-2
Expiration:   Leave blank (unlimited)
Active:       YES
```

### For Promotions (Time Limited)
```
Daily Limit:  50-100
User Limit:   2-3
Expiration:   End of promo period
Active:       YES (disable manually)
```

### For Testing
```
Daily Limit:  None (blank)
User Limit:   None (blank)
Expiration:   Leave blank
Active:       YES
```

---

## 📊 Statistics Dashboard

### Monitoring Page Shows:
```
┌─────────────────────────┐
│ Today's Redemptions: 45 │ ← How many today
├─────────────────────────┤
│ Points Used Today: 8,750│ ← Total points used
├─────────────────────────┤
│ Weekly Count: 287       │ ← This week's count
├─────────────────────────┤
│ Active Redemptions: 102 │ ← Still valid
└─────────────────────────┘
```

### Table Shows:
```
User Name | Email | Type | Points | Value | Status | Time
```

---

## ⚙️ Admin Actions

### Edit Offer
```
1. Find offer in list
2. Click "Edit" button
3. Change fields
4. Click Save
```

### Disable Offer
```
1. Click "Edit" 
2. Uncheck "Active"
3. Click Save
(Users won't see it immediately)
```

### Delete Offer
```
1. Click "Delete" button
2. Confirm deletion
(Cannot be undone)
```

### Export Report
```
1. Go to Monitoring page
2. (Optional) Set date range
3. Click "Export Report"
4. Save CSV file
```

---

## 🛡️ Safety Limits

### Daily Limit Example
```
Daily Limit: 50
↓
Max 50 people can redeem TODAY
(Resets tomorrow at midnight)
```

### User Limit Example
```
User Limit: 2
↓
Same person can redeem MAX 2 TIMES
(Prevents hoarding)
```

### Expiration Example
```
Expires: 2025-12-31
↓
Offer disabled automatically on Jan 1
(No need to manually disable)
```

---

## 📈 Common Scenarios

### Scenario 1: Daily Deal
```
Name: "Weekend Flash Sale"
Type: Discount
Points: 300
Value: 15%
Daily Limit: 100
User Limit: 1 (once per day)
Expires: End of weekend
```

### Scenario 2: Premium Access
```
Name: "Premium Week"
Type: Premium Feature
Points: 1000
Days: 7
Daily Limit: 20 (protect revenue)
User Limit: 1
No expiration
```

### Scenario 3: Limited Time Bonus
```
Name: "Holiday Bonus: 30% Off"
Type: Discount
Points: 200 (lower = easier)
Value: 30%
Daily Limit: 200 (high for promotion)
User Limit: 3
Expires: 2025-12-25
```

---

## 🔍 Troubleshooting Quick Fixes

### Problem: "Can't access admin panel"
```
✓ Login first
✓ Check you have admin role
✓ Clear browser cache
```

### Problem: "Offers not appearing for users"
```
✓ Is "Active" checkbox CHECKED?
✓ Is expiration date in FUTURE?
✓ Are points_required realistic?
```

### Problem: "CSV export won't download"
```
✓ Check date range has data
✓ Try without date filter
✓ Check disk space
```

### Problem: "Limits not working"
```
✓ Is Daily Limit or User Limit EMPTY? (= unlimited)
✓ Check if offer is ACTIVE
✓ Verify limit is > 0
```

---

## 📞 Quick Help

### Where is the admin page?
```
http://noteds.test/admin/points-pricing
```

### Where is monitoring?
```
http://noteds.test/admin/points-monitoring
```

### Can I undo a deletion?
```
❌ NO - Backups required
```

### How often to check?
```
Daily: 5 minutes to view stats
Weekly: Export report for analysis
Monthly: Review and plan new offers
```

### What if I mess up?
```
1. Check POINTS_PRICING_SETUP.md
2. Contact development team
3. Request database restore (if recent)
```

---

## 🎯 Performance Tips

### For Best Results:
```
✅ Set realistic limits
✅ Monitor daily
✅ Adjust based on data
✅ Use expiration for promos
✅ Keep active offers to 5-10
❌ Don't create unlimited offers
❌ Don't set very high limits
❌ Don't ignore monitoring
```

### Budget Protection:
```
Conservative:   Daily Limit 20, User Limit 1
Moderate:       Daily Limit 50, User Limit 2
Promotional:    Daily Limit 100, User Limit 3
```

---

## 📚 Documentation Files

| File | Purpose | Read When |
|------|---------|-----------|
| POINTS_PRICING_FEATURE.md | Complete guide | Need details |
| POINTS_PRICING_API.md | Technical reference | Integrating |
| POINTS_PRICING_SETUP.md | Setup instructions | Deploying |
| IMPLEMENTATION_SUMMARY.md | Overview | Getting started |
| FINAL_COMPLETION_REPORT.md | Summary | Need verification |

---

## ✅ Daily Checklist

**Every Morning (5 min):**
```
☐ Check monitoring dashboard
☐ Note today's redemptions
☐ Check for anomalies
☐ Review active offers
```

**Every Week (15 min):**
```
☐ Export weekly report
☐ Analyze trends
☐ Check limits working
☐ Plan next week's offers
```

**Every Month (30 min):**
```
☐ Review monthly performance
☐ Disable old promotions
☐ Adjust limits if needed
☐ Plan new offers
☐ Check for issues
```

---

## 🎊 You're All Set!

Everything is ready to use. Start by:
1. Creating 2-3 initial offers
2. Monitor for a few days
3. Adjust based on actual usage
4. Expand as needed

**Questions?** Check the documentation files or contact support.

**Enjoy!** 🚀

---

**Last Updated:** December 7, 2025  
**Status:** Ready to Use ✅
