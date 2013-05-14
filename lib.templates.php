<?

class Thing{
	protected $foafNS="http://xmlns.com/foaf/0.1/";
	protected $dctNS="http://purl.org/dc/terms/";
	protected $dcNS="http://purl.org/dc/elements/1.1/";
	protected $biboNS="http://purl.org/ontology/bibo/";
	protected $isbdNS="http://iflastandards.info/ns/isbd/elements/";
	protected $geoNS="http://www.w3.org/2003/01/geo/wgs84_pos#";
	protected $vcardNS="http://www.w3.org/2006/vcard/ns#";
	protected $resNS="http://www.medsci.ox.ac.uk/vocab/researchers/0.1/";
	protected $pvNS="http://linkedscience.org/pv/ns#";

	function __construct($uri,$result,$lang)
	{
		global $firephp;
		$this->firephp = $firephp;
		$this->uri = $uri;
		$this->result = $result;
		$this->lang = $lang;
		$this->assarray = ass_array($result->rows,$lang);
		//$this->assarray = $assAr;
		//$firephp -> log($this->assarray);
		
	}

	function getHtmlMap(){
		return '<div class="span3 hidden-phone pull-right"></div>';
	}

	function getHtmlTabs(){
		return '';
	}

	function getHtmlProperties(){
		return '';
	}


}

class Agent extends Thing{

	function getName(){
 		if(array_key_exists($this->foafNS.'name',$this->assarray)){
 			return $this->assarray[$this->foafNS.'name'][0];
 		}
 		return "";
 	}

 	function getHomepage(){
 		if(array_key_exists($this->foafNS.'homepage',$this->assarray)){
 			return $this->assarray[$this->foafNS.'homepage'][0];
 		}
 		return "";
 	}

 	function getMbox_sha1sum(){
 		if(array_key_exists($this->foafNS.'mbox_sha1sum',$this->assarray)){
 			return $this->assarray[$this->foafNS.'mbox_sha1sum'][0];
 		}
 		return "";
 	}

 	//should be in range of owl:Thing
 	function getDepiction(){
 		if(array_key_exists($this->foafNS.'depiction',$this->assarray)){
 			return $this->assarray[$this->foafNS.'depiction'][0];
 		}
 		return "";
 	}

}

class Group extends Agent{

	function getMember(){
 		if(array_key_exists($this->foafNS.'member',$this->assarray)){
 			return $this->assarray[$this->foafNS.'member'][0];
 		}
 		return "";
 	}

 	
}

class Person extends Agent{


 	function getImg(){
 		if(array_key_exists($this->foafNS.'img',$this->assarray)){
 			return $this->assarray[$this->foafNS.'img'][0];
 		}
 		return "";
 	}

 	function getFax(){
 		if(array_key_exists('http://www.w3.org/2006/vcard/ns#fax',$this->assarray)){
 			return $this->assarray['http://www.w3.org/2006/vcard/ns#fax'][0];
 		}
 		return "";
 	}
 	
 	function getPhone(){
 		if(array_key_exists($this->foafNS.'phone',$this->assarray)){
 			return $this->assarray[$this->foafNS.'phone'][0];
 		}
 		return "";
 	}

 	function getFirstName(){
 		if(array_key_exists($this->foafNS.'firstName',$this->assarray)){
 			return $this->assarray[$this->foafNS.'firstName'][0];
 		}
 		return "";
 	}

 	 function getLastName(){
 	 	if(array_key_exists($this->foafNS.'lastName',$this->assarray)){
 			return $this->assarray[$this->foafNS.'lastName'][0];
 		}
 		return "";
 	}

 	function getFamilyName(){
 		if(array_key_exists($this->foafNS.'familyName',$this->assarray)){
 			return $this->assarray[$this->foafNS.'familyName'][0];
 		}
 		return "";
 	}

 	function getGivenName(){
 		if(array_key_exists($this->foafNS.'givenName',$this->assarray)){
 			return $this->assarray[$this->foafNS.'givenName'][0];
 		}
 		return "";
 	}

 	 function getTitle(){
 	 	if(array_key_exists($this->foafNS.'title',$this->assarray)){
 			return $this->assarray[$this->foafNS.'title'][0];
 		}
 		return "";
 	}

