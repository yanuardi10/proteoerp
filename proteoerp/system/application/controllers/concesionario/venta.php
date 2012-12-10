<?php
require_once(BASEPATH.'application/controllers/ventas/sfac.php');
class venta extends sfac_add {

	var $titp='Veh&iacute;culos';
	var $tits='Ventas de Veh&iacute;culos';
	var $url ='concesionario/venta/';

	function venta(){
		parent::Controller();
		$this->back_dataedit='compras/scst/datafilter';
		$this->load->library('rapyd');
		//$this->datasis->modulo_id(216,1);
	}

	function index($id=null){
		if(is_null($id)) redirect('concesionario/inicio');
		$this->rapyd->load('dataedit');
		$iva  = $this->datasis->ivaplica();

		$sel=array('a.codigo_sinv','a.modelo','a.color','a.motor','a.carroceria','a.uso','a.anio','a.placa','b.iva',
		'a.peso','a.precioplaca','a.transmision','b.precio1','b.precio2','b.precio3','b.precio4','b.descrip','a.clase','a.tipo');
		$this->db->select($sel);
		$this->db->from('sinvehiculo AS a');
		$this->db->join('sinv AS b','a.codigo_sinv=b.codigo');
		$this->db->where('a.id',$id);
		//$this->db->where('a.id_sfac IS NULL');
		$query = $this->db->get();

		if ($query->num_rows() > 0){
			$row = $query->row();

			$iiva        = $row->iva;
			$codigo_sinv = $row->codigo_sinv;
			$modelo      = $row->modelo;
			$color       = $row->color;
			$motor       = $row->motor;
			$carroceria  = $row->carroceria;
			$uso         = $row->uso;
			$anio        = $row->anio;
			$peso        = $row->peso;
			$placa       = $row->placa;
			$transmision = $row->transmision;
			$descrip     = $row->descrip;
			$precioplaca = $row->precioplaca;
			$clase       = $row->clase;
			$tipo        = $row->tipo;
			$precio1     = round($row->precio1*100/(100+$iiva),2);
			$precio2     = round($row->precio2*100/(100+$iiva),2);
			$precio3     = round($row->precio3*100/(100+$iiva),2);
			$precio4     = round($row->precio4*100/(100+$iiva),2);

		}

		$jsc='function calcula(){
			if($("#vh_precio").val().length>0) base=parseFloat($("#vh_precio").val()); else base=0;
			if($("#vh_tasa").val().length>0  ) tasa=parseFloat($("#vh_tasa").val())  ; else tasa=0;
			$("#vh_monto").text(nformat(base*(1+(tasa/100))+'.$precioplaca.',2));
		}

		function calculaiva(){
			if($("#vh_tasa").val().length>0){
				tasa=parseFloat($("#vh_tasa").val());
				if($("#vh_montoiva").val().length>0) montoiva=parseFloat($("#montoiva").val()); else montoiva=0;
				$("#vh_base").val(roundNumber(montoiva*100/tasa,2));
			}
		}

		function post_modbus_sinv(){
			$("#descrip_0_val").text($("#descrip_0").val());
		}

		function post_modbus_sprv(){
			$("#nombre_val").text($("#nombre").val());
		}
		';

		$edit = new DataForm($this->url.'venta/'.$id.'/insert');

		$edit->back_url = site_url($this->url.'filteredgrid');

		$edit->cliente = new inputField('Cliente','cod_cli');
		$edit->cliente->size = 6;
		$edit->cliente->autocomplete=false;
		$edit->cliente->rule='required|existescli';
		//$edit->cliente->append($boton);
		$edit->cliente->group = 'Datos de la factura';

		$edit->rifci = new freeField('Modelo', 'rif','<span id="rifci_val"></span>');
		$edit->rifci->group = 'Datos del veh&iacute;culo';
		$edit->rifci->in='cliente';

