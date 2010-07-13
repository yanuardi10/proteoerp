<?php
class Invfis extends Controller {

var $url = 'inventario/invfis/';

	function Invfis(){
		parent::Controller();
		$this->load->library("rapyd");
		$this->datasis->modulo_id(319,1);
	}

	function index(){
		redirect($this->url.'define');
	}

	function define(){
		$this->rapyd->load("dataform");

		$form0 = new DataForm('inventario/invfis/define/process/crear');
		$form0->_title = "Crear Inventario";
		$form0->alma = new dropdownField("Almacen", "alma");
		$form0->alma->options("SELECT TRIM(ubica),TRIM(ubides) FROM caub WHERE gasto='N' AND invfis='N' ORDER BY ubides");
		$form0->alma->rule='required';
		$form0->fecha = new dateonlyField("Fecha", "fecha");
		$form0->fecha->rule='required|chfecha';
		$form0->fecha->insertValue = date("Y-m-d");
		$form0->fecha->size=12;
		$form0->submit("btnsubmit","Crear Inventario F&iacute;sico");

		$form1 = new DataForm('inventario/invfis/define/process/contar');
		$form1->_title = "Introducir Resultados del Conteo de Inventario F&iacute;sico";
		$form1->inv = new dropdownField("Inventario Fisico", "inv");
		$form1->inv->rule = 'required';
		$form1->inv->style = 'width:400px';
		$form1->submit("btnsubmit","Introducir Conteo F&iacute;sico");

		$form2 = new DataForm('inventario/invfis/define/process/cerrar');
		$form2->_title = "Cierre de Inventario";
		$form2->inv = new dropdownField("Inventario Fisico", "inv2");
		$form2->inv->rule = 'required';
		$form2->inv->style = 'width:400px';
		$mSQL=$this->db->query("SHOW TABLES LIKE 'INV%'");
		foreach($mSQL->result_array() AS $row){
			foreach($row AS $key=>$value){
				$vval='Almacen:'.$this->datasis->dameval("SELECT ubides FROM caub WHERE ubica ='".substr($value,3,strlen($value)-11)."'").' de Fecha '.dbdate_to_human(substr($value,-8));
				$form2->inv->option($value,$vval);
				$form1->inv->option($value,$vval);
			}
		}
		$form2->submit("btnSiCero" ,"Cierre de Inventario (Asume existencia cero para los no contados)");
		$form2->submit("btnNoCero" ,"Cierre de Inventario (Pasa solo los contados)");

		$form0->build_form();
		$form1->build_form();
		$form2->build_form();

		$error='';
		//crea un nuevo inventario
		if ($form0->on_success()){
			$alma  = $form0->alma->newValue;
			$fecha = $form0->fecha->newValue;
			$error.=$this->_crear($alma,$fecha);
			if(strlen($error)==0)
				redirect($this->url.'define');
		}

		//entra en conteo
		if ($form1->on_success()){
			$inv=$form1->inv->newValue;
			redirect($this->url.'inven/'.$inv);
		}

		//cierra el inventario
		if ($form2->on_success()){
			$tabla=$form2->inv->newValue;
			if($this->input->post('btnSiCero')!==false){
				$error.=$this->_cerrar($tabla,false); //asume existencias en cero
			}else{
				$error.=$this->_cerrar($tabla,true);
			}
			if(strlen($error)==0)
				redirect($this->url.'define');
		}

		$data['content'] = "<div class='alert'>$error</div>";
		$data['content'] .= $form0->output.'</br>'.$form1->output.'</br>'.$form2->output;
		$data['title']   = '<h1>Inventario F&iacute;sico</h1>';
		$data["head"]    = $this->rapyd->get_head().script('jquery.js').script("plugins/jquery.numeric.pack.js").script("plugins/jquery.json.min.js");
		$this->load->view('view_ventanas', $data);
	}

