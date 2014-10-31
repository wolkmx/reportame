$(document).ready(function(){

	//Funcion para cerrrar el formulario
	$("#cerrar_formulario").click(function(){ $("#reporte_panel").hide(); });
	
	map.on('click', onMapClick);
	
	//Funcion para capturar el avance del formulario de reporte de incidencia
	$(".siguiente").click(function(event){
		//Se obtiene el paso en al que se desea acceder del formulario
		var paso = $(this).attr("id").split("_");
		var numero = paso[2];

		//Switch para verificar que haya llenado los campos obligatorios
		switch (parseInt(numero)) {
			case 2:
				pasoDos();
				break;
			case 3:
				pasoTres();
				break;
			case 4:
				pasoCuatro();
				break;
			case 5:
				day = "Thursday";
				break;
			case 6:
				day = "Friday";
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
	if($("#enfermedad_reporte").val() == "" || $("#enfermedad_reporte").val() == null ){
		alert("Debes escoger una enfermedad primero");
		event.stopPropagation();
		
	}
 }
 
  //Funcion para verificar el paso 3
 function pasoTres(event){
	if($("#descripcion_reporte").val() == "" || $("#descripcion_reporte").val() == null ){
		/*alert("Comparte un poco de informacion sobre este evento, hace cuanto lo detectaron, es recurrente, etc.");
		event.stopPropagation();*/
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
				if(x['existe']){
				$("#owner").val(1);
					//Se actualizan los datos del sexo de la persona
					if(x['objeto'][0]['sex']){
						$("#opcion_mujer").prop("checked", false);
						$("#opcion_hombre").prop("checked", true);
					}else{
						$("#opcion_mujer").prop("checked", true);
						$("#opcion_hombre").prop("checked", false);
					}
					//Se recuperan los datos de fecha de nacimiento, etc.
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
					
				}else{
					
					//alert("No existe");
				}
				
			});

		
	}else{
		//Si es de otra persona se debe buscar si existen perfiles existentes
		$("#owner").val(0);
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
					//Se construyen la lista desplegable correspondiente
					var lista = "<option value='volvo'>Volvo</option>";
					$("#opciones_reporte").html(lista);
					//Se vacian los datos del formulario
					limpiarPerfil();
					
					
				}else{
					//Si no existe ningun perfil se debera mostrar un mensaje que informe al usuario de que debe crear nuevos perfiles
				}
				
			});
	}
 }
 
 //Funcion para refrescar el punto en el mapa con la posicion
 function onMapClick(e) {
 
	eliminareventos();
	evento[0] = L.marker(e.latlng,{animate: true}).addTo(map);
	evento[0].bindPopup("<b>&iquestEsta no es tu ubicaci\u00f3n?</b><br> Prueba dando clic en <br/>otra parte del mapa ;) <br/>").openPopup()
	cuestionario(e.latlng);
	$("#reporte_panel").show();
}

function cuestionario(latlon){
	$('#reporte_panel').css('height','0px');
	$('#reporte_panel').css('height',"");
	$('#datos_reporte_1').show();
	
	//Se crea el formulario que aparecera en el div
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
	//buscar el perfil en ajax
	

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

}
