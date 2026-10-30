const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

$.ajaxSetup({
    headers: {
        'X-CSRF-Token': csrfToken
    }
});