	function inven($tabla){
		$this->rapyd->load("datagrid","dataobject","fields","datafilter2");

		$mSPRV=array(
				'tabla'   =>'sprv',
				'columnas'=>array(
				'proveed' =>'C&oacute;odigo',
				'nombre'=>'Nombre',
				'contacto'=>'Contacto'),
				'filtro'  =>array('proveed'=>'C&oacute;digo','nombre'=>'Nombre'),
				'retornar'=>array('proveed'=>'proveed'),
				'titulo'  =>'Buscar Proveedor');

		$bSPRV=$this->datasis->modbus($mSPRV);

		$link2=site_url('inventario/common/get_linea');
		$link3=site_url('inventario/common/get_grupo');

		$script='
		$(document).ready(function(){

			$("#depto").change(function(){
				depto();
				$.post("'.$link2.'",{ depto:$(this).val() },function(data){$("#linea").html(data);})
				$.post("'.$link3.'",{ linea:"" },function(data){$("#grupo").html(data);})
			});
			$("#linea").change(function(){
				linea();
				$.post("'.$link3.'",{ linea:$(this).val() },function(data){$("#grupo").html(data);})
			});

			$("#grupo").change(function(){
				grupo();
			});
			depto();
			linea();
			grupo();
		});

		function depto(){
			if($("#depto").val()!=""){
				$("#nom_depto").attr("disabled","disabled");
			}
			else{
				$("#nom_depto").attr("disabled","");
			}
		}

		function linea(){
			if($("#linea").val()!=""){
				$("#nom_linea").attr("disabled","disabled");
			}
			else{
				$("#nom_linea").attr("disabled","");
			}
		}

		function grupo(){
			if($("#grupo").val()!=""){
				$("#nom_grupo").attr("disabled","disabled");
			}
			else{
				$("#nom_grupo").attr("disabled","");
			}
		}';

		$filter = new DataFilter2("Filtro por Producto");
		$filter->db->select("e.existen,e.modificado,e.contado,e.agregar,e.quitar,e.sustituir,a.tipo AS tipo,id,e.codigo,a.descrip,precio1,precio2,precio3,precio4,b.nom_grup AS nom_grup,b.grupo AS grupoid,c.descrip AS nom_linea,c.linea AS linea,d.descrip AS nom_depto,d.depto AS depto");
		$filter->db->from("$tabla AS e");
		$filter->db->join('sinv AS a','a.codigo=e.codigo');
		$filter->db->join('grup AS b','a.grupo=b.grupo');
		$filter->db->join('line AS c','b.linea=c.linea');
		$filter->db->join('dpto AS d','c.depto=d.depto');
		$filter->db->where('activo','S');
		$filter->db->where('actualizado IS NULL','',false);
		$filter->script($script);

		$filter->codigo = new inputField('C&oacute;digo', 'codigo');
		$filter->codigo ->db_name = 'e.codigo';
		$filter->codigo -> size=25;

		$filter->descrip = new inputField("Descripci&oacute;n", "descrip");
		$filter->descrip->db_name='CONCAT_WS(" ",a.descrip,a.descrip2)';
		$filter->descrip -> size=25;

		$filter->tipo = new dropdownField("Tipo", "tipo");
		$filter->tipo->db_name=("a.tipo");
		$filter->tipo->option("","Todos");
		$filter->tipo->option("Articulo","Art&iacute;culo");
		$filter->tipo->option("Servicio","Servicio");
		$filter->tipo->option("Descartar","Descartar");
		$filter->tipo->option("Consumo","Consumo");
		$filter->tipo->option("Fraccion","Fracci&oacute;n");
		$filter->tipo ->style='width:220px;';

		$filter->clave = new inputField("Clave", "clave");
		$filter->clave -> size=25;

		$filter->activo = new dropdownField("Activo", "activo");
		$filter->activo->option("","Todos");
		$filter->activo->option("S","Si");
		$filter->activo->option("N","No");
		$filter->activo ->style='width:220px;';

		$filter->proveed = new inputField("Proveedor", "proveed");
		$filter->proveed->append($bSPRV);
		$filter->proveed->clause ="in";
		$filter->proveed->db_name='( a.prov1, a.prov2, a.prov3 )';
		$filter->proveed -> size=25;

		$filter->depto2 = new inputField("Departamento", "nom_depto");
		$filter->depto2->db_name="d.descrip";
		$filter->depto2 -> size=10;

		$filter->depto = new dropdownField("Departamento","depto");
		$filter->depto->db_name="d.depto";
		$filter->depto->option("","Seleccione un Departamento");
		$filter->depto->options("SELECT depto, descrip FROM dpto WHERE tipo='I' ORDER BY depto");
		$filter->depto->in="depto2";

		$filter->linea = new inputField("Linea", "nom_linea");
		$filter->linea->db_name="c.descrip";
		$filter->linea -> size=10;

		$filter->linea2 = new dropdownField("L&iacute;nea","linea");
		$filter->linea2->db_name="c.linea";
		$filter->linea2->option("","Seleccione un Departamento primero");
		$filter->linea2->in="linea";
		$depto=$filter->getval('depto');
		if($depto!==FALSE){
			$filter->linea2->options("SELECT linea, descrip FROM line WHERE depto='$depto' ORDER BY descrip");
		}else{
			$filter->linea2->option("","Seleccione un Departamento primero");
		}

		$filter->grupo2 = new inputField("Grupo", "nom_grupo");
		$filter->grupo2->db_name="b.nom_grup";
		$filter->grupo2 -> size=10;

		$filter->grupo = new dropdownField("Grupo", "grupo");
		$filter->grupo->db_name="b.grupo";
		$filter->grupo->option("","Seleccione una L&iacute;nea primero");
		$filter->grupo->in="grupo2";
		$linea=$filter->getval('linea2');
		if($linea!==FALSE){
			$filter->grupo->options("SELECT grupo, nom_grup FROM grup WHERE linea='$linea' ORDER BY nom_grup");
		}else{
			$filter->grupo->option("","Seleccione un Departamento primero");
		}

		$filter->marca = new dropdownField("Marca", "marca");
		$filter->marca->option('','Todas');
		$filter->marca->options("SELECT TRIM(marca) AS clave, TRIM(marca) AS valor FROM marc ORDER BY marca");
		$filter->marca -> style='width:220px;';

		$filter->buttons("reset","search");
		$filter->build();

		function caja($campo2,$valor,$codigo,$readonly=false){
			$campo = new inputField2("Title", $campo2);
			$campo->status = "create";
			$campo->css_class='inputnum';
			$campo->size=10;
			$campo->insertValue=$valor;
			$campo->readonly = $readonly;
			$campo->name = $codigo;
			$campo->id   = 'I'.$campo2.'_'.$codigo;
			$campo->build();
			return $campo->output;
		}

		function pinta($modifi,$cont){
			if($modifi==null)
				return $cont;
			else
				return "<span style='color:FF0000;'>$cont</span>";
		}

		$grid = new DataGrid("Inventario Fisico");
		$grid->per_page = 10;
		$grid->db->limit = 10;
		$grid->use_function('caja','pinta');

		$grid->column_orderby("Codigo","<pinta><#modificado#>|<#codigo#></pinta>","codigo",'align=center');
		$grid->column_orderby("Descripci&oacute;n","<pinta><#modificado#>|<#descrip#></pinta>","descrip");
		$grid->column_orderby("Anterior","<pinta><#modificado#>|<#existen#></pinta>","existen",'align=right');
		$grid->column_orderby("Contado","<caja>c|<#contado#>|<#codigo#>|true</caja>");
		$grid->column("Agregar","<caja>a|<#agregar#>|<#codigo#></caja>");
		$grid->column("Quitar","<caja>q|<#quitar#>|<#codigo#></caja>");
		$grid->column("Sustituir","<caja>s|<#sustituir#>|<#codigo#></caja>");

		$grid->build();
		//echo $grid->db->last_query();

		$data['script']  ='<script language="javascript" type="text/javascript">
		$(function() {
			$(".inputnum").numeric(".");

			function traer(cod){
				$.post("'.site_url($this->url.'traer').'",{ codigo:cod,tabla:"'.$tabla.'" },function(data){
					$("#Ic_"+cod).val(data);
				})
			}

			$("input[id^=\'I\']").focus(function(){
				cod =$(this).attr("name");
				traer(cod);
			});

			$("input[id^=\'Ia_\']").change(function(){
				cod =$(this).attr("name");
				val = $("#Ia_"+cod).val();
				con = $("#Ic_"+cod).val();
				$.post("'.site_url($this->url.'agregar').'",{ codigo:cod,valor:val,tabla:"'.$tabla.'",contado:con },function(data){
					$("#Ia_"+cod).val(0);
					if(data){
						alert(data);
					}
				})
				traer(cod);
			});

			$("input[id^=\'Iq_\']").change(function(){
					cod =$(this).attr("name");
					val = $("#Iq_"+cod).val();
					con = $("#Ic_"+cod).val();
					$.post("'.site_url($this->url.'quitar').'",{ codigo:cod,valor:val,tabla:"'.$tabla.'",contado:con },function(data){
						$("#Iq_"+cod).val(0);
						if(data){
							alert(data);
						}
					})
					traer(cod);
			});

			$("input[id^=\'Is_\']").change(function(){
					cod =$(this).attr("name");
					val = $("#Is_"+cod).val();
					con = $("#Ic_"+cod).val();
					$.post("'.site_url($this->url.'sustituir').'",{ codigo:cod,valor:val,tabla:"'.$tabla.'",contado:con },function(data){
						$("#Is_"+cod).val(0);
						if(data){
							alert(data);
						}
					})
					traer(cod);
			});
		});
		</script>';

		$salida=anchor($this->url,"Regresar");
		$data['content'] = $filter->output.$salida.$grid->output;
		//$data['title']   = "($codigoadm) $codigoadmdes $tipo";
		$data["head"]    = script("jquery.js").script("plugins/jquery.numeric.pack.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function agregar(){
		$this->_actualiza('+');
	}

	function quitar(){
		$this->_actualiza('-');
	}

	function sustituir(){
		$this->_actualiza('=');
	}

	function traer(){
		$codigo = $this->db->escape($this->input->post('codigo'));
		$tabla  = $this->input->post('tabla');
		echo $this->datasis->dameval("SELECT contado FROM $tabla WHERE codigo=$codigo");
	}

	function _crear($alma,$fecha){
		$tabla='INV'.$alma.$fecha;
		$error='';

		if(!$this->db->table_exists($tabla)){
			$dbalma=$this->db->escape($alma);
			$mSQL="CREATE TABLE IF NOT EXISTS `$tabla`(
				`codigo` VARCHAR(15) NULL,
				`grupo` VARCHAR(4) NULL,
				`alma` VARCHAR(4) NULL DEFAULT $dbalma,
				`existen` DECIMAL(13,2) NULL DEFAULT '0',
				`contado` DECIMAL(10,1) NOT NULL DEFAULT '0.0',
				`agregar` DECIMAL(10,1) NOT NULL DEFAULT '0.0',
				`quitar` DECIMAL(10,1) NOT NULL DEFAULT '0.0',
				`sustituir` DECIMAL(10,1) NOT NULL DEFAULT '0.0',
				`fecha` DATETIME NOT NULL DEFAULT '0000-00-00 00:00',
				`modificado` DATETIME DEFAULT '0000-00-00 00:00',
				`actualizado` DATETIME DEFAULT '0000-00-00 00:00',
				`pond` DECIMAL(10,1) NOT NULL DEFAULT '0.0',
				PRIMARY KEY (`codigo`)
			)
			COLLATE=".$this->db->dbcollat."
			ENGINE=MyISAM
			ROW_FORMAT=DEFAULT";
			$ban=$this->db->simple_query($mSQL);
			if(!$ban){ $error.='Error creando la tabla parcial '; memowrite($mSQL,'INVFIS');  }

			$mSQL = "INSERT IGNORE INTO `$tabla`
			(`codigo`,`grupo`,`existen`,`contado`,`agregar`,`quitar`,`sustituir`,`fecha`,`modificado`,`actualizado`,`pond`)
			SELECT TRIM(a.codigo),TRIM(a.grupo),b.existen,0 contado,0 agregar,0 quitar,0 sustituir, NOW() fecha,CAST(NULL AS DATE ) modificado, CAST(NULL AS DATE) actualizado,a.pond
			FROM sinv a
			LEFT JOIN itsinv b ON a.codigo=b.codigo AND b.alma=$dbalma";
			$ban=$this->db->simple_query($mSQL);
			if(!$ban){ $error.='Error llenando la tabla parcial '; memowrite($mSQL,'INVFIS'); }
		}else{
			$error.="Ya existe un inventario creado para el almac&eacute;n $alma y la fecha seleccionada";
		}
		return $error;
	}

