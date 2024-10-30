(function ($, global) {
    $(document).ready(function() {
        var meta_boxes = global.woocommerce_admin_meta_boxes;
        if (meta_boxes) {
            var ajax_url = meta_boxes.ajax_url;
            var order_id = meta_boxes.post_id;

            $('.button.void-transaction').on('click', function(event) {
                var data = {
                    action: 'woocommerce_void_order',
                    order_id: order_id
                };

                $.post(ajax_url, data, function( response ) {
                    if ( true === response.success ) {
                        // Reload to update status
                        window.location.href = window.location.href;
                    } else {
                        alert( 'Failed to void order #' + order_id + '(error: ' + response.data.error + ')' );
                    }
                })

                event.preventDefault();
            });
        }
    })
}(jQuery, window));