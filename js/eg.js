
// this is the click handler that makes the howto information appear and disappear upon clicking the questions
$(function () {
	$('.guideStep').click( function (e) 
	{ 
		e.preventDefault();
		$(this).next().toggle();
	});
});