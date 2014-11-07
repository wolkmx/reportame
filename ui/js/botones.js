$(document).ready(function(){
	$('.usuario1').mouseover(function(){ 
		$('#popover').show('slow');
	});

	$('.usuario1').mouseleave(function(){ 
		$('#popover').hide('slow');
	});

	$(".usuario2").mouseover(function(){
		$('#popover2').show('slow');

	});

	$('.usuario2').mouseleave(function(){
		$('#popover2').hide('slow');
	});

	$('.usuario3').mouseover(function(){
		$('#popover3').show('slow');
	});

	$('.usuario3').mouseleave(function(){
		$('#popover3').hide('slow');
	});

});