	function _cerrar($tabla,$tipo){
		$fecha  = date_format(date_create_from_format('Ymd', substr($tabla,-8)), 'Y-m-d');
		$nstra  = $this->db->escape($this->datasis->fprox_numero('nstra'));
		$alma   = substr($tabla,3,strlen($tabla)-11);
		$alma   = $this->db->escape($alma);
		$error  ='';

		if($tipo)
			$where='modificado IS NOT NULL AND actualizado IS NULL'; //no asume ceros
		else
			$where='actualizado IS NULL'; //asume ceros
		$fromwhere="FROM $tabla a JOIN sinv b ON a.codigo=b.codigo WHERE $where";

		$cana=$this->datasis->dameval("SELECT COUNT(*) $fromwhere");
		if($cana>0){
			$id=$this->_idsem($tabla);
			$seg=sem_get($id,1,0666,-1);
			sem_acquire($seg);

			$mSQL="INSERT INTO itstra (`numero`,`codigo`,`descrip`,`cantidad`,`anteri`)
				SELECT $nstra,a.codigo,CONCAT_WS(b.descrip,b.descrip2)descrip,IF(a.modificado IS NULL,0,a.contado),a.existen $fromwhere";

			$ban = $this->db->simple_query($mSQL);
			if(!$ban){$error.="No se pudo crear el registro en stra"; memowrite($mSQL,'INVFIS');}

			$mSQL="INSERT INTO stra (`numero`,`fecha`,`envia`,`recibe`,`observ1`)
				VALUES ($nstra,'$fecha','INFI',$alma,'INVENTARIO FISICO')";

			$ban = $this->db->simple_query($mSQL);
			if(!$ban) {$error.="No se pudo crear el registro en stra "; memowrite($mSQL,'INVFIS'); }

			if(strlen($error)==0){
				$mSQL="DROP TABLE $tabla";
				$ban = $this->db->simple_query($mSQL);
				if(!$ban) {$error.="No se pudo limpiar la base de datos"; memowrite($mSQL,'INVFIS');}
				sem_release($seg);
				sem_remove($seg);
				logusu('INVFIS',"Se creo transferencia de Inventario Fisico $nstra");
			}else{
				sem_release($seg);
			}
		}else{
			$error='No hay productos contados para cerrar el inventario';
		}
		return $error;
	}

