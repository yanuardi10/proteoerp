<?php
require_once(BASEPATH.'application/controllers/validaciones.php');
class pfacc extends validaciones {

	function pfacc()
	{
		parent::Controller();
		$this->load->library("rapyd");
		//		$this->datasis->modulo_id(104,1);
	}
	function index() {
		redirect("ventas/pfacc/filteredgrid");
	}

	function filteredgrid(){
		$this->rapyd->load("datagrid","datafilter");

		$atts = array(
              'width'      => '800',
              'height'     => '600',
              'scrollbars' => 'yes',
              'status'     => 'yes',
              'resizable'  => 'yes',
              'screenx'    => '0',
              'screeny'    => '0'
              );

              $scli=array(
	  'tabla'   =>'scli',
	  'columnas'=>array(
		'cliente' =>'C&oacute;digo Cliente',
		'nombre'=>'Nombre',
		'contacto'=>'Contacto'),
	  'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
	  'retornar'=>array('cliente'=>'cod_cli'),
	  'titulo'  =>'Buscar Cliente');
               
       $boton=$this->datasis->modbus($scli);

       $filter = new DataFilter("Filtro de Pedidos Clientes",'pfac');

       $filter->fechad = new dateonlyField("Desde", "fechad",'d/m/Y');
       $filter->fechah = new dateonlyField("Hasta", "fechah",'d/m/Y');
       $filter->fechad->clause  =$filter->fechah->clause="where";
       $filter->fechad->db_name =$filter->fechah->db_name="fecha";
       $filter->fechad->insertValue = date("Y-m-d");
       $filter->fechah->insertValue = date("Y-m-d");
       $filter->fechah->size=$filter->fechad->size=10;
       $filter->fechad->operator=">=";
       $filter->fechah->operator="<=";

       $filter->numero = new inputField("N&uacute;mero", "numero");
       $filter->numero->size = 30;

       $filter->cliente = new inputField("Cliente", "cod_cli");
       $filter->cliente->size = 30;
       $filter->cliente->append($boton);

       $filter->buttons("reset","search");
       $filter->build();

       $uri = anchor('ventas/pfacc/dataedit/show/<#numero#>','<#numero#>');
       $uri2 = anchor_popup('formatos/verhtml/PFAC/<#numero#>',"Ver HTML",$atts);

       $grid = new DataGrid();
       $grid->order_by("fecha","desc");
       $grid->per_page = 15;

       $grid->column("N&uacute;mero",$uri);
       $grid->column("Fecha","<dbdate_to_human><#fecha#></dbdate_to_human>","align='center'");
       $grid->column("Nombre","nombre");
       $grid->column("Sub.Total","<number_format><#totals#>|2</number_format>","align=right");
       $grid->column("IVA","<number_format><#iva#>|2</number_format>","align=right");
       $grid->column("Total","<number_format><#totalg#>|2</number_format>","align=right");
       $grid->column("Vista",$uri2,"align='center'");
       
       $grid->add("ventas/pfacc/dataedit/create");
       $grid->build();
       
       $data['content'] =$filter->output.$grid->output;
       $data["head"]    = $this->rapyd->get_head();
       $data['title']   ='<h1>Pedidos Clientes</h1>';
       $this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load('dataobject','datadetails');

		$modbus=array(
			'tabla'   =>'sinv',
			'columnas'=>array(
			'codigo' =>'C&oacute;digo',
			'descrip'=>'descrip'),
			'filtro'  =>array('codigo' =>'C&oacute;digo','descrip'=>'descrip'),
			'retornar'=>array('codigo'=>'codigoa_<#i#>','descrip'=>'desca_<#i#>'
			,'precio1'=>'__p1'
			,'precio2'=>'__p2'
			,'precio3'=>'__p3'
			,'precio4'=>'__p4'
			,'iva'=>'iva_<#i#>'
			,'ultimo'=>'costo_<#i#>'
			,'tdecimal'=>'tdec_<#i#>'
			),
			'p_uri'=>array(4=>'<#i#>'),
			'titulo'  =>'Buscar Articulo',
			'script'  =>array('ejecuta(<#i#>)'));

			$btn=$this->datasis->p_modbus($modbus,'<#i#>');
			$script="
		function post_add_itpfac(id){
			$('#cana_'+id).numeric(".");
			return true;
		}
				
		";

			$mSCLId=array(
			'tabla'   =>'scli',
			'columnas'=>array(
			'cliente' =>'C&oacute;digo Cliente',
			'nombre'=>'Nombre', 
			'cirepre'=>'Rif/Cedula',
			'dire11'=>'Direcci&oacute;n','tipo'=>'Tipo'),
			'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
			'retornar'=>array('cliente'=>'cod_cli','nombre'=>'nombre','cirepre'=>'rifci','dire11'=>'direc','tipo'=>'t_cli'),
			'titulo'  =>'Buscar Cliente');

			$boton =$this->datasis->modbus($mSCLId);

			$do = new DataObject("pfac");
			$do->rel_one_to_many('itpfac', 'itpfac',array('numero'=>'numa'));
			//			$do->rel_pointer('itspre','sinv','itspre.codigo=sinv.codigo','sinv.descrip as sinvdescrip');

			$edit = new DataDetails("Pedidos", $do);
			$edit->back_url = site_url("ventas/pfacc/filteredgrid");
			$edit->set_rel_title('itpfac','Producto <#o#>');

			$edit->script($script,'create');
			$edit->script($script,'modify');

			$edit->pre_process('insert' ,'_pre_insert');
			$edit->pre_process('update' ,'_pre_update');
			$edit->post_process('insert','_post_insert');
			$edit->post_process('update','_post_update');
			$edit->post_process('delete','_post_delete');

			$edit->fecha = new DateonlyField("Fecha", "fecha","d/m/Y");
			$edit->fecha->insertValue = date("Y-m-d");
			$edit->fecha->mode="autohide";
			$edit->fecha->size = 10;

			$edit->vd = new  dropdownField ("Vendedor", "vd");
			$edit->vd->options("SELECT vendedor, CONCAT(vendedor,' ',nombre) nombre FROM vend ORDER BY vendedor");
			$edit->vd->style ="10px";

			$edit->numero = new inputField("N&uacute;mero", "numero");
			$edit->numero->size = 10;
			$edit->numero->mode="autohide";
			$edit->numero->maxlength=8;
			$edit->numero->apply_rules=false; //necesario cuando el campo es clave y no se pide al usuario
			$edit->numero->when=array('show','modify');

			$edit->nombre = new inputField("Nombre", "nombre");
			$edit->nombre->size = 30;
			$edit->nombre->maxlength=40;
			$edit->nombre->rule= "required";

			$edit->peso = new inputField("Peso", "peso");
			$edit->peso->mode="autohide";
			$edit->peso->css_class ='inputnum';
			$edit->peso->when=array('show','modify');
			$edit->peso->size      = 10;

			
			$edit->cliente = new inputField("Cliente","cod_cli");
			$edit->cliente->size = 10;
			$edit->cliente->maxlength=5;
			$edit->cliente->append($boton);

			$edit->rifci   = new inputField("RIF/CI","rifci");
			$edit->rifci->size = 10;

			$edit->direc = new inputField("Direcci&oacute;n","direc");
			$edit->direc->size = 30;
			$edit->direc->rule= "required";

			$edit->dire1 = new inputField(" ","dire1");
			$edit->dire1->size = 30;

			//Campos para el detalle
			$edit->codigoa = new inputField("C&oacute;digo <#o#>", "codigoa_<#i#>");
			$edit->codigoa->size=10;
			$edit->codigoa->db_name='codigoa';
			$edit->codigoa->append($btn);
			$edit->codigoa->rel_id='itpfac';

			$edit->desca = new inputField("Descripci&oacute;n <#o#>", "desca_<#i#>");
			$edit->desca->size=36;
			$edit->desca->db_name='desca';
			$edit->desca->maxlength=50;
			$edit->desca->rel_id='itpfac';

			$edit->cana = new inputField("Cantidad <#o#>", "cana_<#i#>");
			$edit->cana->db_name  ='cana';
			$edit->cana->css_class='inputnum';
			$edit->cana->rel_id   ='itpfac';
			$edit->cana->maxlength=10;
			$edit->cana->size     =7;
			$edit->cana->rule='required';
			$edit->cana->onchange='totalizar(<#i#>)';
			
			$edit->mostrado = new inputField("P.IVA <#o#>", "mostrado_<#i#>");
			$edit->mostrado->db_name='mostrado';
			$edit->mostrado->css_class='inputnum';
			$edit->mostrado->rel_id   ='itpfac';
			$edit->mostrado->size=7;
			$edit->mostrado->rule='required';
			$edit->mostrado->onchange="valida(<#i#>)";
			
			$edit->importe = new inputField("importe <#o#>", "importe_<#i#>");
			$edit->importe->db_name=' ';
			$edit->importe->css_class='inputnum';
			$edit->importe->rel_id   ='itpfac';
			$edit->importe->size=7;
			$edit->importe->pointer='true';
			$edit->importe->onchange="valida(<#i#>)";
			
			$edit->preca = new inputField("Precio <#o#>", "preca_<#i#>");
			$edit->preca->db_name='preca';
			$edit->preca->css_class='inputnum';
			$edit->preca->rel_id   ='itpfac';
			$edit->preca->size=7;
			$edit->preca->rule='required';
			$edit->preca->mode="autohide";
			$edit->preca->when=array("");

			$edit->tota = new inputField("Tota <#o#>", "tota_<#i#>");
			$edit->tota->db_name='tota';
			$edit->tota->size=7;
			$edit->tota->css_class='inputnum';
			$edit->tota->rel_id   ='itpfac';
			$edit->tota->onchange='totalizar(<#i#>)';
			$edit->tota->mode="autohide";
			$edit->tota->when=array("");

			$edit->pvp = new inputField("PVP <#o#>", "pvp_<#i#>");
			$edit->pvp->db_name='pvp';
			$edit->pvp->size=0;
			$edit->pvp->css_class='inputnum';
			$edit->pvp->rel_id   ='itpfac';
			$edit->pvp->mode="autohide";
			$edit->pvp->when=array("");

			$edit->iva = new inputField("iva <#o#>", "iva_<#i#>");
			$edit->iva->db_name='iva';
			$edit->iva->size=0;
			$edit->iva->css_class='inputnum';
			$edit->iva->rel_id   ='itpfac';
			$edit->iva->mode="autohide";
			$edit->iva->when=array("");

			$edit->costo = new inputField("costo <#o#>", "costo_<#i#>");
			$edit->costo->db_name='costo';
			$edit->costo->size=0;
			$edit->costo->css_class='inputnum';
			$edit->costo->rel_id   ='itpfac';
			$edit->costo->mode="autohide";
			$edit->costo->when=array("");

			//fin de campos para detalle

			$edit->ivat = new inputField("TOTAL IVA", "iva");
			$edit->ivat->mode="autohide";
			$edit->ivat->css_class ='inputnum';
			$edit->ivat->when=array('show','modify');
			$edit->ivat->size      = 10;

			$edit->totals = new inputField("SUB-TOTAL", "totals");
			$edit->totals->mode="autohide";
			$edit->totals->css_class ='inputnum';
			$edit->totals->when=array('show','modify');
			$edit->totals->size      = 10;

			$edit->totalg = new inputField("TOTAL", "totalg");
			$edit->totalg->mode="autohide";
			$edit->totalg->css_class ='inputnum';
			$edit->totalg->when=array('show','modify');
			$edit->totalg->size      = 10;

			$edit->buttons("modify", "save", "undo", "delete", "back","add_rel");
			$edit->build();

			$conten["form"]  =&  $edit;
			$data['content'] = $this->load->view('view_pfat', $conten,true);
			$data['title']   = "<h1>Pedidos De Cliente</h1>";
			$data["head"]    = script('jquery.js').script('jquery-ui.js').script("plugins/jquery.numeric.pack.js").script('plugins/jquery.meiomask.js').style('vino/jquery-ui.css').$this->rapyd->get_head().phpscript('nformat.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js');
			$this->load->view('view_ventanas', $data);
	}

