$(document).ready(function(){

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
		alert("Comparte un poco de informacion sobre este evento, hace cuanto lo detectaron, es recurrente, etc.");
		event.stopPropagation();
	}
 }
 
   //Funcion para verificar el paso 4
 function pasoCuatro(event){
	//Se verifica la opcion seleccionada si es propio buscara el perfil existente, si no es propio dejara cargar un nuevo perfil
	//@todo falta agregar la opcion de cargar los perfiles existentes
	//Si es propio
	if($('input[name=group1]:checked', '#reporte_formulario').val() == "propio"){
		//Se hace la consulta via ajax para saber si ya tiene su perfil creado
		alert("es propio");
		event.stopPropagation();
	}else{
		alert("es de otra persona");
	}
 }
 
 //Funcion para refrescar el punto en el mapa con la posicion
 function onMapClick(e) {
 
	eliminareventos();
	evento[0] = L.marker(e.latlng,{animate: true}).addTo(map);
	evento[0].bindPopup("<b>&iquestEsta no es tu ubicaci\u00f3n?</b><br> Prueba dando clic en <br/>otra parte del mapa ;) <br/>").openPopup()
	cuestionario(e.latlng);
}

function cuestionario(latlon){
	$('#reporte_panel').css('height','0px');
	$('#reporte_panel').css('height',"");
	$('#datos_reporte_1').show();
	
	//Se crea el formulario que aparecera en el div

}