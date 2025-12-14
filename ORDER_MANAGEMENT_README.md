# Order Management System Documentation

## Overview

This custom order management system replaces WooCommerce for selling survey services. It integrates seamlessly with the existing project management system.

## Features

### Services
- **نقشه برداری نیم روزه** (Half-day survey) - `half_day`
- **نقشه برداری تمام روزه** (Full-day survey) - `full_day`

Each service has:
- Configurable price per session
- Image (admin can upload)
- Description
- Session-based pricing

### User Pages

#### 1. Services Page (`/services/`)
- **Shortcode:** `[hamnaghsheh_services]`
- **Access:** Login required (redirects to auth page)
- **Features:**
  - Display service cards with image, name, price
  - Quantity selector with live price calculation
  - Order button

#### 2. Order Details Page (`/order-details/`)
- **Shortcode:** `[hamnaghsheh_order_form]`
- **Access:** Login required
- **Features:**
  - Service summary
  - Address input (required)
  - Area size input (required)
  - Phone number input (required)
  - Special requirements (optional)
  - Disclaimer acceptance checkbox

#### 3. My Orders Page (`/my-orders/`)
- **Shortcode:** `[hamnaghsheh_my_orders]`
- **Access:** Login required
- **Features:**
  - List all user's orders
  - Color-coded status badges
  - Unread message notifications
  - Quick access to order details
  - Payment button when applicable

#### 4. Single Order Detail Page (`/order/?order_id=X`)
- **Shortcode:** `[hamnaghsheh_order_detail]`
- **Access:** Login required, order ownership verified
- **Features:**
  - Order summary
  - Admin re-estimation (if provided)
  - Order information display
  - Message thread with admin
  - Payment section (when status = awaiting_payment)
  - Linked project view (when available)
  - Activity timeline
  - Cancel order button

### Admin Pages

#### Orders Management (`Hamnaghsheh → Orders`)
- List all orders with filters
- View order details
- Set price quotes
- Manage order status
- Send messages to customers
- Create projects for orders

#### Services Settings (`Hamnaghsheh → Orders → Services Settings`)
- Edit service names, prices, descriptions
- Upload service images
- Enable/disable services

## Order Statuses

| Status | Label (Persian) | Color | Description |
|--------|----------------|-------|-------------|
| `pending` | در انتظار بررسی | Gray | Initial status after order submission |
| `reviewed` | در حال کارشناسی | Blue | Admin is reviewing the order |
| `quoted` | برآورد ارسال شده | Orange | Admin sent price quote |
| `user_accepted` | تایید شده | Light Green | User accepted the quote |
| `awaiting_payment` | در انتظار پرداخت | Yellow | Waiting for payment |
| `payment_uploaded` | رسید بارگذاری شده | Purple | User uploaded payment receipt |
| `paid` | پرداخت تایید شده | Green | Payment confirmed |
| `in_progress` | در حال انجام | Dark Blue | Work in progress |
| `completed` | تکمیل شده | Dark Green | Order completed |
| `cancelled` | لغو شده | Red | Order cancelled |

## Database Schema

### Tables Created
1. `wp_hamnaghsheh_services` - Service catalog
2. `wp_hamnaghsheh_orders` - Order records
3. `wp_hamnaghsheh_order_messages` - Message threads
4. `wp_hamnaghsheh_order_activity` - Activity log

### Order Number Format
`HN-{YEAR}{MONTH}-{SEQUENTIAL}`

Example: `HN-202512-0001`

## AJAX Endpoints

### Frontend
- `hamnaghsheh_submit_order` - Submit new order
- `hamnaghsheh_accept_quote` - Accept admin quote
- `hamnaghsheh_send_order_message` - Send message
- `hamnaghsheh_edit_order` - Edit pending order
- `hamnaghsheh_cancel_order` - Cancel order
- `hamnaghsheh_mark_messages_read` - Mark messages as read

### Admin
- `hamnaghsheh_admin_set_quote` - Set price quote
- `hamnaghsheh_admin_update_status` - Update order status
- `hamnaghsheh_admin_send_message` - Send message to customer
- `hamnaghsheh_admin_create_project` - Create project for order

## Email Notification Hooks

### User Notifications
- `hamnaghsheh_order_submitted` - Order confirmation
- `hamnaghsheh_quote_received` - New quote from admin
- `hamnaghsheh_payment_confirmed` - Payment accepted
- `hamnaghsheh_project_created` - Project created
- `hamnaghsheh_order_completed` - Order completed
- `hamnaghsheh_order_message` - New message from admin

### Admin Notifications
- `hamnaghsheh_new_order` - New order submitted
- `hamnaghsheh_quote_accepted` - User accepted quote
- `hamnaghsheh_payment_uploaded` - Payment receipt uploaded
- `hamnaghsheh_admin_message` - New message from user

## Integration with Existing System

### Auto-Project Creation
When order status changes to `paid`:
1. Project is created automatically
2. Project name: `سفارش #{order_number} - {service_name}`
3. Project linked to order
4. Activity logged
5. Notification sent
6. Status updated to `in_progress`

### Access Control
Protected pages automatically redirect non-logged-in users to `/auth/?redirect_to={current_page}`

## Files Structure

```
includes/
  class-services.php
  class-orders.php
  class-order-messages.php
  class-order-activity.php
  admin/
    class-admin-orders.php
    class-admin-services.php

templates/
  services-page.php
  order-form.php
  my-orders.php
  order-detail.php
  admin/
    orders-list.php
    order-detail.php
    services-settings.php
  parts/
    order-card.php
    service-card.php
    order-messages.php
    order-activity.php

assets/
  css/
    orders.css
    admin-orders.css
  js/
    orders.js
    admin-orders.js
  img/
    placeholder-service.jpg
```

## Security Features

- Nonce verification on all AJAX requests
- Capability checks for admin actions (`manage_options`)
- Input sanitization (text fields, textareas, URLs)
- Order ownership validation
- SQL injection protection via $wpdb->prepare()
- XSS prevention via esc_html(), esc_attr(), esc_url()

## Usage

### For Users

1. Navigate to `/services/`
2. Select service and quantity
3. Click "ثبت سفارش"
4. Fill in order details
5. Submit order
6. Track order status at `/my-orders/`
7. Communicate with admin via messages
8. Pay when status is "در انتظار پرداخت"
9. Access project files when ready

### For Admins

1. Go to `Hamnaghsheh → Orders`
2. View order details
3. Review customer request
4. Set price quote (optional)
5. Update order status
6. Send messages to customer
7. Create project when payment confirmed
8. Mark as completed when done

## Customization

### Adding New Services

Use the Services Settings page or insert directly into database:

```php
global $wpdb;
$wpdb->insert($wpdb->prefix . 'hamnaghsheh_services', array(
    'service_key' => 'custom_service',
    'service_name_fa' => 'خدمت سفارشی',
    'price_per_session' => 1000000,
    'description' => 'توضیحات خدمت',
    'image_url' => 'URL تصویر',
    'is_active' => 1
));
```

### Adding Email Notifications

Hook into the notification actions:

```php
add_action('hamnaghsheh_order_submitted', function($order_id) {
    // Send email
});
```

## Support

For issues or questions, contact the development team.
