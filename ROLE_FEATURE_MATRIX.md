# 📋 ROLE-BASED FEATURE MAPPING

**Document:** Feature Access Control Matrix  
**Last Updated:** December 9, 2025  
**Purpose:** Clear documentation of which features should be accessible to which roles

---

## 🎯 ROLE DEFINITIONS

### ADMIN (role: 'admin')
- **Purpose:** Platform management, content moderation, financial oversight
- **Access Level:** Full system access (with audit trail)
- **Special Privileges:** 
  - Can impersonate other users
  - Can bypass KYC requirements
  - Can modify any user or content
  - Can configure system settings
  - Can audit all transactions

### SELLER (role: 'seller')
- **Purpose:** Create and sell digital notes
- **Access Level:** Content creation, sales management, analytics
- **Special Privileges:**
  - Can create and publish notes
  - Can access studio (paid services)
  - Can earn commissions
  - Can manage affiliate links
  - Can view own analytics

### BUYER (role: 'buyer')
- **Purpose:** Purchase and consume digital notes
- **Access Level:** Marketplace, purchases, learning
- **Special Privileges:**
  - Can purchase notes
  - Can resell purchased notes
  - Can manage subscriptions
  - Can participate in referral program
  - Can request refunds

---

## 🗺️ COMPLETE FEATURE MATRIX

### Legend
| Symbol | Meaning |
|--------|---------|
| ✅ | Full Access |
| 📖 | Read-Only Access |
| ⚠️ | Limited/Conditional Access |
| ❌ | No Access |
| ⭕ | Should Have (but currently blocked) |

---

## FEATURE ACCESS TABLE

### ACCOUNT & PROFILE

| Feature | Admin | Seller | Buyer | Current Status | Issues |
|---------|-------|--------|-------|---|---|
| View Own Profile | ✅ | ✅ | ✅ | ✅ Correct | None |
| Edit Own Profile | ✅ | ✅ | ✅ | ✅ Correct | None |
| Upload KTP/Selfie | ✅ | ✅ | ✅ | ✅ Correct | None |
| View Other Profiles | 📖 | 📖 | 📖 | ✅ Correct | None |
| Change Role | ✅ | ❌ | ❌ | ✅ Correct | None |
| Manage Users | ✅ | ❌ | ❌ | ✅ Correct | None |
| Suspend User | ✅ | ❌ | ❌ | ✅ Correct | None |
| Verify User Identity | ✅ | ❌ | ❌ | ✅ Correct | None |

---

### CONTENT CREATION & MANAGEMENT

| Feature | Admin | Seller | Buyer | Current | Issues |
|---------|-------|--------|-------|---------|--------|
| Create Notes | ✅ | ✅ | ❌ | ⚠️ No middleware | #008 |
| Edit Own Notes | ✅ | ✅ | ❌ | ⚠️ Missing ownership | #009 |
| Delete Own Notes | ✅ | ✅ | ❌ | ⚠️ Missing ownership | #009 |
| Edit Other Notes | ✅ | ❌ | ❌ | ⚠️ Missing ownership | #009 |
| Delete Other Notes | ✅ | ❌ | ❌ | ⚠️ Missing ownership | #009 |
| Create Series | ✅ | ✅ | ❌ | ⚠️ Missing checks | #009 |
| Create Collections | ✅ | ⚠️ | ✅ | ⚠️ Implicit | #011 |
| Create Folders | ✅ | ✅ | ✅ | ⚠️ Implicit | #011 |
| Upload Attachments | ✅ | ✅ | ✅ | ⚠️ Implicit | #011 |
| Publish/Unpublish | ✅ | ✅ | ❌ | ⚠️ No checks | #009 |
| Set Note Price | ✅ | ✅ | ❌ | ⚠️ No checks | #009 |
| Create Templates | ✅ | ✅ | ❌ | ⚠️ Implicit | #011 |
| Monetize Notes | ✅ | ✅ | ❌ | ✅ Correct | None |

---

### MARKETPLACE & PURCHASING