	function _actualiza($ac){
		$codigo = $this->db->escape($this->input->post('codigo'));
		$valor  = $this->input->post('valor');
		$tabla  = $this->input->post('tabla');
		$contado= $this->input->post('contado');
		$id=$this->_idsem($tabla);
		if(is_numeric($valor)){
			$seg=sem_get($id,1,0666,-1);
			sem_acquire($seg);
			if($this->db->table_exists($tabla)){
				$condb  = $this->datasis->damerow("SELECT contado,actualizado FROM ${tabla} WHERE codigo=${codigo}");
				if($condb['actualizado']!=null)
					echo "Advertencia: El productos ya fue actualizado, no lo puede modificar";
				elseif(round($contado,2) != round($condb['contado'],2))
					echo "Advertencia: El valor Contado (${contado}) se modifico a ($condb[contado]) mientras usted modificaba el valor";
				else{
					switch($ac){
						case '=':
							$mSQL="UPDATE ${tabla} SET contado=${valor},modificado=now() WHERE codigo=${codigo}";
						break;
						case '+':
							$mSQL="UPDATE ${tabla} SET contado=contado+${valor},modificado=now() WHERE codigo=${codigo}";
						break;
						case '-':
							$mSQL="UPDATE ${tabla} SET contado=contado-${valor},modificado=now() WHERE codigo=${codigo}";
						break;
					}
					$ban=$this->db->simple_query($mSQL);
					if(!$ban){ echo "Error actualizando"; }
				}
				sem_release($seg);
			}else{
				sem_release($seg);
				sem_remove($seg);
				echo "Advertencia: Este inventario ya fue cerrado, no puede modificar mas nada";
			}
		}
	}

	function _idsem($var){
		$len=strlen($var);
		$int=0;
		for($i=0;$i<$len;$i++){
			$int+=ord($var[$i]);
		}
	}
}