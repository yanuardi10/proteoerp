<?php
require_once(BASEPATH.'application/controllers/validaciones.php');
class margenes_sinv extends validaciones {

        function margenes_sinv(){
                parent::Controller();
                $this->load->library("rapyd");
                //$this->datasis->modulo_id(302,1);
                //define ("THISFILE",   APPPATH."controllers/inventario/". $this->uri->segment(2).EXT);
        }

        function index(){
                redirect("inventario/margenes_sinv/margenes");
        }


        function margenes(){
                $this->rapyd->load("dataedit","dataform");
                $script='
                <script language="javascript" type="text/javascript">
                $(function(){
                        $(".inputnum").numeric(".");
                });
                </script>
                ';
                        
                $form = new DataForm("inventario/margenes_sinv/margenes/modifica");

                $form->margen1 = new inputField2("Magen1", "margen1");
                $form->margen1->size = 10;
                $form->margen1->maxlength=8;
                $form->margen1->css_class='inputnum';
                $form->margen1->rule = 'required|callback_chporcent';

                $form->margen2 = new inputField2("Magen2", "margen2");
                $form->margen2->size = 10;
                $form->margen2->maxlength=8;
                $form->margen2->css_class='inputnum';
                $form->margen2->rule = 'required|callback_chporcent';

                $form->margen3 = new inputField2("Magen3", "margen3");
                $form->margen3->size = 10;
                $form->margen3->maxlength=8;
                $form->margen3->css_class='inputnum';
                $form->margen3->rule      = 'required|callback_chporcent';

                $form->margen4 = new inputField2("Magen4", "margen4");
                $form->margen4->size = 10;
                $form->margen4->maxlength=8;
                $form->margen4->css_class='inputnum';
                $form->margen4->rule      = 'required|callback_chporcent';

                $form->submit("btnsubmit","MODIFICAR");
                $form->build_form();
                if ($form->on_success()){
                        set_time_limit(600);
                        $margen1=$_POST['margen1'];
                        $margen2=$_POST['margen2'];
                        $margen3=$_POST['margen3'];
                        $margen4=$_POST['margen4'];
                        
                        redirect("inventario/margenes_sinv/modifica/$margen1/$margen2/$margen3/$margen4");
                        //$this->procesa();
                }

                

                $data['content'] =$form->output;
                $data['head']    = script('jquery.js').script('jquery-ui.js').script("plugins/jquery.numeric.pack.js").script('plugins/jquery.meiomask.js').style('vino/jquery-ui.css').$this->rapyd->get_head().$script;

                $data['title']   = '<h1>Cambiar Margenes</h1>';
                $this->load->view('view_ventanas', $data);
        }


        function modifica($m1,$m2,$m3,$m4){
//              echo $m1."/".$m2."/".$m3."/".$m4;50,00;37,50;33,33;23,08
                $query="UPDATE sinv SET
                margen1=".$m1.",
                margen2=".$m2.",
                margen3=".$m3.",
                margen4=".$m4.",
                base1 =IF(formcal='U',ultimo,IF(formcal='P',pond,GREATEST(ultimo,pond)))*100/(100-".$m1."),
                base2 =IF(formcal='U',ultimo,IF(formcal='P',pond,GREATEST(ultimo,pond)))*100/(100-".$m2."),
                base3 =IF(formcal='U',ultimo,IF(formcal='P',pond,GREATEST(ultimo,pond)))*100/(100-".$m3."),
                base4 =IF(formcal='U',ultimo,IF(formcal='P',pond,GREATEST(ultimo,pond)))*100/(100-".$m4."),
                precio1=(IF(formcal='U',ultimo,IF(formcal='P',pond,GREATEST(ultimo,pond)))*100/(100-".$m1."))*(1+(iva/100)),
                precio2=(IF(formcal='U',ultimo,IF(formcal='P',pond,GREATEST(ultimo,pond)))*100/(100-".$m2."))*(1+(iva/100)),
                precio3=(IF(formcal='U',ultimo,IF(formcal='P',pond,GREATEST(ultimo,pond)))*100/(100-".$m3."))*(1+(iva/100)),
                precio4=(IF(formcal='U',ultimo,IF(formcal='P',pond,GREATEST(ultimo,pond)))*100/(100-".$m4."))*(1+(iva/100))";
                $this->db->query($query);
                $atras=site_url('inventario/margenes_sinv/margenes');
                $data['content'] ="MODIFICADO MARGENES";
                $data['smenu']="<a href=".$atras.">ATRAS</a>";
                $data['title']   = "<h1>Actualizaci&oacute;n de Inventario</h1>";
                $data["head"]    = script("jquery.pack.js").$this->rapyd->get_head();
                $this->load->view('view_ventanas', $data);
        }
}
?>
