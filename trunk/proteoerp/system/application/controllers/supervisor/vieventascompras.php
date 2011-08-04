<?php
class vieventascompras extends Controller {
	var $titp='Ventas-Compras';
	var $tits='Ventas-Compras';
	var $url ='supervisor/vieventascompras/';
        
	function vieventascompras(){
		parent::Controller();
		$this->load->library("rapyd");
		$this->datasis->modulo_id('20D',1);
	}
	function index(){
		redirect($this->url."viewinventario");
	}
	function filteredgrid($barras='',$farmacia=''){
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

                if(strlen($barras)>0)
                $filter->db->where('barras',$barras);
                
                if(strlen($farmacia)>0)
                $filter->db->where('farmacia',$farmacia);

		$filter->barras = new inputField('Barras','barras');
		$filter->barras->size      =17;
		$filter->barras->maxlength =15;
                $filter->barras->append($boton);
                //$filter->barras->insertValue=$barras;

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

		$uri = anchor($this->url.'kardex/<#barras#>/<#farmacia#>','<#barras#>');
		$urif= anchor($this->url."filteredgrid/$barras/<#farmacia#>",'<#farmacia#>');

		$grid = new DataGrid(anchor($this->url.'viewinventario','Ir al Inventario por sucursales'));
		$grid->order_by('venta','desc');
		$grid->per_page = 40;

		$grid->column_orderby('Barras'            ,$uri                                          ,'barras'  ,'align="left"');
		$grid->column_orderby('Ano/Mes'           ,"fecha"                                       ,'fecha'   ,'align="right"');
		$grid->column_orderby('Farmacia'          ,$urif                                         ,'farmacia','align="left"');
		$grid->column_orderby('Descripci&oacute;n',"descrip"                                     ,'descrip' ,'align="left"');
		$grid->column_orderby('Ventas'            ,"<nformat><#venta#></nformat>"                ,'venta'   ,'align="right"');
		$grid->column_orderby('Compras'           ,"<nformat><#compras#></nformat>"              ,'compras' ,'align="right"');
		$grid->column_orderby('Saldo'             ,"<nformat><#saldo#></nformat>"                ,'saldo'   ,'align="right"');

		$grid->build();
//echo $grid->db->last_query();

		$data['filtro']  = $filter->output;
		$data['content'] = $grid->output;
		$data['head']    = $this->rapyd->get_head().script('jquery.js');
		$data['title']   = heading($this->tits);
		$this->load->view('view_ventanas', $data);

	}
        
        function viewinventario(){
		$this->rapyd->load('datafilter','datagrid');
		
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

                $this->rapyd->set_connection('consolidado');
		$this->rapyd->load_db();

		$filter = new DataFilter($this->titp, 'viewinventario');

		$filter->barras = new inputField('Barras','barras');
		$filter->barras->size      =17;
		$filter->barras->maxlength =15;
		$filter->barras->append($boton);

		$filter->descrip = new inputField('Descripci&oacute;n','descrip');
		$filter->descrip->size      =47;
		$filter->descrip->maxlength =45;

		$filter->existen = new inputField('Existencia','existen');
		$filter->existen->size      =36;
		$filter->existen->maxlength =34;

		$filter->marca = new inputField('Marca','marca');
		$filter->marca->size      =24;
		$filter->marca->maxlength =22;

		$filter->grupo = new inputField('Grupo','grupo');
		$filter->grupo->size      =6;
		$filter->grupo->maxlength =4;

		$filter->buttons('reset', 'search');
		$filter->build();

		$uri = anchor($this->url.'filteredgrid/<#barras#>/','<#barras#>');

		$grid = new DataGrid('');
		$grid->order_by('barras');
		$grid->per_page = 40;

		$grid->column_orderby('Barras'            ,"$uri"                            ,'barras'    ,'align="left"');
		$grid->column_orderby('Descripci&oacute;n',"descrip"                         ,'descrip'   ,'align="left"');
		$grid->column_orderby('Existencia'        ,"<nformat><#existen#></nformat>"  ,'existen'   ,'align="right"');
		$grid->column_orderby('Marca'             ,"marca"                           ,'marca'     ,'align="left"');
		$grid->column_orderby('Grupo'             ,"grupo"                           ,'grupo'     ,'align="left"');
		$grid->column_orderby('Farmia'            ,"<nformat><#farmia#></nformat>"   ,'farmia'    ,'align="right"');
		$grid->column_orderby('Botica'            ,"<nformat><#botica#></nformat>"   ,'botica'    ,'align="right"');
		$grid->column_orderby('Elcarmen'          ,"<nformat><#elcarmen#></nformat>" ,'elcarmen'  ,'align="right"');
		$grid->column_orderby('Ermita'            ,"<nformat><#ermita#></nformat>"   ,'ermita'    ,'align="right"');
		$grid->column_orderby('Estacion'          ,"<nformat><#estacion#></nformat>" ,'estacion'  ,'align="right"');
		$grid->column_orderby('Galenica'          ,"<nformat><#galenica#></nformat>" ,'galenica'  ,'align="right"');
		$grid->column_orderby('Gema'              ,"<nformat><#gema#></nformat>"     ,'gema'      ,'align="right"');
		$grid->column_orderby('Sebastian'         ,"<nformat><#sebastian#></nformat>",'sebastian' ,'align="right"');

		$grid->build();

		$data['filtro']  = $filter->output;
		$data['content'] = $grid->output;
		$data['head']    = $this->rapyd->get_head().script('jquery.js');
		$data['title']   = $this->titp;
		$this->load->view('view_ventanas', $data);

	}
	
	function kardex($barras,$farmacia){
	
	
	}
}
?>