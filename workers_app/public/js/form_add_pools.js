function myFunction(input, checkbox ) {
    var bool = document.getElementById(checkbox).checked;
    if (bool) {
        document.getElementById(input).required = true;
        document.getElementById(input).disabled = false;
    }
    else {
        document.getElementById(input).value = '';
        document.getElementById(input).required = false;
        document.getElementById(input).disabled = true;
    }
}