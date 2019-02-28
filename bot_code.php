<?php

        ####################################################################
	############################## INCLUDE #############################      
	####################################################################
	include "invoke.php";
	
	####################################################################
	############################# FUNCTION #############################      
	####################################################################
	
	# Funzione che invia il messaggio specificato alla chat indicata
	# sfruttando le API offerte da Telegram
	function inviaMessaggio($url, $chat_id, $message) {
		# Concateno all'URL la funzione per inviare il messaggio più i parametri
		# chat_id: identificativo della chat a cui spedire il messaggio
		# text: messaggio da inviare alla chat specificata, codificato tramite la funzione urlencode
		$url .= "/sendMessage?chat_id=$chat_id&text=".urlencode($message);
		
		# Funzione che spedisce il messaggio
		file_get_contents($url);
	}
	
	# Funzione che analizza il testo ricevuto nella chat per capire
	# come il bot deve agire
	function analizzaTesto($testo, $url, $chat_id, $nome, $cognome) {
		# I comandi per il bot iniziano tutti con il carattere /
		if (substr($testo, 0, 1) == '/') {
			# È un comando per me, recupero solo l'info del comando.
			$all_testo = explode(" ", substr($testo, 1));
			
			# Capisco che inizia un comando perché ho lo " ".
			# Facendo l'explode recupero l'elemento in posizione 0			
			$descrizione_comando = $all_testo[0];
			
			# Rimuovo l'elemento in posizione 0 perché l'ho già salvato
			unset($all_testo[0]);
			
			# Creo l'informazione sulla Serie TV
			$serie_tv = creaSerieTV($all_testo);
			
			switch ($descrizione_comando) {
				case "start": {
					inviaMessaggio($url, $chat_id, "Benvenuto ".$nome." ".$cognome);
				}
				break;
				case "tvupdates": {
					inviaMessaggio($url, $chat_id, "Per ".$serie_tv." ho trovato");
					
					$elenco_torrent = json_decode(getLastUpdate($serie_tv));
					for ($j = 0; $j < count($elenco_torrent); $j++)
						inviaMessaggio($url, $chat_id, $elenco_torrent[$j]);
				}
				break;
				default: {
					inviaMessaggio($url, $chat_id, "Comando sconosciuto");
				}
				break;
			}
		}		
	}
	
	# Funzione che crea l'informazione sulla Serie TV desiderata
	function creaSerieTV($array_testo) {
		$serie_tv = "";
		for ($i = 1; $i <= count($array_testo); $i++) {
			$serie_tv .= $array_testo[$i]." ";
		}
		
		# Rimuovo lo spazio finale aggiuntivo 
		return substr($serie_tv, 0, count($serie_tv) - 2);
	}
	
	####################################################################
	############################### CODE ###############################      
	####################################################################
	
	# Indirizzo web delle funzioni offerte nativamente da Telegram
	$TELEGRAM_API = "https://api.telegram.org/bot";
	# Parametro identificativo del BOT
	$TOKEN = "EH EH";
	$URL = $TELEGRAM_API.$TOKEN;
	
	# Recupero ciò che riceve il bot
	$command_invoke = json_decode(file_get_contents("php://input"), TRUE);
	
	# Identificativo della chat che sta comunicando con il bot
	$chat_id = $command_invoke["message"]["chat"]["id"];
	# Testo ricevuto
	$testo_letto = $command_invoke["message"]["text"];	
	# Info sull'utente
	$nome = $command_invoke["message"]["from"]["first_name"];
	$cognome = $command_invoke["message"]["from"]["last_name"];
		
	analizzaTesto($testo_letto, $URL, $chat_id, $nome, $cognome);	
?>