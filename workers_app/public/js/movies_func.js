var $collectionHolder;
var $collectionValues;

var $addMovieButton = $('<button type="button" class="btn btn-primary mt-2 mr-2">Dodaj film</button>');
var $deleteLastMovieButton = $('<button id="deleteLastMovieButton" type="button" class="btn btn-danger mt-2">Usuń ostatni film</button>');

$(document).ready(function () {
    $('#form_poczatekseansu_time').children('select').attr('class', 'form-control mx-2').css('width', '4.3rem');

    $('#smf_group').on('click', function () {
        $(this).children('input').removeClass('is-invalid');
        $(this).children('.invalid-feedback').val("");
    });

    $collectionHolder = $('#form_seansMaFilmy');
    $collectionHolder.empty();
    $collectionHolder.css('padding-right', '1rem');

    $collectionHolder.data('index', $collectionHolder.find(':input').length);

    $addMovieButton.on('click', function (e) {
        addMoiveForm($collectionHolder, null);
    });
    $deleteLastMovieButton.on('click', function (e) {
        deleteMovieForm($collectionHolder);
    });

    $collectionValues = $('#form_collectionValues');
    recreateValues($collectionValues);
});

function recreateValues($collectionValues) {
    var string = $collectionValues.val();
    if (string.indexOf("/")) {
        var option = $('#form_wydarzeniaspecjalne').val();
        var values = string.split("/");
        $collectionValues.val(null);

        for (var i = 0; i < values.length; i++) {
            addMoiveForm($collectionHolder, values[i]);
        }
        $('#form_wydarzeniaspecjalne').val(option);
    } else {
        addMoiveForm($collectionHolder, string);
    }
}

function addMoiveForm($collectionHolder, val) {
    var prototype = $collectionHolder.data('prototype');
    var index = $collectionHolder.data('index');

    var $newForm = $((prototype.replace(/__name__/g, index)).replace(/__display_name__/g, index + 1));

    $newForm.addClass('row mb-2 movieCollection');
    $newForm.children('label').addClass('col-1');
    $newForm.children('.form-control').addClass('col-11');
    $newForm.children('.form-control').data('index', index);
    if (val == null || val == '') {
        val = $newForm.children('.form-control').children('option:first').val();
    }
    $newForm.children('.form-control').val(val);

    $newForm.children('.form-control').on('change', function () {
        var globalIndex = $collectionHolder.data('index');
        var index = $(this).data('index');
        if (globalIndex > 1) {
            var value = $collectionValues.val().split('/');
            value[index] = $(this).val();
            $collectionValues.val(value.join('/'));
        } else {
            $collectionValues.val($(this).val());
        }
    });

    if (index > 0) {
        var value = $collectionValues.val().split('/');
        value.push(val);
        $collectionValues.val(value.join('/'));
    } else {
        $collectionValues.val(val)
    }

    $collectionHolder.append($newForm);

    $collectionHolder.data('index', index + 1);

    if (index == 1) {
        $('#form_wydarzeniaspecjalne').attr('disabled', false);
        $('#form_wydarzeniaspecjalne').attr('required', true);
    }

    $collectionHolder.append($addMovieButton);
    if (index == 5) {
        $addMovieButton.attr('hidden', true);
    }
    if(index > 0)
    {
        $collectionHolder.append($deleteLastMovieButton);
        $deleteLastMovieButton.attr('hidden', false);
    }
}

function deleteMovieForm($collectionHolder) {
    var index = $collectionHolder.data('index');
    $collectionHolder.data('index', index - 1);
    $collectionHolder.children('.movieCollection').last().remove();


    $collectionHolder.append($addMovieButton);
    $addMovieButton.attr('hidden', false);

    $collectionHolder.append($addMovieButton);
    if (index == 2) {
        $('#form_wydarzeniaspecjalne').attr('disabled', true);
        $('#form_wydarzeniaspecjalne').attr('required', false);
        $('#form_wydarzeniaspecjalne').val("");
        $deleteLastMovieButton.attr('hidden', true);
    }
    else {
        $collectionHolder.append($deleteLastMovieButton);
    }

    var string = $collectionValues.val();
    var values = string.split("/");
    values.splice(-1, 1);
    string = values.join('/');
    $collectionValues.val(string);
}