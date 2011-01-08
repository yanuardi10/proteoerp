<?php
class Gastos extends Controller {

	function Gastos(){
		parent::Controller();
		$this->load->library("rapyd");
		//$this->datasis->modulo_id(604,1);
	}
	function index() {
		redirect("finanzas/gastos/filteredgrid");
	}
	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter("Filtro de Gastos",'gser');
		//		$filter->db->select("numero,fecha,vence,nombre,totiva,totneto");
		//		$filter->db->from('gser');

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
			
		$filter->proveed = new inputField("Proveedor", "proveed");
		//$filter->proveed->append($boton);
		$filter->proveed->db_name = "proveed";

		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('finanzas/gastos/dataedit/show/<#id#>','<#numero#>');

		$grid = new DataGrid();
		$grid->order_by("numero","desc");
		$grid->per_page = 15;
		$grid->column("N&uacute;mero",$uri);
		$grid->column("Fecha","<dbdate_to_human><#fecha#></dbdate_to_human>","align='center'");
		$grid->column("Vence","<dbdate_to_human><#vence#></dbdate_to_human>","align='center'");
		$grid->column("Nombre","nombre");
		$grid->column("IVA"  ,"totiva"  ,"align='right'");
		$grid->column("monto" ,"totneto" ,"align='right'");

		$grid->add("finanzas/gastos/dataedit/create");
		$grid->build();
		//echo $grid->db->last_query();

		$data['content'] =$filter->output.$grid->output;
		$data["head"]    = $this->rapyd->get_head();
		$data['title']   ='<h1>Gastos</h1>';
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load('dataobject','datadetails');

		$modbus=array(
			'tabla'   =>'mgas',
			'columnas'=>array(
			'codigo' =>'C&oacute;digo',
			'descrip'=>'descrip'),
			'filtro'  =>array('codigo' =>'C&oacute;digo','descrip'=>'descrip'),
			'retornar'=>array('codigo'=>'codigo_<#i#>','descrip'=>'descrip_<#i#>'),
			'p_uri'=>array(4=>'<#i#>'),
			'titulo'  =>'Buscar Articulo',
			'script'  =>array('lleva(<#i#>)'));

		$btn=$this->datasis->p_modbus($modbus,'<#i#>');

		$script="
		function post_add_gitser(id){
			$('#precio_'+id).numeric(".");
			return true;
		}
				
		";

