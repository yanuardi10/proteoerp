<?php
class Recep extends Controller {

	var $titp   = 'Recepciones de Mercancia';
	var $tits   = 'Recepcion';
	var $url    = 'inventario/recep/';

	function Recep(){
		parent::Controller();
		$this->load->library("rapyd");
		//$this->datasis->modulo_id(135,1);
	}

	function index(){
		redirect($this->url."filteredgrid");
	}

	function filteredgrid(){
		//$this->datasis->modulo_id(101,1);
		$this->rapyd->load("datafilter2","datagrid");

		$filter = new DataFilter2("");

		//$filter->db->select(array("b.cuenta","a.comprob","a.fecha","a.origen","a.debe","a.haber","a.status","a.descrip","a.total"));
		$filter->db->from("recep a");
		$filter->db->join("seri b" ,"a.recep=b.recep");

		$filter->recep = new inputField("Numero", "recep");
		$filter->recep->size  =10;
		$filter->recep->db_name="a.recep";
/*
		$filter->fechad = new dateonlyField("Desde", "fechad",'d/m/Y');
		$filter->fechah = new dateonlyField("Hasta", "fechah",'d/m/Y');
		$filter->fechad->clause  =$filter->fechah->clause ="where";
		$filter->fechad->db_name =$filter->fechah->db_name="a.fecha";
		$filter->fechah->size=$filter->fechad->size=10;
		$filter->fechad->operator=">=";
		$filter->fechah->operator="<=";

		//$filter->fecha = new dateonlyField("Fecha", "fecha");
		//$filter->fecha->size=12;

		$filter->descrip = new inputField("Descripci&oacute;n", "descrip");
		$filter->descrip->db_name="a.descrip";
		
		$filter->descripd = new inputField("Concepto Detalle", "descripd");
		$filter->descripd->db_name="b.concepto";
		
		$filter->cuenta = new inputField("Cuenta", "cuenta");
		$filter->cuenta->db_name="b.cuenta";

		$filter->status = new dropdownField("Status", "status");
		$filter->status->db_name="a.status";
		$filter->status->option("","Todos");
		$filter->status->option("A","Actualizado");
		$filter->status->option("D","Diferido");

		$filter->vdes = new checkboxField("Ver solo asientos descuadrados","vdes",'S','N');
		$filter->vdes->insertValue='N';
		$filter->vdes->clause='';
*/
		$filter->buttons("reset","search");

		$filter->build();
		$uri = anchor($this->url.'dataedit/show/<#numero#>','<#numero#>');

		function sta($status){
			switch($status){
				case "C2":return "Cuadrado";break;
				case "C1":return "Pendiente";break;
			}
		}

		$grid = new DataGrid("");
		$grid->order_by("a.recep","desc");
		$grid->per_page = 20;
		$grid->use_function('substr','str_pad');
		$grid->use_function('sta');

		$grid->column_orderby("Numero Recepcion" ,$uri                                            ,"numero");
		$grid->column_orderby("Fecha"            ,"<dbdate_to_human><#fecha#></dbdate_to_human>"  ,"fecha"    ,"align='center'"      );
		$grid->column_orderby("Cod. Proveedor"   ,"cod_prov"                                      ,"cod_prov" ,"align='center'"      );
		$grid->column_orderby("Observacion"      ,"observa"                                       ,"observa"  ,"align='left'  NOWRAP");
		
		$grid->add($this->url."dataedit/create");
		$grid->build();
                //echo $grid->db->last_query();

		//$data['content'] = $filter->output.$grid->output;
		$data['filtro']  = $filter->output;
		$data['content'] = $grid->output;
		$data['script']  = script("jquery.js")."\n";
		$data['title']   = "$this->titp";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		
		$this->rapyd->load('dataobject','datadetails');

		$do = new DataObject("recep");
		$do->rel_one_to_many('seri', 'seri', array('recep'=>'recep'));

		$edit = new DataDetails($this->tits, $do);
		$edit->back_url = site_url($this->url."filteredgrid");
		$edit->set_rel_title('itcasi','Rubro <#o#>');

		$edit->pre_process('insert'  ,'_valida');
		$edit->pre_process('update'  ,'_valida');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		
		$edit->recep  = new inputField("Numero", "recep");
		$edit->recep->mode ="autohide";
		$edit->recep->when=array('show','modify');

		$edit->fecha = new  dateonlyField("Fecha",  "fecha");
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->size        =12;
		$edit->fecha->rule        = 'required';

		$edit->observa = new textAreaField("Observaci&oacute;n", 'observa');
		$edit->observa->cols = 100;
		$edit->observa->rows = 3;
/*
		$edit->status = new dropdownField("Status", "status");
		$edit->status->style="width:110px";
		$edit->status->option("C1","Cuadrado");
		$edit->status->option("C2","Pendiente");
*/
		$edit->itbarras = new inputField("(<#o#>) Barras", "barras_<#i#>");
		$edit->itbarras->rule         ='trim|required';
		$edit->itbarras->size         =20;
		$edit->itbarras->db_name      ='barras';
		$edit->itbarras->rel_id       ='seri';
		$edit->itbarras->autocomplete =false;
//		$edit->itbarras->append($button);

                $edit->itcodigo = new inputField("(<#o#>) Codigo", "codigo_<#i#>");
		$edit->itcodigo->rule         ='trim|required';
		$edit->itcodigo->size         =10;
		$edit->itcodigo->db_name      ='codigo';
		$edit->itcodigo->rel_id       ='seri';
		$edit->itcodigo->autocomplete =false;

		$edit->itdescrip = new inputField("(<#o#>) Descrip", "descrip_<#i#>");
		$edit->itdescrip->rule         ='trim|required';
		$edit->itdescrip->size         =40;
		$edit->itdescrip->db_name      ='descrip';
		$edit->itdescrip->rel_id       ='seri';
		$edit->itdescrip->autocomplete =false;
                
                $edit->itserial = new inputField("(<#o#>) Serial", "serial_<#i#>");
		$edit->itserial->rule         ='trim|required';
		$edit->itserial->size         =20;
		$edit->itserial->db_name      ='serial';
		$edit->itserial->rel_id       ='seri';
		$edit->itserial->autocomplete =false;

		$status=$edit->get_from_dataobjetct('status');
		if($status=='C1'){
			$action = "javascript:window.location='" .site_url($this->url.'/actualizar/'.$edit->rapyd->uri->get_edited_id()). "'";
			$edit->button_status("btn_status",'Cerrar Asiento',$action,"TR","show");
			$edit->buttons("modify","delete","save");
		}elseif($status=='C2'){
			$action = "javascript:window.location='" .site_url($this->url.'/reversar/'.$edit->rapyd->uri->get_edited_id()). "'";
			$edit->button_status("btn_rever",'Reversar',$action,"TR","show");
		}else{
			$edit->buttons("save");
		}

		$edit->buttons("modify","save","undo","back","add_rel","add");
		$edit->build();

		$smenu['link']   = barra_menu('198');
		$data['smenu']   = $this->load->view('view_sub_menu', $smenu,true);
		$conten['orden']   = $orden;
		$conten["form"]  =&  $edit;
		$data['content'] = $this->load->view('view_casi', $conten,true);
		//$data['content'] = $edit->output;
		$data['title']   = "$this->tits";
		$data["head"]    = $this->rapyd->get_head().script('jquery.js').script('jquery-ui.js').script("plugins/jquery.numeric.pack.js").script('plugins/jquery.meiomask.js').style('vino/jquery-ui.css');
		$this->load->view('view_ventanas', $data);
	}

