<?php
require_once("../_lib/php/db.class.php/db.class.php");
require_once("../_lib/php/version.php");
$DB = new DB();

$startMonth = '08';
$startYear = '2010';
$endMonth = '01';
$endYear = '2011';

$startDate = strtotime("$startYear/$startMonth/01");
$endDate   = strtotime("$endYear/$endMonth/01");

$datearr = array();
$currentDate = $endDate;
while ($currentDate >= $startDate) {
    $date = date('Y-m-d',$currentDate);
		$datearr[] = $date;
    $currentDate = strtotime( date('Y/m/01/',$currentDate).' -1 month');
}
asort($datearr);

function xlabels(){
	global $datearr;
	$xlabels = array();
	foreach($datearr as $d){
		$da = explode('-',$d);
		$xlabels[] = $da[1].'-'.$da[0];
	}
	return $xlabels;
}
print_r(xlabels());

function getBarGraphData($which){
	global $datearr;
	global $DB;
	
	$labels = array();
	$ret = array();
	$sqlarr = array();
	
	foreach($datearr as $d){
		$sql = "SELECT 
						SUM(estimated) as est,
						MONTH('".$d."') as m,
						YEAR('".$d."') as y
						FROM leads 
						WHERE 
						(YEAR(targetdate) = YEAR('".$d."') 
						AND MONTH(targetdate) = MONTH('".$d."'))
						AND status = '".$which."'";
		$sqlarr[] = $sql;
		$r = $DB->queryUniqueObject($sql);
		$ret[] = ($r->est == '')?0:$r->est; 
		
		$labels []= $r->m.' - '.$r->y;
	}
	
	switch($which){
		case 'sql':
		return $sqlarr;
		default:
		return $ret;
	}

}

$lost = implodee(', ',getBarGraphData('lost'));
$closed = implodee(', ',getBarGraphData('closed'));
$pending = implodee(', ',getBarGraphData('pending'));
$sqls = implodee('<br />',getBarGraphData('sql'));
$xlabels = "'".implodee("', '",getBarGraphData('xlabel'))."'"; 
//echo $sqls;

function implodee($delim , $arr){
	sort($arr);
	return (is_array($arr))?implode($delim, $arr):'0';
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Riot : Plot Data</title>
<link href="../_lib/js/dist/jquery.jqplot.css" rel="stylesheet" type="text/css" />
<link href="../_lib/css/style.css" rel="stylesheet" type="text/css" />
<!--[if IE]><script type="text/javascript" src="../_lib/js/dist/excanvas.min.js"></script><![endif]-->
<style>
body{
	font-size: 16px;
}
</style>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
<script type="text/javascript" src="../_lib/js/dist/jquery.jqplot.min.js"></script>
<script type="text/javascript" src="../_lib/js/dist/plugins/jqplot.categoryAxisRenderer.min.js"></script>
<script type="text/javascript" src="../_lib/js/dist/plugins/jqplot.barRenderer.js"></script>
<script type="text/javascript" src="../_lib/js/dist/plugins/jqplot.dateAxisRenderer.js"></script>
<script type="text/javascript" src="../_lib/js/dist/plugins/jqplot.highlighter.min.js"></script>
<script type="text/javascript" src="../_lib/js/dist/plugins/jqplot.cursor.min.js"></script>
<script type="text/javascript" src="../_lib/js/dist/plugins/jqplot.pointLabels.js"></script>
<script type="text/javascript">
$(document).ready(function(){

// begin click event
var line1 = [<?php echo $closed;?>];
var line2 = [<?php echo $lost;?>];
var line3 = [<?php echo $pending;?>];
var xlabels = [<?php echo "'".implode("','",xlabels())."'";?>];



var handler = function(ev, gridpos, datapos, neighbor, plot) {

	if(neighbor){
	xpre = neighbor.data[0];
	y = neighbor.data[1];
	x = xlabels[parseInt(xpre)-1];
	
	//find which series item was clicked - 
	si = neighbor.seriesIndex;
	bar = plot.series[si].label;
	alert(bar+'|'+x);
	}
	
};

$.jqplot.eventListenerHooks.push(['jqplotClick', handler]);
//end click event

plot2 = $.jqplot('chart1', [line1, line2, line3], {
																											
		sortData:0,												
    legend:{show:true, location:'nw'},
    title:'Qurterly View',
		grid:{background: '#FFFFFF', gridLineColor:'#e0e0e0'},
    seriesDefaults:{
        renderer:$.jqplot.BarRenderer, 
        rendererOptions:{barPadding: 5, barMargin: 10, 
					highlightMouseOver: true,
					highlightMouseDown: false,
					highlightColor: null
				},
				pointLabels: {show:true}
    },

		
    series:[
        {label:'closed', color:'#80c779'}, 
        {label:'lost', color:'#ef3f24'}, 
        {label:'pending', color:'#666666'}
    ],
    axes:{
        xaxis:{
            renderer:$.jqplot.CategoryAxisRenderer, 
						ticks:xlabels
						}, 
        yaxis:{min:0, 
							 tickOptions:{formatString:'$%d'},
							 tickInterval:10000}
    }
		
});

	
});

</script>
</head>

<body>
<div id="chart1" style="height:500px;width:800px; "></div>
</body>
</html>