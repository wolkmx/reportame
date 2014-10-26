$(document).ready(function(){
	
	//FUncion para cerrar los datos de informacion rapida del home
	$('#cerrar_datos_reporte_home').click(function(){
		$('#info_evento_home').css('height','0px');
		$('#cerrar_datos_reporte_home').fadeOut('slow');
		$('#datos_reporte_home').fadeOut('slow');
		/*$('#datos_reporte_home').html(null);*/
	});	

});

