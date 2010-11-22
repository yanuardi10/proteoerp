<html>
<head>
<title>Sistemas DataSIS</title>
<?php $this->load->view('estilos'); ?>
<?php $this->load->view('jsmenu'); ?>
<script>
function inicia() {
    document.getElementById('us_nombre').innerHTML = 
    <?php 
    $usuario = $this->session->userdata('us_nombre');
    if( !empty($usuario) ) { 
      echo '"Bienvenido: '.$usuario.'"'; 
    } else { 
      echo '"Usuario no Registrdo"'; 
    } ?>;
    initjsDOMenu();
}
</script>


</head>
<body onload="inicia()">
<div id="encab">
<?php $this->load->view('encab1'); ?>
</div>
<div id="staticMenuBar"></div>
<center>

<H1>OBLIGACIONES TRIBUTARIAS</H1>

<table width="95%" align="center" border=1>
<tr>
    <TD colspan=2><H3>PAPELERIA MODERNA</H3></TD><TD>LIBROS DE VENTAS</TD><TD>LIBROS DE COMPRAS</TD>
</tr>

<tr>
    <td>ANO</td>
    <td>2006</td>
    <td>
<?php
	for ( $mes=200601; $mes<200613; $mes++ )
	{
	    echo "<A href=\"libros/wlvexcelpdv/$mes\">".substr($mes,4,2)."</A>\n";
	}
?>
       
    </td>
    <td>
<?php
	for ( $mes=200601; $mes<200613; $mes++ )
	{
	    echo "<A href=\"libros/wlcexcel/$mes\">".substr($mes,4,2)."</A>\n";
	}
?>
    </td>
</tr>

<tr>
    <td>ANO</td>
    <td>2007</td>
    <td>
<?php
	for ( $mes=200701; $mes<200713; $mes++ )
	{
	    echo "<A href=\"libros/wlvexcelpdv/$mes\">".substr($mes,4,2)."</A>\n";
	}
?>
    
    </td>
    <td>
<?php
	for ( $mes=200701; $mes<200713; $mes++ )
	{
	    echo "<A href=\"libros/wlcexcel/$mes\">".substr($mes,4,2)."</A>\n";
	}
?>
    </td>
</tr>
</table>

<br>
<table width="95%" align="center" border=1>

<tr>
    <td>PRORRATA</td>
    <td>2006</td>
    <td>
<?php
	for ( $mes=200601; $mes<200613; $mes++ )
	{
	    echo "<A href=\"libros/prorrata/$mes\">".substr($mes,4,2)."</A>\n";
	}
?>
    </td>
    <td>2007</td>
    <td>
<?php
	for ( $mes=200701; $mes<200713; $mes++ )
	{
	    echo "<A href=\"libros/prorrata/$mes\">".substr($mes,4,2)."</A>\n";
	}
?>
    </td>
</tr>

</table>
<br>
<br>


</center>
<div id="pie"><?php $this->load->view('pie'); ?></div>
</body>
</html>

