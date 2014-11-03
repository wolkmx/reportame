$(document).ready(function(){

	//Funcion para cerrrar el formulario
	$("#cerrar_formulario").click(function(){ $("#reporte_panel").hide(); });
	
	//Funcion para actualizar el mapa
	$("#actualizar_mapa").click(function(){ 
		$("#info_evento_home").css('height','0px'); 
		eliminareventos();
		cargarEventos();
	});
	
	map.on('click', onMapClick);
	
	$("#reporte_paso_6").click(function(){ pasoSeis(); });
	
	//Funcion para capturar el avance del formulario de reporte de incidencia
	$(".siguiente").click(function(event){
		//Se obtiene el paso en al que se desea acceder del formulario
		var paso = $(this).attr("id").split("_");
		var numero = paso[2];

		//Switch para verificar que haya llenado los campos obligatorios
		switch (parseInt(numero)) {
			case 2:
				var result = pasoDos(event);
				if(result == '0'){
					return false;
				}
				break;
			case 3:
				var result = pasoTres(event);
				if(result == '0'){
					return false;
				}
				break;
			case 4:
				pasoCuatro();
				break;
			case 5:
				var result = pasoCinco(event);
				if(result == '0'){
					return false;
				}
				break;
			default:
				alert("Opción no valida");
				break;
		} 
		
		$("#datos_reporte_"+(parseInt(numero)-1)).toggle("slow");	
		$("#datos_reporte_"+numero).toggle("slow");
		
		
	
	});
	
		$(".anterior").click(function(){
		//Se obtiene el paso en al que se desea acceder del formulario
		var paso = $(this).attr("id").split("_");
		var numero = paso[2];
		
		$("#datos_reporte_"+(parseInt(numero)+1)).toggle("slow");	
		$("#datos_reporte_"+numero).toggle("slow");
	
	});
	

 });
 
 var popup = L.popup();
 
 
 //Funcion para verificar el paso 2
 function pasoDos(event){
	//Se verifica si ya esta seleccionado al menos un tipo de reporte, si no esta seleccionado no se le deja avanzar
	if($("#categoria_reporte").val() == "" || $("#categoria_reporte").val() == null ){
		alert("Debes decidir que estas reportando");
		return '0';
		
	}else{
		//Si ya esta seleccionado un tipo de reporte, se verifica si es de tipo enfermedad para que sea obligatorio seleccionarlo
		//El valor por defecto de enfermedad es 1
		if($("#categoria_reporte").val() == 1){
			if($("#enfermedad_reporte").val() == "" || $("#enfermedad_reporte").val() == null ){
				alert("Debes escoger una enfermedad primero");
				return '0';
			}
		}		
	}
 }
 
  //Funcion para verificar el paso 3
 function pasoTres(event){
	if($("#descripcion_reporte").val() == "" || $("#descripcion_reporte").val() == null ){
		alert("Comparte un poco de informacion sobre este evento, hace cuanto lo detectaron, es recurrente, etc.");
		return '0';
	}
 }
 
   //Funcion para verificar el paso 4
 function pasoCuatro(event){
	//Se verifica la opcion seleccionada si es propio buscara el perfil existente, si no es propio dejara cargar un nuevo perfil
	//@todo falta agregar la opcion de cargar los perfiles existentes
	//Si es propio
	if($('input[name=group1]:checked', '#reporte_formulario').val() == "propio"){
		$("#perfiles").hide();
		//Se hace la consulta via ajax para saber si ya tiene su perfil creado
		$.ajax({
			type: 'POST',
			url: '/existeMiPerfil', 
			}).done(function(respuesta){
				var x = JSON.parse(respuesta);
				//alert(x['objeto'][0]['idPerfil']+"--"+x['existe']);
				//Si el perfil existe recuperara los valores y los llenara en los campos correspondientes
				
				if(parseInt(x['existe'])){
				$('#m_perfil').hide();				
				$("#owner").val(1);
				$("#owner_exist").val(1);
					
					//Se recuperan los datos de fecha de nacimiento, etc.
					cargarDatos(x);
					
				}else{
					//El usuario no ha creado su perfil todabia
					$('#m_perfil').show();
					$("#owner").val(1);
					$("#owner_exist").val(0);
				}
				
			});

		
	}else{
		//Si es de otra persona se debe buscar si existen perfiles existentes
		$("#owner").val(0);
		$('#m_perfil').hide();
		//Funcion ajax que revisa si existen perfiles asociados a este usuario y que no sea el propio
		$.ajax({
			type: 'POST',
			url: '/existenPerfiles', 
			}).done(function(respuesta){
				var x = JSON.parse(respuesta);
				//alert(x['objeto'][0]['idPerfil']+"--"+x['existe']);
				//Si existen perfiles se debe mostrar un selec con los perfiles que existen
				if(x['existe']){
				//alert("entro");
					//Si existen mas perfiles se debe mostrar un select con los perfiles existentes
					$("#perfiles").show();
					//Se vacian los datos del formulario
					limpiarPerfil();
					
					
				}else{
					//Si no existe ningun perfil se debera mostrar un mensaje que informe al usuario de que debe crear nuevos perfiles
				}
				
			});
	}
 }
 
