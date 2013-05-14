<?php 


$mapHTML = '';

function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}


function getBestSupportedMimeType($mimeTypes = null) {
    // Values will be stored in this array
    $AcceptTypes = Array ();

    // Accept header is case insensitive, and whitespace isn’t important
    $accept = strtolower(str_replace(' ', '', $_SERVER['HTTP_ACCEPT']));
    // divide it into parts in the place of a ","
    $accept = explode(',', $accept);
    foreach ($accept as $a) {
        // the default quality is 1.
        $q = 1;
        // check if there is a different quality
        if (strpos($a, ';q=')) {
            // divide "mime/type;q=X" into two parts: "mime/type" i "X"
            list($a, $q) = explode(';q=', $a);
        }
        // mime-type $a is accepted with the quality $q
        // WARNING: $q == 0 means, that mime-type isn’t supported!
        $AcceptTypes[$a] = $q;
    }
    arsort($AcceptTypes);

    // if no parameter was passed, just return parsed data
    if (!$mimeTypes) return $AcceptTypes;

    $mimeTypes = array_map('strtolower', (array)$mimeTypes);

    // let’s check our supported types:
    foreach ($AcceptTypes as $mime => $q) {
       if ($q && in_array($mime, $mimeTypes)) return $mime;
    }
    // no mime-type found
    return null;
}

function getQueryResults($query){
	global $firephp;
    $db = sparql_connect( "http://data.uni-muenster.de/sparql" );
	//$db = sparql_connect( "http://83.169.33.54:8080/parliament/sparql" );
	if( !$db ) { $htmltable.= $db->errno() . ": " . $db->error(). "\n"; exit; }
	
	//	foreach ($namespaces as $short => $long) {	
	//		$db->ns( $short, $long );
	//	}

	$result = $db->query( $query ); 
	if( !$result ) { $firephp -> log($db->errno() . ": " . $db->error(). "\n"); exit; }

	return $result;
}



function getMapData($resource){
	global $mapHTML;	
		
	$query = "SELECT ?feature ?wkt WHERE { GRAPH ?g1 {<$resource> <http://www.opengis.net/ont/geosparql#hasGeometry> ?feature . ";
	$query .= "?feature	<http://www.opengis.net/ont/geosparql#hasSerialization>	?wkt}}";
	/*
	echo "<br>";
	echo htmlspecialchars($query);
	echo "<br>";
	*/
	$result = getQueryResults($query);
	while( $row = $result->fetch_array( $result ) ){		
		$mapHTML .= $row['wkt'];
	}
}
	
function ass_array($rows,$lang="en"){
		global $firephp;
		$firephp-> log($rows);
		$tempAr=array();
		foreach( $rows as $i=>$row )
		{
			//$firephp-> log($row['Predicate']['value']);
			if (array_key_exists($row['Predicate']['value'], $tempAr)) {
				if(isset($row['Object']['lang']) && $row['Object']['lang']==$lang){

					array_unshift($tempAr[$row['Predicate']['value']],$row['Object']['value']);
				}else{
					array_push($tempAr[$row['Predicate']['value']],$row['Object']['value']);
				}	
			} else{
				
				$tempAr[$row['Predicate']['value']]=array($row['Object']['value']);
			}
		}
			//$firephp -> log($tempAr);
		return $tempAr;
}


function generateTabLists($tabqueries,$lang){
	global $firephp;
	$temp=array();
	$active=false;
	while (list($key,$value) = each($tabqueries)) 
    {
    	$html="";
        $result = getQueryResults($value);
        
        if(($result->num_rows())<1){
      
			//break;
		}else{
			if(!$active){
				$html.='<div class="tab-pane active" id="'.strtolower($key).'">';
				$active=true;
			}else{
				$html.='<div class="tab-pane" id="'.strtolower($key).'">';
			}

			$html.='<ul class="list-style content">';
			$fields= $result->field_array();      
			//while ( $row = $result->fetch_array( $result ) ){
			$firephp->log($result);
			$tempAr=array();
			foreach( $result->rows as $i=>$row )
			{
				if (array_key_exists($row[$fields[0]]['value'], $tempAr)) {
					if(isset($row[$fields[1]]['lang']) && $row[$fields[1]]['lang']==$lang){

						array_unshift($tempAr[$row[$fields[0]]['value']],$row[$fields[1]]['value']);
					}else{
						array_push($tempAr[$row[$fields[0]]['value']],$row[$fields[1]]['value']);
					}	
				} else{
					$tempAr[$row[$fields[0]]['value']]=array($row[$fields[1]]['value']);
				}
			}
			$firephp->log($tempAr);
			foreach( $tempAr as $i=>$row )
			{
				$html.='<li><a href="'.$i.'">'.$row[0].'</a></li>';
				//$firephp->log($row);
				//$html.='<li><a href="'.$row[$fields[0]]['value'].'">'.$row[$fields[1]]['value'].'</a></li>';
			} 
			//}
			$html.='</ul>';
			$html.='<div id="page_navigation" class="pagination pagination-centered"></div>';
			$html.='</div>';
			$temp[$key]=$html;
		}

    }

    $html='<ul class="nav nav-tabs" id="list-tabs">';
    $active=false;
    $tabcontent='<div class="tab-content">';
	while (list($key,$value) = each($temp)) 
    {		
    	if(!$active){
    		$html.='<li class="active"><a href="#'.strtolower($key).'"  data-toggle="tab">'.$key.'</a></li>';	
    		$active=true;
    	}else{
    		$html.='<li><a href="#'.strtolower($key).'">'.$key.'</a></li>';	
    	}
    	$tabcontent.=$value;
    	  
    }
    $html.='</ul>';
    $tabcontent.='</div>'; //end tab-content
    return $html.$tabcontent;				

	

}

