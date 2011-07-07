//

if (!window.Sigma){
	window.Sigma={};
}
Sigma.Msg=Sigma.Msg || {};
SigmaMsg=Sigma.Msg;

Sigma.Msg.Grid = Sigma.Msg.Grid || {};

Sigma.Msg.Grid.es={
	LOCAL	: "ES",
	ENCODING		: "UTF-8",
	NO_DATA : "Sin Data",


	GOTOPAGE_BUTTON_TEXT: 'Ir a',

	FILTERCLEAR_TEXT: "Quitar Filtors",
	SORTASC_TEXT	: "Ascendente",
	SORTDESC_TEXT	: "Descendente",
	SORTDEFAULT_TEXT: "Original",

	ERR_PAGENUM		: "El nro de pagina debe ser un ebtero entre 1 y #{1}.",

	EXPORT_CONFIRM	: "Esta operacion afectara todos los registros de la tabla.\n\n( Presione \"Cancelar\" para limitarse a la pagina actual.)",
	OVER_MAXEXPORT	: "Numero de registros #{1} excede el maximo permitido .",

	PAGE_STATE	: "#{1} - #{2} displayed,  #{3}pages #{4} records totally.",
	PAGE_STATE_FULL	: "Pagina #{5}, #{1} - #{2} desplegados,  #{3} paginas #{4} registros.",

	SHADOWROW_FAILED: "Relevant info not available",
	NEARPAGE_TITLE	: "",
	WAITING_MSG : 'Favor espere...',

	NO_RECORD_UPDATE: "Nothing Modified",
	UPDATE_CONFIRM	: "Are you sure to save them?",
	NO_MODIFIED: "Nothing Modified",

	PAGE_BEFORE : 'Pagina',
	PAGE_AFTER : '',

	PAGESIZE_BEFORE :   '',
	PAGESIZE_AFTER :   'Por Pagina',

	RECORD_UNIT : '',
	
	CHECK_ALL : 'Marcar Todos',

	COLUMNS_HEADER : 'Columnas',

	DIAG_TITLE_FILTER : 'Opciones de Filtro',
	DIAG_NO_FILTER : 'Sin Filtro',
	TEXT_ADD_FILTER	: "Agregar",
	TEXT_CLEAR_FILTER	: "Quitar todos",
	TEXT_OK	: "OK",
	TEXT_DEL : "Borrar",
	TEXT_CANCEL	: "Cancela",
	TEXT_CLOSE	: "Cerrar",
	TEXT_UP : "Arriba",
	TEXT_DOWN : "Abajo",

	NOT_SAVE : "Do you want to save the changes? \n Click \"Cancel\" to discard.",

	DIAG_TITLE_CHART  : 'Chart',

	CHANGE_SKIN : "Skins",

	STYLE_NAME_DEFAULT : "Classic",
	STYLE_NAME_PINK : "Pink",
	STYLE_NAME_MAC : "Mac",

	MENU_FREEZE_COL : "Bloquear Colunas",
	MENU_SHOW_COL : "Esconder Colunas",
	MENU_GROUP_COL : "Agrupar Span",

	TOOL_RELOAD : "Actuaizar" ,
	TOOL_ADD : "Agrega" ,
	TOOL_DEL : "Borrar" ,
	TOOL_SAVE : "Guardar" ,

	TOOL_PRINT : "Imprimir" ,
	TOOL_XLS : "Exportar a xls" ,
	TOOL_PDF : "Exportar a pdf" ,
	TOOL_CSV : "Exportar a csv" ,
	TOOL_XML : "Exportar a xml",
	TOOL_FILTER : "Filtro" ,
	TOOL_CHART : "Chart" 

};

Sigma.Msg.Grid['default']=Sigma.Msg.Grid.es;


if (!Sigma.Msg.Validator){
	Sigma.Msg.Validator={ };
}

Sigma.Msg.Validator.es={

		'required'	: '{0#This field} es requerido.',
		'date'		: '{0#This field} must be in proper format ({1#YYYY-MM-DD}).',
		'time'		: '{0#This field} must be in proper format ({1#HH:mm}).',
		'datetime'	: '{0#This field} must be in proper format ({1#YYYY-MM-DD HH:mm}).',
		'email'		: '{0#This field} must be in proper email format.',
		'telephone'	: '{0#This field} must be in proper phone no format.',
		'number'	: '{0} debe ser un numeror.',
		'integer'	: '{0} debe ser entero.',
		'float'		: '{0} debe ser entero o decimal.',
		'money'		: '{0} debe ser entero o decimal con fraccion de 2 digitos.',
		'range'		: '{0} debe estar entre {1} y {2}.',
		'equals'	: '{0} debe ser igual a {1}.',
		'lessthen'	: '{0} debe ser menor a {1}.',
		'idcard'	: '{0} must be in proper ID format',

		'enchar'	: 'Letras, digitos o piso permitido solo para {0}',
		'cnchar'	: '{0} must be Chinese charactors',
		'minlength'	: '{0} debe contar con mas de {1} caracteres.',
		'maxlength'	: '{0} must tener menos de {1} caracteres.'

}

Sigma.Msg.Validator['default'] = Sigma.Msg.Validator.es;

//