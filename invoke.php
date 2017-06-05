<?php

	# Funzione che richiamo tramite il Bot
	function getLastUpdate($serie_tv) {	
		
		$INVOKE_URL = "http://www.tntvillage.scambioetico.org/src/releaselist.php";

		# Array da inviare in POST a tnvvillage
		$dati = array(
			"cat" => 0,
			"page" => 1,
			"srcrel" => $serie_tv
		);
		
		# Opzioni per effettuare correttamente la chiamata a tntvillage
		$options = array(
			'http' => array (
				'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
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
		
		# Miglioria 1: la tabella ha solo il titolo perché la ricerca non ha prodotto risultati
		# Miglioria 2: rivedere la parte di recupero titolo. Ad esempio, con Moana, ho problemi
		
		$pattern = "/[a-zA-Z ]* [A-Z][0-9]{2}[a-z][0-9]{2}(-[0-9]{2})?/";
		
		$ret = array();
			
		foreach ($dom_document->getElementsByTagName("table") as $table_tag) {
			foreach($table_tag->childNodes as $child) {
				$episodio = $child->nodeValue;
				preg_match($pattern, $episodio, $matches); #matches contiene tutte le stringhe che combaciano con il pattern		
				for ($i = 0; $i < count($matches); $i++) {
					$ret[] = $matches[$i];
				}
			}
		}
		
		return json_encode($ret);
	}
?>