		$mSPRV=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
			'proveed' =>'C&oacute;odigo',
			'nombre'=>'Nombre',
			'rif'=>'Rif'),
			'filtro'  =>array('proveed'=>'C&oacute;digo','nombre'=>'Nombre'),
			'retornar'=>array('proveed'=>'proveed','nombre'=>'nombre'),
			
			'titulo'  =>'Buscar Codigo');
		$bSPRV=$this->datasis->modbus($mSPRV);
			

		$mSIVA=array(
			'tabla'   =>'civa',
			'columnas'=>array(
			'tasa' =>'Tasa',
			'redutasa'=>'Redutasa'
			),
			'filtro'  =>array('tasa'=>'Tasa'),
			'retornar'=>array('tasa'=>'iva_<#i#>'),
			'p_uri'=>array(4=>'<#i#>'),
			'titulo'  =>'Buscar IVA',
			'script'  =>array('totalizar(<#i#>)'));
			$bSIVA=$this->datasis->p_modbus($mSIVA,'<#i#>');

			$do = new DataObject("gser");
			$do->rel_one_to_many('gitser', 'gitser',array('id'=>'idgser'));
			//			$do->rel_pointer('itspre','sinv','itspre.codigo=sinv.codigo','sinv.descrip as sinvdescrip');
				
			$edit = new DataDetails("Gastos", $do);
			$edit->back_url = site_url("finanzas/gastos/filteredgrid");
			$edit->set_rel_title('gitser','Gasto <#o#>');

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

			$edit->id = new inputField("ID", "id");
			$edit->id->size = 10;
			$edit->id->mode="autohide";
			$edit->id->maxlength=8;
			$edit->id->when=array('show','modify');
				
			$edit->numero = new inputField("N&uacute;mero", "numero");
			$edit->numero->size = 10;
			$edit->numero->mode="autohide";
			$edit->numero->maxlength=8;
			//$edit->numero->apply_rules=false; //necesario cuando el campo es clave y no se pide al usuario
			$edit->numero->when=array('show','modify');

			$edit->proveedg = new inputField("Proveedor","proveed");
			$edit->proveedg->size = 10;
			$edit->proveedg->maxlength=5;
			$edit->proveedg->append($bSPRV);
			//$edit->proveedg->mode="autohide";

			$edit->nombre = new inputField("Nombre", "nombre");
			$edit->nombre->size = 30;
			$edit->nombre->maxlength=40;
			$edit->nombre->rule= "required";

			$edit->totpre  = new inputField("Sub.Total", "totpre");
			$edit->totpre->size = 10;
			$edit->totpre->css_class='inputnum';

			$edit->totbruto= new inputField("Total", "totbruto");
			$edit->totbruto->size = 10;
			$edit->totbruto->css_class='inputnum';

			//			$edit->rifci   = new inputField("RIF/CI","rifci");
			//			$edit->rifci->size = 10;

			//Campos para el detalle
			$edit->codigo = new inputField("C&oacute;digo <#o#>", "codigo_<#i#>");
			$edit->codigo->size=10;
			$edit->codigo->db_name='codigo';
			$edit->codigo->append($btn);
			$edit->codigo->rel_id='gitser';

			$edit->descrip = new inputField("Descripci&oacute;n <#o#>", "descrip_<#i#>");
			$edit->descrip->size=36;
			$edit->descrip->db_name='descrip';
			$edit->descrip->maxlength=50;
			$edit->descrip->rel_id='gitser';

			$edit->precio = new inputField("Precio <#o#>", "precio_<#i#>");
			$edit->precio->db_name='precio';
			$edit->precio->css_class='inputnum';
			$edit->precio->size=7;
			$edit->precio->rule='required';
			$edit->precio->rel_id='gitser';
			$edit->precio->onchange="totalizar(<#i#>)";

			$edit->iva = new inputField("IVA <#o#>", "iva_<#i#>");
			$edit->iva->db_name='iva';
			$edit->iva->css_class='inputnum';
			$edit->iva->size=7;
			$edit->iva->rule='required';
			$edit->iva->rel_id='gitser';
			$edit->iva->onchange="totalizar(<#i#>)";
			$edit->iva->append($bSIVA);
			$edit->iva->rule='required';

			$edit->importe = new inputField("importe <#o#>", "importe_<#i#>");
			$edit->importe->db_name='importe';
			$edit->importe->css_class='inputnum';
			$edit->importe->rel_id   ='gitser';
			$edit->importe->size=7;
			$edit->importe->onchange="valida(<#i#>)";

			$edit->proveed = new inputField("Proveedor <#o#>", "proveed_<#i#>");
			$edit->proveed->db_name='proveed';
			$edit->proveed->size=0;
			$edit->proveed->rel_id   ='gitser';
			$edit->proveed->mode="autohide";
			$edit->proveed->when=array("");
				
			$edit->idgser = new inputField("id <#o#>", "idgser_<#i#>");
			$edit->idgser->db_name='idgser';
			$edit->idgser->size=0;
			$edit->idgser->rel_id   ='gitser';
			$edit->idgser->mode="autohide";
			$edit->idgser->when=array("");

			//fin de campos para detalle

			$edit->totiva = new inputField("TOTAL IVA", "totiva");
			$edit->totiva->mode="autohide";
			$edit->totiva->css_class ='inputnum';
			$edit->totiva->when=array('show','modify');
			$edit->totiva->size      = 10;

			$edit->buttons("modify", "save", "undo", "delete", "back","add_rel");
			$edit->build();
			echo $edit->_dataobject->db->last_query();

			$conten["form"]  =&  $edit;
			$data['content'] = $this->load->view('view_gser', $conten,true);
			$data['title']   = "<h1>Gastos</h1>";
			$data["head"]    = script('jquery.js').script('jquery-ui.js').script("plugins/jquery.numeric.pack.js").script('plugins/jquery.meiomask.js').style('vino/jquery-ui.css').$this->rapyd->get_head().phpscript('nformat.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js');
			$this->load->view('view_ventanas', $data);
	}

	function _pre_insert($do){
		$numero=$this->datasis->fprox_numero('ngser');
		$trans=$this->datasis->fprox_numero('ntransa');
		$do->set('numero',$numero);
		$do->pk['numero'] = $numero; //Necesario cuando la clave primara se calcula por secuencia
		$do->set('transac',$trans);
		$datos=$do->get_all();
		$ivat=0;$subt=0;$total=0;

		foreach($datos['gitser'] as $rel){
			$p=$rel['precio'];
			$i=$rel['iva'];
			$total+=$p+($i*$p/100);
			$subt+=$p;
			//echo 'importe=>'.$rel['totaorg'].'    preca=>'.$rel['preca'].'    cana=>'.$rel['cana'].'   iva=>'.$rel['iva'].'<br>';
		}
		$ivat=$total-$subt;

		$do->set('totpre',$subt);
		$do->set('totbruto',$total);
		$do->set('totiva',$ivat);
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

		foreach($datos['gitser'] as $rel){
			$p=$rel['precio'];
			$i=$rel['iva'];
			$total+=$p+($i*$p/100);
			$subt+=$p;
			//echo 'importe=>'.$rel['totaorg'].'    preca=>'.$rel['preca'].'    cana=>'.$rel['cana'].'   iva=>'.$rel['iva'].'<br>';
		}
		$ivat=$total-$subt;

		$do->set('totpre',$subt);
		$do->set('totbruto',$total);
		$do->set('totiva',$ivat);


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

	function instala(){
		$query="TABLE `gser`  DROP INDEX `PRIMARY`,  ADD UNIQUE INDEX `gser` (`fecha`, `proveed`, `numero`)";
		$this->db->query($query);
		$query="TABLE `gser`  ADD COLUMN `id`
				INT(15) UNSIGNED NOT NULL AUTO_INCREMENT AFTER `serie`,
				ADD PRIMARY KEY (`id`)";
		$this->db->query($query);
		$query="ALTER TABLE `gitser`  ADD COLUMN `idgser`
				INT(15) UNSIGNED NOT NULL DEFAULT '0' AFTER `id`";
		$this->db->query($query);
		$query="update gitser as a 
				join gser as b on (a.numero=b.numero and a.fecha = b.fecha and a.proveed = b.proveed)
				set a.idgser=b.id";
		$this->db->query($query);
	}

}
?>