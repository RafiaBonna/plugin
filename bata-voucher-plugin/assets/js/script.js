jQuery(document).ready(function($) {
    $('.add-to-cart-btn').on('click', function(e) {
        e.preventDefault();
        
        var product_id = $(this).data('product-id');
        
        $.ajax({
            type: 'POST',
            url: bataVoucher.ajax_url,
            data: {
                action: 'bata_add_to_cart',
                product_id: product_id
            },
            success: function(response) {
                if (response.success) {
                    console.log('Product added to cart!');
                    $(document.body).trigger('wc_fragment_refresh');
                    alert('Product added to cart!');
                } else {
                    alert('Failed to add product to cart.');
                }
            },
            error: function() {
                alert('An error occurred.');
            }
        });
    });
});