var row;
var seat;
var seatTable;
var rowTable;

const ROW_NORMAL = 1;
const ROW_BUYING_ONLY = 2;
const SEAT_NORMAL = 1;
const SEAT_INACTIVE = 0;

window.onload = function () {
    row = document.getElementById("rowCount").value;
    seat = document.getElementById("seatCount").value;
    var rowCode = document.getElementById("rowCode").value.toString();
    var seatCode = document.getElementById("seatCode").value.toString();
    document.getElementById("inputRowNumber").value = row;
    document.getElementById("inputSeatNumber").value = seat;
    document.getElementById("inputRoomNumber").value = document.getElementById("roomNumber").value;
    createTables(rowCode, seatCode.split(","));
    createView();
};

function createTables(rowCode ,seatCode) {
    seatTable = [];
    rowTable = [];
    for (var i = 0; i < row; i++) {
        rowTable[i] = Number(rowCode[i]);
        seatTable[i] = [];
        for (var j = 0; j < seat; j++) {
            seatTable[i][j] = Number(seatCode[i][j]);
        }
    }
}

function createNewTables(row ,seat) {
    seatTable = [];
    rowTable = [];
    for (var i = 0; i < row; i++) {
        rowTable[i] = 1;
        seatTable[i] = [];
        for (var j = 0; j < seat; j++) {
            seatTable[i][j] = 1;
        }
    }
}

function createRoom() {
    var newRow = document.getElementById("inputRowNumber").value;
    var newSeat = document.getElementById("inputSeatNumber").value;
    if(validateRowAndSeat(newRow, newSeat)) {
        row = newRow;
        seat = newSeat;
        createNewTables(row, seat);
        createView();
    }
}

function validateRowAndSeat(row, seat){

    var inputRowNumber = document.getElementById("inputRowNumber");
    var inputSeatNumber = document.getElementById("inputSeatNumber");
    inputRowNumber.classList.remove("is-invalid");
    inputSeatNumber.classList.remove("is-invalid");
    var isInvalid = false;
    var error = "";
    const reg = new RegExp("[0-9]{1,2}");
    if(row === "" || !reg.test(row.toString()) || row < 5 || row > 40){
        error += "Błędna liczba rzędów. Należy podać liczbę od 5 do 40.";
        isInvalid = true;
        inputRowNumber.classList.add("is-invalid")
    }
    if(seat === "" || !reg.test(seat.toString()) || seat < 5 || seat > 40){
        if(isInvalid)
            error += "\n";
        error += "Błędna liczba miejsc. Należy podać liczbę od 5 do 40.";
        isInvalid = true;
        inputSeatNumber.classList.add("is-invalid");
    }
    if(isInvalid){
        var text = "<div class=\"alert alert-danger fade show\" id='alertMessage'>" + error + "</div>";
        document.getElementById('validErrorAlert').innerHTML = text;
        setTimeout(function() {
            $(".alert").fadeTo(500, 0).slideUp(500, function(){
                $(this).remove();
            });
        }, 4000);
        return false;
    }else {
        return true;
    }
}

function validateRoomNumber(roomNumber) {
    var inputRoomNumber = document.getElementById("inputRoomNumber");
    inputRoomNumber.classList.remove("is-invalid");
    const reg = new RegExp("^[a-zA-Z0-9]{1,3}$");
    var error = "";
    if (roomNumber.toString().length > 3 || roomNumber.toString().length < 1 || roomNumber.toString() === "" || !reg.test(roomNumber)) {
        error = "Błędny numer sali. Możliwe jest użycie tylko i wyłącznie cyfr i liter.";
        var validErrorAlertMessage = document.getElementById("validErrorAlertMessage");
        var validErrorAlert = document.getElementById("validErrorAlert");
        inputRoomNumber.classList.add("is-invalid");
        validErrorAlertMessage.textContent = error;
        validErrorAlert.hidden = false;
        return false;
    }
    return true;
}

