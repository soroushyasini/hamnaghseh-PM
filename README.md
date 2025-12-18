# Hamnaghsheh - Project Management & Survey Services

WordPress plugin for managing survey projects and order management with simplified phone-based workflow.

## Current Version: 3.1 (Simplified Order System)

This version implements a streamlined order management system that reflects the actual business workflow:
phone-based negotiation instead of complex in-app messaging and quote systems.

## Workflow Diagram

```
┌─────────────────┐
│ User submits    │
│ order request   │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Status: PENDING │
│ Admin receives  │
│ notification    │
└────────┬────────┘
         │
         ▼
┌─────────────────────┐
│ Admin calls         │
│ customer on phone   │
│ Discusses details   │
│ Agrees on price     │
└────────┬────────────┘
         │
         ▼
┌──────────────────────┐
│ Admin sets final     │
│ price in system      │
│ Status:              │
│ AWAITING_PAYMENT     │
└────────┬─────────────┘
         │
         ▼
┌─────────────────┐
│ User receives   │
│ notification    │
│ Pays online     │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Status: PAID    │
│ Admin confirms  │
│ Creates project │
└────────┬────────┘
         │
         ▼
┌─────────────────────┐
│ Status: IN_PROGRESS │
│ Survey work begins  │
└────────┬────────────┘
         │
         ▼
┌──────────────────┐
│ Status: COMPLETED│
│ Files uploaded   │
│ User downloads   │
└──────────────────┘
```

## Features

### Order Management (Simplified)
- ✅ Simple order submission with service selection
- ✅ Phone-based price negotiation (no in-app messaging)
- ✅ 6 clear status stages
- ✅ Auto-project creation on payment confirmation
- ✅ Activity timeline for audit trail

### Project Management
- ✅ File upload/download (DWG, DXF, TXT)
- ✅ Project sharing with guest links
- ✅ Access control (view/download permissions)
- ✅ File version tracking
- ✅ Storage management

### Services
- 📋 نقشه برداری نیم روزه (Half-day surveying)
- 📋 نقشه برداری تمام روزه (Full-day surveying)
- ⚙️ Customizable pricing per service

## Installation

1. Upload plugin to `/wp-content/plugins/hamnaghsheh/`
2. Activate through WordPress admin
3. Database tables created automatically
4. Configure services in admin panel

## Order Status Flow

| Status | Persian | Description |
|--------|---------|-------------|
| `pending` | در انتظار بررسی | Order submitted, admin needs to call |
| `awaiting_payment` | آماده پرداخت | Price set, waiting for payment |
| `paid` | پرداخت شده | Payment confirmed |
| `in_progress` | در حال انجام | Project created, work started |
| `completed` | تکمیل شده | Survey finished |
| `cancelled` | لغو شده | Order cancelled |

## Admin Features

### Order Management
- View all orders with filters (status, service, date)
- Phone number prominently displayed for easy calling
- Simple price input form
- Status management
- Internal notes (hidden from users)
- Activity log

### Quick Actions
- Set final price
- Change status
- Create project
- Mark as paid

## User Features

### Order Submission
- Select service type
- Specify quantity
- Provide address and details
- Upload supporting files
- Submit with estimated price

### Order Tracking
- View order status
- See final price when set
- Pay online when ready
- Access project files when available
- Track activity history

## Database Tables

- `wp_hamnaghsheh_projects` - Project management
- `wp_hamnaghsheh_files` - File storage metadata
- `wp_hamnaghsheh_users` - Extended user data
- `wp_hamnaghsheh_shares` - Guest sharing
- `wp_hamnaghsheh_project_assignments` - Access control
- `wp_hamnaghsheh_file_logs` - File activity logs
- `wp_hamnaghsheh_services` - Service definitions
- `wp_hamnaghsheh_orders` - Order management
- `wp_hamnaghsheh_order_activity` - Activity timeline
- ~~`wp_hamnaghsheh_order_messages`~~ - Deprecated (messaging removed)

## API Endpoints

### User Endpoints
- `hamnaghsheh_submit_order` - Submit new order
- (Messaging and editing endpoints removed in v3.1)

### Admin Endpoints
- `hamnaghsheh_admin_set_price` - Set final price and update status
- `hamnaghsheh_admin_update_status` - Change order status
- `hamnaghsheh_admin_create_project` - Create project from order

## Shortcodes

```php
[hamnaghsheh_services] // Display services catalog
[hamnaghsheh_order_form] // Order submission form
[hamnaghsheh_my_orders] // User's order list
[hamnaghsheh_order_detail] // Single order view
[hamnaghsheh_dashboard] // User dashboard
[hamnaghsheh_new-project] // Project creation
[hamnaghsheh_project_show] // Project file viewer
```

## Configuration

### Service Settings
Admin → سفارش‌ها → تنظیمات خدمات

Configure:
- Service name (Persian)
- Price per session
- Description
- Active/inactive status

### File Upload Settings
- Supported formats: DWG, DXF, TXT
- Max file size: Configurable
- Storage quota per user

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for detailed version history.

### v3.1 (Current - Simplified)
- Removed complex quote/messaging system
- Added `final_price` field
- Simplified to 6 statuses
- Phone-based workflow
- Streamlined admin interface

### v3.0 (Archived - Full-Featured)
- Full messaging system
- Quote negotiation
- Order editing
- Complex status flow
- See `feature/full-order-system` branch

## Migration from v3.0

If upgrading from the full-featured version:
- Database migrates automatically on activation
- Old statuses mapped to new ones
- Existing data preserved
- Messages table kept (can be manually dropped)

## Developer Notes

### Code Style
- RTL/Persian support throughout
- WordPress coding standards
- Security: Nonces, capability checks, sanitization
- Mobile-responsive design

### Customization
- Status badges: `assets/css/orders.css`
- Admin interface: `templates/admin/`
- User interface: `templates/`
- AJAX handlers: `includes/admin/class-admin-orders.php`

### Extending
```php
// Add custom order status
add_filter('hamnaghsheh_order_statuses', function($statuses) {
    $statuses['custom_status'] = 'Custom Label';
    return $statuses;
});

// Hook into price set
add_action('hamnaghsheh_price_set', function($order_id) {
    // Your custom logic
});
```

## Support

For issues, feature requests, or questions:
- GitHub Issues: [Repository Issues](https://github.com/soroushyasini/hamnaghseh-PM/issues)
- Documentation: See `/docs` directory

## License

Proprietary - All rights reserved

## Credits

- **Authors**: Milad Karimi, Soroush Yasini
- **Version**: 1.1.7 (Plugin) / 3.1 (Order System)
- **Persian**: Full RTL and Persian language support