//Funcion para verificar el paso 5
 function pasoCinco(event){
	if($("#perfil").val() == "" || $("#perfil").val() == null ){
		if( ($("#cumple_reporte").val() == "") || ($("#pais_reporte").val() == "") || ($("#ciudad_reporte").val() == "") || ($("#municipio_reporte").val() == "")){
			alert("Por favor rellena todos los campos obligatorios antes de continuar.");
			return '0';
		}	
	}else{
		if( ($("#cumple_reporte").val() == "") || ($("#pais_reporte").val() == "") || ($("#ciudad_reporte").val() == "") || ($("#municipio_reporte").val() == "")){
			alert("Por favor rellena todos los campos obligatorios antes de continuar.");
			return '0';
		}	
	}
 }
 
//Funcion para hacer el registro del nuevo evento
 function pasoSeis(event){
	//Se construye el objeto a enviar
	var data = { usuarioId : $('#usuario_id_reporte').val(), 
				latlon: $('#latitud_logintud_reporte').val(),
				owner: $('#owner').val(),
				owner_exist: $('#owner_exist').val(),
				perfil: $('#perfil').val(),
				enfermedad_reporte: $('#enfermedad_reporte').val(),
				descripcion_reporte: $('#descripcion_reporte').val(),
				tipoPerfil: $('input[name=group1]:checked', '#reporte_formulario').val(),
				perfil_reporte: $('#perfil_reporte').val(),
				sexo_reporte: $('input[name=sexo]:checked', '#reporte_formulario').val(),
				cumple_reporte: $('#cumple_reporte').val(),
				pais_reporte: $('#pais_reporte').val(),
				ciudad_reporte: $('#ciudad_reporte').val(),
				municipio_reporte: $('#municipio_reporte').val(),
				nombre_reporte: $('#nombre_reporte').val(),
				apellido_reporte: $('#apellido_reporte').val(),
				telefono_reporte: $('#telefono_reporte').val(),
				celular_reporte: $('#celular_reporte').val(),
				profesion_reporte: $('#profesion_reporte').val(),
				documentType_reporte: $('#documentType_reporte').val(),
				estadoCivil_reporte: $('#estadoCivil_reporte').val(),
				numeroHijos_reporte: $('#numeroHijos_reporte').val(),
				peso_reporte: $('#peso_reporte').val(),
				tipoSangre_reporte: $('#tipoSangre_reporte').val(),
				categoria_reporte: $('#categoria_reporte').val(),
				
				};
	
	$.ajax({
			type: 'POST',
			url: '/guardarEvento', 
			data: data,
			}).done(function(respuesta){
				var x = JSON.parse(respuesta);
				//Si existe quiere decir que se guardo correctamente y se refresca el mapa con la informacion del evento correspondiente
				if(x['resultado'] == 1){
					//alert('entro al if de resultado');
					//x['objeto'][0]['eventoId']
					limpiarPerfil();
					$("#perfiles").hide();
					$("#owner").val('');
					$("#owner_exist").val('');
					$('#datos_reporte_5').hide();
					$('#datos_reporte_1').show();
					$('#reporte_panel').hide();
					$('#tipo_enfermedad').hide();
					//Se limpian los eventos
					eliminareventos();
					//Icono
					var icono = L.icon({
						iconUrl: '/uploads/'+x['objeto'][0]["image"],
						iconSize:     [68, 61], // size of the icon
						iconAnchor:   [65, 58], // point of the icon which will correspond to marker's location
						popupAnchor:  [-3, -76] // point from which the popup should open relative to the iconAnchor
					});
					//Se crea un nuevo marcador con los datos del ultimo guardado y muestra la informacion del mismo
					evento[0] = L.marker([x['objeto'][0]["lat"], x['objeto'][0]["lon"]],{animate: true, icon: icono}).addTo(map);

					//Se abre la informacion del evento
					var contenido =  "<ul><li><span class='titulo_dato_home'>Tipo de reporte:<span> "+x['objeto'][0]["tipo_reporte"]+"</span></span></li><li><span class='titulo_dato_home'>Enfermedad:<span>"+x['objeto'][0]["enfermedad"]+"</span></span></li><li><span class='titulo_dato_home'>Usuario que Reporta:<span> "+x['objeto'][0]["usuario"]+"</span></span></li><li><span class='titulo_dato_home'>Reportado el:<span> "+x['objeto'][0]['created']+"</span></span></li></ul>";
					/*$('#info_evento_home img').fadeIn();*/
					$('#info_evento_home').css('height','0px');
					$('#info_evento_home').css('height','135px');
					$('#cerrar_datos_reporte_home').fadeIn('slow');
					$('#datos_reporte_home').html(contenido);
					$('#datos_reporte_home').fadeIn('slow');
					
					//Se debe mostrar un formulario para agregar etiquetas y consejos
					/*$.get( "formularioEtiquetas", function( data ) {
						$( "#datos_reporte_home" ).append( data);
					});*/
					var id = {"evento": x["evento"]};
					$.ajax({
							type: 'POST',
							url: '/formularioEtiquetas', 
							data: id,
							}).done(function(formulario){
								$( "#datos_reporte_home" ).append( formulario);
								
							});
					
				}
				
				
			});
	
 }
 
 //Funcion para refrescar el punto en el mapa con la posicion
 function onMapClick(e) {
 
	eliminareventos();
	evento[0] = L.marker(e.latlng,{animate: true}).addTo(map);
	evento[0].bindPopup("<b>&iquestEsta no es tu ubicaci\u00f3n?</b><br> Prueba dando clic en <br/>otra parte del mapa ;) <br/>").openPopup()
	cuestionario(evento[0].getLatLng().lng+','+evento[0].getLatLng().lat);
	$("#reporte_panel").show();
}

