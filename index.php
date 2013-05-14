<?php 

//uncomment for debugging purpose only
//error_reporting(E_ALL); 
//ini_set('display_errors', 1);  

require_once( "lib/sparqllib.php" );
require_once( "queryHelper.php" );
require_once('../FirePHPCore/FirePHP.class.php');
require_once("lib.simple_html_dom.php");
require_once("lib.templates.php");

//firebug
ob_start();

$firephp = FirePHP::getInstance(true);
$firephp->setEnabled(false);

class fbTimer{
  protected static $timerStart = 0;
  protected static $timerEnd = 0;
  public static function start(){
    self::$timerStart = microtime();
    self::$timerEnd = 0;
  }

  public static function stop(){
    self::$timerEnd =microtime();
  }

  public static function get(){
    if(self::$timerEnd == 0) self::stop();
      return self::$timerEnd - self::$timerStart;
    }
  }

fbTimer::start();


 

$host = $_SERVER['HTTP_HOST'];
$req = $_SERVER['REQUEST_URI'];

$uri = "http://".$host.$req;

if(endsWith($uri, ".html")){
	returnHTML(substr($uri, 0, -5));
} else if (endsWith($uri, ".rdf")){
	returnRDF(substr($uri, 0, -4), 'application/rdf+xml');
} else if (endsWith($uri, ".ttl")){
	returnRDF(substr($uri, 0, -4), 'text/turtle');
} else if (endsWith($uri, ".nt")){
	returnRDF(substr($uri, 0, -3), 'text/plain');
} else {

	// Content negotiation:
	// figure out what mime type the client would like to get:
	$mime = getBestSupportedMimeType(Array ('application/xhtml+xml', 'text/html', 'application/rdf+xml', 'text/turtle', 'text/plain'));
	$redirect = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

	if($mime == 'application/xhtml+xml' || $mime == 'text/html'){
		header("HTTP/1.1 303 See Other");
		header("Location: $redirect.html");
	} else if($mime == 'text/turtle'){
		header("HTTP/1.1 303 See Other");
		header("Location: $redirect.ttl");
	} else if($mime == 'text/plain'){
		header("HTTP/1.1 303 See Other");
		header("Location: $redirect.nt");
	} else { // default: RDF/XML
		header("HTTP/1.1 303 See Other");
		header("Location: $redirect.rdf");
	}
}

function returnRDF($uri, $contentType){

	header("Content-Type: $contentType");
	
	if(strpos($uri, "datacontainer") !== false){
		// return the whole contents of the datacontainer via graph store protocol:
		$ch = curl_init("http://data.uni-muenster.de/graphstore?graph=".$uri);	

		// the graphstore endpoint is password protected:
		$login = file_get_contents('../../store.txt');
		curl_setopt($ch, CURLOPT_USERPWD, $login);
	
	}else{
		// we simply fire a DESCRIBE query against the SPARQL endpoint with the corresponding content type and return the result to the client:		
		$ch = curl_init("http://data.uni-muenster.de/sparql?query=".urlencode("DESCRIBE <".$uri.">"));	
			
	}
	curl_setopt($ch, CURLOPT_HTTPHEADER, array ("Accept: $contentType" ));
	curl_exec($ch);
	curl_close($ch);
}


