var NAMES_ARRAY = [];
var SEPARATIONS_ARRAY = [];
var CHAIRS = [];
var BEST_AVG = null;
var BEST_MIN = null;
var EDITING_CHAIR = null;
var SHOWING_AVERAGE = false;
var SAVEAS_NAME = null;

$(function() {
	$("#EditChair").hide();
	$("#Results").hide();
	SetNames();
	SetSeparations();

	CHAIRS = [];

	/* Left side
	AddChair(0, 7, 270);
	AddChair(0, 24, 270);
	AddChair(0, 41, 270);
	AddChair(0, 58, 270);
	AddChair(0, 75, 270);
	AddChair(0, 92, 270);

	// Back row
	AddChair(11, 0, 180);
	AddChair(26, 0, 180);
	AddChair(45, 0, 180);
	AddChair(60, 0, 180);
	AddChair(79, 0, 180);
	AddChair(94, 0, 180);

	// Back left pod
	AddChair(7, 30, 55);
	AddChair(14, 40, 55);
	AddChair(30, 12, 235);
	AddChair(37, 22, 235);

	// Front left pod
	AddChair(9, 69, 145);
	AddChair(19, 62, 145);
	AddChair(24, 94, 325);
	AddChair(34, 87, 325);

	// Center pod
	AddChair(39, 41, 90);
	AddChair(39, 55, 90);
	AddChair(66, 41, 270);
	AddChair(66, 55, 270);

	// Back right pod
	AddChair(69, 30, 55);
	AddChair(76, 40, 55);
	AddChair(92, 12, 235);
	AddChair(99, 22, 235);

	// Front right pod
	AddChair(67, 80, 55);
	AddChair(74, 90, 55);
	AddChair(90, 62, 235);
	AddChair(97, 72, 235);

	// Printer area
	AddChair(43, 99, 180);
	AddChair(60, 99, 180);*/

	/* 2022 chairs
	AddChair(7, 0, 180);
	AddChair(24, 0, 180);
	AddChair(41, 0, 180);
	AddChair(58, 0, 180);
	AddChair(75, 0, 180);
	AddChair(92, 0, 180);

	AddChair(7, 20, 180);
	AddChair(24, 20, 180);
	AddChair(41, 20, 180);
	AddChair(58, 20, 180);
	AddChair(75, 20, 180);
	AddChair(92, 20, 180);

	AddChair(7, 40, 180);
	AddChair(24, 40, 180);
	AddChair(41, 40, 180);
	AddChair(58, 40, 180);
	AddChair(75, 40, 180);
	AddChair(92, 40, 180);

	AddChair(7, 60, 180);
	AddChair(24, 60, 180);
	AddChair(41, 60, 180);
	AddChair(58, 60, 180);
	AddChair(75, 60, 180);
	AddChair(92, 60, 180);

	AddChair(7, 80, 180);
	AddChair(24, 80, 180);
	AddChair(41, 80, 180);
	AddChair(58, 80, 180);
	AddChair(75, 80, 180);
	AddChair(92, 80, 180);

	AddChair(7, 100, 180);
	AddChair(24, 100, 180);
	AddChair(41, 100, 180);
	AddChair(58, 100, 180);
	AddChair(75, 100, 180);
	AddChair(92, 100, 180); */

	// 2023 chairs
	AddChair(-9, 0, 180);
	AddChair(4, 0, 180);
	AddChair(21, 0, 180);
	AddChair(34, 0, 180);
	AddChair(51, 0, 180);
	AddChair(64, 0, 180);
	AddChair(81, 0, 180);
	AddChair(94, 0, 180);

	AddChair(-9, 40, 180);
	AddChair(4, 40, 180);
	AddChair(21, 40, 180);
	AddChair(34, 40, 180);
	AddChair(51, 40, 180);
	AddChair(64, 40, 180);
	AddChair(81, 40, 180);
	AddChair(94, 40, 180);

	AddChair(21, 60, 180);
	AddChair(34, 60, 180);
	AddChair(51, 60, 180);
	AddChair(64, 60, 180);
	AddChair(-9, 60, 180);
	AddChair(4, 60, 180);

	AddChair(21, 20, 180);
	AddChair(34, 20, 180);
	AddChair(51, 20, 180);
	AddChair(64, 20, 180);
	AddChair(81, 20, 180);
	AddChair(94, 20, 180);
	AddChair(-9, 20, 180);
	AddChair(4, 20, 180);

	SetChairs();
});

