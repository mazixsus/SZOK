$('document').ready(function () {
    var fields = document.getElementsByName('form[czykobieta]');

    if (fields[0].checked || fields[0].checked) {
        document.getElementById('czyKobietaControl').checked = true;
    }
    else {
        document.getElementById('czyKobietaControl').checked = false;
        fields[0].disabled = true;
        fields[1].disabled = true;
    }

    if ($('#form_staz').val()) {
        document.getElementById('stazControl').checked = true;
    }
    else {
        document.getElementById('stazControl').checked = false;
        document.getElementById('form_staz').disabled = true;
    }
});

function genderOnClick(genderCheckbox) {
    var fields = document.getElementsByName('form[czykobieta]');

    if (genderCheckbox.checked) {
        fields[0].disabled = false;
        fields[1].disabled = false;
    } else {
        fields[0].disabled = true;
        fields[0].checked = false;
        fields[1].disabled = true;
        fields[1].checked = false;
    }
}

function seniorityOnClick(seniorityCheckbox) {
    if (seniorityCheckbox.checked) {
        document.getElementById('form_staz').disabled = false;
    } else {
        $('#form_staz').val(null);
        document.getElementById('form_staz').disabled = true;
    }
}