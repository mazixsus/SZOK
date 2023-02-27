$('document').ready(function () {
    if(document.getElementById('form_where_0').checked){
        document.getElementById('form_employee').disabled = false;
        document.getElementById('form_payment_1').disabled = false;
        document.getElementById('form_payment_2').disabled = false;
    } else {
        document.getElementById('form_employee').disabled = true;
        $('#form_employee').val("");
        document.getElementById('form_payment_1').disabled = true;
        document.getElementById('form_payment_2').disabled = true;
        document.getElementById('form_payment_1').checked = false;
        document.getElementById('form_payment_2').checked = false;
    }

    if(document.getElementById('form_where_1').checked){
        document.getElementById('form_payment_3').disabled = false;
    } else {
        document.getElementById('form_payment_3').disabled = true;
        document.getElementById('form_payment_3').checked = false;
    }

    $("#form_where_0").click(function () {
        if(document.getElementById('form_where_0').checked){
            document.getElementById('form_employee').disabled = false;
            document.getElementById('form_payment_1').disabled = false;
            document.getElementById('form_payment_2').disabled = false;
        } else {
            document.getElementById('form_employee').disabled = true;
            $('#form_employee').val("");
            document.getElementById('form_payment_1').disabled = true;
            document.getElementById('form_payment_2').disabled = true;
            document.getElementById('form_payment_1').checked = false;
            document.getElementById('form_payment_2').checked = false;
        }
    });

    $("#form_where_1").click(function () {
        if(document.getElementById('form_where_1').checked){
            document.getElementById('form_payment_3').disabled = false;
        } else {
            document.getElementById('form_payment_3').disabled = true;
            document.getElementById('form_payment_3').checked = false;
        }
    });
});