function setData() {
    var roomNumber = document.getElementById("inputRoomNumber").value;
    if(validateRoomNumber(roomNumber)) {
        var seatTableString = '';
        var rowTableString = '';
        for (var i = 0; i < row; i++) {
            rowTableString += rowTable[i];
            if (i != 0)
                seatTableString += ",";
            for (var j = 0; j < seat; j++) {
                seatTableString += seatTable[i][j];
            }
        }
        document.getElementById("roomNumber").value = roomNumber;
        document.getElementById("rowCount").value = row;
        document.getElementById("seatCount").value = seat;
        document.getElementById("rowCode").value = rowTableString;
        document.getElementById("seatCode").value = seatTableString;
        return true;
    }
    else{
        return false;
    }
}

function changeTypeOfSeat(button) {
    var seat_number = button.textContent - 1;
    var row_number = button.name - 1;
    var seat_type = seatTable[row_number][seat_number];
    var row_type = rowTable[row_number];
    if (seat_type == SEAT_NORMAL) {
        seatTable[row_number][seat_number] = SEAT_INACTIVE;
        if (row_type == ROW_NORMAL) {
            button.classList.remove("seat_normal");
            button.classList.add("seat_inactive");
        }
        else {
            button.classList.remove("seat_buying_only");
            button.classList.add("seat_inactive");
        }
    }
    else {
        seatTable[row_number][seat_number] = SEAT_NORMAL;
        if (row_type == ROW_NORMAL) {
            button.classList.remove("seat_inactive");
            button.classList.add("seat_normal");
        }
        else {
            button.classList.remove("seat_inactive");
            button.classList.add("seat_buying_only");
        }
    }
}

function createButton(rowType, seatType, rowValue, seatValue) {
    var text = "";

    if (seatType == SEAT_INACTIVE) {
        text = "<button class=\"seat seat_inactive mr-2 pointer\" id=\"" + rowValue + "_" + seatValue + "\" name=\"" + rowValue + "\"  onClick=\"changeTypeOfSeat(this)\" type=\"button\">" + seatValue + "</button>";
    }
    else {
        if (rowType == ROW_BUYING_ONLY) {
            text = "<button class=\"seat seat_buying_only mr-2 pointer\" id=\"" + rowValue + "_" + seatValue + "\" name=\"" + rowValue + "\"  onClick=\"changeTypeOfSeat(this)\" type=\"button\">" + seatValue + "</button>";
        }
        else {
            text = "<button class=\"seat seat_normal mr-2 pointer\" id=\"" + rowValue + "_" + seatValue + "\" name=\"" + rowValue + "\"  onClick=\"changeTypeOfSeat(this)\" type=\"button\">" + seatValue + "</button>";
        }
    }

    return text;
}

function createView() {
    var rowValue;
    var seatValue;
    var text_2;
    var text = "";
    text += "<div class=\"mb-3\"><div class=\"text_center screen\" style=\"width: " + (seat*38-8) + "px\">EKRAN</div></div>";
    for (var i = 0; i < row; i++) {
        text += "<div class=\"inline mb-2\">";
        rowValue = i + 1;
        text += "<a class=\"text-primary mr-3 rowNumber pointer\" id=\"buttonRow" + rowValue + "\" name=\"" + rowValue + "\" onClick=\"changeTypeOfRow(this)\">" + rowValue + "</a>";
        for (var j = 0; j < seat; j++) {
            seatValue = j + 1;
            text_2 = createButton(rowTable[i], seatTable[i][j], rowValue, seatValue);
            text += text_2;
        }
        text += "</div>";
    }
    document.getElementById("roomContent").innerHTML = text;
}

function changeTypeOfRow(button) {
    var row_number = button.textContent - 1;
    var row_type = rowTable[row_number];

    if (row_type == ROW_NORMAL) {
        rowTable[row_number] = ROW_BUYING_ONLY;
    }
    else {
        rowTable[row_number] = ROW_NORMAL;
    }
    createView();
}
