$(document).ready(function(){
	
	//FUncion para cerrar los datos de informacion rapida del home
	$('#cerrar_datos_reporte_home').click(function(){
		$('#info_evento_home').css('height','0px');
		$('#cerrar_datos_reporte_home').fadeOut('slow');
		$('#datos_reporte_home').fadeOut('slow');
		/*$('#datos_reporte_home').html(null);*/
	});

	//Funcion para buscar los puntos especificos de una enfermedad en el mapa y refrescarlo
	$('#search_home_send').click(function(){
		
		//Obtengo el valor que escribo la persona en el campo, elimino espacios y lo convierno a mayusculas
		var busqueda = (($('#search_home_input').val()).trim()).toUpperCase(); 
		
		//Si al menos tiene mas de tres letras buscare
		if(busqueda.length > 3){
			
			//Se envia la cadena a un controlador para que busque solo los marcadores correspondientes
			/*@todo falta optimizar esta declaracion*/
			var data = { busquedaajax : busqueda, ciudadano: 1, gobierno: 0 };
		
			$('#info_evento_home').css('height','0px');
			
			/*Se revisa si estan seleccionados los botones de ciudadano y gobierno*/
			if( $("#search_ciudadano").is(':checked') &&  $("#search_gobierno").is(':checked')) {  
				data = { busquedaajax : busqueda, ciudadano: 1, gobierno: 1 };
			} else {  
				//Si no estan los dos seleccionados se debe verificar cual de los dos esta seleccionado para buscarlo
				if($("#search_gobierno").is(':checked')){
					data = { busquedaajax : busqueda, ciudadano: 0, gobierno: 1 };
				}else{
					$("#search_ciudadano").attr('checked', true);  
				}
				 
			} 
			
			
			$.ajax({
			type: 'POST',
			data: data,
			url: '/busquedaHome', 
			}).done(function(eventos){
				/*@todo se convierte el resultado que se imprime con echo desde php en un objeto json se debe revisar si es necesario hacerlo*/
				var x = JSON.parse(eventos);
				//la manera de accederlos es la siguiente x['objeto'][0][0][0]['descripcion']
				//alert('termino la busqueda'+x['objeto'][0][0][0]['descripcion']);
				//alert(evento.length);
				//Se eliminan los markers anteriores
				for(i=0;i < evento.length ; i++){
					map.removeLayer(evento[i]);
				}
				//Se destruye el array que existia anteriormente
				evento = [];
				//alert(x.length);
				//Se crean los nuevos markers
				/*@todo se debe dejar una sola funcion que realice este trabajo*/
				for(i=0;i < x.length ; i++){
					
					evento[i] = L.marker([x['objeto'][0][i][0]["lat"], x['objeto'][0][i][0]["lon"]],{ title: x['objeto'][0][i][0]["categoriaName"]+': '+x['objeto'][0][i][0]["name"] }).addTo(map);
					
					evento[i].indice = i;

				}

				/*Se agregan los eventos @todo se debe crear una sola funcion que se encarge de esto*/
				for(var j = 0; j < x.length; j++){
	
					evento[j].on('click', function(){ 

						var contenido =  "<ul><li><span class='titulo_dato_home'>Tipo de reporte:<span> "+x['objeto'][0][this.indice][0]['categoriaName']+"</li><li><span class='titulo_dato_home'>Enfermedad:<span> "+x['objeto'][0][this.indice][0]['name']+"</li><li><span class='titulo_dato_home'>Usuario que Reporta:<span> "+x['objeto'][0][this.indice][0]['alias']+"</li><li><span class='titulo_dato_home'>Reportado el:<span> "+x['objeto'][0][this.indice][0]['created_at']+"</li></ul>";
							/*$('#info_evento_home img').fadeIn();*/
							$('#info_evento_home').css('height','0px');
							$('#info_evento_home').css('height','135px');
							$('#cerrar_datos_reporte_home').fadeIn('slow');
							$('#datos_reporte_home').html(contenido);
							$('#datos_reporte_home').fadeIn('slow');
						 } );

					}
				
				//$( "#map" ).html( html );
			});

		}

	});
/*
var recognition = new webkitSpeechRecognition();
recognition.onresult = function(event) { 
  console.log(event) 
}
recognition.start();*/

});

