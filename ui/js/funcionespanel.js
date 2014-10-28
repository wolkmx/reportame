$(document).ready(function(){

	map.on('click', onMapClick);

 });
 
 var popup = L.popup();
 
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