$(document).ready(function() {
    $('[id^="payment-form-"]').hide();

    $('input[name="payment_method"]').on('change', function () {
        $('input[name="payment_method"]').prop('checked', false);
        $(this).prop('checked', true);
        const selectedId = $(this).val();
        showPaymentForm(selectedId);
    });

    const initialSelected = $('input[name="payment_method"]:checked').val();
    if (initialSelected) {
        showPaymentForm(initialSelected);
    }

    function showPaymentForm(selectedId) {
        // Masquer tous les formulaires et retirer required de leurs champs
        $('[id^="payment-form-"]').each(function() {
            $(this).hide();
            $(this).find('[required]').each(function() {
                $(this).attr('data-required', 'true');
                $(this).removeAttr('required');
            });
        });
        // Afficher le formulaire sélectionné et remettre required sur ses champs
        const $form = $('#payment-form-' + selectedId);
        $form.show();
        $form.find('[data-required]').each(function() {
            $(this).attr('required', 'required');
        });
    }
});
