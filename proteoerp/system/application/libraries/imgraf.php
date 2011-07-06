<?php
include('pChart/pData.class');
include('pChart/pChart.class');

class imgraf{

	function imgraf(){
		
	}

	function pie($valores,$label,$titulo){
		$nombre=tempnam('/tmp', 'g').'.png';

		$DataSet = new pData;
		$DataSet->AddPoint($valores,'Serie1');
		$DataSet->AddPoint($label  ,'Serie2');
		$DataSet->AddAllSeries(); 
		$DataSet->SetAbsciseLabelSerie('Serie2');

		$Test = new pChart(300,200);
		$Test->setFontProperties(APPPATH.'libraries/pChart/Fonts/tahoma.ttf',8);
		$Test->drawFilledRoundedRectangle(7,7,293,193,5,240,240,240);
		$Test->drawRoundedRectangle(5,5,295,195,5,230,230,230);

		$Test->AntialiasQuality = 0;
		$Test->setShadowProperties(2,2,200,200,200);
		$Test->drawFlatPieGraphWithShadow($DataSet->GetData(),$DataSet->GetDataDescription(),120,110,60,PIE_PERCENTAGE,8);
		$Test->clearShadow();
		$Test->drawPieLegend(210,30,$DataSet->GetData(),$DataSet->GetDataDescription(),250,250,250);
		$Test->drawTitle(10,22,$titulo,50,50,50,300);
		$Test->Render($nombre);
		return $nombre;
	}

	function bar($valores,$label,$titulo){
		$nombre=tempnam('/tmp', 'g').'.png';

		$DataSet = new pData;
		$DataSet->AddPoint(array(18000.34),'Serie1');
		$DataSet->AddPoint(array(26840.02),'Serie2');
		$DataSet->AddAllSeries();
		$DataSet->SetAbsciseLabelSerie();
		$DataSet->SetSerieName('Contado' ,'Serie1');
		$DataSet->SetSerieName('Credito' ,'Serie2');

		$Test = new pChart(300,200);
		$Test->setFontProperties(APPPATH.'libraries/pChart/Fonts/tahoma.ttf',8);
		$Test->setGraphArea(50,30,250,180);
		$Test->drawFilledRoundedRectangle(7,7,293,193,5,240,240,240);
		$Test->drawRoundedRectangle(5,5,295,195,5,230,230,230);
		$Test->drawGraphArea(255,255,255,TRUE);
		$Test->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,150,150,150,TRUE,0,2);
		$Test->drawGrid(4,TRUE,200,200,200,50);

		$Test->setFontProperties(APPPATH.'libraries/pChart/Fonts/tahoma.ttf',6);
		$Test->drawTreshold(0,143,55,72,TRUE,TRUE);
		$Test->drawBarGraph($DataSet->GetData(),$DataSet->GetDataDescription(),TRUE);
		//$Test->drawOverlayBarGraph($DataSet->GetData(),$DataSet->GetDataDescription());
		$Test->setFontProperties(APPPATH.'libraries/pChart/Fonts/tahoma.ttf',8);
		$Test->drawLegend(300,30,$DataSet->GetDataDescription(),255,255,255);
		$Test->setFontProperties(APPPATH.'libraries/pChart/Fonts/tahoma.ttf',10);
		$Test->drawTitle(10,22,$titulo,50,50,50,300);
		$Test->Render($nombre);

		return $nombre;
	}
}
