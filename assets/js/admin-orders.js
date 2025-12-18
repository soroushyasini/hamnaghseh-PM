/**
 * Admin Order Management JavaScript
 */

(function($) {
    'use strict';

    // Helper function to format numbers
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    // Set loading state for buttons
    function setButtonLoading(button, isLoading, originalText) {
        if (isLoading) {
            button.prop('disabled', true);
            button.data('original-text', button.text());
            button.text('در حال پردازش...');
        } else {
            button.prop('disabled', false);
            button.text(originalText || button.data('original-text') || button.text());
        }
    }

    // Confirm dialog wrapper
    function confirmAction(message) {
        return confirm(message || 'آیا از انجام این عملیات اطمینان دارید؟');
    }

    // Show admin notification
    function showNotification(message, type) {
        const noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
        const notice = $('<div class="notice ' + noticeClass + ' is-dismissible"><p>' + message + '</p></div>');
        
        $('.wrap > h1').after(notice);
        
        setTimeout(function() {
            notice.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }

    // AJAX error handler
    function handleAjaxError(xhr, status, error) {
        console.error('AJAX Error:', status, error);
        showNotification('خطا در ارتباط با سرور. لطفاً دوباره تلاش کنید.', 'error');
    }

    // REMOVED: Real-time price calculation in admin quote form - simplified version uses simple price input

    // Bulk actions for orders list
    if ($('.wp-list-table').length) {
        $('#doaction, #doaction2').on('click', function(e) {
            const action = $(this).siblings('select[name="action"]').val();
            
            if (action === '-1') {
                e.preventDefault();
                return false;
            }

            const checkedBoxes = $('.wp-list-table tbody input[type="checkbox"]:checked');
            
            if (checkedBoxes.length === 0) {
                e.preventDefault();
                alert('لطفاً حداقل یک سفارش را انتخاب کنید.');
                return false;
            }

            if (!confirmAction('آیا از انجام این عملیات روی ' + checkedBoxes.length + ' سفارش اطمینان دارید؟')) {
                e.preventDefault();
                return false;
            }
        });
    }

    // REMOVED: Auto-refresh unread message count - no messaging in simplified version

    // Quick status change from orders list
    $(document).on('click', '.quick-status-change', function(e) {
        e.preventDefault();
        
        const orderId = $(this).data('order-id');
        const newStatus = $(this).data('status');
        
        if (!confirmAction('آیا از تغییر وضعیت این سفارش اطمینان دارید؟')) {
            return false;
        }

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'hamnaghsheh_admin_update_status',
                nonce: $(this).data('nonce'),
                order_id: orderId,
                status: newStatus
            },
            beforeSend: function() {
                showNotification('در حال بروزرسانی...', 'info');
            },
            success: function(response) {
                if (response.success) {
                    showNotification(response.data.message, 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    showNotification(response.data.message, 'error');
                }
            },
            error: handleAjaxError
        });
    });

    // Export orders to CSV
    $(document).on('click', '#export-orders-csv', function(e) {
        e.preventDefault();
        
        const filters = {
            status: $('select[name="status"]').val(),
            service_type: $('select[name="service_type"]').val(),
            search: $('input[name="s"]').val()
        };

        const queryString = $.param(filters);
        window.location.href = ajaxurl + '?action=hamnaghsheh_export_orders&' + queryString;
    });

    // REMOVED: Scroll to latest message - no messaging in simplified version

    // Highlight changed fields
    $('.admin-order-detail-grid input, .admin-order-detail-grid textarea, .admin-order-detail-grid select')
        .on('change', function() {
            $(this).addClass('field-changed');
        });

    // Keyboard shortcuts
    $(document).on('keydown', function(e) {
        // Ctrl/Cmd + S to save (prevent default browser save)
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            const activeForm = $('form:visible').first();
            if (activeForm.length) {
                activeForm.submit();
            }
        }

        // ESC to close modals
        if (e.key === 'Escape') {
            $('.modal-overlay').fadeOut();
        }
    });

    // Copy order number to clipboard
    $(document).on('click', '.copy-order-number', function(e) {
        e.preventDefault();
        
        const orderNumber = $(this).data('order-number');
        const tempInput = $('<input>');
        $('body').append(tempInput);
        tempInput.val(orderNumber).select();
        document.execCommand('copy');
        tempInput.remove();
        
        showNotification('شماره سفارش کپی شد: ' + orderNumber, 'success');
    });

    // Auto-save draft notes
    let autoSaveTimer;
    $('textarea[name="admin_notes"]').on('input', function() {
        clearTimeout(autoSaveTimer);
        const notes = $(this).val();
        const orderId = $('input[name="order_id"]').val();
        
        autoSaveTimer = setTimeout(function() {
            // Auto-save to localStorage
            localStorage.setItem('admin_notes_draft_' + orderId, notes);
        }, 1000);
    });

    // Restore draft notes on page load
    if ($('textarea[name="admin_notes"]').length) {
        const orderId = $('input[name="order_id"]').val();
        const savedNotes = localStorage.getItem('admin_notes_draft_' + orderId);
        
        if (savedNotes && !$('textarea[name="admin_notes"]').val()) {
            if (confirm('یک پیش‌نویس ذخیره شده برای این سفارش یافت شد. آیا می‌خواهید آن را بازیابی کنید؟')) {
                $('textarea[name="admin_notes"]').val(savedNotes);
            } else {
                localStorage.removeItem('admin_notes_draft_' + orderId);
            }
        }
    }

    // Clear draft after successful submission
    $(document).on('ajaxSuccess', function(event, xhr, settings) {
        // REMOVED: hamnaghsheh_admin_set_quote - now uses hamnaghsheh_admin_set_price
        if (settings.data && settings.data.indexOf('hamnaghsheh_admin_set_price') > -1) {
            const orderId = $('input[name="order_id"]').val();
            localStorage.removeItem('admin_notes_draft_' + orderId);
        }
    });

    // Initialize on document ready
    $(document).ready(function() {
        // Add confirmation to delete actions
        $('.delete-order').on('click', function(e) {
            if (!confirmAction('آیا از حذف این سفارش اطمینان دارید؟ این عملیات قابل بازگشت نیست.')) {
                e.preventDefault();
                return false;
            }
        });

        // Enhance select2 for better UX (if available)
        if ($.fn.select2) {
            $('select[name="status"], select[name="service_type"]').select2({
                width: '200px',
                dir: 'rtl'
            });
        }
    });

})(jQuery);
