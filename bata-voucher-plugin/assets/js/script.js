jQuery(document).ready(function($) {
    // Add to cart functionality using AJAX
    $(document).on('click', '.add-to-cart-btn', function(e) {
        e.preventDefault();
        
        const productId = $(this).data('product-id');
        const button = $(this);
        
        button.text('Adding...').prop('disabled', true);

        $.ajax({
            url: bataVoucher.ajax_url,
            type: 'POST',
            data: {
                action: 'bata_add_to_cart', // Our custom action name
                product_id: productId
            },
            success: function(response) {
                if (response.success) {
                    button.text('Added!').addClass('added');
                    
                    // Update mini-cart and cart fragments
                    $('body').trigger('added_to_cart', [response.data.fragments, response.data.cart_hash, button]);
                    
                } else {
                    button.text('Failed to add');
                    console.error('Error:', response);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                button.text('Failed to add');
            },
            complete: function() {
                setTimeout(() => {
                    button.text('Add to Cart').prop('disabled', false).removeClass('added');
                }, 2000);
            }
        });
    });
});