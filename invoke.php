<?php

	# Funzione che richiamo tramite il Bot
	function getLastUpdate($serie_tv) {

		####################################################################
		############################## INCLUDE #############################
		####################################################################
		include "config.php";

		# Array da inviare in POST a tnvvillage
		$dati = array(
			"cat" => 0,
			"page" => 1,
			"srcrel" => $serie_tv
		);
		
		# Opzioni per effettuare correttamente la chiamata a tntvillage
		$options = array(
			'http' => array (
				'header' => "Content-Type:application/x-www-form-urlencoded",
				'method' => "POST",
				'content' => http_build_query($dati)
			)
		);
			
		# Chiamata HTTP
		$context = stream_context_create($options);
		# Risultato della chiamata
		$result = file_get_contents($INVOKE_URL, false, $context);
		if ($result != FALSE) {
			# A questo punto nel $result ho dentro tutto codice HTML
			# devo riuscire ad analizzare questo codice	
			return analizzaResult($result);
		}
		else {
			return "Nessun risultato trovato";
		}
	}
	
	# Funzione che analizza il risultato HTML di tntvillage
	# e mi restituisce gli ultimi aggiornamenti
	function analizzaResult($risultato) {		
		/** Rimozione di fastidiosi Warning */
		$internalErrors = libxml_use_internal_errors(true);
		$dom_document = new DOMDocument();
		$dom_document->loadHTML($risultato);
		libxml_use_internal_errors($internalErrors);
		
		$pattern = "/[< a-z=':\/.?0-9_A-Z->\(\)]*/";
		
		$ret = array();
		
		# Meccanismo di analisi del DOM che scarta la prima riga ($second_foreach) perché
		# è l'intestazione della tabella e che recupera solo la colonna ($i) numero 6 perché
		# è quella contenente il titolo del file 		
		foreach ($dom_document->getElementsByTagName("table") as $table_tag) {
			$second_foreach = 0;
			foreach($table_tag->childNodes as $child) {
				$i = 0;				
				foreach($child->childNodes as $child_level_down) {
					if ($i == 6 && $second_foreach > 0) {
						$episodio = $child_level_down->nodeValue;
						preg_match($pattern, $episodio, $matches); #matches contiene tutte le stringhe che combaciano con il pattern
						for ($j = 0; $j < count($matches); $j++) {
							$ret[] = $matches[$j];
						}
					}
					$i++;
				}
				$second_foreach++;
			}
		}
					
		# Nel caso in cui non trovo risultati, messaggio user-friendly
		if (count($ret) == 0)
			return json_encode(array(0 => "Nessun risultato trovato"));
		
		return json_encode($ret);
	}
?>