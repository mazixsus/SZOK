$(document).ready(function () {
    $('.is-invalid').change(function () {
        $(this).removeClass('is-invalid');
        $(this).parent().find('div.invalid-feedback').empty();
    });
});