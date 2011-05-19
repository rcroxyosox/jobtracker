<?php    
session_start();

/**/
	//for internal
	define('TTF_DIR','../fonts/');
	
	$id = (isset($_REQUEST['userid']))?$_REQUEST['userid']:$_SESSION['loggedin'];
	$type = $_REQUEST['type'];
	$size = array('w'=>$_REQUEST['w'],'h'=>$_REQUEST['h']);
	
	//debug
	//$id = "10";
	//$type = "dcount"; // mcount, dcount
	
    //include the graph
	require_once ('jpgraph-3.5.0b1/src/jpgraph.php');
	require_once ('jpgraph-3.5.0b1/src/jpgraph_line.php');
	require_once ('jpgraph-3.5.0b1/src/jpgraph_pie.php');
	require_once ('jpgraph-3.5.0b1/src/jpgraph_pie3d.php');

	// include the db functions
	require_once('db.class.php/db.class.php');
	
	//define("TTF_DIR","../fonts/");
	
	
	$DB = new DB();
	
	// this month
	$rep_month_count = $DB->queryUniqueValue("SELECT COUNT(*) FROM `leads` 
										WHERE rep = ".$id." AND 
										MONTH(createdon) = MONTH(CURDATE()) AND 
										YEAR(createdon) = YEAR(CURDATE())");
	$all_month_count = $DB->queryUniqueValue("SELECT COUNT(*) FROM `leads` 
										WHERE 
										MONTH(createdon) = MONTH(CURDATE()) AND 
										YEAR(createdon) = YEAR(CURDATE())");		
	
	
	$rep_total_dollars = $DB->queryUniqueValue("SELECT ROUND(SUM((estimated_high+estimated_low)/2),2) 
												FROM `leads` WHERE rep = ".$id." AND 
												MONTH(createdon) = MONTH(CURDATE()) AND 
												YEAR(createdon) = YEAR(CURDATE())");

	$total_dollars = $DB->queryUniqueValue("SELECT ROUND(SUM((estimated_high+estimated_low)/2),2) 
												FROM `leads` WHERE  
												MONTH(createdon) = MONTH(CURDATE()) AND 
												YEAR(createdon) = YEAR(CURDATE())");

switch($type){
	
	// count all image
	case 'mcount': 	
	$sliceCol = "riot_tracker_red1";
	$data = array($rep_month_count,$all_month_count);
	$legends = array('my assigned leads','other leads');
	$format = "%d";
	$title = "leads assigned to me";
	break;

	case 'dcount': 	
	$sliceCol = "riot_dark_green";
	$data = array($rep_total_dollars,$total_dollars);
	$legends = array('my assigned potential $','other $');
	$format = "$%.2f";
	$title = "Potential $";
	break;
	
    default:
   	$sliceCol = "riot_tracker_red1";	
	$data = array($rep_month_count,$all_month_count);
	$legends = array('my assigned leads','other leads');
	$format = "%d";
	$title = "leads assigned to me";
}


$graph = new PieGraph($size['w'],$size['h']);
$graph->SetShadow();
$graph->SetColor('#333333');

// style the legend
$graph->legend->SetColor('#dfdfdf');
$graph->legend->SetFillColor('#333333');
$graph->legend->SetFont(FF_UNIVERS,FS_LIGHT,8);
$graph->legend->SetMarkAbsHSize(16);

//style the title
$graph->title->SetColor('#4d4d4d');
$graph->title->Set($title);
$graph->title->SetFont(FF_UNIVERS,FS_BOLD,24);


$p1 = new PiePlot3D($data);
$graph->Add($p1);


$p1->SetSliceColors(
array(
	$sliceCol,
	"riot_tracker_grey1"
));

$p1->value->SetColor('#dfdfdf');
$p1->value->SetFont(FF_UNIVERS,FS_BOLD,12);

$p1->SetLegends($legends);
$p1->SetAngle(30);
$p1->ExplodeSlice(0);
$p1->SetCenter(0.5, 0.4);
$p1->SetLabelType(PIE_LABEL_ABS);
$p1->SetValueType(PIE_VALUE_ABS);
$p1->value->SetFormat($format);

$graph->Stroke();
	

?> 