 	function getFullName(){
 		return $this->getTitle().' '.$this->getFirstName().' '.$this->getLastName();
 	}

 	function getPublications(){
 		if(array_key_exists($this->foafNS.'publications',$this->assarray)){
 			return $this->assarray[$this->foafNS.'publications'][0];
 		}
 		return "";
 	}

 	function getHtmlMap(){
		return '<div class="span3 hidden-phone spark pull-right htmlproperties" id="map_canvas" style="height: 250px;width: 250px;"
							data-spark-format="http://data.uni-muenster.de/rdf-spark/jquery.spark.leaflet.js"
							data-spark-param-zoom="13"
							data-spark-param-center="Muenster, Germany"
							data-spark-query="
							SELECT ?name ?lat ?long ?wkt WHERE {
							?orga foaf:member <'.$this->uri.'> .
							?orga <http://vocab.lodum.de/helper/building> ?building.
							?building foaf:name ?name.
							?building geo:lat ?lat.
							?building geo:long ?long.
							OPTIONAL{
							?building  <http://www.opengis.net/ont/OGC-GeoSPARQL/1.0/hasGeometry> ?geo.
							?geo <http://www.opengis.net/ont/OGC-GeoSPARQL/1.0/asWKT> ?wkt.
							}
							}"
							data-spark-param-latitude="lat"
							data-spark-param-longitude="long"
							data-spark-param-label="name"
							data-spark-param-link="wkt"
				></div>';
	}

	function getHtmlProperties(){
		/* $orgas=getResultsAndShowList(
			"PREFIX foaf:<http://xmlns.com/foaf/0.1/>
			SELECT ?uri ?name WHERE {
				?uri foaf:member <".$this->uri."> .
				?uri foaf:name ?name.
				FILTER(langMatches(lang(?name),'en'))
				}"
		); */

		$html='';

		$this->getFullName()!=''	? $html.= '<b>'.$this->getFullName().'</b><br/>' : $html.='';
		$this->getPhone()!=''	? $html.= '<i>Phone: </i>'.$this->getPhone().'<br/>' : $html.='';
		$this->getFax()!=''	? $html.= '<i>Fax: </i>'.$this->getFax().'</br>' : ($html.='');
		//$orgas!=''	? $html.= '<br/>'.$orgas : ($html.='');
		return $html;
	}

