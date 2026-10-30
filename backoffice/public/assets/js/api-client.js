// Cross-origin API calls need browser credentials enabled for HTTP Basic Auth.
$.ajaxSetup({
    xhrFields: {
        withCredentials: true
    }
});
