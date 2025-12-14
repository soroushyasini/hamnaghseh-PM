<?php
if (!defined('ABSPATH'))
    exit;
?>

<div class="messages-container space-y-4 max-h-96 overflow-y-auto mb-4">
    <?php if (empty($messages)) : ?>
        <p class="text-gray-500 text-center py-4">هنوز پیامی وجود ندارد.</p>
    <?php else : ?>
        <?php foreach ($messages as $msg) : 
            $sender = get_userdata($msg->user_id);
            $is_admin_msg = $msg->is_admin == 1;
        ?>
        <div class="flex <?php echo $is_admin_msg ? 'justify-start' : 'justify-end'; ?>">
            <div class="max-w-md <?php echo $is_admin_msg ? 'bg-blue-50' : 'bg-green-50'; ?> rounded-lg p-4 shadow">
                <div class="flex items-center gap-2 mb-2">
                    <span class="font-semibold text-sm <?php echo $is_admin_msg ? 'text-blue-800' : 'text-green-800'; ?>">
                        <?php echo $is_admin_msg ? 'پشتیبانی' : 'شما'; ?>
                    </span>
                    <span class="text-xs text-gray-500">
                        <?php echo date_i18n('Y/m/d H:i', strtotime($msg->created_at)); ?>
                    </span>
                </div>
                <p class="text-gray-700 text-sm"><?php echo nl2br(esc_html($msg->message)); ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<form id="message-form" class="border-t pt-4">
    <textarea id="message-input" rows="3" required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 mb-2"
              placeholder="پیام خود را بنویسید..."></textarea>
    <button type="submit" 
            class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition-all">
        ارسال پیام
    </button>
</form>

<script>
jQuery(document).ready(function($) {
    $('#message-form').on('submit', function(e) {
        e.preventDefault();
        
        var message = $('#message-input').val();
        
        $.ajax({
            url: hamnaghsheh_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'hamnaghsheh_send_order_message',
                nonce: hamnaghsheh_ajax.nonce,
                order_id: <?php echo $order->id; ?>,
                message: message
            },
            beforeSend: function() {
                $('button[type="submit"]').prop('disabled', true).text('در حال ارسال...');
            },
            success: function(response) {
                if (response.success) {
                    $('#message-input').val('');
                    alert('پیام ارسال شد.');
                    location.reload();
                } else {
                    alert(response.data.message);
                }
                $('button[type="submit"]').prop('disabled', false).text('ارسال پیام');
            }
        });
    });
});
</script>
