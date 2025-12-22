<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Email Notifications for Order Management System
 * 
 * Sends HTML-formatted RTL emails with Persian dates to admins and users
 * at key order lifecycle stages.
 */
class Hamnaghsheh_Email_Notifications
{
    /**
     * Enable/disable specific notifications
     * Set to false to disable a notification type
     */
    private $notifications_enabled = array(
        'admin_new_order' => true,        // Admin: New order received
        'user_order_confirmed' => true,   // User: Order confirmation
        'user_price_set' => true,         // User: Price quote ready
        'user_payment_confirmed' => true, // User: Payment confirmed
        'user_project_started' => true,   // User: Project started
        'user_project_completed' => true, // User: Project completed
    );

    /**
     * Constructor - Hook into action hooks
     */
    public function __construct()
    {
        // Admin notification
        add_action('hamnaghsheh_new_order', array($this, 'send_admin_new_order_email'));

        // User notifications
        add_action('hamnaghsheh_new_order', array($this, 'send_user_order_confirmed_email'));
        add_action('hamnaghsheh_price_set', array($this, 'send_user_price_set_email'));
        add_action('hamnaghsheh_payment_confirmed', array($this, 'send_user_payment_confirmed_email'));
        add_action('hamnaghsheh_project_created', array($this, 'send_user_project_started_email'));
        add_action('hamnaghsheh_order_completed', array($this, 'send_user_project_completed_email'));
    }

    /**
     * Convert Gregorian date to Persian (Jalali) date
     */
    private function gregorian_to_jalali($gy, $gm, $gd)
    {
        $g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
        $gy2 = ($gm > 2) ? ($gy + 1) : $gy;
        $days = 355666 + (365 * $gy) + (int)(($gy2 + 3) / 4) - (int)(($gy2 + 99) / 100) + (int)(($gy2 + 399) / 400) + $gd + $g_d_m[$gm - 1];
        $jy = -1595 + (33 * (int)($days / 12053));
        $days %= 12053;
        $jy += 4 * (int)($days / 1461);
        $days %= 1461;
        if ($days > 365) {
            $jy += (int)(($days - 1) / 365);
            $days = ($days - 1) % 365;
        }
        $jm = ($days < 186) ? 1 + (int)($days / 31) : 7 + (int)(($days - 186) / 30);
        $jd = 1 + (($days < 186) ? ($days % 31) : (($days - 186) % 30));
        return [$jy, $jm, $jd];
    }

    /**
     * Format datetime as Persian date and time
     */
    private function format_persian_date($datetime)
    {
        $timestamp = strtotime($datetime);
        list($gy, $gm, $gd) = explode('-', date('Y-m-d', $timestamp));
        list($jy, $jm, $jd) = $this->gregorian_to_jalali($gy, $gm, $gd);

        $farsi_digits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $english_digits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        // Format date
        $date = sprintf('%04d/%02d/%02d', $jy, $jm, $jd);
        $date = str_replace($english_digits, $farsi_digits, $date);

        // Format time
        $time = date('H:i', $timestamp);
        $time = str_replace($english_digits, $farsi_digits, $time);

        return $date . ' - ' . $time;
    }

    /**
     * Format price in Toman
     */
    private function format_price($price)
    {
        $farsi_digits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $english_digits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        
        $formatted = number_format($price, 0, '.', ',');
        $formatted = str_replace($english_digits, $farsi_digits, $formatted);
        
        return $formatted . ' تومان';
    }