function SetChairs() {
	$(".chair, .desk").remove();
	for (var i = 0; i < CHAIRS.length; i++) {
		var _Desk = $("<span></span>");
		var _Angle = CHAIRS[i].Angle;
		var _OffsetX = (Math.sin(_Angle * Math.PI * 2 / 360));
		var _OffsetY = (Math.cos(_Angle * Math.PI * 2 / 360));
		_Desk.css("border", "2px solid black").css("border-radius","3px");
		_Desk.css("background-color", "#dddddd");
		_Desk.css("position", "absolute");
		_Desk.css("width","100px").css("height","100px");
		_Desk.css("border-radius", "5px");
		_Desk.css("left", "" + (80 + ((8 * CHAIRS[i].X) + (60 * _OffsetX)) * 1) + "px");
		_Desk.css("top", "" + (78 + ((8 * CHAIRS[i].Y) - (60 * _OffsetY)) * 1) + "px");
		_Desk.css("vertical-align", "middle").css("text-align", "center");
		_Desk.addClass("desk");
		_Desk.attr("id", "desk_" + i);
		_Desk.css("-webkit-transform","rotate(" + _Angle + "deg)");
		_Desk.css("cursor", "pointer");
		_Desk.attr("onclick", "DeskClicked($(this))");
		_Desk.attr("onmouseover", "if(EDITING_CHAIR === null){$(this).css('border','3px solid black').css('background-color','gold')}");
		_Desk.attr("onmouseout", "RedrawDesks()");
		$("#Classroom").append(_Desk);

		var _Chair = $("<span></span>");
		_Chair.css("border", "2px solid black").css("border-radius","3px");
		_Chair.css("background-color", "#000000");
		_Chair.css("position", "absolute");
		_Chair.css("width","6px").css("height","6px");
		_Chair.css("border-radius", "5px");
		_Chair.css("left", "" + (127 + CHAIRS[i].X * 8) + "px");
		_Chair.css("top", "" + (127 + CHAIRS[i].Y * 8) + "px");
		_Chair.css("vertical-align", "middle").css("text-align", "center");
		_Chair.addClass("chair");
		_Chair.attr("id", "chair_" + i);
		_Chair.attr("onclick", "DeskClicked($(this))");
		$("#Classroom").append(_Chair);
	}
}

function AddChair(pX, pY, pAngle, pKid = null, pProhibit = null, pBlank = null) {
	CHAIRS.push({
		X: pX,
		Y: pY,
		Angle: pAngle,
		Kid: pKid,
		Exclude: pProhibit === null ? [] : pProhibit,
		Blank: pBlank
	});
}

function SetNames() {
	var _NameBlock = $("#Names").val();
	var _NamesParsed = _NameBlock.split("\n");
	var _Names = [];
	for (var i = 0; i < _NamesParsed.length; i++) {
		var _Name = _NamesParsed[i].trim();
		if (_Name !== null && _Name !== "") {
			if (_Names.indexOf(_Name) !== -1) {
				ThrowError("More than one person with name '" + _Name + "'. Please make each unique (use last initial if needed)");
				return false;
			}
			_Names.push(_Name);
		}
	}

	NAMES_ARRAY = _Names;
	return true;
}