	function _pre_insert($do){
		$numero=$this->datasis->fprox_numero('npfac');
		$trans=$this->datasis->fprox_numero('ntransa');
		$do->set('numero',$numero);
		$do->pk['numero'] = $numero; //Necesario cuando la clave primara se calcula por secuencia
		$do->set('transac',$trans);
		$datos=$do->get_all();
		$ivat=0;$subt=0;$total=0;
	
		foreach($datos['itpfac'] as $rel){
			$c=$rel['cana'];
			$p=$rel['mostrado'];
			$total+=$c*$p;
			$subt+=$rel['preca']*$rel['cana'];
			//echo 'importe=>'.$rel['totaorg'].'    preca=>'.$rel['preca'].'    cana=>'.$rel['cana'].'   iva=>'.$rel['iva'].'<br>';
		}
		$ivat=$total-$subt;
		
		$do->set('totals',$subt);
		$do->set('totalg',$total);
		$do->set('iva',$ivat);
//		exit;


		//		echo "EL SUB-Totla es :".$subt." y el el iva es".$iva."  para un total de $total";
		//		exit;
		return true;
	}

	function _pre_update($do){
		//				print("<pre>");
		//		echo $do->get_rel('itspre','preca',2);
		$datos=$do->get_all();
		$ivat=0;$subt=0;$total=0;
		foreach($datos['itpfac'] as $rel){
			$c=$rel['cana'];
			$p=$rel['mostrado'];
			$total+=$c*$p;
			$subt+=$rel['preca']*$rel['cana'];
			//echo 'importe=>'.$rel['totaorg'].'    preca=>'.$rel['preca'].'    cana=>'.$rel['cana'].'   iva=>'.$rel['iva'].'<br>';
		}
		$ivat=$total-$subt;
//		echo "Total es:".$total."<br>";
//		echo "SubTotal es:".$subt."<br>";
//		echo "Iva es:".$ivat."<br>";
//		exit;
		$do->set('totals',$subt);
		$do->set('totalg',$total);
		$do->set('iva',$ivat);
		
		
		return true;
	}

