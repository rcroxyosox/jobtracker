<?php
	
session_start();	
	
function getStatusDropArr(){
	global $DB;
    $enum_array = array();
    $sql = 'SHOW COLUMNS FROM leads LIKE "status"';
    $res = $DB->query($sql);
    $r = mysql_fetch_array($res);
    preg_match_all('/\'(.*?)\'/', $r[1], $enum_array);
    if(!empty($enum_array[1])) {
        // Shift array keys to match original enumerated index in MySQL (allows for use of index values instead of strings)
        foreach($enum_array[1] as $mkey => $mval) $enum_fields[$mkey+1] = $mval;
        return $enum_fields;
    }
    else return array(); // Return an empty array to avoid possible errors/warnings 
						 //if array is passed to foreach() without first being checked with !empty().
}


// get the stauses in a dropdown menu
function getStatusDrop(){
	$choices = getStatusDropArr();	
	
	$ret = '<select class="input" name="status" id="status">';
	foreach($choices as $k=>$v){
		$selected = ($v == getVal('status'))?'selected="selected"':'';
		$ret .= '<option value="'.$v.'" '.$selected.'>'.$v.'</option>';
	}
	$ret .= '</select>';
	return $ret;
}


if(strlen(getVal('quotefile')) > 0){
	$_SESSION['startedfile'] = getVal('quotefile');
}

?>