		$edit->nombre = new inputField('Nombre', 'nombre');
		$edit->nombre->size = 50;
		$edit->nombre->maxlength=40;
		$edit->nombre->type  = 'inputhidden';
		$edit->nombre->group = 'Datos de la factura';
		$edit->nombre->in = 'cliente';

		$edit->almacen= new dropdownField ('Almac&eacute;n', 'almacen');
		$edit->almacen->options('SELECT ubica,ubides FROM caub WHERE gasto="N" ORDER BY ubides');
		$edit->almacen->rule='required';
		$alma = $this->secu->getalmacen();
		if(strlen($alma)<=0){
			$alma = $this->datasis->traevalor('ALMACEN');
		}
		$edit->almacen->insertValue=$alma;
		$edit->almacen->style='width:200px;';
		$edit->almacen->group = 'Datos de la factura';

		$edit->vd = new  dropdownField ('Vendedor', 'vd');
		$edit->vd->options('SELECT vendedor, CONCAT(vendedor,\' \',nombre) nombre FROM vend ORDER BY vendedor');
		$edit->vd->style='width:200px;';
		$edit->vd->insertValue=$this->secu->getvendedor();
		$edit->vd->group = 'Datos de la factura';

		$edit->codigo = new freeField('Modelo', 'codigo_0',$codigo_sinv);
		$edit->codigo->group = 'Datos del veh&iacute;culo';

		$edit->descrip = new freeField('Modelo','modelo',$modelo);
		$edit->descrip->group    = 'Datos del veh&iacute;culo';
		$edit->descrip->in='codigo';

		$edit->anio = new freeField('A&ntildeo','vh_anio',$anio);
		$edit->anio->group = 'Datos del veh&iacute;culo';
		$edit->anio->in='codigo';

		$edit->color = new freeField('Color','vh_color',$color);
		$edit->color->group = 'Datos del veh&iacute;culo';

		$edit->motor = new freeField('Serial de Motor','vh_motor','<b>'.$motor.'</b>');
		$edit->motor->group = 'Datos del veh&iacute;culo';

		$edit->carroceria = new freeField('Serial de Carrocer&iacute;a','vh_carroceria','<b>'.$carroceria.'</b>');
		$edit->carroceria->group = 'Datos del veh&iacute;culo';

		$edit->uso = new  freeField('Uso','vh_uso',$uso);
		$edit->uso->group = 'Datos del veh&iacute;culo';

		$edit->tipo = new  freeField('Tipo','vh_tipo',$tipo);
		$edit->tipo->group = 'Datos del veh&iacute;culo';

		$edit->clase = new  freeField('Clase','vh_clase',$clase);
		$edit->clase->group = 'Datos del veh&iacute;culo';

		$edit->transmision = new  freeField('Transmisi&oacute;n','vh_transmision',$transmision);
		$edit->transmision->group = 'Datos del veh&iacute;culo';

		$edit->peso = new freeField('Peso Kg.','peso',nformat($peso));
		$edit->peso->group = 'Datos del veh&iacute;culo';

		$edit->placa = new freeField('Placa','vh_placa',$placa);
		$edit->placa->group = 'Datos del veh&iacute;culo';

		$edit->precioplaca = new freeField('Precio placa.','vh_precioplaca',$precioplaca);
		$edit->precioplaca->group = 'Datos del financieros';

		$edit->base =  new dropdownField('Monto base de venta','vh_precio');
		$edit->base->rule  = 'required|numeric';
		$edit->base->style = 'width:180px';
		$edit->base->group = 'Datos del financieros';
		$edit->base->option($precio1,nformat($precio1));
		$edit->base->option($precio2,nformat($precio2));
		$edit->base->option($precio3,nformat($precio3));
		$edit->base->option($precio4,nformat($precio4));

