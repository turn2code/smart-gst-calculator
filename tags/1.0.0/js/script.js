jQuery(document).ready(function($) {
    $('#calculate_gst').on('click', function(e) {
        e.preventDefault();
        
        var originalPrice = parseFloat($('#original_price').val());
        var gstRate = parseFloat($('#gst_rate').val());
        var transactionType = $('#transaction_type').val();
        
        if (isNaN(originalPrice) || originalPrice <= 0) {
            alert('Please enter a valid original price');
            return;
        }
        
        // Show loading state
        $(this).html('<i class="fas fa-spinner fa-spin"></i> Calculating...');
        
        // AJAX request
        $.ajax({
            url: gst_calculator_ajax.ajaxurl,
            type: 'POST',
            data: {
                action: 'gst_calculator_calculate',
                original_price: originalPrice,
                gst_rate: gstRate,
                transaction_type: transactionType,
                nonce: gst_calculator_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Update results
                    $('#original_price_result').text('₹' + response.original_price);
                    $('#gst_rate_result').text(response.gst_rate + '%');
                    $('#gst_amount').text('₹' + response.gst_amount);
                    $('#total_price').text('₹' + response.total_price);
                    
                    // Update based on transaction type
                    if (transactionType === 'intra') {
                        $('#cgst_amount').text('₹' + response.cgst_amount);
                        $('#sgst_amount').text('₹' + response.sgst_amount);
                        $('#igst_amount').text('₹0.00');
                        
                        $('#cgst_container').show();
                        $('#sgst_container').show();
                        $('#igst_container').hide();
                    } else {
                        $('#igst_amount').text('₹' + response.igst_amount);
                        
                        $('#cgst_container').hide();
                        $('#sgst_container').hide();
                        $('#igst_container').show();
                    }
                    
                    // Show results
                    $('.gst-results').fadeIn();
                } else {
                    alert('Error in calculation. Please try again.');
                }
            },
            error: function() {
                alert('Error connecting to server. Please try again.');
            },
            complete: function() {
                // Reset button
                $('#calculate_gst').html('<i class="fas fa-calculator"></i> Calculate GST');
            }
        });
    });
    
    // Toggle CGST/SGST/IGST display based on transaction type
    $('#transaction_type').on('change', function() {
        if ($(this).val() === 'intra') {
            $('#cgst_container').show();
            $('#sgst_container').show();
            $('#igst_container').hide();
        } else {
            $('#cgst_container').hide();
            $('#sgst_container').hide();
            $('#igst_container').show();
        }
    });
    
    // Initialize display
    $('#cgst_container').show();
    $('#sgst_container').show();
    $('#igst_container').hide();
});