| Feature | Admin | Seller | Buyer | Current | Issues |
|---------|-------|--------|-------|---------|--------|
| Browse Marketplace | ✅ | ✅ | ✅ | ✅ Correct | None |
| Search Notes | ✅ | ✅ | ✅ | ✅ Correct | None |
| View Note Details | ✅ | ✅ | ✅ | ✅ Correct | None |
| Purchase Notes | ⚠️ | ⚠️ | ✅ | ⚠️ Role check in controller | #007 |
| Resell Notes | ⚠️ | ✅ | ✅ | ⚠️ Implicit | #011 |
| Leave Reviews | ✅ | ✅ | ✅ | ✅ Correct | None |
| Comment on Notes | ✅ | ✅ | ✅ | ✅ Correct | None |
| Create Conversations | ✅ | ✅ | ✅ | ✅ Correct | None |
| Message Seller | ✅ | ✅ | ✅ | ✅ Correct | None |
| View Price History | 📖 | 📖 | 📖 | ✅ Correct | None |

---

### COLLECTIONS & BOOKMARKS

| Feature | Admin | Seller | Buyer | Current | Issues |
|---------|-------|--------|-------|---------|--------|
| Create Collections | ✅ | ⚠️ | ✅ | ⚠️ Implicit | #011 |
| Add to Collections | ✅ | ⚠️ | ✅ | ⚠️ Implicit | #011 |
| Share Collections | ✅ | ⚠️ | ✅ | ⚠️ Implicit | #011 |
| Bookmark Notes | ✅ | ✅ | ✅ | ✅ Correct | None |
| Save Searches | ✅ | ✅ | ✅ | ⚠️ Implicit | #011 |

---

### STUDIO (SERVICE ORDERS)

| Feature | Admin | Seller | Buyer | Current | Issues |
|---------|-------|--------|-------|---------|--------|
| Browse Studio | ✅ | ✅ | ✅ | ✅ Correct | None |
| Create Brief | ⚠️ | ✅ | ✅ | ✅ Proper middleware | None |
| View Orders | ✅ | ✅ | ✅ | ✅ Correct | None |
| Assign Vendor | ✅ | ❌ | ✅ | ✅ Correct | None |
| Submit Work | ❌ | ✅ | ❌ | ✅ Correct | None |
| Approve Work | ✅ | ❌ | ✅ | ✅ Correct | None |
| Request Revision | ✅ | ❌ | ✅ | ✅ Correct | None |
| Verify Order (Admin) | ✅ | ❌ | ❌ | ✅ Correct | None |
| Fund Escrow | ✅ | ❌ | ✅ | ✅ Correct | None |
| Release Payment | ✅ | ❌ | ✅ | ✅ Correct | None |
| Request Refund | ⚠️ | ⚠️ | ✅ | ⚠️ Implicit | #011 |

---

### ANALYTICS & INSIGHTS

| Feature | Admin | Seller | Buyer | Current | Issues |
|---------|-------|--------|-------|---------|--------|
| Seller Dashboard | 📖 | ✅ | ❌ | ❌ Blocked | #004 |
| Sales Analytics | 📖 | ✅ | ⚠️ | ⚠️ Limited | None |
| Note Performance | 📖 | ✅ | ❌ | ❌ Blocked | #004 |
| Share Analytics | ⭕ | ✅ | ❌ | ❌ Blocked | #004 |
| Share Leaderboard | ⭕ | ✅ | ❌ | ❌ Blocked | #004 |
| Buyer Analytics | 📖 | ❌ | ✅ | ✅ Correct | None |
| Reading History | 📖 | ❌ | ✅ | ✅ Correct | None |
| Referral Analytics | 📖 | ✅ | ✅ | ⭕ Blocked | #002 |

---

### AFFILIATE SYSTEM

| Feature | Admin | Seller | Buyer | Current | Issues |
|---------|-------|--------|-------|---------|--------|
| View Affiliate Dashboard | ⭕ | ✅ | ✅ | ❌ Blocked | #001 |
| Create Affiliate Links | ⚠️ | ✅ | ✅ | ⚠️ Blocked | #001 |
| View Affiliate Links | ⭕ | ✅ | ✅ | ❌ Blocked | #001 |
| View Conversions | ⭕ | ✅ | ✅ | ❌ Blocked | #001 |
| View Commissions | ⭕ | ✅ | ✅ | ❌ Blocked | #001 |
| Request Payout | ❌ | ✅ | ✅ | ✅ Correct | None |
| Manage Payouts | ✅ | ❌ | ❌ | ⭕ Needs interface | #015 |
| Configure Settings | ✅ | ❌ | ❌ | ✅ Correct | None |
| View Promotional Materials | ✅ | ✅ | ✅ | ✅ Correct | None |
| Upload Promotional Materials | ⚠️ | ✅ | ✅ | ⚠️ Implicit | #011 |
| Audit Affiliate System | ✅ | ❌ | ❌ | ❌ Missing | #015 |