		$edit->tasa =  new dropdownField('Tasa %','vh_tasa');
		$edit->tasa->rule  = 'required|numeric';
		$edit->tasa->style = 'width:100px';
		$edit->tasa->mode  = 'autohide';
		$edit->tasa->insertValue=$iva['tasa'];
		$edit->tasa->group = 'Datos del financieros';
		foreach($iva AS $nom=>$val) $edit->tasa->option($val,nformat($val).'%');

		$edit->precio =  new freeField('Monto a pagar','monto','<b id="vh_monto">0.0</b>');

		$accion="javascript:window.location='".site_url('concesionario/inicio')."'";
		$edit->button('btn_cargar','Regresar',$accion,'BL');
		$edit->submit('btnsubmit','Realizar venta');
		$edit->build_form();

		if($edit->on_success()){
			$this->genesal=false;

			$sel=array('a.rifci','a.dire11');
			$this->db->select($sel);
			$this->db->from('scli AS a');
			$this->db->where('a.cliente',$edit->cliente->newValue);
			//$this->db->where('a.id_sfac IS NULL');
			$query = $this->db->get();
			if ($query->num_rows() > 0){
				$row    = $query->row();
				$rifci  = $row->rifci;
				$dire11 = $row->dire11;
			}
			$descrip=$this->datasis->dameval("SELECT descrip FROM sinv WHERE codigo='PLACA'");

			$_POST['btn_submit']  = 'Guardar';
			$_POST['pfac']        = '';
			$_POST['fecha']       = date('d/m/Y');
			$_POST['cajero']      = $this->secu->getcajero();
			$_POST['vd']          = $edit->vd->newValue;
			$_POST['almacen']     = $edit->almacen->newValue;
			$_POST['tipo_doc']    = 'F';
			$_POST['factura']     = '';
			$_POST['cod_cli']     = $edit->cliente->newValue;
			$_POST['sclitipo']    = '1';
			$_POST['nombre']      = $edit->nombre->newValue;
			$_POST['rifci']       = $rifci ;
			$_POST['direc']       = $dire11;

			$_POST['codigoa_0']   = 'PLACA';
			$_POST['desca_0']     = (empty($descrip))? 'PLACA':$descrip;
			//$_POST['detalle_0']   = 'PLACA '.$placa;
			$_POST['detalle_0']   = '';
			$_POST['cana_0']      = 1;
			$_POST['preca_0']     = $precioplaca;
			$_POST['tota_0']      = $precioplaca;
			$_POST['precio1_0']   = $precioplaca;
			$_POST['precio2_0']   = $precioplaca;
			$_POST['precio3_0']   = $precioplaca;
			$_POST['precio4_0']   = $precioplaca;
			$_POST['itiva_0']     = 0;
			$_POST['sinvpeso_0']  = 0;
			$_POST['sinvtipo_0']  = 'Servicio';

			$_POST['codigoa_1']   = $codigo_sinv;
			$_POST['desca_1']     = $modelo;
			$_POST['detalle_1']   = '';
			$_POST['cana_1']      = 1;
			$_POST['preca_1']     = $edit->base->newValue;
			$_POST['tota_1']      = $edit->base->newValue;
			$_POST['precio1_1']   = $precio1;
			$_POST['precio2_1']   = $precio2;
			$_POST['precio3_1']   = $precio3;
			$_POST['precio4_1']   = $precio4;
			$_POST['itiva_1']     = $edit->tasa->newValue;
			$_POST['sinvpeso_1']  = $peso;
			$_POST['sinvtipo_1']  = 'Articulo';

			$totals = $precioplaca+$edit->base->newValue;
			$iva    = $edit->base->newValue*($edit->tasa->newValue/100);
			$totalg = $totals+$iva;

			$_POST['tipo_0']      = '';
			$_POST['sfpafecha_0'] = '';
			$_POST['num_ref_0']   = '';
			$_POST['banco_0']     = '';
			$_POST['monto_0']     = $totalg;

			$_POST['totals']      = $totals;
			$_POST['iva']         = $iva   ;
			$_POST['totalg']      = $totalg;

			$rt=$this->dataedit();
			if(preg_match('/Venta Guardada (?P<id>\d+)/', $rt, $matches)){

				$data=array();
				$data['id_sfac'] = $this->claves['id'];
				$mSQL = $this->db->update_string('sinvehiculo', $data,'id='.$this->db->escape($id));
				$this->db->simple_query($mSQL);

				//$content = $rt.br();
				//$content.= anchor('formatos/ver/FACTURA/'.$this->claves['id'],'Descargar factura').br();
				//$content.= anchor($this->url,'Regresar');
                //
				//$data['content'] = $content;
				//$data['script']  = script('jquery.js');
				//$data['script'] .= script('jquery-ui.js');
				//$data['script'] .= script('plugins/jquery.numeric.pack.js');
				//$data['script'] .= script('plugins/jquery.floatnumber.js');
				//$data['script'] .= script('plugins/jquery.ui.autocomplete.autoSelectOne.js');
				//$data['script'] .= phpscript('nformat.js');
				//$data['head']    = $this->rapyd->get_head();
				//$data['head']   .= style('redmond/jquery-ui-1.8.1.custom.css');
				//$data['title']   = heading($this->tits);
				//$this->load->view('view_ventanas', $data);
				redirect($this->url.'dataprint/modify/'.$data['id_sfac']);
				return;
			}else{
				$edit->error_string =  htmlentities($rt);
				$edit->build_form();
			}
		}

