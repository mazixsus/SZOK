$(document).ready(function () {
    $('.is-invalid').change(function () {
        $(this).removeClass('is-invalid');
        $(this.parent).find('.invalid-feedback').val("");
    });
});