function SetSeparations() {
	var _SeparationBlock = $("#Separations").val();
	var _SeparationsArray = [];
	var _Separations = _SeparationBlock.split("\n");
	for (var s = 0; s < _Separations.length; s++) {
		var _Sep = [];
		var _SeparateKids = _Separations[s].split(",");
		for (var i = 0; i < _SeparateKids.length; i++) {
			var _KidName = _SeparateKids[i].trim();
			if (_KidName === null || _KidName === "") {
				continue;
			}

			var _Index = NAMES_ARRAY.indexOf(_KidName);

			if (_Sep.indexOf(_Index) !== -1) {
				ShowError(_SeparateKids + "\n\n" + "Cannot separate " + _KidName + " from him/herself");
				return false;
			}
			else if (_Index === -1) {
				ShowError(_SeparateKids + "\n\n" + "Can't find name '" + _KidName + "' in class list");
				return false;
			}
			else {
				_Sep.push(_Index);
			}
		}

		_SeparationsArray.push(_Sep);
	}

	SEPARATIONS_ARRAY = _SeparationsArray;
	return true;
}

function Go() {
	$("#generateLabel").html("Generating...");
	$("#Results").hide();
	setTimeout(GoNow, 10);
}

function GoNow() {
	var _NumTries = parseInt($("#Tries").val());
	if (isNaN(_NumTries)) {
		ThrowError("Not a valid number of seating charts");
		return;
	}

	var _Threshold = parseInt($("#Threshold").val());
	if (isNaN(_Threshold)) {
		ThrowError("Not a valid number for threshold");
		return;
	}

	var _ForceEmptyChairs = 0;
	for (var i = 0; i < CHAIRS.length; i++) {
		if (CHAIRS[i].Blank === true) {
			_ForceEmptyChairs++;
		}
	}

	if (SetNames() === false) {
		return;
	}
	if (SetSeparations() === false) {
		return;
	}

	if (_ForceEmptyChairs + NAMES_ARRAY.length > CHAIRS.length) {
		ThrowError("Too many chairs set to always be empty. Not enough remaining chairs for all the kids.");
		return;
	}

	var _BestMin = null;
	var _BestAvg = null;

	for (var i = 0; i < _NumTries; i++) {
		var _Results = [];
		var _RemainingKids = [];
		var _RemainingChairs = [];
		var _SeparationDistances = [];

		for (var j = 0; j < NAMES_ARRAY.length; j++) {
			_RemainingKids[j] = j;
		}

		for (var j = 0; j < CHAIRS.length; j++) {
			_RemainingChairs[j] = j;
		}

		for (var j = 0; j < CHAIRS.length; j++) {
			var _Chair = CHAIRS[j];

			if (_Chair.Blank === true) {
				_Results[j] = -1;
				_RemainingChairs.splice(_RemainingChairs.indexOf(j), 1);
			}
			else if (_Chair.Kid !== null) {
				_Results[j] = _Chair.Kid;
				_RemainingChairs.splice(_RemainingChairs.indexOf(j), 1);
				_RemainingKids.splice(_RemainingKids.indexOf(_Chair.Kid), 1);
			}
			else {
				_Results[j] = null;
			}
		}

		for (var j = 0; j < CHAIRS.length; j++) {
			var _Chair = CHAIRS[j];
			if (_Chair.Blank === false && _Chair.Kid === null) {
				var _Loops = 0;
				while (1) {
					var _RandomInt = Math.floor(Math.random() * _RemainingKids.length);
					var _KidIndex = _RemainingKids[_RandomInt];

					if (_Chair.Exclude.indexOf(_KidIndex) === -1) {
						_Results[j] = _KidIndex;
						_RemainingChairs.splice(_RemainingChairs.indexOf(j), 1);
						_RemainingKids.splice(_RemainingKids.indexOf(_KidIndex), 1);
						break;
					}

					if (_Loops++ > 1000) {
						break;
					}
				}
			}

		}

		for (var j = 0; j < _RemainingKids.length; j++) {
			var _KidIndex = _RemainingKids[j];
			var _NumChairsLeft = _RemainingChairs.length;
			var _RandomInt = Math.floor(Math.random() * _NumChairsLeft);
			var _Loops = 0;
			while (1) {
				var _ChairIndex = _RemainingChairs[_RandomInt];

				if (CHAIRS[_ChairIndex].Exclude.indexOf(_KidIndex) === -1) {
					_Results[_ChairIndex] = _KidIndex;
					_RemainingChairs.splice(_RemainingChairs.indexOf(_ChairIndex), 1);
					break;
				}

				if (_Loops++ > 1000) {
					break;
				}
			}
		}

		var _MissingAny = false;
		for (var j = 0; j < NAMES_ARRAY.length; j++) {
			if (_Results.indexOf(j) === -1) {
				_MissingAny = true;
				break;
			}
		}

		if (_MissingAny === true) {
			continue;
		}

		var _PairsAlreadyCounted = [];

		for (var j = 0; j < SEPARATIONS_ARRAY.length; j++) {
			for (var a = 0; a < SEPARATIONS_ARRAY[j].length; a++) {
				var _KidA = (SEPARATIONS_ARRAY[j])[a];
				var _ChairA = _Results.indexOf(_KidA);
				var _XA = CHAIRS[_ChairA].X;
				var _YA = CHAIRS[_ChairA].Y;
				for (var b = a + 1; b < SEPARATIONS_ARRAY[j].length; b++) {
					var _KidB = (SEPARATIONS_ARRAY[j])[b];
					var _Sum1 = (_KidA * 1000) + _KidB;
					var _Sum2 = (_KidB * 1000) + _KidA;

					if (_PairsAlreadyCounted.indexOf(_Sum1) !== -1 || _PairsAlreadyCounted.indexOf(_Sum2) !== -1) {
						continue;
					}

					_PairsAlreadyCounted.push(_Sum1);

					var _ChairB = _Results.indexOf((SEPARATIONS_ARRAY[j])[b]);
					if (_ChairB < 0) {
						console.log(_Results);
						console.log(SEPARATIONS_ARRAY[j][b]);
					}
					var _XB = CHAIRS[_ChairB].X;
					var _YB = CHAIRS[_ChairB].Y;

					var _Distance = Math.sqrt(((_XA - _XB)*(_XA - _XB)) + ((_YA - _YB)*(_YA - _YB)));
					_SeparationDistances.push(_Distance);
				}
			}
		}

		var _MinDistance = Math.min(..._SeparationDistances);
		var _SumDistance = 0;
		for (var d = 0; d < _SeparationDistances.length; d++) {
			_SumDistance += _SeparationDistances[d];
		}
		var _AvgDistance = (_SeparationDistances.length <= 0) ? 0 : (_SumDistance / _SeparationDistances.length);

		if (_BestMin === null || _BestMin.Minimum < _MinDistance) {
			_BestMin = {
				Minimum: _MinDistance,
				Average: _AvgDistance,
				Seats: _Results
			};
		}

		if ((_BestAvg === null || _BestAvg.Average < _AvgDistance) && _MinDistance >= _Threshold) {
			_BestAvg = {
				Minimum: _MinDistance,
				Average: _AvgDistance,
				Seats: _Results
			};
		}

		BEST_AVG = _BestAvg;
		BEST_MIN = _BestMin;
	}

	$("#generateLabel").html("Generate this many seating charts");
	ShowResults(false);
}

