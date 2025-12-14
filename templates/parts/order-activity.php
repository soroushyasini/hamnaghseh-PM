<?php
if (!defined('ABSPATH'))
    exit;
?>

<div class="space-y-3">
    <?php if (empty($activity)) : ?>
        <p class="text-gray-500 text-sm">بدون فعالیت</p>
    <?php else : ?>
        <?php foreach ($activity as $act) : 
            $user = $act->created_by ? get_userdata($act->created_by) : null;
            $activity_label = Hamnaghsheh_Order_Activity::get_activity_label($act->activity_type);
        ?>
        <div class="border-r-4 <?php echo $act->is_admin ? 'border-blue-500' : 'border-green-500'; ?> pr-3">
            <div class="text-sm font-semibold text-gray-700">
                <?php echo esc_html($activity_label); ?>
            </div>
            <?php if ($act->description) : ?>
                <div class="text-xs text-gray-600 mb-1">
                    <?php echo esc_html($act->description); ?>
                </div>
            <?php endif; ?>
            <div class="text-xs text-gray-500">
                <?php echo date_i18n('Y/m/d H:i', strtotime($act->created_at)); ?>
                <?php if ($user) : ?>
                    - <?php echo $act->is_admin ? 'ادمین' : esc_html($user->display_name); ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