    /**
     * Get HTML email template
     */
    private function get_email_template($title, $content, $color = '#2563eb')
    {
        return '
<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Tahoma, Arial, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
            direction: rtl;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .header {
            background-color: ' . $color . ';
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 30px 20px;
            color: #1f2937;
            line-height: 1.8;
        }
        .info-box {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
        }
        .info-row {
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: bold;
            color: #4b5563;
            display: inline-block;
            width: 120px;
        }
        .info-value {
            color: #1f2937;
        }
        .button {
            display: inline-block;
            background-color: ' . $color . ';
            color: #ffffff;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 6px;
            margin: 20px 0;
            font-weight: bold;
        }
        .footer {
            background-color: #f9fafb;
            padding: 20px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>' . $title . '</h1>
        </div>
        <div class="content">
            ' . $content . '
        </div>
        <div class="footer">
            <p>' . get_bloginfo('name') . '</p>
            <p>این ایمیل به صورت خودکار ارسال شده است.</p>
        </div>
    </div>
</body>
</html>';
    }

    /**
     * Get order details by ID
     */
    private function get_order_details($order_id)
    {
        $order = Hamnaghsheh_Orders::get_order_by_id($order_id);
        if (!$order) {
            return null;
        }

        $service = Hamnaghsheh_Services::get_service_by_key($order->service_type);
        $user = get_userdata($order->user_id);

        return array(
            'order' => $order,
            'service' => $service,
            'user' => $user
        );
    }

    /**
     * Admin: New Order Received
     */
    public function send_admin_new_order_email($order_id)
    {
        if (!$this->notifications_enabled['admin_new_order']) {
            return;
        }

        $details = $this->get_order_details($order_id);
        if (!$details) {
            return;
        }

        $order = $details['order'];
        $service = $details['service'];
        $user = $details['user'];

        $admin_email = get_option('admin_email');
        $site_name = get_bloginfo('name');
        $order_url = esc_url(admin_url('admin.php?page=hamnaghsheh-order-detail&order_id=' . intval($order_id)));

        $subject = sprintf('[%s] 🔔 سفارش جدید - %s', $site_name, $order->order_number);

        $content = '
            <p>سلام،</p>
            <p>یک سفارش جدید ثبت شده است:</p>
            
            <div class="info-box">
                <div class="info-row">
                    <span class="info-label">شماره سفارش:</span>
                    <span class="info-value">' . $order->order_number . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">نام مشتری:</span>
                    <span class="info-value">' . esc_html($user->display_name) . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">تلفن:</span>
                    <span class="info-value">' . esc_html($order->phone) . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">ایمیل:</span>
                    <span class="info-value">' . esc_html($user->user_email) . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">خدمات:</span>
                    <span class="info-value">' . $service->service_name_fa . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">تعداد جلسات:</span>
                    <span class="info-value">' . $order->requested_quantity . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">آدرس:</span>
                    <span class="info-value">' . $order->address . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">متراژ:</span>
                    <span class="info-value">' . $order->area_size . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">قیمت درخواستی:</span>
                    <span class="info-value">' . $this->format_price($order->requested_total_price) . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">تاریخ ثبت:</span>
                    <span class="info-value">' . $this->format_persian_date($order->created_at) . '</span>
                </div>
            </div>';

        if (!empty($order->special_requirements)) {
            $content .= '
            <div class="info-box">
                <strong>توضیحات خاص:</strong>
                <p>' . nl2br(esc_html($order->special_requirements)) . '</p>
            </div>';
        }

        $content .= '
            <p style="text-align: center;">
                <a href="' . $order_url . '" class="button">مشاهده جزئیات سفارش</a>
            </p>';

        $message = $this->get_email_template('🔔 سفارش جدید', $content, '#2563eb');

        // Set headers for HTML email
        $headers = array('Content-Type: text/html; charset=UTF-8');

        wp_mail($admin_email, $subject, $message, $headers);
    }

    /**
     * User: Order Confirmed
     */
    public function send_user_order_confirmed_email($order_id)
    {
        if (!$this->notifications_enabled['user_order_confirmed']) {
            return;
        }

        $details = $this->get_order_details($order_id);
        if (!$details) {
            return;
        }

        $order = $details['order'];
        $service = $details['service'];
        $user = $details['user'];

        $site_name = get_bloginfo('name');
        $order_url = esc_url(home_url('/order-details/?order_id=' . intval($order_id)));

        $subject = sprintf('[%s] ✅ سفارش شما ثبت شد - %s', $site_name, $order->order_number);

        $content = '
            <p>سلام ' . esc_html($user->display_name) . '،</p>
            <p>سفارش شما با موفقیت ثبت شد و در حال بررسی توسط تیم ما است.</p>
            
            <div class="info-box">
                <div class="info-row">
                    <span class="info-label">شماره سفارش:</span>
                    <span class="info-value">' . $order->order_number . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">خدمات:</span>
                    <span class="info-value">' . $service->service_name_fa . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">تعداد جلسات:</span>
                    <span class="info-value">' . $order->requested_quantity . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">قیمت درخواستی:</span>
                    <span class="info-value">' . $this->format_price($order->requested_total_price) . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">تاریخ ثبت:</span>
                    <span class="info-value">' . $this->format_persian_date($order->created_at) . '</span>
                </div>
            </div>
            
            <p>به زودی تیم ما با شما تماس خواهد گرفت و قیمت نهایی را اعلام خواهد کرد.</p>
            
            <p style="text-align: center;">
                <a href="' . $order_url . '" class="button">مشاهده وضعیت سفارش</a>
            </p>';

        $message = $this->get_email_template('✅ سفارش ثبت شد', $content, '#10b981');

        $headers = array('Content-Type: text/html; charset=UTF-8');

        wp_mail($user->user_email, $subject, $message, $headers);
    }

    /**
     * User: Price Quote Ready
     */
    public function send_user_price_set_email($order_id)
    {
        if (!$this->notifications_enabled['user_price_set']) {
            return;
        }

        $details = $this->get_order_details($order_id);
        if (!$details) {
            return;
        }

        $order = $details['order'];
        $service = $details['service'];
        $user = $details['user'];

        $site_name = get_bloginfo('name');
        $order_url = esc_url(home_url('/order-details/?order_id=' . intval($order_id)));

        $subject = sprintf('[%s] 💰 قیمت سفارش شما تعیین شد - %s', $site_name, $order->order_number);

        $content = '
            <p>سلام ' . esc_html($user->display_name) . '،</p>
            <p>قیمت نهایی سفارش شما توسط تیم ما تعیین شد:</p>
            
            <div class="info-box">
                <div class="info-row">
                    <span class="info-label">شماره سفارش:</span>
                    <span class="info-value">' . $order->order_number . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">خدمات:</span>
                    <span class="info-value">' . $service->service_name_fa . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">قیمت نهایی:</span>
                    <span class="info-value" style="font-size: 18px; font-weight: bold; color: #059669;">' . $this->format_price($order->final_price) . '</span>
                </div>
            </div>';

        if (!empty($order->admin_notes)) {
            $content .= '
            <div class="info-box">
                <strong>توضیحات تیم ما:</strong>
                <p>' . nl2br(esc_html($order->admin_notes)) . '</p>
            </div>';
        }

        $content .= '
            <p>لطفاً برای پرداخت و ادامه فرآیند به صفحه سفارش خود مراجعه کنید.</p>
            
            <p style="text-align: center;">
                <a href="' . $order_url . '" class="button">مشاهده جزئیات و پرداخت</a>
            </p>';

        $message = $this->get_email_template('💰 قیمت تعیین شد', $content, '#f59e0b');

        $headers = array('Content-Type: text/html; charset=UTF-8');

        wp_mail($user->user_email, $subject, $message, $headers);
    }

    /**
     * User: Payment Confirmed
     */
    public function send_user_payment_confirmed_email($order_id)
    {
        if (!$this->notifications_enabled['user_payment_confirmed']) {
            return;
        }

        $details = $this->get_order_details($order_id);
        if (!$details) {
            return;
        }

        $order = $details['order'];
        $service = $details['service'];
        $user = $details['user'];

        $site_name = get_bloginfo('name');
        $order_url = esc_url(home_url('/order-details/?order_id=' . intval($order_id)));

        $subject = sprintf('[%s] ✅ پرداخت شما تایید شد - %s', $site_name, $order->order_number);

        $content = '
            <p>سلام ' . esc_html($user->display_name) . '،</p>
            <p>پرداخت شما با موفقیت تایید شد و سفارش شما در صف اجرا قرار گرفت.</p>
            
            <div class="info-box">
                <div class="info-row">
                    <span class="info-label">شماره سفارش:</span>
                    <span class="info-value">' . $order->order_number . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">خدمات:</span>
                    <span class="info-value">' . $service->service_name_fa . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">مبلغ پرداختی:</span>
                    <span class="info-value">' . $this->format_price($order->final_price) . '</span>
                </div>
            </div>
            
            <p>به زودی پروژه شما ایجاد خواهد شد و تیم ما شروع به کار خواهد کرد.</p>
            
            <p style="text-align: center;">
                <a href="' . $order_url . '" class="button">مشاهده وضعیت سفارش</a>
            </p>';

        $message = $this->get_email_template('✅ پرداخت تایید شد', $content, '#10b981');

        $headers = array('Content-Type: text/html; charset=UTF-8');

        wp_mail($user->user_email, $subject, $message, $headers);
    }

    /**
     * User: Project Started
     */
    public function send_user_project_started_email($order_id)
    {
        if (!$this->notifications_enabled['user_project_started']) {
            return;
        }

        $details = $this->get_order_details($order_id);
        if (!$details) {
            return;
        }

        $order = $details['order'];
        $service = $details['service'];
        $user = $details['user'];

        $site_name = get_bloginfo('name');
        $project_url = esc_url(home_url('/project/' . intval($order->project_id)));

        $subject = sprintf('[%s] 🚀 پروژه شما شروع شد - %s', $site_name, $order->order_number);

        $content = '
            <p>سلام ' . esc_html($user->display_name) . '،</p>
            <p>خبر خوب! پروژه شما ایجاد شد و تیم ما شروع به کار کرده است.</p>
            
            <div class="info-box">
                <div class="info-row">
                    <span class="info-label">شماره سفارش:</span>
                    <span class="info-value">' . $order->order_number . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">خدمات:</span>
                    <span class="info-value">' . $service->service_name_fa . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">شناسه پروژه:</span>
                    <span class="info-value">' . $order->project_id . '</span>
                </div>
            </div>
            
            <p>شما می‌توانید پیشرفت کار و فایل‌های پروژه را در صفحه پروژه مشاهده کنید.</p>
            
            <p style="text-align: center;">
                <a href="' . $project_url . '" class="button">مشاهده پروژه</a>
            </p>';

        $message = $this->get_email_template('🚀 پروژه شروع شد', $content, '#8b5cf6');

        $headers = array('Content-Type: text/html; charset=UTF-8');

        wp_mail($user->user_email, $subject, $message, $headers);
    }

    /**
     * User: Project Completed
     */
    public function send_user_project_completed_email($order_id)
    {
        if (!$this->notifications_enabled['user_project_completed']) {
            return;
        }

        $details = $this->get_order_details($order_id);
        if (!$details) {
            return;
        }

        $order = $details['order'];
        $service = $details['service'];
        $user = $details['user'];

        $site_name = get_bloginfo('name');
        $project_url = esc_url(home_url('/project/' . intval($order->project_id)));

        $subject = sprintf('[%s] 🎉 پروژه شما تکمیل شد - %s', $site_name, $order->order_number);

        $content = '
            <p>سلام ' . esc_html($user->display_name) . '،</p>
            <p>با خوشحالی اعلام می‌کنیم که پروژه شما تکمیل شد! 🎉</p>
            
            <div class="info-box">
                <div class="info-row">
                    <span class="info-label">شماره سفارش:</span>
                    <span class="info-value">' . $order->order_number . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">خدمات:</span>
                    <span class="info-value">' . $service->service_name_fa . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">شناسه پروژه:</span>
                    <span class="info-value">' . $order->project_id . '</span>
                </div>
            </div>
            
            <p>تمامی فایل‌های نهایی در صفحه پروژه شما آماده دانلود است.</p>
            <p>از اینکه ما را برای انجام این پروژه انتخاب کردید متشکریم!</p>
            
            <p style="text-align: center;">
                <a href="' . $project_url . '" class="button">مشاهده و دانلود فایل‌ها</a>
            </p>';

        $message = $this->get_email_template('🎉 پروژه تکمیل شد', $content, '#ec4899');

        $headers = array('Content-Type: text/html; charset=UTF-8');

        wp_mail($user->user_email, $subject, $message, $headers);
    }
}