		$script= '<script type="text/javascript" >
		$(function() {
			$(".inputnum").numeric(".");
			$(".inputonlynum").numeric();
			$("#vh_tasa").change(function(){ calcula(); });
			$("#vh_precio").change(function(){ calcula(); });
			//$("#vh_base").bind("keyup",function() { calcula(); });
			//$("#vh_montoiva").bind("keyup",function() { calculaiva(); });
			calcula();

			$("#cod_cli").autocomplete({
				source: function( req, add){
					$.ajax({
						url:  "'.site_url('ajax/buscascli').'",
						type: "POST",
						dataType: "json",
						data: "q="+req.term,
						success:
							function(data){
								var sugiere = [];
								if(data.length==0){
									$("#cod_cli").val("");
									$("#nombre").val("");
									$("#nombre_val").text("");
									$("#rifci_val").text("");

								}else{
									$.each(data,
										function(i, val){
											sugiere.push( val );
										}
									);
								}
								add(sugiere);
							},
					})
				},
				minLength: 2,
				select: function( event, ui ) {
					$("#cod_cli").attr("readonly", "readonly");
					$("#nombre").val(ui.item.nombre);
					$("#nombre_val").text(ui.item.nombre);

					$("#rifci_val").text(ui.item.rifci);

					$("#cod_cli").val(ui.item.cod_cli);
					setTimeout(function() {  $("#cod_cli").removeAttr("readonly"); }, 1500);
				}
			});

		});
		'.$jsc.'
		</script>';

		$data['content'] = $edit->output;
		$data['script']  = script('jquery.js');
		$data['script'] .= script('jquery-ui.js');
		$data['script'] .= script('plugins/jquery.numeric.pack.js');
		$data['script'] .= script('plugins/jquery.floatnumber.js');
		$data['script'] .= script('plugins/jquery.ui.autocomplete.autoSelectOne.js');
		$data['script'] .= phpscript('nformat.js');
		$data['head']    = $this->rapyd->get_head();
		$data['head']   .= style('redmond/jquery-ui-1.8.1.custom.css');
		//$data['script']  = script('jquery.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js').phpscript('nformat.js');
		$data['script'] .= $script;
		$data['title']   = heading($this->tits);
		$this->load->view('view_ventanas', $data);

	}

	function dataprint($st,$uid){
		$this->back_url='concesionario/inicio/index';
		parent::dataprint($st,$uid);
	}
}
