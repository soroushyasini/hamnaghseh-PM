# Order Management System Architecture

## System Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                        WordPress Frontend                        │
└─────────────────────────────────────────────────────────────────┘
                                 │
        ┌────────────────────────┼────────────────────────┐
        │                        │                        │
        ▼                        ▼                        ▼
┌──────────────┐        ┌──────────────┐        ┌──────────────┐
│   Services   │        │ Order Form   │        │  My Orders   │
│     Page     │        │              │        │              │
│              │        │              │        │              │
│  - Browse    │───────▶│  - Submit    │───────▶│  - Track     │
│  - Select    │        │  - Details   │        │  - View      │
│  - Quantity  │        │  - Confirm   │        │  - Messages  │
└──────────────┘        └──────────────┘        └──────────────┘
                                                         │
                                                         ▼
                                                ┌──────────────┐
                                                │ Order Detail │
                                                │              │
                                                │  - View      │
                                                │  - Chat      │
                                                │  - Pay       │
                                                │  - Cancel    │
                                                └──────────────┘
```

## Core Classes

```
┌─────────────────────────────────────────────────────────────────┐
│                         Core Layer                               │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌─────────────────┐  ┌─────────────────┐  ┌────────────────┐ │
│  │ class-services  │  │  class-orders   │  │ class-order-   │ │
│  │                 │  │                 │  │   messages     │ │
│  │ - get_services  │  │ - create_order  │  │ - add_message  │ │
│  │ - update_service│  │ - get_orders    │  │ - get_messages │ │
│  │                 │  │ - update_status │  │ - mark_read    │ │
│  └─────────────────┘  └─────────────────┘  └────────────────┘ │
│                                                                  │
│  ┌────────────────────────────────────────────────────────────┐ │
│  │           class-order-activity                             │ │
│  │           - log_activity                                   │ │
│  │           - get_activity                                   │ │
│  └────────────────────────────────────────────────────────────┘ │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

## Admin Classes

```
┌─────────────────────────────────────────────────────────────────┐
│                         Admin Layer                              │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌─────────────────────────┐  ┌──────────────────────────────┐ │
│  │ class-admin-orders      │  │ class-admin-services         │ │
│  │                         │  │                              │ │
│  │ - render_orders_list    │  │ - render_services_page       │ │
│  │ - render_order_detail   │  │ - ajax_save_service          │ │
│  │ - ajax_set_quote        │  │                              │ │
│  │ - ajax_update_status    │  │                              │ │
│  │ - ajax_send_message     │  │                              │ │
│  │ - ajax_create_project   │  │                              │ │
│  └─────────────────────────┘  └──────────────────────────────┘ │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

## Database Schema

```
┌──────────────────────────────────────────────────────────────────┐
│                         Database Layer                            │
├──────────────────────────────────────────────────────────────────┤
│                                                                   │
│  wp_hamnaghsheh_services                                         │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │ id | service_key | service_name_fa | price_per_session |  │  │
│  │ description | image_url | is_active | created_at |        │  │
│  │ updated_at                                                 │  │
│  └────────────────────────────────────────────────────────────┘  │
│                                                                   │
│  wp_hamnaghsheh_orders                                           │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │ id | user_id | order_number | service_type |              │  │
│  │ requested_quantity | requested_price_per_session |        │  │
│  │ requested_total_price | admin_estimated_* |                │  │
│  │ address | area_size | phone | special_requirements |      │  │
│  │ uploaded_files | status | project_id | created_at |       │  │
│  │ updated_at                                                 │  │
│  └────────────────────────────────────────────────────────────┘  │
│                                                                   │
│  wp_hamnaghsheh_order_messages                                   │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │ id | order_id | user_id | message | is_admin |            │  │
│  │ is_read | created_at                                       │  │
│  └────────────────────────────────────────────────────────────┘  │
│                                                                   │
│  wp_hamnaghsheh_order_activity                                   │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │ id | order_id | activity_type | old_value | new_value |   │  │
│  │ description | created_by | is_admin | created_at          │  │
│  └────────────────────────────────────────────────────────────┘  │
│                                                                   │
└──────────────────────────────────────────────────────────────────┘
```

## User Workflow

```
1. Browse Services
   │
   ├─→ Select Service
   │   ├─→ Choose Quantity
   │   └─→ Click Order
   │
