require('jquery-ujs');


$(function () {
    $(document)
        .on('pjax:click', '[data-remote]', function(event, options) {
            event.preventDefault();
            // console.log(options.container.context.URL);
        })
        .on('ajax:success', '[data-alter]', function(event, data, status, xhr) {
            if(data.success)
                toastr.success(data.message, null, {timeOut: 5000})
            else
                toastr.error(data.message, null, {timeOut: 5000})
        })
        .on('ajax:success', '[data-refresh]', function(event, data, status, xhr) {
            let r = parseInt($(this).data('refresh'));
            if(isNaN(r))
                r = 0;
            setTimeout(() => {
                window.location.reload();
            }, r);
        })
});
