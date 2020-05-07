var $emailInput = $('#singupForm input[name=\'email\']');
$emailInput.keyup(delay(function (e) {
    $.ajax({
        url: '/check-email/' + this.value,
        method: 'GET'
    }).then(function (data) {
        if (data.unique) {
            $('.alert-dismissible').removeClass('d-none').fadeIn();
        } else {
            $('.alert-dismissible').fadeOut("slow");
        }
    });
}, 500));

$('.alert-dismissible').on('click', function () {
    $(this).fadeOut("slow");
})

function delay(callback, ms) {
    var timer = 0;
    return function () {
        var context = this, args = arguments;
        clearTimeout(timer);
        timer = setTimeout(function () {
            callback.apply(context, args);
        }, ms || 0);
    };
}