function ShowError(pMessage) {
	alert("ERROR\n\n" + pMessage);
}

function BackToEdit() {
	$("#Inputs").show();
	$("#Results").hide();
}

function BackToResults() {
	$("#Results").show();
	$("#Inputs").hide();
}

function ShowResults(pAverage) {
	$("#min_min").html(BEST_MIN === null ? "" : BEST_MIN.Minimum.toFixed(2));
	$("#min_avg").html(BEST_MIN === null ? "" : BEST_MIN.Average.toFixed(2));
	$("#avg_min").html(BEST_AVG === null ? "" : BEST_AVG.Minimum.toFixed(2));
	$("#avg_avg").html(BEST_AVG === null ? "" : BEST_AVG.Average.toFixed(2));

	SHOWING_AVERAGE = pAverage;
	var _Results = (pAverage ? BEST_AVG : BEST_MIN);
	if (_Results === null) {
		//ShowError("No results for best " + (pAverage ? " average" : " minimum"));
		ClearDesks();
		return;
	}

	$("#Results").show();
	$("#Inputs").hide();
	$("#ShowResultsButton").show();

	$("#min_row td").css("background-color", pAverage ? "white" : "lightgreen");
	$("#avg_row td").css("background-color", pAverage ? "lightgreen" : "white");

	$("#Minimum").html(_Results.Minimum);
	$("#Average").html(_Results.Average);

	for (var i = 0; i < CHAIRS.length; i++) {
		var _Occupant = _Results.Seats[i];
		var _El = $("#desk_" + i);

		_El.html("<table style='width:100%;height:100%;text-align:center'><tr><td><strong>" + (i >= _Results.Seats.length ? "" : ((_Occupant === null || _Occupant < 0) ? "-----" : NAMES_ARRAY[_Occupant])) + "</strong></td></tr></table>");
	}

	$("#separationResults tr.resultRow").remove();
	var _Distances = GetDistancesFromResults(_Results.Seats);

	for (var i = 0; i < _Distances.length; i++) {
		$("#separationResults").append("<tr onmouseover='MouseOverResult($(this))' onmouseout='MouseOutResult($(this))'"
									 + " chairA='" + _Distances[i].ChairA + "' chairB='" + _Distances[i].ChairB
									 + "' class='resultRow'><td style='border:1px solid black'>" + _Distances[i].Names 
									 + "</td><td style='border:1px solid black'>" + _Distances[i].Distance.toFixed(2)
									 + "</td></tr>");
	}
}

