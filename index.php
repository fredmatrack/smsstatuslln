
<?php
	
	$platform = 'https://MIJNSCHOOL.smartschool.be/Webservices/V3?wsdl';
	$paswoord = 'PASSWORDWEBSERVICES';
	
	$client = new SoapClient($platform);
	$tmp_arr_klassen = $client->getClassList($paswoord);
	
	$tmp_arr_klassen = unserialize($tmp_arr_klassen);
	
	/*
	echo "<pre>";
	var_dump($tmp_arr_klassen);
	echo "</pre>";
	exit;
	
	*/
	
	$arr_klassen = array();
	
	foreach ($tmp_arr_klassen as $tmpKlas) {
		if($tmpKlas['isOfficial']){
			$arr_klassen[] = $tmpKlas['code'];
		}
	}
	
	sort($arr_klassen);

?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Smartschool status leerlingen</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<script language="JavaScript" src="javascript/javascript.js"></script>
<link href="css/css.css" rel="stylesheet" type="text/css" /> 
</head>

<body bgcolor="#cbc9c9">

<div id="outer" style="width:100%;">  
     <div id="inner" style="width:600px; margin-left: auto; margin-right: auto; border: 1px solid black; margin-top: 80px; height: 500px; background-color: white;">
        
            <div id="header">
            	<form action="index.php" method="GET">
            		<input type="hidden" name="actie" value="zoeken">
	                <div class="titel">Zoek leerling</div>
	                <div class="label">Klas:</div>	
	                		<div class="input">	
	                			<select name="lstKlas">
	                			<?php
	                			
	                				foreach ($arr_klassen as $klas) {
	                					
	                					$selected = '';
	                					if(isset($_GET['lstKlas'])){
	                						if($_GET['lstKlas'] == $klas){
	                							$selected = 'SELECTED';
	                						}
	                					}
	                					
	                					echo "<option value=\"$klas\" $selected>";
	                					echo $klas;
	                					echo "</option>";
	                				}
	                		
	                			?>
	                			</select>
	                		</div>
	                <div class="label">&nbsp;</div>		<div class="input"><input type="submit" value="Zoeken"></div>
                </form>
            </div>
            
    
            <div id="content">
            	<?php
            		
            		if(isset($_GET['actie']) && ($_GET['actie'] == 'zoeken') && (isset($_GET['lstKlas'])) && (in_array($_GET['lstKlas'], $arr_klassen))){
            			
	            			$klas = $_GET['lstKlas'];
	            			
			                $client = new SoapClient($platform);
			                $wsresult = $client->getAllAccounts($paswoord,"$klas","1");
		                
		                	$xmlresult = base64_decode($wsresult);
		                	$xmldata = simplexml_load_string($xmlresult);
		                	
		                	
		                	$arr_leerlingen = array();
		                	
		                	foreach ($xmldata as $account) {
		                		$tmpLeerling = array();
		                		$tmpLeerling['naam'] = (string)$account->naam;
		                		$tmpLeerling['voornaam'] = (string)$account->voornaam;
		                		$tmpLeerling['internnummer'] = (string)$account->internnummer;
		                		
		                		$arr_leerlingen[] = $tmpLeerling;             			
		                	}
		                	
		                	sort($arr_leerlingen);
		                	
		                	foreach ($arr_leerlingen as $leerling) {
		                		echo "<a href=\"index.php?actie=leerling&internnummer={$leerling['internnummer']}&lstKlas=$klas&naam={$leerling['naam']}&voornaam={$leerling['voornaam']}\" class=\"leerling_link\">{$leerling['naam']} {$leerling['voornaam']}</a><br>";
		                	}
	                	
	                
	                }
	                
	                
	                if(isset($_GET['actie']) &&  $_GET['actie'] == 'leerling' && isset($_GET['internnummer']) && isset($_GET['lstKlas']) && in_array($_GET['lstKlas'], $arr_klassen)){
	                
	                	$internnummer = $_GET['internnummer'];
	                	$klas         = $_GET['lstKlas'];
	                
	                	$naam         = isset($_GET['naam'])?$_GET['naam']:'';
	                	$voornaam     = isset($_GET['voornaam'])?$_GET['voornaam']:'';
	                
	                	// SOAP request naar Smartschool en alle leerlingen opvragen
	                		$wsresult = $client->getAllAccountsExtended($paswoord, $klas,"0");
	                	
	                	// wsresult verwerken json
	                		$arr_lln = json_decode($wsresult);
	                		
	                		$gevonden = false;
	                		if(is_array($arr_lln)){
	                	    	foreach ($arr_lln as $lln) {
	                	    		if($lln->internnummer == $internnummer){
	                	    			$gevonden = true;
	                	    			
	                	    			echo "<div>";
	                	    			echo "$naam $voornaam";
	                	    			echo "</div>";
	                	    			
	                	    			
	                	    			echo "<div>";
	                	        		echo "Huidige Smartschool-status: $lln->status";
	                	    			echo "</div>";
	                	    			
	                	    			
	                	    			
	                	    			echo "<br><br>";
	                	    			
	                	    			echo "<div>";
	                	    				if(trim($lln->status) == 'uitgeschakeld' || trim($lln->status) =='inactief' || trim($lln->status) =='inactive' || trim($lln->status) =='disabled' || trim($lln->status) =='administratief'){
	                	    					echo "<a href=\"index.php?actie=activeerleerling&lstKlas=$klas&internnummer=$internnummer\">Account terug activeren</a>";
	                	    				}
	                	    			echo "</div>";
	                	    			
	                	    			
	                	    			echo "<div style=\"font-weight: bold;\">";
	                	    			echo "Paswoord resetten";
	                	    			echo "</div>";
	                	    			
	                	    			echo "<div>Nieuw paswoord:</div>";
	                	    			echo "<form action=\"index.php\" method=\"GET\">";
	                	    			
	                	    				echo "<input type=\"hidden\" name=\"actie\" value=\"paswoordinstellen\">";
	                	    				echo "<input type=\"hidden\" name=\"internnummer\" value=\"$internnummer\">";
	                	    				echo "<input type=\"hidden\" name=\"lstKlas\" value=\"$klas\">";
	                	    				
		                	    			echo "<div><input type=\"text\" name=\"txtPaswoord\" size=\"15\"></div>";
		                	    			echo "<div><input type=\"submit\" value=\"Wijzig paswoord\"></div>";
	                	    			echo "</form>";
	                	    			
	                	    		}
	                	    	}
	                		}
	                		
	                		if($gevonden == false){
	                			echo "Leerling niet gevonden in Smartschool!";
	                		}
	                
	                }
	                
	                if(isset($_GET['actie']) &&  $_GET['actie'] == 'activeerleerling' && isset($_GET['internnummer']) && isset($_GET['lstKlas']) && in_array($_GET['lstKlas'], $arr_klassen)){

							$internnummer = $_GET['internnummer'];
							
							$wsresult = $client->setAccountStatus($paswoord,$internnummer,"actief");
							
							if($wsresult == 0){
								echo "Leerling terug geactiveerd";
							}
							else {
								echo "Fout: foutcode $wsresult";
							}
					}
                	
                	if(isset($_GET['actie']) &&  $_GET['actie'] == 'paswoordinstellen' && isset($_GET['txtPaswoord']) && isset($_GET['internnummer']) && isset($_GET['lstKlas']) && in_array($_GET['lstKlas'], $arr_klassen)){
                		
                		$internnummer = $_GET['internnummer'];
                		$txtPaswoord  = $_GET['txtPaswoord'];
                		
                		$wsresult = $client->savePassword($paswoord,$internnummer,"$txtPaswoord","0");
                		
                		if($wsresult == 0){
                			echo "Paswoord gewijzigd";
                		}
                		else {
                			echo "Fout: foutcode $wsresult";
                		} 
                		
 
                	}
                ?>
                
            </div>
            
    </div>
</div>


</body>
</html>