function returnHTML($uri){	
	//$uri="http://data.uni-muenster.de/context/cris/person/9148";
	//$uri="http://data.uni-muenster.de/context/cris/person/6608";
	//$uri="http://data.uni-muenster.de/context/cris/publication/26911";
	//$uri="http://data.uni-muenster.de/context/infrastructure/building/8351";
	//$uri="http://data.uni-muenster.de/context/cris/project/1098";
	//$uri="http://data.uni-muenster.de/context/cris/organization/5046";
	//$uri="http://data.uni-muenster.de/context/cris/addresses/48151/WeselerStr253";
	global $mapHTML;
	global $div;
	global $firephp;

	switch (substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2)){
    case "de":
		$lang="de";
        break;
    case "en":
    	$lang="en";
        break;        
    default:
        $lang="en";
        break;
	}

	// for testing purpose
	//$lang ="en";

	$div.='<div class="row-fluid"><div class="span12">';


	$dataAvailable = false;
	$noQuery = false;
	$hidePropertyTable=true;
	$typeLabel = "";
	$label = "";
	$types;

	if ($_SERVER['REQUEST_URI'] === "/data/") {
		$noQuery = true;
	} else {
		$container = false;

		$result = getQueryResults("prefix dc: <http://purl.org/dc/terms/> 
			prefix skos: <http://www.w3.org/2004/02/skos/core#> 
			prefix foaf: <http://xmlns.com/foaf/0.1/> 

			SELECT * WHERE { 
			  <".$uri."> a ?type .

			  
			  OPTIONAL {
			    ?type skos:prefLabel ?typeLabel .   	
			  }.

			  OPTIONAL{
			  	  <".$uri."> foaf:depiction ?depiction.
			  }

			  OPTIONAL { 
			    { <" . $uri . "> skos:prefLabel ?label. FILTER(langMatches(lang(?label),\"$lang\")).}
			    UNION
			    { <" . $uri . "> foaf:name ?label. FILTER(langMatches(lang(?label),\"$lang\")).} 
			    UNION
			    { <" . $uri . "> dc:title ?label. FILTER(langMatches(lang(?label),\"$lang\")).} 
			    UNION
			    { <" . $uri . "> <http://linkedscience.org/pv/ns#title> ?label. FILTER(langMatches(lang(?label),\"$lang\")).} 
			    UNION 
			    { <" . $uri . "> skos:prefLabel ?label }
			    UNION
			    { <" . $uri . "> foaf:name ?label } 
			    UNION
			    { <" . $uri . "> dc:title ?label } 
			  }.
			  {
			  	SELECT (GROUP_CONCAT(?ty; separator = \";\") as ?types) {
			  		<".$uri."> a ?ty .
			  	} 	
			  }
		          
			} LIMIT 1");
		
		//	$firephp->log($result);

		while ( $row = $result->fetch_array( $result ) ){
			$dataAvailable = true;
			$type = $row["type"];

			if ($type === "http://www.w3.org/2004/03/trix/rdfg-1/Graph") {
				$container = true;
			}

			if(isset($row["typeLabel"])){
				$typeLabel = $row["typeLabel"];
			}		

			if(isset($row["label"])){
				$label = $row["label"];
			}

			if(isset($row["depiction"])){
				$depiction = $row["depiction"];
			}
			//$firephp->log( $row["types"]);
			if(isset($row["types"])){
				$types= explode (";",$row["types"]);
			}
		}//end while
	}//end else

	if ($container) {

		$div.= ' <p><strong>URI Browser for <a href=\"'.$type.'\">'.$typeLabel.'</a> with URI </p></strong>
				  <h3><a href=\"'.$uri.'\">'.$uri.'</a></h3>';

		//explain($uri);

		$div.= '<h4>Metadata for this graph:</h4>';

		//showPOGTable("SELECT ?Predicate ?Label ?Object WHERE {
		 // GRAPH ?Graph { <$uri> ?Predicate ?Object . } GRAPH <http://hxl.carsten.io/graph/hxlvocab>{OPTIONAL { ?Predicate <http://www.w3.org/2004/02/skos/core#prefLabel> ?Label . }}}", false, $uri);
		
		$div.= '<h4>Data in this graph:</h4>';
		
		// get all triples in this container (aka. named graph), except those ABOUT the named graph because we already show those metadata above.
		//showPOGTable("SELECT ?Subject ?Predicate ?Label ?Object WHERE { GRAPH <$uri> { ?Subject ?Predicate ?Object . } GRAPH <http://hxl.carsten.io/graph/hxlvocab>{OPTIONAL { ?Predicate <http://www.w3.org/2004/02/skos/core#prefLabel> ?Label.}} FILTER (?Subject != <$uri>) } ORDER BY ?Subject", true, $uri);

	} else if ($dataAvailable) {
		$result = getQueryResults("SELECT ?Predicate ?Label ?Object ?Graph 
									WHERE { GRAPH ?Graph { <$uri> ?Predicate ?Object. } } 
									ORDER BY ?Subject");

		//select php object depending on the tpyes
		if(in_array("http://xmlns.com/foaf/0.1/Person",$types)){
			$thing = new Person($uri,$result,$lang);
		}else if(in_array("http://xmlns.com/foaf/0.1/Organization",$types)){
			$thing= new Organization($uri,$result,$lang);
		}else if(in_array("http://purl.org/ontology/bibo/Document",$types)){
			$thing= new Document($uri,$result,$lang);
		}else if(in_array("http://dbpedia.org/ontology/building",$types)){
			$thing= new Building($uri,$result,$lang);
		}else if(in_array("http://linkedscience.org/pv/ns#ResearchProject",$types)){
			$thing= new Project($uri,$result,$lang);
		}else{
			$thing = new Thing($uri,$result,$lang);
		} 

		/* $div.=	'<div class="row-fluid">
					<div class="span4">
						<p><strong>URI Browser for <a href=\"'.$type.'\">'.$typeLabel.'</a></p></strong>

					</div>
					<div class="span4 offset4">
						<a href="#" class="muted" id="showpropertytable" ><img src="img/iconmonstr-list-view-icon.png" class="icon" rel="tooltip" data-original-title="Switch to Property Table View"></a>
						<a href="'.$uri.'.rdf" class="muted"><img src="semicon/png/32/rdf.png" class="icon" rel="tooltip" data-original-title="Get RDF/XML Format"></a>
						<a href="'.$uri.'.ttl" class="muted"><img src="semicon/png/32/ttl.png" class="icon" rel="tooltip" data-original-title="Get Turtle Format"></a>
						<a href="'.$uri.'.nt" class="muted"><img src="semicon/png/32/n_triple5.png" class="icon" rel="tooltip" data-original-title="Get N-Triple Format"></a>
					</div>
				</div>'; */
		$div.=	'<div class="row-fluid">';
				
		if(isset($depiction)){
			$div.='<div class="span8">
				<div class="span2"><img src="'.$depiction.'" style="width:100px"/></div>';
			$div.='<div class="span10">';
		}else{
			$div.='<div class="span8"><div class="span10">';
		}
		

		if ($label != ""){
			 $div.= '<h4>'.$label.'</h4>
			 <p><strong>URI: <a href="'.$uri.'">'.$uri.'</a></strong></p>';
		}else{
			$div.= '<h5><a href="'.$uri.'">'.$uri.'</a></h5>';
		}

		$div.='</div><div class="span10 htmlproperties" style="margin-top:50px;" >'.$thing->getHtmlProperties().'</div></div>'; //end span span

		$div.=$thing->getHtmlMap();

		$div.='</div>'; //end span, end fluid-row

		
		$div.='<div class="htmlproperties" id="htmlproperties" style="display:block;margin-top:20px;">';

		( (($thing instanceof Person) || ($thing instanceof Building) || ($thing instanceof Project) || ($thing instanceof Organization) || ($thing instanceof Document) )  ? $div.= $thing->getHtmlTabs() : $hidePropertyTable=false) ;
		
		$div.='</div>';
		


		$div.=showPOGTable($result, true, $hidePropertyTable, $uri);
		$div.='<p class="text-center">
				<a href="'.$uri.'.rdf" class="muted" rel="tooltip" data-original-title="Get RDF/XML Format">RDF </a>
				<a href="'.$uri.'.ttl" class="muted" rel="tooltip" data-original-title="Get Turtle Format">| TTL |</a>
				<a href="'.$uri.'.nt" class="muted" rel="tooltip" data-original-title="Get N-Triple Format"> NT</a>
				</p>';


	} else if (!$dataAvailable) {

		$div.= "<br />";
		if ($noQuery) {
			$div.= "<div class=\"alert alert-info\"><p>No specific data has been requested.<br />To get you started, these are the last graphs that have been modified:</p></div>";
		} else {
			$div.= "<div class=\"alert alert-error\"><p>The URI didn't match any successful query or there is no description for it.<br /> You may want to search throught the most recent LODUM graphs:</p></div>";
		}

		/* showPOGTable("SELECT ?container " .
		"WHERE { ".
		"	GRAPH ?metadata { ".
		"		?container a <http://www.w3.org/2004/03/trix/rdfg-1/Graph> ; ".
		"	} } ORDER BY DESC(?submitted) LIMIT 10", false, $uri); */
	} else {
		
		$div.= "<br />";
		$div.= "<p><div class=\"alert alert-error\">An unexpected result occurred.<br />You may want to search throught the most recent LODUM graphs:</p></div>";

		/* showPOGTable("SELECT ?container " .
		"WHERE { ".
		"	GRAPH ?metadata { ".
		"		?container a <http://www.w3.org/2004/03/trix/rdfg-1/Graph> ; ".
		"	} } ORDER BY DESC(?submitted) LIMIT 10", false, $uri); */

	}


	/* if ($mapHTML != '') {

		include_once('lib/geoPHP.inc');

		$wkt_reader = new WKT();
		$geometry = $wkt_reader->read($mapHTML,TRUE);
		$centroid = $geometry->centroid();
		$x = $centroid->x();
		$y = $centroid->y();
		$json_writer = new GeoJSON();
		$json_geometry = $json_writer->write($geometry);
		}
	?> */
	
	
	//js pagination stuff
	$div.='    <input type="hidden" id="current_page" /><input type="hidden" id="show_per_page" />';
	
	$div.='</div></div>';
	$div.='<script src="http://data.uni-muenster.de/php/context/uribrowser.js"></script>';


	//include into default wordpress template
	$html = file_get_html('http://data.uni-muenster.de/php/cache/cache_wordpress.php.html');
	$htmlheader=' <!-- disable cache -->
	    <meta http-equiv="expires" content="0"> 
	    <meta http-equiv="pragma" content="no-cache"> 
	    
	    <!--[if lt IE 9]>
	      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	    <![endif]-->
	    
	    <!-- Leaflet --> 
	    <link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.4/leaflet.css" />
	 	<!--[if lte IE 8]>
	     <link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.4/leaflet.ie.css" />
	 	<![endif]-->
	  	<script src="http://cdn.leafletjs.com/leaflet-0.4/leaflet.js"></script>

	  	<script type="text/javascript" src="http://data.uni-muenster.de/rdf-spark/jquery.spark.js"></script>

		<style type="text/css">
		icon-small{
		width:20px;height:20px;
		}	

		.closed { background:url(button.png) left no-repeat; }

		.open { background:url(button.png) right no-repeat; }


		#tooglebutton {
		 	margin-right:5px;
		  	line-height: 1;
			float:left;
			display:block;
			height:18px;
			width:17px;
			text-indent:-9999px;
		}

		/*.available{position:relative;}*/
		.bottom{position:absolute;bottom:0}
		</style>
	  	';

	//add content to wordpress template
	$html->find('head', 0)->innertext .=$htmlheader;
	$footer=$html->find('footer', 0)->innertext;
	//$html->find('div[id=contentrow]', 0)->innertext = $div;
	$html->find('div[class=container main]', 0)->innertext = $div.'<footer>'.$footer.'</footer>';

	$html->find('div[class=nav-collapse collapse]',0)->innertext.='<a href="#" id="showpropertytable" class="pull-right" style="margin-top:15px;margin-right:15px;"><i class="icon-list" rel="tooltip" data-original-title="Switch to Property Table View"></i></a>';

	echo $html;
		// some code you want to benchmark
	fbTimer::stop();
	$firephp->log("execution time :" . fbTimer::get() . ' seconds' );

} // end getHTML 
?>