function MouseOutResult(pRow) {
	var _ChairA = pRow.attr("chairA");
	var _ChairB = pRow.attr("chairB");
	RedrawDesks();
}

function MouseOverResult(pRow) {
	var _ChairA = pRow.attr("chairA");
	var _ChairB = pRow.attr("chairB");
	$("#desk_" + _ChairA + ", #desk_" + _ChairB).css("background-color", "lightgreen").css("border", "3px solid black");
}

function GetDistancesFromResults(pResults) {
	var _PairsAlreadyCounted = [];
	var _SeparationData = [];

	for (var j = 0; j < SEPARATIONS_ARRAY.length; j++) {
		for (var a = 0; a < SEPARATIONS_ARRAY[j].length; a++) {
			var _KidA = (SEPARATIONS_ARRAY[j])[a];
			var _ChairA = pResults.indexOf(_KidA);
			var _XA = CHAIRS[_ChairA].X;
			var _YA = CHAIRS[_ChairA].Y;
			for (var b = a + 1; b < SEPARATIONS_ARRAY[j].length; b++) {
				var _KidB = (SEPARATIONS_ARRAY[j])[b];
				var _Sum1 = (_KidA * 1000) + _KidB;
				var _Sum2 = (_KidB * 1000) + _KidA;

				if (_PairsAlreadyCounted.indexOf(_Sum1) !== -1 || _PairsAlreadyCounted.indexOf(_Sum2) !== -1) {
					continue;
				}

				_PairsAlreadyCounted.push(_Sum1);

				var _ChairB = pResults.indexOf((SEPARATIONS_ARRAY[j])[b]);
				var _XB = CHAIRS[_ChairB].X;
				var _YB = CHAIRS[_ChairB].Y;

				var _Distance = Math.sqrt(((_XA - _XB)*(_XA - _XB)) + ((_YA - _YB)*(_YA - _YB)));

				_SeparationData.push({
					Names: NAMES_ARRAY[_KidA] + ", " + NAMES_ARRAY[_KidB],
					Distance: _Distance,
					ChairA: _ChairA,
					ChairB: _ChairB
				});
			}
		}
	}

	_SeparationData.sort(function(a, b) {
		return (a.Distance - b.Distance);
	});

	return _SeparationData;
}

