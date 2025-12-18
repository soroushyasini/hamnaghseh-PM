/**
 * Frontend Order Management JavaScript
 */

(function($) {
    'use strict';

    // Helper function to format numbers in Persian
    function formatPersianNumber(num) {
        const persianDigits = '۰۱۲۳۴۵۶۷۸۹';
        return num.toString().replace(/\d/g, x => persianDigits[x]);
    }

    // Helper function to show loading state
    function setLoading(element, isLoading) {
        if (isLoading) {
            element.addClass('loading').prop('disabled', true);
            element.data('original-text', element.text());
            element.text('در حال پردازش...');
        } else {
            element.removeClass('loading').prop('disabled', false);
            if (element.data('original-text')) {
                element.text(element.data('original-text'));
            }
        }
    }

    // Services Page: Quantity controls
    $(document).on('click', '.quantity-minus', function() {
        const input = $(this).siblings('.quantity-input');
        const val = parseInt(input.val());
        if (val > 1) {
            input.val(val - 1).trigger('change');
        }
    });

    $(document).on('click', '.quantity-plus', function() {
        const input = $(this).siblings('.quantity-input');
        const val = parseInt(input.val());
        input.val(val + 1).trigger('change');
    });

    // Update total price
    $(document).on('change', '.quantity-input', function() {
        const card = $(this).closest('.service-card');
        const quantity = parseInt($(this).val());
        const price = parseFloat(card.data('price'));
        const total = quantity * price;
        
        card.find('.total-price').text(total.toLocaleString('fa-IR'));
    });

    // Form validation helper
    function validateForm(formElement) {
        let isValid = true;
        const requiredFields = formElement.find('[required]');

        requiredFields.each(function() {
            const field = $(this);
            if (!field.val() || field.val().trim() === '') {
                isValid = false;
                field.addClass('border-red-500');
            } else {
                field.removeClass('border-red-500');
            }
        });

        if (!isValid) {
            alert('لطفاً تمام فیلدهای الزامی را پر کنید.');
        }

        return isValid;
    }

    // Phone number validation (Iranian format)
    function validatePhone(phone) {
        const phoneRegex = /^09[0-9]{9}$/;
        return phoneRegex.test(phone);
    }

    // Order form validation
    if ($('#order-form').length) {
        $('#order-form').on('submit', function(e) {
            const phone = $('#phone').val();
            if (phone && !validatePhone(phone)) {
                e.preventDefault();
                alert('لطفاً شماره تلفن همراه معتبر وارد کنید (مثال: 09123456789)');
                $('#phone').addClass('border-red-500').focus();
                return false;
            }
        });
    }

    // REMOVED: Auto-scroll messages - no messaging in simplified version
    // REMOVED: User cancel order - admin only in simplified version

    // Real-time character counter for textareas
    $('textarea[maxlength]').each(function() {
        const textarea = $(this);
        const maxLength = textarea.attr('maxlength');
        const counter = $('<div class="text-sm text-gray-500 mt-1"></div>');
        textarea.after(counter);

        function updateCounter() {
            const remaining = maxLength - textarea.val().length;
            counter.text(remaining + ' کاراکتر باقیمانده');
        }

        textarea.on('input', updateCounter);
        updateCounter();
    });

    // Smooth scroll to element
    function scrollToElement(element, offset = 100) {
        if (element.length) {
            $('html, body').animate({
                scrollTop: element.offset().top - offset
            }, 500);
        }
    }

    // Show success message
    function showSuccessMessage(message) {
        const alertDiv = $('<div class="fixed top-4 left-1/2 transform -translate-x-1/2 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50"></div>');
        alertDiv.text(message);
        $('body').append(alertDiv);
        
        setTimeout(function() {
            alertDiv.fadeOut(function() {
                $(this).remove();
            });
        }, 3000);
    }

    // Show error message
    function showErrorMessage(message) {
        const alertDiv = $('<div class="fixed top-4 left-1/2 transform -translate-x-1/2 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50"></div>');
        alertDiv.text(message);
        $('body').append(alertDiv);
        
        setTimeout(function() {
            alertDiv.fadeOut(function() {
                $(this).remove();
            });
        }, 3000);
    }

    // Initialize tooltips if needed
    $('[data-tooltip]').each(function() {
        const element = $(this);
        const tooltipText = element.data('tooltip');
        
        element.hover(
            function() {
                const tooltip = $('<div class="tooltip-popup"></div>').text(tooltipText);
                $('body').append(tooltip);
                
                const offset = element.offset();
                tooltip.css({
                    top: offset.top - tooltip.outerHeight() - 10,
                    left: offset.left + (element.outerWidth() / 2) - (tooltip.outerWidth() / 2)
                });
            },
            function() {
                $('.tooltip-popup').remove();
            }
        );
    });

    // Prevent double submission
    $('form').on('submit', function() {
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        
        if (form.data('submitting')) {
            return false;
        }
        
        form.data('submitting', true);
        setLoading(submitBtn, true);
        
        setTimeout(function() {
            form.data('submitting', false);
            setLoading(submitBtn, false);
        }, 3000);
    });

})(jQuery);