2. Fill Order Details
   │
   ├─→ Address (required)
   ├─→ Area Size (required)
   ├─→ Phone (required)
   ├─→ Special Requirements (optional)
   └─→ Accept Disclaimer
   │
3. Submit Order
   │
   ├─→ Order Created (status: pending)
   ├─→ Activity Logged
   └─→ Notification Sent
   │
4. Track Order
   │
   ├─→ View Status
   ├─→ Read Admin Messages
   ├─→ Reply to Messages
   └─→ Accept/Reject Quote
   │
5. Payment (when status: awaiting_payment)
   │
   ├─→ External Payment Gateway
   └─→ Upload Receipt
   │
6. Access Project (when status: paid/in_progress)
   │
   └─→ View Survey Files
```

## Admin Workflow

```
1. Receive Order
   │
   ├─→ Notification
   └─→ View in Orders List
   │
2. Review Order
   │
   ├─→ Check Customer Details
   ├─→ Review Requirements
   └─→ Update Status (reviewed)
   │
3. Estimate Price
   │
   ├─→ Select Service Type
   ├─→ Set Quantity
   ├─→ Set Price (standard/custom)
   ├─→ Add Notes
   └─→ Send Quote (status: quoted)
   │
4. Wait for Customer
   │
   └─→ Customer Accepts (status: user_accepted)
   │
5. Request Payment
   │
   └─→ Update Status (awaiting_payment)
   │
6. Confirm Payment
   │
   └─→ Update Status (paid)
   │
7. Create Project
   │
   ├─→ Auto-create Project
   ├─→ Link to Order
   └─→ Update Status (in_progress)
   │
8. Complete Order
   │
   └─→ Update Status (completed)
```

## Integration Points

```
┌──────────────────────────────────────────────────────────────┐
│                    External Integrations                      │
├──────────────────────────────────────────────────────────────┤
│                                                               │
│  ┌────────────────┐  ┌────────────────┐  ┌───────────────┐ │
│  │   WordPress    │  │   Payment      │  │   Email       │ │
│  │   User System  │  │   Gateway      │  │   System      │ │
│  │                │  │                │  │               │ │
│  │  - Auth        │  │  - Pay Link    │  │  - Hooks      │ │
│  │  - Roles       │  │  - Receipt     │  │  - Templates  │ │
│  │  - Caps        │  │                │  │               │ │
│  └────────────────┘  └────────────────┘  └───────────────┘ │
│                                                               │
│  ┌──────────────────────────────────────────────────────────┐ │
│  │           Existing Project Management System             │ │
│  │                                                          │ │
│  │  - Auto-create project when order paid                  │ │
│  │  - Link order ↔ project bidirectionally                 │ │
│  │  - Access project files from order detail               │ │
│  └──────────────────────────────────────────────────────────┘ │
│                                                               │
└──────────────────────────────────────────────────────────────┘
```

## AJAX Communication

```
Frontend (orders.js)          Backend (PHP)          Database
     │                             │                      │
     ├─ submit_order ─────────────▶│                      │
     │                             ├─ validate            │
     │                             ├─ sanitize            │
     │                             └─ create ────────────▶│
     │                                                     │
     ◀─────────────────────────────┴─ success ────────────┘
     │
     ├─ send_message ──────────────▶│
     │                             ├─ check ownership     │
     │                             └─ insert ─────────────▶│
     │                                                     │
     ◀─────────────────────────────┴─ success ────────────┘
     │
     ├─ accept_quote ──────────────▶│
     │                             ├─ verify status       │
     │                             └─ update ─────────────▶│
     │                                                     │
     ◀─────────────────────────────┴─ success ────────────┘