	function _post_insert($do){
		$codigo=$do->get('numero');
		logusu('pfat',"PEDIDO $codigo CREADO");
		$query='select sum(b.cana * c.peso) as valor
				from pfac as a
				join itpfac as b on a.numero=b.numa
				join sinv as c on c.codigo=b.codigoa
				where a.numero="'.$codigo.'"';
		$mSQL_1 = $this->db->query($query);
		$resul = $mSQL_1->row();
		$valor=$resul->valor;
		$query='update pfac set peso="'.$valor.'" where numero="'.$codigo.'" ';
		$this->db->query($query);

	}

	function _post_update($do){
		$codigo=$do->get('numero');
		logusu('pfat',"PEDIDO $codigo MODIFICADO");
		$query='select sum(b.cana * c.peso) as valor
				from pfac as a
				join itpfac as b on a.numero=b.numa
				join sinv as c on c.codigo=b.codigoa
				where a.numero="'.$codigo.'"';
		$mSQL_1 = $this->db->query($query);
		$resul = $mSQL_1->row();
		$valor=$resul->valor;
		$query='update pfac set peso="'.$valor.'" where numero="'.$codigo.'" ';
		$this->db->query($query);
	}

	function _post_delete($do){
		$codigo=$do->get('numero');
		logusu('pfac',"PEDIDO $codigo ELIMINADO");
	}

}
?>