function getResultsAndShowList($query){
	global $firephp;

	$result = getQueryResults($query);
	if(($result->num_rows())<1){
		return "";
	}
	$html='<ul>';
	//$html='<dl>';
	//$html.='<dt id="'.strtolower($headname).'" class="span btn btn-small"><i id="tooglebutton" class="closed"></i><div class="pull-left">'.$headname.'</div></dt>';
	//$firephp -> log($result->field_array());
	$fields= $result->field_array();      
	while ( $row = $result->fetch_array( $result ) ){
		//$firephp -> log($row);
		//$html.='<dd style="display:none;"><a href="'.$row[$fields[0]].'">'.$row[$fields[1]].'</a></dd>';
		$html.='<li><a href="'.$row[$fields[0]].'">'.$row[$fields[1]].'</a></li>';
	}
	//$html.="</dl>";
	$html.='</ul>';
	return $html;
}

/**
 * Fires the sparql request and gets results. Only the first binding will be returned and joined by a seperator,
 * independently of how many variables have been selected in the query.
 *
 * @param String $query The Sparql-Query
 * @param String $sep Seperator e.g. ','
 *
 * @return String 
 */
function getResultsAndShowSimpleFacts($query,$seperator=", "){
	global $firephp;

	$result = getQueryResults($query);
	if(($result->num_rows())<1){
		return '';
	}
	
	$stack=array();
	$fields= $result->field_array();      
	while ( $row = $result->fetch_array( $result ) ){
		//$firephp -> log($row);
		array_push($stack, $row[$fields[0]]);
		//$html.=$row[$fields[0]];
	}

	$html='';
	if(count($stack)>0){
		$html=implode($seperator,$stack);
	}
	return $html;
}


// if there is a result field "Predicate", this function will look for a result field "Label" in the same row and try to display the label
function showPOGTable($result, $group, $hide, $uri){	
	$htmltable="";
    $namespaces = array(
        "http://xmlns.com/foaf/0.1/" => "foaf:",
        "http://purl.org/dc/terms/" => "dc:",
        "http://www.w3.org/2004/02/skos/core#" => "skos:",
        "http://www.w3.org/1999/02/22-rdf-syntax-ns#" => "rdf:",
        "http://www.opengis.net/ont/geosparql#" => "geo:"
    );

   /* $htmltable.= '<br />';
	$htmltable.= 'Query: ' . htmlspecialchars($query);
	*/
	
	

	$fields = $result->field_array( $result );


   $htmltable.= "<br/><br/>";
    if($hide){
    	$htmltable.= "<table class=\"table table-bordered table-striped table-hover\" id=\"propertytable\" style=\"width:100%;display:none;\" >";
   	}else{
    	$htmltable.= "<table class=\"table table-bordered table-striped table-hover\" id=\"propertytable\" style=\"width:100%;\" >";
   
    }
    
	$htmltable.= "<thead>";
	$htmltable.= "<tr>";
	
	$htmltable.= "<th style=\"width: 10px;\" >#</th>";
	foreach( $fields as $field ){
		if($field == "Graph"){
			$htmltable.= "<th style=\"width: 30px;\" >Graph</th>";
		}else if($field == "Label"){
			// nada
		}else{
			$htmltable.= "<th>$field</th>";
		}
	}
	
	$htmltable.= "</tr>";
	$htmltable.= "</thead>";
	$htmltable.= "<tbody>";
	//$lastsubject = "";
	$value = '';
	$display = '';
    $i = 1;
	while( $row = $result->fetch_array( $result ) )
	{
		$isDate = false;
		$htmltable.= "<tr>";
			$htmltable.= '<td>';
        	$htmltable.= $i;
			$htmltable.= '</td>';
		foreach( $fields as $field )
		{
			
			if($field == "Label"){
				//nada - skip this field; we'll show the Label for Predicate
				continue;
			}else if ($field == "Predicate"){
				// use the label if there is one
				if(isset($row["Label"])){
					$value=$row["Label"];
					$link=$row["Predicate"];
				}else{
						$value=$row[$field];
						$link=$row[$field];
					}

			}else if ($field == "Subject"){								
						$value=$row[$field];
						$link=$row[$field];									
			}else{
				$value=$row[$field];
				$link=$row[$field];
			}

			if(substr($link,0,7) == 'http://'){
				
				/* if(isDateProp($link)){
					$isDate = true;
				} */
				
				if($field == "Graph"){
					$value = "<a href='$value' class='btn btn-mini'>Graph</a>";
				}else{

		        $display = $row[$field];
		        foreach ($namespaces as $namespaceUri => $prefix ) {
		            if (stristr($value, $namespaceUri)) {
		                $display = str_replace($namespaceUri, $prefix, $value);
		            }
		        }

				$value = "<a href='$link' class='btn btn-small' >$display</a>";
				}				
			}else{
				// try to format date string:
				if(strlen($value) > 0 && $isDate){
					try {
				   		$date = new DateTime($value);
						$value = $date->format('M d, Y \a\t H:i:s');
					} catch (Exception $e) { }
				}				
			}
			$htmltable.= '<td>';

			if (strlen($value) > 200) {
				$htmltable.= '<div class="uberflow" style=\"max-height: 200px;overflow: auto;\">'.$value.'</div>';
			}else{
				$htmltable.= $value;
			}
				
			$htmltable.= '</td>';

		}
		$htmltable.= "</tr>";
        $i++;
	}
	$htmltable.= "</tbody>";
	$htmltable.= "</table>";

	return $htmltable;
}




?>