	function getHtmlTabs(){
		//global $firephp;
		//$firephp -> log($result);

		$uri = $this->uri;

		$tabqueries = array();
		$tabqueries['Affiliations']="PREFIX foaf:<http://xmlns.com/foaf/0.1/>
			SELECT ?uri ?name WHERE {
				?uri foaf:member <".$uri."> .
				?uri foaf:name ?name.
				}"; 


		$tabqueries['Publications']="
			prefix bibo: <http://purl.org/ontology/bibo/> 
			SELECT ?a ?titleyear WHERE {
				<".$uri.">  ^bibo:producer ?a .
				?a <http://purl.org/dc/terms/title> ?name .
				       OPTIONAL{?a <http://purl.org/dc/terms/issued> ?date} .
				BIND (concat(str(?name),' (',str(?date),')') as ?titleyear).
				FILTER regex(str(?a),'http://data.uni-muenster.de/context/cris/').
			} ORDER BY DESC(?date)";

		$tabqueries['Teaching']="
			SELECT ?uri ?name  WHERE {
				<".$uri."> ^<http://linkedscience.org/teach/ns#teacher> ?uri.
				?uri <http://linkedscience.org/teach/ns#courseTitle> ?na.
				?uri <http://linkedscience.org/teach/ns#academicTerm> ?term.
				BIND(concat(concat(?na,' ('),concat(?term,')')) as ?name)
			} ORDER BY DESC(?term)"; 

		$tabqueries['Co-Authors']="
			prefix owl: <http://www.w3.org/2002/07/owl#>
			prefix bibo: <http://purl.org/ontology/bibo/> 
			prefix foaf: <http://xmlns.com/foaf/0.1/> 
			SELECT ?uri ?name (COUNT(*) AS ?count) WHERE {
					  ?pub bibo:producer <".$uri.">  .
					  ?pub bibo:producer ?uri .
					  FILTER( !EXISTS{?uri owl:sameAs <".$uri."> }).
					  ?uri foaf:name ?name .
					FILTER (!regex(str(?uri),'/csa/')).
					 } GROUP BY ?name ?uri ORDER BY DESC(?count)";

		$tabqueries['Projects']="
			SELECT ?a ?name WHERE {
			<".$uri."> ^<http://linkedscience.org/pv/ns#participant> ?a .
			?a <http://linkedscience.org/pv/ns#title> ?name.
			}";

		$tabqueries['Awards']="
			SELECT ?uri ?name WHERE {
			<".$uri."> <http://www.medsci.ox.ac.uk/vocab/researchers/0.1/holdsAward> ?uri .
			?uri <http://purl.org/dc/elements/1.1/title> ?name.
			}";

		$html ='<div class="row-fluid">
					'.generateTabLists($tabqueries,$this->lang).'
				</div>';
		return $html;
	}


}

class Organization extends Agent{
	function getVcard(){
			
			$results= getQueryResults("
				prefix vcard:<http://www.w3.org/2006/vcard/ns#>
				prefix foaf: <http://xmlns.com/foaf/0.1/> 
				SELECT ?uri ?st ?reg ?plz ?name WHERE {
				<".$this->uri."> vcard:adr ?uri.
				<".$this->uri."> foaf:name ?name.
				OPTIONAL{?uri vcard:street-address ?st}.
				OPTIONAL{?uri vcard:region ?reg}.
				OPTIONAL{?uri vcard:postal-code ?plz}.
				} LIMIT 1
			");
			$html='';
			if(sizeof($results->rows)>0){
				$name=$results->rows[0]['name']['value'];
				$plz=$results->rows[0]['plz']['value'];
				$st=$results->rows[0]['st']['value'];
				$reg=$results->rows[0]['reg']['value'];

				$html='<address>';
				$name!=''	? $html.='<strong>'.$name.'</strong><br>' : $html.='';
				$st!=''	? $html.=$st.'<br>': $html.='';
				$plz!=''	? $html.=$plz : $html.='';
				$reg!=''	? $html.=' '.$reg.'<br/>' : $html.='<br/>';
				$this->getHomepage()!=''	? $html.='<abbr title="Homepage">URL:</abbr> '.$this->getHomepage().'<br/>' : $html.='<br/>';
				 // <abbr title="Phone">P:</abbr> 
				$html.='</address>';
			}

			return $html;
			//$this -> firephp -> log($results->rows[0]['plz']['value']);

	}

	function getHtmlProperties(){

		$html='';

		$html.=$this->getVcard();


		//$this->getName()!=''	? $html.= '<b>'.$this->getName().'</b><br/>' : $html.='';
		/* $this->getPhone()!=''	? $html.= '<i>Phone: </i>'.$this->getPhone().'<br/>' : $html.='';
		$this->getFax()!=''	? $html.= '<i>Fax: </i>'.$this->getFax().'</br>' : ($html.='');
		$orgas!=''	? $html.= '<br/>'.$orgas : ($html.='');*/
		return $html;
	}

	function getHtmlMap(){
		return '<div class="span3 hidden-phone spark pull-right htmlproperties" style="height: 250px;width: 250px;"
							data-spark-format="http://data.uni-muenster.de/rdf-spark/jquery.spark.leaflet.js"
							data-spark-param-zoom="13"
							data-spark-param-center="Muenster, Germany"
							data-spark-query="
							SELECT ?name ?lat ?long ?wkt WHERE {
							<'.$this->uri.'> <http://vocab.lodum.de/helper/building> ?building.
							?building foaf:name ?name.
							?building geo:lat ?lat.
							?building geo:long ?long.
							OPTIONAL{
							?building  <http://www.opengis.net/ont/OGC-GeoSPARQL/1.0/hasGeometry> ?geo.
							?geo <http://www.opengis.net/ont/OGC-GeoSPARQL/1.0/asWKT> ?wkt.
							}
							}"
							data-spark-param-latitude="lat"
							data-spark-param-longitude="long"
							data-spark-param-label="name"
							data-spark-param-link="wkt"
				></div>';
	}

	function getHtmlTabs(){
		//global $firephp;
		//$firephp -> log($result);

		$uri = $this->uri;

		$tabqueries = array();

		$tabqueries['Head-Organizations']="
			PREFIX foaf:<http://xmlns.com/foaf/0.1/> 
			PREFIX aiiso:<http://purl.org/vocab/aiiso/schema#>
			SELECT ?a (SAMPLE(?name) as ?orgaName) WHERE {
			<".$uri."> <http://purl.org/vocab/aiiso/schema#part_of> ?a .
			?a foaf:name ?name.
			FILTER(langMatches(lang(?name),'en')).
			FILTER regex(str(?a),'http://data.uni-muenster.de/context/cris/').
			}GROUP BY ?a";


		$tabqueries['Sub-Organizations']="
			PREFIX foaf:<http://xmlns.com/foaf/0.1/> 
			PREFIX aiiso:<http://purl.org/vocab/aiiso/schema#>
			SELECT ?a ?name WHERE {
			?a aiiso:part_of <".$uri.">  .
			?a foaf:name ?name.
			FILTER(langMatches(lang(?name),'en')).
			FILTER regex(str(?a),'http://data.uni-muenster.de/context/cris/').
			}";

		$tabqueries['Employees']="
			PREFIX foaf:<http://xmlns.com/foaf/0.1/>
			SELECT ?a ?name WHERE {
			<".$uri.">  foaf:member ?a .
			?a foaf:name ?name ;
			FILTER regex(str(?a),'http://data.uni-muenster.de/context/cris/person/').
			} ORDER BY ASC (?name)"; 


		$tabqueries['Projects']="
			PREFIX foaf:<http://xmlns.com/foaf/0.1/> 
			SELECT DISTINCT ?b ?name WHERE {
			<".$uri."> foaf:member ?a .
			?a ^<http://linkedscience.org/pv/ns#participant> ?b .
			?b <http://linkedscience.org/pv/ns#acronym> ?name.
				FILTER regex(str(?a),'http://data.uni-muenster.de/context/cris/person/').
			}";

		$tabqueries['Teaching']="
			prefix aiiso:<http://purl.org/vocab/aiiso/schema#>
			prefix teach:<http://linkedscience.org/teach/ns#>
			SELECT ?a ?name WHERE {
			<".$uri."> aiiso:teaches ?a .
			?a teach:courseTitle ?na.
			?a teach:academicTerm ?term.
			BIND(concat(concat(?na,' ('),concat(?term,')')) as ?name)
			}";		

		$tabqueries['Publisher']="
			prefix aiiso:<http://purl.org/vocab/aiiso/schema#>
			prefix teach:<http://linkedscience.org/teach/ns#>
			prefix dc:<http://purl.org/dc/elements/1.1/>
			SELECT ?link ?name WHERE {
				?link dc:publisher <".$uri.">.
				?link dc:title ?name.
			}";		

		

		$html ='<div class="row-fluid">
					'.generateTabLists($tabqueries,$this->lang).'
				</div>';
		return $html;
	}

}

class Project extends Group{
	function getDescription(){
	 		if(array_key_exists($this->pvNS.'description',$this->assarray)){
	 			return $this->assarray[$this->pvNS.'description'][0];
	 		}
	 		return "";
	 }