Admin (admin-orders.js)       Backend (PHP)          Database
     │                             │                      │
     ├─ set_quote ─────────────────▶│                     │
     │                             ├─ check caps          │
     │                             ├─ calculate           │
     │                             └─ update ─────────────▶│
     │                                                     │
     ◀─────────────────────────────┴─ success ────────────┘
     │
     ├─ create_project ────────────▶│
     │                             ├─ check status        │
     │                             ├─ create project ─────▶│
     │                             ├─ link order ─────────▶│
     │                             └─ log activity ───────▶│
     │                                                     │
     ◀─────────────────────────────┴─ success ────────────┘
```

## Security Layers

```
┌──────────────────────────────────────────────────────────────┐
│                        Security Stack                         │
├──────────────────────────────────────────────────────────────┤
│                                                               │
│  Layer 1: Access Control                                     │
│  ┌────────────────────────────────────────────────────────┐  │
│  │ - Login required for all order pages                   │  │
│  │ - Auto-redirect to auth page                           │  │
│  │ - Order ownership validation                           │  │
│  └────────────────────────────────────────────────────────┘  │
│                                                               │
│  Layer 2: AJAX Security                                      │
│  ┌────────────────────────────────────────────────────────┐  │
│  │ - Nonce verification on every request                  │  │
│  │ - Capability checks (manage_options for admin)         │  │
│  │ - Action-specific validation                           │  │
│  └────────────────────────────────────────────────────────┘  │
│                                                               │
│  Layer 3: Data Security                                      │
│  ┌────────────────────────────────────────────────────────┐  │
│  │ - Input sanitization (sanitize_text_field, etc.)       │  │
│  │ - Output escaping (esc_html, esc_attr, esc_url)        │  │
│  │ - SQL injection prevention (wpdb->prepare)             │  │
│  └────────────────────────────────────────────────────────┘  │
│                                                               │
│  Layer 4: Business Logic                                     │
│  ┌────────────────────────────────────────────────────────┐  │
│  │ - Status-based action restrictions                     │  │
│  │ - Order modification rules                             │  │
│  │ - Activity logging for audit trail                     │  │
│  └────────────────────────────────────────────────────────┘  │
│                                                               │
└──────────────────────────────────────────────────────────────┘
```

## File Organization

```
hamnaghseh-PM/
│
├── includes/
│   ├── admin/                    ← Admin functionality
│   │   ├── class-admin-orders.php
│   │   └── class-admin-services.php
│   │
│   ├── class-services.php        ← Service management
│   ├── class-orders.php          ← Order management
│   ├── class-order-messages.php  ← Messaging system
│   ├── class-order-activity.php  ← Activity tracking
│   ├── class-loader.php          ← Load & initialize
│   ├── class-activator.php       ← Database setup
│   └── ...
│
├── templates/
│   ├── admin/                    ← Admin templates
│   │   ├── orders-list.php
│   │   ├── order-detail.php
│   │   └── services-settings.php
│   │
│   ├── parts/                    ← Reusable components
│   │   ├── order-card.php
│   │   ├── service-card.php
│   │   ├── order-messages.php
│   │   └── order-activity.php
│   │
│   ├── services-page.php         ← Frontend pages
│   ├── order-form.php
│   ├── my-orders.php
│   ├── order-detail.php
│   └── ...
│
├── assets/
│   ├── css/
│   │   ├── orders.css            ← Frontend styles
│   │   └── admin-orders.css      ← Admin styles
│   │
│   ├── js/
│   │   ├── orders.js             ← Frontend scripts
│   │   └── admin-orders.js       ← Admin scripts
│   │
│   └── img/
│       └── placeholder-service.jpg
│
└── hamnaghsheh.php               ← Main plugin file
```
