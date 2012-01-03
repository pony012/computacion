/* Configuración Regional para el timepicker */
$.datepicker.regional['es'] = {
	closeText: 'Cerrar',
	prevText: '&#x3c;Ant',
	nextText: 'Sig&#x3e;',
	currentText: 'Hoy',
	monthNames: ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
	'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
	monthNamesShort: ['Ene','Feb','Mar','Abr','May','Jun',
	'Jul','Ago','Sep','Oct','Nov','Dic'],
	dayNames: ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'],
	dayNamesShort: ['Dom','Lun','Mar','Mié','Juv','Vie','Sáb'],
	dayNamesMin: ['D','L','M','I','J','V','S'],
	weekHeader: 'Sm',
	dateFormat: 'dd/mm/yy',
	firstDay: 1,
	isRTL: false,
	showMonthAfterYear: false,
	yearSuffix: ''};
$.datepicker.setDefaults($.datepicker.regional['es']);
$.timepicker.regional['es'] = {
	timeOnlyTitle: 'Seleccionar tiempo',
	timeText: 'Tiempo',
	hourText: 'Hora',
	minuteText: 'Minutos',
	secondText: 'Segundos',
	millisecText: 'Milisegundos',
	currentText: 'Hoy',
	closeText: 'Hecho',
	ampm: false
};
$.timepicker.setDefaults($.timepicker.regional['es']);