	function getTitle(){
	 		if(array_key_exists($this->pvNS.'title',$this->assarray)){
	 			return $this->assarray[$this->pvNS.'title'][0];
	 		}
	 		return "";
	 }

	function getAcronym(){
	 		if(array_key_exists($this->pvNS.'acronym',$this->assarray)){
	 			return $this->assarray[$this->pvNS.'acronym'][0];
	 		}
	 		return "";
	 }	

	function getProjectNumber(){
	 		if(array_key_exists($this->pvNS.'projectNumber',$this->assarray)){
	 			return $this->assarray[$this->pvNS.'projectNumber'][0];
	 		}
	 		return "";
	 }

	 function getHtmlTabs(){
		$uri = $this->uri;
		$tabqueries = array();
		$tabqueries['Participants']="
			prefix foaf: <http://xmlns.com/foaf/0.1/> 
			SELECT ?a ?name WHERE {
			<".$uri."> <http://linkedscience.org/pv/ns#participant> ?a .
			?a foaf:name ?name ;
			FILTER regex(str(?a),'http://data.uni-muenster.de/context/cris/person/').
			} ORDER BY ASC (?name)
			";

		$html ='<div class="row-fluid">
					'.generateTabLists($tabqueries,$this->lang).'
				</div>';
		return $html;
	}

