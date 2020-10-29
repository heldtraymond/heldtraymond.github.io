$(document).ready(function() {
	var _AnimateEls = $("#name,#topSubtitle,#resume,#divider1,#gamesTitle,#gamesSubtitle,#optimizeddesktop,#memory,#one,#optimizedmobile,#fiveDice,#trivia,#sourceCode,#divider2,#contactSection");
	_AnimateEls.each(function(pIndex) {
		var _DelayLength = pIndex * 200;

		if (pIndex > 4) {
			_DelayLength = (600 + (pIndex * 50));
		}

		$(this).hide();
		$(this).delay(_DelayLength).fadeIn();
	});

	$("#resume").attr("onclick", "OpenInNewTab('heldt.raymond_resume.pdf')");
	$("#memory").attr("onclick", "OpenInNewTab('concentration/index.html')");
	$("#one").attr("onclick", "OpenInNewTab('one/pregame.html')");
	$("#trivia").attr("onclick", "OpenInNewTab('trivia/Trivia.html')");
	$("#fiveDice").attr("onclick", "OpenInNewTab('fiveDice/fiveDice.html')");
	$("#sourceCode").attr("onclick", "OpenInNewTab('heldt.raymond_sourceCode.zip')");
});

function OpenInNewTab(pUrl) {
	window.open(pUrl, "_blank");
}