function DeskClicked(pEl) {
	SetNames();
	SetSeparations();

	$(".desk").css("background-color", "#dddddd").css("border", "2px solid black");
	pEl.css("background-color", "gold").css("border", "3px solid black");
	EDITING_CHAIR = parseInt(pEl.attr("id").substring(5));
	$("#EditChair").show();
	$("#Controls").hide();
	$("#chairnum").html("Desk " + EDITING_CHAIR);
	$("#chairedittable tr").remove();

	$("#chairedittable").append("<tr><td style='border:1px solid black;text-align:right'>[EMPTY]</td><td style='border:1px solid black;text-align:left'><button style='background-color:lightgreen' onclick='Always(-1)'>Always</button><button style='background-color:lightpink' onclick='Never(-1)'>Never</button></td></tr>");
	for (var i = 0; i < NAMES_ARRAY.length; i++) {
		$("#chairedittable").append("<tr><td style='border:1px solid black;text-align:right'>" + NAMES_ARRAY[i] + "</td><td style='border:1px solid black;text-align:left'><button style='background-color:lightgreen' onclick='Always(" + i + ")'>Always</button><button style='background-color:lightpink' onclick='Never(" + i + ")'>Never</button></td></tr>");
	}

	RedrawChairInfo();
}

function Always(pIndex) {
	if (EDITING_CHAIR === null) {
		return;
	}

	if (pIndex >= 0) {
		for (var i = 0; i < CHAIRS.length; i++) {
			if (CHAIRS[i].Kid === pIndex) {
				CHAIRS[i].Kid = null;
			}
		}

		CHAIRS[EDITING_CHAIR].Kid = pIndex;

		if (CHAIRS[EDITING_CHAIR].Exclude.indexOf(pIndex) !== -1) {
			CHAIRS[EDITING_CHAIR].Exclude.splice(CHAIRS[EDITING_CHAIR].Exclude.indexOf(pIndex), 1);
		}

		if (CHAIRS[EDITING_CHAIR].Blank === true) {
			CHAIRS[EDITING_CHAIR].Blank = null;
		}
	} else {
		CHAIRS[EDITING_CHAIR].Kid = null;
		CHAIRS[EDITING_CHAIR].Exclude = [];
		CHAIRS[EDITING_CHAIR].Blank = true;
	}

	RedrawChairInfo();
	RedrawDesks();
}


function Never(pIndex) {
	if (EDITING_CHAIR === null) {
		return;
	}

	if (pIndex >= 0) {
		if (CHAIRS[EDITING_CHAIR].Kid === pIndex) {
			CHAIRS[EDITING_CHAIR].Kid = null;
		}

		if (CHAIRS[EDITING_CHAIR].Exclude.indexOf(pIndex) === -1) {
			CHAIRS[EDITING_CHAIR].Exclude.push(pIndex);
		}
	} else {
		CHAIRS[EDITING_CHAIR].Blank = false;
	}

	RedrawChairInfo();
	RedrawDesks();
}

function RedrawChairInfo() {
	if (EDITING_CHAIR === null) {
		return;
	}

	var _Chair = CHAIRS[EDITING_CHAIR];
	$("#chairedittable tr").each(function(pIndex) {
		var _Index = pIndex - 1;
		if (_Index < 0) {
			if (_Chair.Blank === true) {
				$(this).css("background-color", "green").css("color","white");
			}
			else if (_Chair.Blank === false) {
				$(this).css("background-color", "red").css("color","white");
			}
			else {
				$(this).css("background-color", "#ffffaa").css("color","black");
			}
		}
		else {
			if (_Chair.Kid === _Index) {
				$(this).css("background-color", "green").css("color","white");
			}
			else if (_Chair.Exclude.indexOf(_Index) !== -1) {
				$(this).css("background-color", "red").css("color","white");
			}
			else {
				$(this).css("background-color", "#ffffaa").css("color","black");
			}
		}
	});
}

