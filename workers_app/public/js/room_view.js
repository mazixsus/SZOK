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

function createButton(rowType, seatType, rowValue, seatValue) {
    var text = "";

    if (seatType == SEAT_INACTIVE) {
        text = "<div class=\"no-seat seatViewPadding mr-2\" id=\"" + rowValue + "_" + seatValue + "\" name=\"" + rowValue + "\"></div>";
    }
    else {
        if (rowType == ROW_BUYING_ONLY) {
            text = "<div class=\"seat seat_buying_only seatViewPadding mr-2\" id=\"" + rowValue + "_" + seatValue + "\" name=\"" + rowValue + "\" >" + seatValue + "</div>";
        }
        else {
            text = "<div class=\"seat seat_normal seatViewPadding mr-2\" id=\"" + rowValue + "_" + seatValue + "\" name=\"" + rowValue + "\" >" + seatValue + "</div>";
        }
    }

    return text;
}

function createView() {
    var rowValue;
    var seatValue;
    var text_2;
    var text = "";
    text += "<div class=\"mb-3\"><div class=\"text_center screen\" style=\"width: " + (seat*38-8) + "px \">EKRAN</div></div>";
    for (var i = 0; i < row; i++) {
        text += "<div class=\"inline mb-2\">";
        rowValue = i + 1;
        text += "<div class=\"mr-3 rowNumber seatViewPadding\" id=\"Row" + rowValue + "\">" + rowValue + "</div>";
        for (var j = 0; j < seat; j++) {
            seatValue = j + 1;
            text_2 = createButton(rowTable[i], seatTable[i][j], rowValue, seatValue);
            text += text_2;
        }
        text += "</div>";
    }
    document.getElementById("roomContent").innerHTML = text;
}