function cuestionario(latlon){
	$('#reporte_panel').css('height','0px');
	$('#reporte_panel').css('height',"");
	//Se actualiza la latitud y longitud
	$('#latitud_logintud_reporte').val(latlon);	
}

//Funcion para obtener el Id de la categoria seleccionada y mostrar o no la seccion de enfermedad
function tipoReporte(select){
	var tipo = select.value;
	if(tipo == 1){
		$('#tipo_enfermedad').show();
	}else{
		$('#tipo_enfermedad').hide();
	}
}

//Funcion para obtener el Id del perfil seleccionado
function cambio(select){
	var idPerfil = select.value;
	if(idPerfil == ""){
		limpiarPerfil();
	}else{
		cargarPerfil(idPerfil);
	}
}

//Funcion para cargar el perfil seleccionado
function cargarPerfil(perfil){
	//funcion de ajax para recuperar los datos del perfil especifico
	
	var data = { perfilId : perfil };
	
	$.ajax({
			type: 'POST',
			url: '/getPerfil', 
			data: data,
			}).done(function(respuesta){
				var x = JSON.parse(respuesta);
				//alert(x['objeto'][0]['idPerfil']+"--"+x['existe']);
				//Se cargan los valores del formulario con los que se estan recibiendo
				cargarDatos(x);
				
			});

}

//Funcion para limpiar el perfil
function limpiarPerfil(){
	//Se recuperan los datos de fecha de nacimiento, etc.
	$("#cumple_reporte").val("");
	$("#cumple_reporte").val("");
	$("#pais_reporte").val("");
	$("#ciudad_reporte").val("");
	$("#municipio_reporte").val("");
	$("#nombre_reporte").val("");
	$("#apellido_reporte").val("");
	$("#telefono_reporte").val("");
	$("#celular_reporte").val("");
	$("#profesion_reporte").val("");
	$("#documentType_reporte").val("");
	$("#estadoCivil_reporte").val("");
	$("#numeroHijos_reporte").val("");
	$("#peso_reporte").val("");
	$("#tipoSangre_reporte").val("");
	$("#perfil").val("");

}

function cargarDatos(x){
	
	$("#perfil").val(x['objeto'][0]['idPerfil'] );
	
	//Se actualizan los datos del sexo de la persona
	if(parseInt(x['objeto'][0]['sex'])){
		$("#opcion_mujer").prop("checked", false);
		$("#opcion_hombre").prop("checked", true);
	}else{
		$("#opcion_mujer").prop("checked", true);
		$("#opcion_hombre").prop("checked", false);
	}
	
	$("#cumple_reporte").val(x['objeto'][0]['birthday']);
	$("#cumple_reporte").val(x['objeto'][0]['birthday']);
	$("#pais_reporte").val(x['objeto'][0]['country']);
	$("#ciudad_reporte").val(x['objeto'][0]['city']);
	$("#municipio_reporte").val(x['objeto'][0]['municipio']);
	$("#nombre_reporte").val(x['objeto'][0]['firtsName']);
	$("#apellido_reporte").val(x['objeto'][0]['lastName']);
	$("#telefono_reporte").val(x['objeto'][0]['phone']);
	$("#celular_reporte").val(x['objeto'][0]['cellphone']);
	$("#profesion_reporte").val(x['objeto'][0]['profesion']);
	$("#documentType_reporte").val(x['objeto'][0]['documentType']);
	$("#estadoCivil_reporte").val(x['objeto'][0]['estadoCivil']);
	$("#numeroHijos_reporte").val(x['objeto'][0]['numeroHijos']);
	$("#peso_reporte").val(x['objeto'][0]['peso']);
	$("#tipoSangre_reporte").val(x['objeto'][0]['tipoSangre']);

}