---

### REFERRAL SYSTEM

| Feature | Admin | Seller | Buyer | Current | Issues |
|---------|-------|--------|-------|---------|--------|
| View Referral Dashboard | ⭕ | ✅ | ✅ | ❌ Blocked | #002 |
| Generate Referral Link | ❌ | ✅ | ✅ | ✅ Correct | None |
| Share Referral Link | ❌ | ✅ | ✅ | ✅ Correct | None |
| View Referrals | ⭕ | ✅ | ✅ | ❌ Blocked | #002 |
| View Referral Earnings | ⭕ | ✅ | ✅ | ❌ Blocked | #002 |
| Request Payout | ❌ | ✅ | ✅ | ✅ Correct | None |
| View Commission Tier | ✅ | ✅ | ✅ | ✅ Correct | None |
| Configure Tiers | ✅ | ❌ | ❌ | ✅ Correct | None |
| Audit Referral System | ✅ | ❌ | ❌ | ❌ Missing | #014 |

---

### FINANCIAL MANAGEMENT

| Feature | Admin | Seller | Buyer | Current | Issues |
|---------|-------|--------|-------|---------|--------|
| View Wallet | ✅ | ✅ | ✅ | ✅ Correct | None |
| Top Up Wallet | ✅ | ✅ | ✅ | ⚠️ Implicit | #011 |
| Request Withdrawal | ❌ | ✅ | ✅ | ⚠️ Implicit | #011 |
| Manage Withdrawals | ✅ | ❌ | ❌ | ✅ Correct | None |
| View Transactions | 📖 | 📖 | 📖 | ✅ Correct | None |
| View Financial Reports | ✅ | ⚠️ | ⚠️ | ⚠️ Implicit | #011 |
| Manage Disputes | ✅ | ⚠️ | ⚠️ | ✅ Correct | None |
| Process Refunds | ✅ | ❌ | ❌ | ✅ Correct | None |
| Configure Payment Methods | ✅ | ❌ | ❌ | ✅ Correct | None |
| Set Commission Rates | ✅ | ❌ | ❌ | ✅ Correct | None |

---

### CONTENT MODERATION

| Feature | Admin | Seller | Buyer | Current | Issues |
|---------|-------|--------|-------|---------|--------|
| Review Flagged Notes | ✅ | ❌ | ❌ | ✅ Correct | None |
| Suspend Notes | ✅ | ❌ | ❌ | ✅ Correct | None |
| Review Note Reports | ✅ | ❌ | ❌ | ✅ Correct | None |
| Moderate Comments | ✅ | ⚠️ | ❌ | ✅ Correct | None |
| Delete Comments | ✅ | ⚠️ | ❌ | ✅ Correct | None |
| Moderate Reviews | ✅ | ❌ | ❌ | ✅ Correct | None |
| Approve Monetization | ✅ | ❌ | ❌ | ✅ Correct | None |
| Manage Forum Posts | ✅ | ✅ | ✅ | ✅ Correct | None |
| Hide Forum Posts | ✅ | ⚠️ | ❌ | ✅ Correct | None |

---

### SUBSCRIPTIONS

| Feature | Admin | Seller | Buyer | Current | Issues |
|---------|-------|--------|-------|---------|--------|
| View Subscription Plans | ✅ | ✅ | ✅ | ✅ Correct | None |
| Subscribe to Plan | ⚠️ | ⚠️ | ✅ | ⚠️ No explicit check | #011 |
| View My Subscription | ✅ | ✅ | ✅ | ✅ Correct | None |
| Cancel Subscription | ✅ | ✅ | ✅ | ✅ Correct | None |
| Change Plan | ✅ | ✅ | ✅ | ✅ Correct | None |
| Gift Subscription | ✅ | ✅ | ✅ | ⚠️ Implicit | #011 |
| Create Plans | ✅ | ❌ | ❌ | ✅ Correct | None |
| Manage Plans | ✅ | ❌ | ❌ | ✅ Correct | None |
| View Subscriber List | ✅ | ✅ | ❌ | ✅ Correct | None |