        function _valida($do){
            $error  ='';
            
            if(!empty($error)){
                $do->error_message_ar['pre_ins']="<div class='alert'>".$error."</div>";
                $do->error_message_ar['pre_upd']="<div class='alert'>".$error."</div>";
                return false;
            }else{
            }
        }

	function reversar($numero){
	}

	function actualizar($numero){
	
	}

        function _post_insert($do){
            $numero = $do->get('recep');
            logusu('recep',"Creo recepcion  $numero");
            //redirect($this->url."actualizar/$numero");
        }
        
        function _post_update($do){
            $numero = $do->get('recep');
            logusu('casi'," Modifico recepcion $numero");
        }
        function _post_delete($do){
            $numero = $do->get('recep');
            logusu('casi'," Elimino recepcion $numero");
        }


        function instalar(){
            $query="ALTER TABLE `seri` ADD COLUMN `recep` CHAR(8) NOT NULL";
            $this->db->simple_query($query);
            $query="ALTER TABLE `seri` ADD COLUMN `frecep` DATE NOT NULL";
            $this->db->simple_query($query);
            $query="ALTER TABLE `seri`  ADD COLUMN `barras` VARCHAR(50) NOT NULL";
            $this->db->simple_query($query);
            
            $query="CREATE TABLE `recep` (
            `recep` CHAR(8) NULL,
            `fecha` DATE NULL,
            `cod_prov` VARCHAR(5) NULL,
            `numero` CHAR(8) NULL,
            `Column 5` CHAR(8) NULL,
            `tipo_doc` CHAR(2) NULL,
            `observa` TEXT NULL,
            `status` CHAR(2) NULL,
            `user` VARCHAR(50) NULL,
            `estampa` TIMESTAMP NULL
            ) COLLATE='latin1_swedish_ci' ENGINE=MyISAM ROW_FORMAT=DEFAULT";
            $this->db->simple_query($query);
            
        }
}
?>
