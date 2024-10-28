jQuery(document).ready(function($) {
    $(document).on('click', '.bcl-generate-payment-link', function() {
        const button = $(this);
        const orderId = button.data('order-id');
        const resultDiv = $('#bcl-payment-link-' + orderId);

        button.prop('disabled', true);
        resultDiv.text('Generating payment link...');

        $.ajax({
            url: bcl_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'bcl_generate_payment_link',
                order_id: orderId,
                nonce: bcl_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    resultDiv.text('Payment link generated successfully. Refreshing page...');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    resultDiv.text('Error: ' + response.data);
                    button.prop('disabled', false);
                }
            },
            error: function() {
                resultDiv.text('Ajax request failed');
                button.prop('disabled', false);
            }
        });
    });

    $(document).on('click', '.bcl-copy-payment-link', function() {
        const copyButton = $(this);
        const paymentLink = copyButton.data('payment-link');

        const tempTextArea = $('<textarea>');
        tempTextArea.text(paymentLink);
        $('body').append(tempTextArea);

        tempTextArea.select();
        document.execCommand('copy');

        tempTextArea.remove();

        const originalText = copyButton.text();
        copyButton.text('Copied!');
        copyButton.addClass('bcl-copied');

        setTimeout(function() {
            copyButton.text(originalText);
            copyButton.removeClass('bcl-copied');
        }, 2000);
    });
});