---

### AWARDS & ACHIEVEMENTS

| Feature | Admin | Seller | Buyer | Current | Issues |
|---------|-------|--------|-------|---------|--------|
| View Badges | ✅ | ✅ | ✅ | ✅ Correct | None |
| Create Badges | ✅ | ❌ | ❌ | ✅ Correct | None |
| Award Badges | ✅ | ❌ | ❌ | ✅ Correct | None |
| View Certifications | ✅ | ✅ | ✅ | ✅ Correct | None |
| Create Certifications | ✅ | ❌ | ❌ | ✅ Correct | None |
| Apply for Certification | ⚠️ | ✅ | ✅ | ✅ Correct | None |
| Review Applications | ✅ | ❌ | ❌ | ✅ Correct | None |

---

### SYSTEM ADMINISTRATION

| Feature | Admin | Seller | Buyer | Current | Issues |
|---------|-------|--------|-------|---------|--------|
| View Dashboard | ✅ | ✅ | ✅ | ⚠️ No validation | #010 |
| System Health Check | ✅ | ❌ | ❌ | ✅ Correct | None |
| View Logs | ✅ | ❌ | ❌ | ✅ Correct | None |
| Manage Settings | ✅ | ❌ | ❌ | ✅ Correct | None |
| Configure Email | ✅ | ❌ | ❌ | ✅ Correct | None |
| Manage Users | ✅ | ❌ | ❌ | ✅ Correct | None |
| Audit Trail | ✅ | ❌ | ❌ | ⚠️ Limited | None |
| View Reports | ✅ | ⚠️ | ⚠️ | ⚠️ Limited | None |

---

## 📊 SUMMARY BY CATEGORY

### ✅ Fully Correct Features
- Account & Profile (all)
- Marketplace & Purchasing (mostly)
- Studio Services (all)
- Content Moderation (all)
- Subscriptions (mostly)
- Awards & Achievements (all)
- System Administration (mostly)

**Count:** ~50 features correctly implemented

---

### ⚠️ Features with Issues
- Content Creation (missing ownership checks)
- Affiliate System (admin blocked)
- Referral System (admin blocked)
- Analytics (seller analytics blocked from admin)
- Share System (admin blocked)
- Financial Management (implicit role checks)

**Count:** ~40 features with issues
**Severity:** Mix of Medium and High

---

### ❌ Blocked/Missing Features
- Affiliate Features for Admin (currently blocked)
- Referral Features for Admin (currently blocked)
- Seller Analytics for Admin (currently blocked)
- Admin Affiliate Management Interface (missing)
- Admin Referral Management Interface (missing)

**Count:** ~5 major features blocked

---

## 🔧 FIX PRIORITY BY FEATURE AREA

### IMMEDIATE (This Week)
1. Unblock admin from affiliate system
2. Unblock admin from referral system
3. Fix auth() helper pattern (complete remaining)

**Impact:** High | **Effort:** 6 hours

### SHORT TERM (Next 2 Weeks)
4. Add admin views for affiliate/referral
5. Add ownership checks to content
6. Standardize middleware patterns
7. Audit all 854 routes for authorization

**Impact:** High | **Effort:** 25 hours

### MEDIUM TERM (Next Month)
8. Create admin affiliate management interface
9. Create admin referral management interface
10. Implement Policy/Gate pattern for authorization
11. Add comprehensive authorization tests

**Impact:** High | **Effort:** 20 hours

---

## 📋 TEMPLATE FOR NEW FEATURES

When adding new features, ensure:

1. **Determine which roles can access:**
   - Admin: Full access + audit?
   - Seller: Can create/manage?
   - Buyer: Can use/consume?

2. **Implement authorization:**
   - Route middleware: `'role:X'`
   - Controller validation: None (middleware handles)
   - Policy/Gate: For resource-level checks
   - View conditionals: Use `@can` or view helpers

3. **Test authorization:**
   - Each role gets correct access
   - Other roles get 403
   - Unauthenticated get 401
   - Ownership is verified

4. **Document:**
   - Feature access in this matrix
   - Route authorization requirements
   - Admin override behavior (if any)

---

**Document Version:** 1.0  
**Last Updated:** December 9, 2025  
**Next Review:** December 23, 2025