	function getHtmlProperties(){
		$html='';
		$this->getAcronym()!='' ? $acro=' ('.$this->getAcronym().')' : $acro='';

		$this->getTitle()!=''	? $html.= '<h5>'.$this->getTitle().$acro.'</h5>' : $html.='';

		$this->getDescription()!=''	? $html.= '<br/><i>'.$this->getDescription().'</i><br/>' : $html.='';
		
		return $html;
	}
}


class Award extends Thing{

}

class Document extends Thing{

	function getTitle(){
 		if(array_key_exists($this->dctNS.'title',$this->assarray)){
 			return $this->assarray[$this->dctNS.'title'][0];
 		}
 		return "";
 	}

	function getIssueYear(){
 		if(array_key_exists($this->dctNS.'issued',$this->assarray)){
 			return $this->assarray[$this->dctNS.'issued'][0];
 		}
 		return "";
 	}

	function getAbstract(){
 		if(array_key_exists($this->biboNS.'abstract',$this->assarray)){
 			return $this->assarray[$this->biboNS.'abstract'][0];
 		}
 		return "";
 	}

	function getIsbn(){
 		if(array_key_exists($this->biboNS.'isbn',$this->assarray)){
 			return $this->assarray[$this->biboNS.'isbn'][0];
 		}
 		return "";
 	}

 	function getSubject(){
 		if(array_key_exists($this->dctNS.'subject',$this->assarray)){
 			return $this->assarray[$this->dctNS.'subject'][0];
 		}
 		return "";
 	}

 	function getIssn(){
 		if(array_key_exists($this->biboNS.'issn',$this->assarray)){
 			return $this->assarray[$this->biboNS.'issn'][0];
 		}
 		return "";
 	}

 	function getUrl(){
 		if(array_key_exists($this->foafNS.'homepage',$this->assarray)){
 			return $this->assarray[$this->foafNS.'homepage'][0];
 		}
 		return "";
 	}

 	function getPublisher(){
		if(array_key_exists($this->dctNS.'publisher',$this->assarray)){
 			$publisher=$this->assarray[$this->dctNS.'publisher'][0];
 			if(!strncmp($publisher, 'http://', strlen('http://'))){
 				$publisher=getResultsAndShowSimpleFacts("
					PREFIX bibo:<http://purl.org/ontology/bibo/>
					PREFIX dct:<http://purl.org/dc/terms/>
					PREFIX foaf:<http://xmlns.com/foaf/0.1/>
					SELECT ?name WHERE {
						<".$this->uri."> dct:publisher ?uri  .
						?uri foaf:name ?name.
					}"
				);
 			}
 			return $publisher;
 		}
 		return "";
 	}




	function getHtmlProperties(){

		$authors=getResultsAndShowList(
			"PREFIX bibo:<http://purl.org/ontology/bibo/>
			PREFIX foaf:<http://xmlns.com/foaf/0.1/>
			SELECT ?uri ?name WHERE {
				<".$this->uri."> bibo:producer ?uri  .
				?uri foaf:name ?name.
				FILTER regex(str(?uri),'http://data.uni-muenster.de/context/cris/person/').
			} ORDER BY ASC (?name)"
		);

		$authorstring=getResultsAndShowSimpleFacts(
			"PREFIX bibo:<http://purl.org/ontology/bibo/>
			PREFIX foaf:<http://xmlns.com/foaf/0.1/>
			SELECT DISTINCT ?name WHERE {
				<".$this->uri."> bibo:authorlist ?list .
				?list ?a ?uri.
				?uri foaf:name ?name.
			}"
		);

		$conference=getResultsAndShowSimpleFacts(
			"PREFIX bibo:<http://purl.org/ontology/bibo/>
			PREFIX foaf:<http://xmlns.com/foaf/0.1/>
			SELECT ?name WHERE {
				<".$this->uri."> bibo:presentedAt ?uri  .
				?uri foaf:name ?name.
			}"
		);



		$html='';
		$this->getIssueYear()!='' ? $year=$authorstring.' ('.$this->getIssueYear().')' : $year='';

		$this->getTitle()!=''	? $html.= '<h5>'.$year.': '.$this->getTitle().'</h5>' : $html.='';
		$conference!=''	? $html.= 'presented at <i>"'.$conference.'"</i><br/>' : $html.='<br/>';
		$this->getPublisher()!=''	? $html.= 'published by <i>"'.$this->getPublisher().'"</i><br/>' : $html.='';
		$this->getAbstract()!=''	? $html.= '<br/><i>'.$this->getAbstract().'</i><br/>' : $html.='';
		$this->getSubject()!=''	? $html.= '<br/>Subjects: <i>'.$this->getSubject().'</i><br/><br/>' : $html.='';
		$this->getUrl()!=''	? $html.= '<a href="'.$this->getUrl().'"><i class="icon-download-alt" style="margin-right:15px;"></i>Download</a><br/><br/>' : $html.='';
		
		$authors!=''	? $html.= '<br/>More Information about the Authors:'.$authors : ($html.='');
		return $html;

	}
}

class Journal extends Document{

}

class Building extends Thing{
	function getName(){
 		if(array_key_exists($this->foafNS.'name',$this->assarray)){
 			return $this->assarray[$this->foafNS.'name'][0];
 		}
 		return "";
 	}