function RedrawDesks() {
	for (var i = 0; i < CHAIRS.length; i++) {
		var _Angle = CHAIRS[i].Angle;
		var _OffsetX = (Math.sin(_Angle * Math.PI * 2 / 360));
		var _OffsetY = (Math.cos(_Angle * Math.PI * 2 / 360));

		$("#desk_" + i).css("border", (EDITING_CHAIR === i ? "3" : "2") + "px solid black");
		$("#desk_" + i).css("background-color", (EDITING_CHAIR === i ? "gold" : (CHAIRS[i].Blank === true ? "black" : (CHAIRS[i].Kid === null ? "#dddddd" : "green"))));
		$("#desk_" + i).css("color", (EDITING_CHAIR === i ? "black" : (CHAIRS[i].Blank === true ? "white" : (CHAIRS[i].Kid === null ? "black" : "white"))));
		$("#desk_" + i).css("left", "" + (80 + ((8 * CHAIRS[i].X) + (60 * _OffsetX)) * 1) + "px");
		$("#desk_" + i).css("top", "" + (78 + ((8 * CHAIRS[i].Y) - (60 * _OffsetY)) * 1) + "px");
		$("#desk_" + i).css("-webkit-transform","rotate(" + _Angle + "deg)");

		$("#chair_" + i).css("left", "" + (127 + CHAIRS[i].X * 8) + "px");
		$("#chair_" + i).css("top", "" + (127 + CHAIRS[i].Y * 8) + "px");
	}
}

function DoneEditingChair() {
	EDITING_CHAIR = null;
	$("#Controls").show();
	$("#EditChair").hide();
	RedrawDesks();
}

function ResetChair() {
	if (EDITING_CHAIR === null) {
		return;
	}

	CHAIRS[EDITING_CHAIR].Kid = null;
	CHAIRS[EDITING_CHAIR].Exclude = [];
	CHAIRS[EDITING_CHAIR].Blank = null;

	RedrawChairInfo();
	RedrawDesks();
}

function DeleteChair() {
	if (EDITING_CHAIR === null) {
		return;
	}

	if (confirm("Are you sure you want to delete this desk? This will probably screw up any currently displaying seating chart")) {
		CHAIRS.splice(EDITING_CHAIR, 1);

		if (BEST_AVG !== null) {
			BEST_AVG.Seats.splice(EDITING_CHAIR, 1);
		}
		if (BEST_MIN !== null) {
			BEST_MIN.Seats.splice(EDITING_CHAIR, 1);
		}

		EDITING_CHAIR = null;

		SetChairs();
		RedrawDesks();
		DoneEditingChair();
	}
}


function ThrowError(pMessage) {
	alert("ERROR\n\n" + pMessage);
}

