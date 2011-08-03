<?php
class vieventascompras extends Controller {
	var $titp='Ventas-Compras';
	var $tits='Ventas-Compras';
	var $url ='supervisor/vieventascompras/';
        
	function vieventascompras(){
		parent::Controller();
		$this->load->library("rapyd");
		$this->datasis->modulo_id(927,1);
	}
	function index(){
		redirect($this->url."filteredgrid");
	}
	function filteredgrid($barras=''){
		$this->rapyd->load('datafilter','datagrid');
                
                $this->rapyd->set_connection('consolidado');
		$this->rapyd->load_db();
                
                $modbus=array(
			'tabla'   =>'sinv',
			'columnas'=>array(
				'codigo' =>'C&oacute;digo',
                                'barras' =>'Barras',
				'descrip'=>'Descripci&oacute;n',
				'precio1'=>'Precio 1',
				'precio2'=>'Precio 2',
				'precio3'=>'Precio 3',
				'precio4'=>'Precio 4'),
			'filtro'  =>array('codigo'=>'C&oacute;digo','barras' =>'Barras','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array('barras'=>'barras'),
			'titulo'  =>'Buscar en inventario',
                        'dbgroup'=>'consolidado');

		$boton=$this->datasis->modbus($modbus);
                
		$filter = new DataFilter($this->titp, 'vieventascompras');

		$filter->barras = new inputField('Barras','barras');
		$filter->barras->size      =17;
		$filter->barras->maxlength =15;
                $filter->barras->append($boton);
                $filter->barras->insertValue($barras);

                $filter->fechad = new dateonlyField("Desde", "fechad",'d/m/Y');
                $filter->fechad->clause ="where";
                $filter->fechad->db_name ="fecha";
                $filter->fechad->operator=">=";
                $filter->fechad->group = "Fecha";
                $filter->fechad->dbformat='Y-m-d';
                
                $filter->fechah = new dateonlyField("Hasta", "fechah",'d/m/Y');
                $filter->fechah->clause="where";
                $filter->fechah->db_name="fecha";
                $filter->fechah->operator="<=";
                $filter->fechah->group = "Fecha";
                $filter->fechah->dbformat='Y-m-d';

		$filter->farmacia = new inputField('Farmacia','farmacia');
		$filter->farmacia->size      =4;
		$filter->farmacia->maxlength =2;

		$filter->descrip = new inputField('Descripci&oacute;n','descrip');
		$filter->descrip->size      =47;
		$filter->descrip->maxlength =45;

		$filter->venta = new inputField('Venta','venta');
		$filter->venta->css_class ='inputnum';
		$filter->venta->size      =40;
		$filter->venta->maxlength =38;

		$filter->compras = new inputField('Compra','compras');
		$filter->compras->css_class ='inputnum';
		$filter->compras->size      =40;
		$filter->compras->maxlength =38;

		$filter->buttons('reset', 'search');
		$filter->build();

		$uri = anchor($this->url.'filterbarras<#barras#>','<#barras#>');

		$grid = new DataGrid('');
		$grid->order_by('venta','desc');
		$grid->per_page = 40;

		$grid->column_orderby('Barras'            ,"barras"                                      ,'barras'  ,'align="left"');
		$grid->column_orderby('Fecha'             ,"<dbdate_to_human><#fecha#></dbdate_to_human>",'fecha'   ,'align="right"');
		$grid->column_orderby('Farmacia'          ,"farmacia"                                    ,'farmacia','align="left"');
		$grid->column_orderby('Descripci&oacute;n',"descrip"                                     ,'descrip' ,'align="left"');
		$grid->column_orderby('Ventas'            ,"<nformat><#venta#></nformat>"                ,'venta'   ,'align="right"');
		$grid->column_orderby('compras'           ,"<nformat><#compras#></nformat>"              ,'compras' ,'align="right"');

		$grid->add($this->url.'dataedit/create');
		$grid->build();

		$data['filtro']  = $filter->output;
		$data['content'] = $grid->output;
		$data['head']    = $this->rapyd->get_head().script('jquery.js');
		$data['title']   = heading($this->tits);
		$this->load->view('view_ventanas', $data);

	}
        
        function filterbarras($barras){
		$this->rapyd->load('datafilter','datagrid');
                
                $this->rapyd->set_connection('consolidado');
		$this->rapyd->load_db();
                
                $modbus=array(
			'tabla'   =>'sinv',
			'columnas'=>array(
				'codigo' =>'C&oacute;digo',
                                'barras' =>'Barras',
				'descrip'=>'Descripci&oacute;n',
				'precio1'=>'Precio 1',
				'precio2'=>'Precio 2',
				'precio3'=>'Precio 3',
				'precio4'=>'Precio 4'),
			'filtro'  =>array('codigo'=>'C&oacute;digo','barras' =>'Barras','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array('barras'=>'barras'),
			'titulo'  =>'Buscar en inventario',
                        'dbgroup'=>'consolidado');

		$boton=$this->datasis->modbus($modbus);
                
		$filter = new DataFilter($this->titp, 'vieventascompras');

		$filter->barras = new inputField('Barras','barras');
		$filter->barras->size      =17;
		$filter->barras->maxlength =15;
                $filter->barras->append($boton);
                $filter->barras->insertValue=$barras;

                $filter->fechad = new dateonlyField("Desde", "fechad",'d/m/Y');
                $filter->fechad->clause ="where";
                $filter->fechad->db_name ="fecha";
                $filter->fechad->operator=">=";
                $filter->fechad->group = "Fecha";
                $filter->fechad->dbformat='Y-m-d';
                
                $filter->fechah = new dateonlyField("Hasta", "fechah",'d/m/Y');
                $filter->fechah->clause="where";
                $filter->fechah->db_name="fecha";
                $filter->fechah->operator="<=";
                $filter->fechah->group = "Fecha";
                $filter->fechah->dbformat='Y-m-d';

		$filter->farmacia = new inputField('Farmacia','farmacia');
		$filter->farmacia->size      =4;
		$filter->farmacia->maxlength =2;

		$filter->descrip = new inputField('Descripci&oacute;n','descrip');
		$filter->descrip->size      =47;
		$filter->descrip->maxlength =45;

		$filter->venta = new inputField('Venta','venta');
		$filter->venta->css_class ='inputnum';
		$filter->venta->size      =40;
		$filter->venta->maxlength =38;

		$filter->compras = new inputField('Compra','compras');
		$filter->compras->css_class ='inputnum';
		$filter->compras->size      =40;
		$filter->compras->maxlength =38;

		$filter->buttons('reset', 'search');
		$filter->build();

		$uri = anchor($this->url.'filterbarras<#barras#>','<#barras#>');

		$grid = new DataGrid('');
		$grid->order_by('venta','desc');
		$grid->per_page = 40;

		$grid->column_orderby('Barras'            ,"barras"                                      ,'barras'  ,'align="left"');
		$grid->column_orderby('Fecha'             ,"<dbdate_to_human><#fecha#></dbdate_to_human>",'fecha'   ,'align="right"');
		$grid->column_orderby('Farmacia'          ,"farmacia"                                    ,'farmacia','align="left"');
		$grid->column_orderby('Descripci&oacute;n',"descrip"                                     ,'descrip' ,'align="left"');
		$grid->column_orderby('Ventas'            ,"<nformat><#venta#></nformat>"                ,'venta'   ,'align="right"');
		$grid->column_orderby('compras'           ,"<nformat><#compras#></nformat>"              ,'compras' ,'align="right"');

		$grid->add($this->url.'dataedit/create');
		$grid->build();

		$data['filtro']  = $filter->output;
		$data['content'] = $grid->output;
		$data['head']    = $this->rapyd->get_head().script('jquery.js');
		$data['title']   = heading($this->tits);
		$this->load->view('view_ventanas', $data);

	}

}
?>