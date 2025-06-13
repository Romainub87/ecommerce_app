import $ from 'jquery';

$(document).ready(function() {
    const url_update_cart = URL_UPDATE_CART;
    const cartFormButtons = $('#cart-form button');
    if (!cartFormButtons) return;

    cartFormButtons.on('click', function (e) {
        const target = e.target;
        if (!target.dataset.action) return;

        const productId = target.dataset.productId;
        let action = target.dataset.action;

        // Prépare les données à envoyer
        const data = new FormData();
        data.append('productId', productId);
        data.append('action', action);

        $.ajax({
            url: url_update_cart,
            type: 'POST',
            data: data,
            processData: false,
            contentType: false,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function() {
                window.location.reload();
            },
            error: function() {
                alert('Une erreur réseau est survenue');
            }
        });
    });
});

