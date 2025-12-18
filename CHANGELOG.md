# Changelog - Order Management System Simplification

## Version 3.1 - Simplified Order System (Current)

### Overview
This version simplifies the order management system to match the real-world business workflow. The full-featured system with quote negotiation and messaging has been preserved in the `v1.0-full-featured` tag and `feature/full-order-system` branch.

### Business Workflow
1. User submits order request with details
2. Admin calls the customer on phone
3. They discuss and agree on price/details over phone
4. Admin sets final price in system
5. User pays
6. Project auto-created
7. Survey completed

### Major Changes

#### Database Changes
- **Added**: `final_price` column to `wp_hamnaghsheh_orders` table for storing the agreed-upon price after phone discussion
- **Migration**: Automatic status migration for existing orders:
  - `reviewed` → `pending`
  - `quoted` → `awaiting_payment`
  - `user_accepted` → `awaiting_payment`
  - `payment_uploaded` → `paid`
- **Preserved**: Old admin estimation columns (marked as deprecated) for backward compatibility

#### Simplified Order Statuses
**Removed statuses:**
- `reviewed` - No longer needed
- `quoted` - Replaced by direct price setting
- `user_accepted` - Eliminated quote acceptance flow
- `payment_uploaded` - Simplified to just `paid`

**Current 6 statuses:**
1. `pending` - Order submitted, waiting for admin to call
2. `awaiting_payment` - Admin has called and set price, waiting for payment
3. `paid` - Payment confirmed by admin
4. `in_progress` - Project created, survey work in progress
5. `completed` - Survey finished, files uploaded
6. `cancelled` - Order cancelled by user or admin

#### Removed Features
- **Messaging System**: Entire order messaging functionality removed
  - Deleted `includes/class-order-messages.php`
  - Deleted `templates/parts/order-messages.php`
  - Removed all messaging AJAX endpoints
  - Removed message UI from templates
  
- **Quote System**: Complex quote/counter-quote workflow removed
  - Removed `ajax_set_quote()` method
  - Removed `ajax_accept_quote()` method
  - Removed quote acceptance UI
  
- **Order Editing**: Users can no longer edit orders after submission
  - Removed `ajax_edit_order()` method
  - Contact admin if changes needed

#### Simplified Admin Interface

**New Admin Order Detail Page:**
- Simple form with customer info (phone number highlighted)
- Order details display
- Single price input field for final price
- Status dropdown with 6 options
- Internal notes field (hidden from users)
- Activity timeline
- Project creation button

**Updated Admin Orders List:**
- Added phone column for quick reference
- Replaced complex price columns with single "Final Price" column
- Shows "Not set" if price not yet determined
- Removed message count badges
- Simplified status filters

#### Simplified User Interface

**New User Order Detail Page:**
- Status-based messaging:
  - `pending`: Shows estimated price + "Expert will call you soon"
  - `awaiting_payment`: Shows final price + payment button
  - `paid` and beyond: Shows payment confirmed
- Link to project when available
- No edit/cancel buttons (contact admin if needed)
- Clean, status-focused design

**Updated User Order Cards:**
- Uses `final_price` instead of `admin_estimated_total_price`
- Removed message count badges
- Removed comparison warnings
- Cleaner, simpler display

#### Code Changes

**Backend (PHP):**
- `includes/class-orders.php`: Removed messaging, quote, and editing methods
- `includes/admin/class-admin-orders.php`: Added `ajax_set_price()`, removed `ajax_set_quote()` and `ajax_send_message()`
- `includes/class-activator.php`: Added `final_price` column and migration logic

**Frontend (JavaScript):**
- `assets/js/orders.js`: Removed messaging and quote handlers
- `assets/js/admin-orders.js`: Removed quote calculation and messaging functionality

**Styles (CSS):**
- `assets/css/orders.css`: Added 6 status badge color classes, removed messaging styles
- `assets/css/admin-orders.css`: Updated status indicators, removed message thread styles

### Backward Compatibility

- Existing orders will work with migrated statuses
- Old database columns preserved (not removed) for data integrity
- Message table kept (can be manually dropped later if desired)

### Accessing Full-Featured Version

If you need to restore the full quote/messaging system:
- Branch: `feature/full-order-system`
- Tag: `v1.0-full-featured`
- Checkout and merge as needed

### Technical Notes

- Database version: Updated to 3.1
- Preserves all security measures (nonces, capability checks, sanitization)
- Maintains mobile responsiveness
- Keeps RTL/Persian support
- Auto-project creation still triggers on status change to `paid`

### Developer Notes

Code comments indicate "SIMPLIFIED VERSION" or "REMOVED:" where major simplifications were made, making it easy to identify changes.

---

## Version 3.0 - Full-Featured System (Archived)

See `feature/full-order-system` branch for the full-featured version with:
- Complete messaging system
- Quote negotiation workflow
- Order editing capabilities
- Complex status flow
- Admin re-estimation forms

---

For questions or to report issues, please contact the development team.