 	function getVcard(){
			
			$results= getQueryResults("
				prefix vcard:<http://www.w3.org/2006/vcard/ns#>
				prefix foaf: <http://xmlns.com/foaf/0.1/> 
				SELECT ?st ?reg ?plz ?country WHERE {
				<".$this->uri."> vcard:adr ?uri.
				FILTER isURI(?uri).
				OPTIONAL{?uri vcard:street-address ?st}.
				OPTIONAL{?uri vcard:region ?reg}.
				OPTIONAL{?uri vcard:postal-code ?plz}.
				OPTIONAL{?uri vcard:country-name ?country}.
				} LIMIT 1
			");
			$html='';
			if(sizeof($results->rows)>0){
				$name=$this->getName();
				$plz=$results->rows[0]['plz']['value'];
				$st=$results->rows[0]['st']['value'];
				$reg=$results->rows[0]['reg']['value'];
				$country=$results->rows[0]['country']['value'];

				$html='<address>';
				//$name!=''	? $html.='<strong>'.$name.'</strong><br>' : $html='';
				$st!=''	? $html.=$st.'<br>': $html.='';
				$plz!=''	? $html.=$plz : $html.='';
				$reg!=''	? $html.=' '.$reg.'<br/>' : $html.='<br/>';
				$country!=''	? $html.=$country.'<br/>' : $html.='<br/>';
				$html.='</address>';
			}

			return $html;
			//$this -> firephp -> log($results->rows[0]['plz']['value']);

	}

	function getHtmlProperties(){

		$html='';

		$html.=$this->getVcard();
		return $html;
	}

	function getHtmlTabs(){
		$uri = $this->uri;

		$tabqueries = array();

		$tabqueries['Building']="
			SELECT ?a ?name WHERE {
			?a <http://vocab.lodum.de/helper/building> <".$uri.">.
			?a <http://xmlns.com/foaf/0.1/name> ?name.
			FILTER langMatches(lang(?name),'DE')
			}";

		$html ='<div class="row-fluid">
					'.generateTabLists($tabqueries,$this->lang).'
				</div>';
		return $html;
	}

	function getHtmlMap(){
		return '<div class="span3 hidden-phone spark pull-right htmlproperties" style="height: 250px;width: 250px;"
							data-spark-format="http://data.uni-muenster.de/rdf-spark/jquery.spark.leaflet.js"
							data-spark-param-zoom="13"
							data-spark-param-center="Muenster, Germany"
							data-spark-query="
							SELECT ?name ?lat ?long ?wkt WHERE {
							<'.$this->uri.'> foaf:name ?name.
							<'.$this->uri.'> geo:lat ?lat.
							<'.$this->uri.'> geo:long ?long.
							OPTIONAL{
							<'.$this->uri.'>  <http://www.opengis.net/ont/OGC-GeoSPARQL/1.0/hasGeometry> ?geo.
							?geo <http://www.opengis.net/ont/OGC-GeoSPARQL/1.0/asWKT> ?wkt.
							}
							}"
							data-spark-param-latitude="lat"
							data-spark-param-longitude="long"
							data-spark-param-label="name"
							data-spark-param-link="wkt"
				></div>';
	}

}







?>