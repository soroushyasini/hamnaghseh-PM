# 🎁 14-Day Trial System - Installation Guide

## 📦 What's New (added by soroush - 8 Dec 2025)

### Free Tier Changes:
- **Basic Free (Default):** View-only forever, 0 MB storage
- **Trial Free (Opt-in):** 14 days with 10 MB + full upload features

---

## 🔧 Installation Steps

### **Step 1: Database Update**
Run this SQL in phpMyAdmin:

```sql
ALTER TABLE wp_hamnaghsheh_users 
ADD COLUMN trial_activated TINYINT(1) DEFAULT 0,
ADD COLUMN trial_started_at DATETIME NULL,
ADD COLUMN trial_ends_at DATETIME NULL;

UPDATE wp_hamnaghsheh_users 
SET trial_activated = 0 
WHERE access_level = 'free';
```

---

### **Step 2: Create New Files**

Create these files in `/includes/`:

1. **`class-trial-manager.php`** - Use artifact `class-trial-manager`

Create this file in `/templates/`:

2. **`trial-banner.php`** - Use artifact `trial-banner-component`

---

### **Step 3: Update Existing Files**

Update these files with the modified artifacts:

3. **`class-file-validator.php`** - Updated with trial support
4. **`class-utils.php`** - Updated `can_perform_action()` method
5. **`class-loader.php`** - Added trial manager loading
6. **`dashboard.php`** - Added trial banner include

---

### **Step 4: Update Function Calls**

In any place you call these functions, update signatures:

**OLD:**
```php
$can_archive = Hamnaghsheh_Utils::can_perform_action('archive', $access_level);
```

**NEW:**
```php
$can_archive = Hamnaghsheh_Utils::can_perform_action('archive', $access_level, $user_id);
```

---

## 🎯 How It Works

### **User Flow:**

1. **New User Registers**
   - Default: Basic Free (view-only)
   - See banner: "🎁 شروع آزمایش رایگان 14 روزه"

2. **User Clicks "Start Trial"**
   - Confirmation popup
   - Trial activated
   - Gets 10 MB storage
   - Can upload DWG, DXF, TXT
   - Can delete/replace/archive

3. **During Trial (Days 1-14)**
   - Banner shows: "⏱️ دوره آزمایشی فعال - باقیمانده: X روز"
   - Full access to features
   - See countdown

4. **After Trial Expires**
   - Banner shows: "⚠️ دوره آزمایشی به پایان رسید"
   - Reverts to Basic Free (view-only)
   - Files remain accessible (view-only)
   - Can't upload anymore
   - Must buy subscription

---

## 🧪 Testing Checklist

- [ ] New user sees trial activation banner
- [ ] Click "Start Trial" activates 14-day period
- [ ] During trial: Can upload files (10 MB limit)
- [ ] During trial: Banner shows remaining days
- [ ] After 14 days: User reverts to view-only
- [ ] Expired trial shows "Must Buy" banner
- [ ] Premium users don't see any banner
- [ ] Trial can only be activated once per user

---

## 📊 Database Schema

```
wp_hamnaghsheh_users:
├── trial_activated (TINYINT) - 0=not used, 1=used
├── trial_started_at (DATETIME) - When activated
└── trial_ends_at (DATETIME) - 14 days after start
```

---

## 🎨 Banner States

### State 1: Available (Blue)
```
🎁 شروع آزمایش رایگان 14 روزه
10 مگابایت فضا + امکانات کامل
[🚀 شروع آزمایش رایگان]
```

### State 2: Active (Green)
```
⏱️ دوره آزمایشی شما فعال است
باقیمانده: 7 روز | فضا: 3/10 MB
[📦 خرید اشتراک]
```

### State 3: Expired (Orange)
```
⚠️ دوره آزمایشی به پایان رسید
شما از آزمایش استفاده کردید
[💳 خرید اشتراک]
```

---

## 🔑 Key Features

✅ One-time trial per user (can't reactivate)
✅ Automatic expiration after 14 days
✅ Smooth transition back to view-only
✅ Clear countdown display
✅ Animated banner with shine effect
✅ Mobile responsive
✅ AJAX activation (no page reload needed for activation)

---

## 💡 Business Benefits

- **Conversion Funnel:** Basic Free → Trial → Paid
- **Lower Barrier:** Users can test before buying
- **View-Only Users:** Perfect for share link recipients
- **Trial Urgency:** Countdown creates urgency to buy
- **One-Time Only:** Prevents abuse

---

## 🚀 Go Live!

After installation:
1. Clear WordPress cache
2. Test with a free user account
3. Activate trial
4. Upload some files
5. Check countdown
6. (Optional) Manually set trial_ends_at to past date to test expiry

**All done!** 🎉