setTimeout(function() {
	$("body").on("keydown", function(e) {
		if (EDITING_CHAIR === null) {
			return;
		}

		if (e.key === "Delete" || e.key === "Backspace") {
			DeleteChair();
			return;
		}

		if (e.key === "ArrowRight" || e.key === "ArrowLeft" || e.key === "ArrowUp" || e.key === "ArrowDown") {
			var _IsLeft = (e.key === "ArrowLeft");
			var _IsRight = (e.key === "ArrowRight");
			var _IsUp = (e.key === "ArrowUp");
			var _IsDown = (e.key === "ArrowDown");

			if (e.ctrlKey) {
				var _DeltaAngle = _IsLeft ? -1 : (_IsRight ? 1 : 0);
				if (e.shiftKey) {
					_DeltaAngle *= 10;
				}

				CHAIRS[EDITING_CHAIR].Angle += _DeltaAngle;
				CHAIRS[EDITING_CHAIR].Angle %= 360;
			}
			else {
				var _DeltaX = _IsLeft ? -0.25 : (_IsRight ? 0.25 : 0);
				var _DeltaY = _IsUp ? -0.25 : (_IsDown ? 0.25 : 0);

				if (e.shiftKey) {
					_DeltaX *= 10;
					_DeltaY *= 10;
				}

				CHAIRS[EDITING_CHAIR].X += _DeltaX;
				CHAIRS[EDITING_CHAIR].Y += _DeltaY;

				if (CHAIRS[EDITING_CHAIR].X < 0) {
					CHAIRS[EDITING_CHAIR].X = 0;
				}
				if (CHAIRS[EDITING_CHAIR].X > 100) {
					CHAIRS[EDITING_CHAIR].X = 100;
				}
				if (CHAIRS[EDITING_CHAIR].Y < 0) {
					CHAIRS[EDITING_CHAIR].Y = 0;
				}
				if (CHAIRS[EDITING_CHAIR].Y > 100) {
					CHAIRS[EDITING_CHAIR].Y = 100;
				}
			}

			RedrawDesks();
		}
	});

	$("#importFile").on("change", function(e) {
		var _FirstFile = e.target.files[0];
		var _Reader = new FileReader();
		_Reader.onload = function(e) {
			var _ConvertedArray = new Uint8Array(e.target.result);

			var _String = "";

			for (var i = 0; i < _ConvertedArray.length; i++) {
			    _String += String.fromCharCode(_ConvertedArray[i]);
			}

			ImportFetchedData(_String);
		};

		_Reader.readAsArrayBuffer(_FirstFile);
	});
}, 10);

function Save() {
	SetNames();
	SetSeparations();

	var _Data = {
		CLASS_NAME: $("#ClassName").val(),
		CHAIRS: CHAIRS,
		NAMES_ARRAY: NAMES_ARRAY,
		SEPARATIONS_ARRAY: SEPARATIONS_ARRAY,
		BEST_AVG: BEST_AVG,
		BEST_MIN: BEST_MIN,
		SHOWING_AVERAGE: SHOWING_AVERAGE
	}

	var _Stringifyed = JSON.stringify(_Data);
    var _Link = $("<a>Click to Download or right-click to Save As</a>");
    _Link.attr("href", "data:text/plain;charset=utf-8," + encodeURIComponent(_Stringifyed));
    _Link.attr("download", $("#ClassName").val().replace(/ /g,"_"));

    $("#SaveAsLink").html("");
    $("#SaveAsLink").append(_Link);

    setTimeout(function() {
	    $("#SaveAsLink").html("");
    }, 10000);
}

function ImportFetchedData(pInputString) {
	var _Saved = JSON.parse(pInputString);
	CHAIRS = _Saved.CHAIRS;
	SEPARATIONS_ARRAY = _Saved.SEPARATIONS_ARRAY;
	NAMES_ARRAY = _Saved.NAMES_ARRAY;
	BEST_AVG = _Saved.BEST_AVG;
	BEST_MIN = _Saved.BEST_MIN;
	SHOWING_AVERAGE = _Saved.SHOWING_AVERAGE;
	$("#Names").val(NAMES_ARRAY.join("\n"));
	$("#ClassName").val(_Saved.CLASS_NAME);

	var _SeparationTexts = [];
	for (var i = 0; i < SEPARATIONS_ARRAY.length; i++) {
		var _Sep = SEPARATIONS_ARRAY[i];
		var _Arr = [];

		for (var j = 0; j < _Sep.length; j++) {
			_Arr.push(NAMES_ARRAY[_Sep[j]]);
		}
		_SeparationTexts.push(_Arr.join(", "));
	}
	$("#Separations").val(_SeparationTexts.join("\n"));

	SetChairs();
	RedrawDesks();
	ShowResults(SHOWING_AVERAGE);
}

function ClearDesks() {
	$(".desk").html("");
}

function AddNewChair() {
	AddChair(50, 50, 180);
	SetChairs();
	ShowResults(SHOWING_AVERAGE);
	RedrawDesks();
	$("#desk_" + (CHAIRS.length - 1)).click();
}