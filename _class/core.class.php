<?php

class core {

	public $db;

    public static $month = ["janvier","février","mars","avril","mai","juin","juillet","août","septembre","octobre","novembre","décembre"];
    public static $m = ["jan","fév","mars","avr","mai","juin","juil","août","sep","oct","nov","déc"];
	public static $days = ["Dimanche","Lundi","Mardi","Mercredi","Jeudi","Vendredi","Samedi"];

	public static function dump( $obj ) {
		echo '<pre>';
		var_dump($obj);
		echo '</pre>';
		return;
	}
	public static function dumpLog( $log ) {
		ob_start();
		var_export($log);
		$c = ob_get_contents();
		ob_end_clean();
		$c = str_replace(["\n","\r"],PHP_EOL,$c);
		error_log($c);
		return;
	}

	public static function end() {
		if( defined('AJAX') && AJAX ) die('{}');
		return true;
	}

	public static function rep( $str ) {
		if( defined('API_ID_USER') )
			api::aError($str);
		if( defined('AJAX') )
			core::aError($str);
		die($str);
	}

	public function __construct() {
		$this->db = new db();
	}

	public static function ajaxError( $str = "Une erreur est survenue" ) {
		die('{ "err" : true, "errMsg" : "'.rawurlencode($str).'" }');
	}
	public static function aError( $str = "Une erreur est survenue" ) {
		self::ajaxError($str);
	}

	public static function error(  $str = "Une erreur est survenue" ) {
		if( defined('AJAX') && AJAX ) self::aError($str);
		return $str;
	}

	public static function ajax( $tab ) {
		die(json_encode($tab));
	}

    public static function getMailAdmin() {
        $db = new db();
        $mails = [];
        $db->execute("SELECT * FROM mails_admin");
        while( $r = $db->assoc() )
            $mails[] = $r['mail'];
        return $mails;
    }

	public static function salt( $mdp ) {
		return md5($mdp.SALT);
	}

	public static function toFloat( $str ) {
		return floatval(str_replace([","," "],[".",""],$str));
	}
	public static function n( $val ) {
		$f = (intval($val) != $val);
		return number_format($val,$f?2:0,","," ");
	}

	public static function apkDate( $apkDate ) {
		if( $apkDate == "" ) return $apkDate;
		if( strpos($apkDate,"/") > -1 ) return $apkDate;
		if( strpos(trim($apkDate)," ") > 0 ) return self::dateOutput($apkDate);
	  $tmp = explode("T", $apkDate);
		$tmp = explode("-",$tmp[0]);
		$tmp = array_reverse($tmp);
	  return implode("/",$tmp);
	}
	public static function apkDate2( $apkDate, $full = false ) {
		if( $apkDate == "" ) return "";
		if( strpos($apkDate,"/") > -1 ) return $apkDate;
		if( strpos(trim($apkDate)," ") > 0 ) return self::dateOutput($apkDate, $full);
		$tmp = explode("T", $apkDate);
		$tmp1 = explode("-",$tmp[0]);
		$tmp2 = explode(".",$tmp[1]);
		return implode("/",array_reverse($tmp1)).( $full ? ' à '.$tmp2[0] : '' );
	}

    public static function isCLI() {
        return (php_sapi_name() === 'cli');
    }

    public static function generateNewPass( $l, $alpaOnly = false ) {
        $a = $alpaOnly ? "ABCDEFGHIJKLMNOPQRSTUWXYZ" : "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
        $p = array();
        $b = strlen($a) - 1;
        for ($i = 0; $i < $l; $i++) {
            $n = rand(0, $b);
            $p[] = $a[$n];
        }
        return implode($p);
    }

	public static function ajaxReturnHtml($table) {
		die('{"html":"'.rawurlencode(is_array($table) ? implode($table) : $table).'"}');
	}

	public static function dateOutput( $timestamp, $full = false ) {
		$tmp = explode(" ",$timestamp);
		$d = implode("/",array_reverse(explode("-",$tmp[0])));
		$t = strtotime($timestamp);
		if( $full && date('G',$t) > 0 )
			$d .= " ".date('G\hi',$t);
		return $d;
	}
    public static function dateInput( $date ) {
        $tmp = explode(" ",$date);
        $tmp = explode("/",$tmp[0]);
        if( count($tmp) != 3 ) {
            return false;
        }
        return implode("-",array_reverse($tmp));
    }

	public static function getMois( $nb ) {
		$mois = ["","Janvier", "Février", "Mars", "Avril", "Mai","Juin","Juillet","Août","Septembre","Octobre","Novembre","Décembre"];
		return $mois[$nb];
	}


    public static function checkTel($phone) {
        $filtered_phone_number = filter_var($phone, FILTER_SANITIZE_NUMBER_INT);
        $phone_to_check = str_replace("-", "", $filtered_phone_number);
        if (strlen($phone_to_check) < 10 || strlen($phone_to_check) > 14) {
            return false;
        } else {
            return true;
        }
    }

    # File get contents pour les gros fichiers, utilisation :
    public static function file_get_contents_chunked($filename, $retbytes = TRUE) {
        define('CHUNK_SIZE', 1024*1024);
        $buffer = '';
        $cnt    = 0;
        $handle = fopen($filename, 'rb');

        if ($handle === false) {
            return false;
        }

        while (!feof($handle)) {
            $buffer = fread($handle, CHUNK_SIZE);
            echo $buffer;
            ob_flush();
            flush();

            if ($retbytes) {
                $cnt += strlen($buffer);
            }
        }

        $status = fclose($handle);

        if ($retbytes && $status) {
            return $cnt;
        }

        return $status;
    }

    public static function conectedRestriction() {
        echo '
            <div id="restrictedArea">
                <h3>Cette section privée</h3>
            </div>
        ';
        return false;
    }

    public static function error404() {
        echo '
            <div id="restrictedArea">
                <h3>Cette page n\'existe pas !</h3>
                <br/>
                <p class="text-center">
                    <a href="'.URL.'">Retour</a><br/>
                </p>
            </div>
        ';
        return false;
    }

		public static function restricted() {
			echo '
				<div class="text-center">
					<div id="restrictedArea2">
							<i class="fas fa-shield-alt"></i>
							<h3>Accès non autorisé</h3>
							<h4>Votre profil de sécurité ne vous permet pas d\'accéder à cette page</h4>
							<br/>
							<p class="text-center">
									<a href="'.URL.'">Retour</a><br/>
							</p>
					</div>
				</div>
			';
			return false;
		}



    public static function webname($url) {
       $url = preg_replace('~[^\\pL0-9_!]+~u', '-', $url);
       $url = trim($url, "-");
       //$url = iconv("utf-8", "us-ascii//TRANSLIT", $url);
       $url = strtolower($url);
       $url = preg_replace('~[^-a-z0-9_!]+~', '', $url);
       return $url;
    }

	public static function printInput( $libelle, $name, $value = '', $disabled = false ) {
		return '
		<div class="form-group">
		<label>'.$libelle.'</label>
		<input type="text" class="form-control" name="'.$name.'" value="'.$value.'" '.($disabled?'disabled':'').'>
		</div>
		';
	}
	public static function printSelect( $libelle, $name, $value = '', $disabled = false, $opt = [] ) {
		$tmp = [];
		$tmp[] = '
		<div class="form-group">
		<label>'.$libelle.'</label>
		<select class="form-control" name="'.$name.'" '.($disabled?'disabled':'').'>';
		foreach( $opt as $k=>$e ) {
			$s = ( $k == $value ? 'selected' : '' );
			$tmp[]= '<option value="'.$k.'" '.$s.'>'.$e.'</option>';
		}
		$tmp[] = '</select></div>';
		return implode($tmp);
	}	

		public static function colSplit( $html, $cols ) {
			$content = [];

			$html = array_chunk($html,$cols);
			foreach( $html as $splited ) {
				$content[] = '<div class="row">';
				foreach( $splited as $elem ) {
					$content[] = '<div class="col">'.$elem.'</div>';
				}
				$content[] = '</div>';
			}

			return implode($content);
		}

		public static function getParamId() {
			global $params;
			return intval(array_pop($params));
		}





    function br2nl($string){
       return eregi_replace('<br[[:space:]]*/?'.'[[:space:]]*>',chr(13).chr(10),$string);
    }



		public static function readFile( $file, $clean = true ) {
			if( file_exists($file) && is_readable($file) ) return $clean ? self::cleanFile(file($file)) : file($file);
			return false;
		}
		public static function cleanFile( $file ) {
			foreach( $file as $k=>$e ) {
				$file[$k] = (trim($e));
			}
			return $file;
		}

		public static function dumpObj($obj,$pre=true) {
			ob_start();
			if($pre) echo '<pre>';
			print_r($obj);
			if($pre) echo '</pre>';
			$content = ob_get_contents();
			ob_end_clean();
			return $content;
		}

		public static function splitCols($file, $delimiter = ":") {
			$return = [];
			foreach( $file as $k=>$e ) {
				$tmp = [];
				$split = explode(";",$e);
				foreach( $split as $el ) {
						if( !strpos($el,$delimiter) === -1 ) {
							$tmp[] = $e;
						}
						else {
							$subsplit = explode($delimiter,$el);
							foreach($subsplit as $ss ) $tmp[] = trim($ss);
						}
				}
				$return[] = $tmp;
			}
			return $return;
		}



		public static function getGencodeImg( $gencode ) {
			ob_start();
			$generator = new barcode_generator();
			echo $generator->render_svg("ean-13", $gencode,["w"=>"300px","h"=>"100px"]);
			$content = ob_get_contents();
			ob_end_clean();
			return $content;
		}

		public static function getDir( $path ) {
			return array_diff(scandir($path),[".",".."]);
		}

		public static function getUploadedFile( $name, $ext = [], $sizeMax = null ) {
			global $db;

			if( !isset($_FILES[$name]) ) return false;
			$file = $_FILES[$name];
			$filename = $db->escape($file['name']);
			$size = $db->escape($file['size']);
			$type = $db->escape($file['type']);

			if( !empty($ext) ) {
				$tmp = explode(".",$filename);
				$e = strtolower(array_pop($tmp));
				if( !in_array($e,$ext) ) {
					error_log("L'extension du fichier n'est pas autorisee : $filename");
					return false;
				}
			}

			if( $sizeMax > 0 && $size > $sizeMax ) {
				error_log("Le poids du fichier depasse la limite : $filename ($size bytes)");
				return false;
			}

			$y = date("Y");
			$m = date("m");
			$d = date("d");
			$p = "upload/$y/$m/$d/";
			$path = self::createUploadDir($p);

			if( !$path ) {
				error_log("Imposslbe de creer le repertoire de depose : $p");
				return false;
			}

			$m = @move_uploaded_file($file['tmp_name'],FILES.$p.$filename);
			if( !file_exists(FILES.$p.$filename) ) {
				error_log("Impossible de deplacer le fichier : $filename");
				return false;
			}
			$q = "INSERT INTO uploaded_files
					(filename,type,size,fullpath)
				VALUES
					(
						'".$filename."',
						'".$type."',
						'".$size."',
						'".$p."'
					)";

			$db->execute($q);
			return $db->lastId();
		}

		public static function createUploadDir( $path ) {
			if( is_dir(FILES.$path) ) return true;
			@mkdir(FILES.$path, 0777, true);
		 	return is_dir(FILES.$path);
		}
		

		public static function getUpload( $id ) {
			global $db;
			$db->execute("SELECT * FROM uploaded_files WHERE id = ".intval($id));
			$file = $db->assoc();
			$file['link'] = URL_APP_ROOT.'datas/'.$file['fullpath'].'/'.$file['filename'];
			$file['root_path'] = FILES.$file['fullpath'].$file['filename'];
			return $file;
		}

		public static function getPublicFileLink( $id ) {
			global $db;
			$db->execute("SELECT * FROM uploaded_files WHERE id = ".intval($id));
			if( !$db->num() ) return "#";
			$file = $db->assoc();
			return "Media/".md5($file['filename']).FILE_HASH_SEPARATOR.intval($id);
		}




		public static function alert( $title, $msg, $dismissible = true ) {
			$d = ( $dismissible ? '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>' : '' );
			return '<div class="alert alert-dismissible">'.$d.'<h4><i class="icon fa fa-info"></i> '.$title.'</h4>'.$msg.'</div>';
		}


		public static function readableSize($bytes, $dec = 2) {
		    $size   = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
		    $factor = floor((strlen($bytes) - 1) / 3);
		    return sprintf("%.{$dec}f", $bytes / pow(1024, $factor)) . ' ' . @$size[$factor];
		}

		public static function dateFrom($time) {
				if( $time == "" ) return '<em class="text-secondary">'.l('jamais').'</em>';
				if( strpos($time,"-") > -1 )
					$time = strtotime($time);

		    $time = time() - $time;
		    $time = ($time<1)? 1 : $time;
		    $tokens = array (
		        31536000 => l('date-an'),
		        2592000 => l('date-mois'),
		        604800 => l('date-semaine'),
		        86400 => l('date-jour'),
		        3600 => l('date-heure'),
		        60 => l('date-minute'),
		        1 => l('date-seconde')
		    );

		    foreach ($tokens as $unit => $text) {
		        if ($time < $unit) continue;
		        $numberOfUnits = floor($time / $unit);
		        return $numberOfUnits.' '.$text.(($numberOfUnits>1&&$text!= l('date-mois') )?'s':'');
		    }

		}
		public static function secondsToTime( $seconds ) {
			$m= floor($seconds/60);
			$h= floor($m/60);
			$m= floor($m%60);
			
			return $h."h ".str_pad($m,2,"0",STR_PAD_LEFT)."m ".str_pad(fmod($seconds,60),2,"0",STR_PAD_LEFT)."s";
		}


		public static function logApk( $message, $error = 0, $id = 0 ) {
			global $db;
			if( ENV == "DEV" ) error_log($message);
			$id_user = ( defined('API_ID_USER') ? API_ID_USER : $id );
			$q = "
				INSERT INTO logs_apk
				(id_user,message, error)
				VALUES
				('".$db->escape($id_user)."','".$db->escape($message)."', $error )
			";
			//error_log($q);
			$db->execute($q);
			return;
		}

		public static function getLastLog() {
			$id = intval($_POST['id']);
			global $db;
			$db->execute("SELECT * FROM logs_apk WHERE id > $id AND deleted = 0");
			if( !$db->num() ) die('{}');
			$datas = $db->getArray();
			$tmp = [];
			foreach( $datas as $k=> $e ) {
				$tmp[] = [
					"id" => $k,
					"e" => $e['error'],
					"d" => core::dateOutput($e['date_creation'],true),
					"u" => user::getNameFromId($e['id_user']),
					"l" => $e['message']
				];
			}
			core::ajax(["logs" => $tmp]);
		}


		public static function countFrenchBusinessDays($year, $month, $untilDay = 0) {
			$untilDay = intval($untilDay);
			$weekdays_off = [6, 7];
			$off = [];

			global $db;
			$m = $month;
			if( $m < 10 ) $m = str_pad($m,2,"0",STR_PAD_LEFT);
			$db->execute("SELECT date_off FROM jours_off WHERE date_off LIKE '$year-$m-%'");
			$today = date('j');
			while( $r = $db->assoc() ) {
				$o = strtotime($r['date_off']??"");
				$dj = date('j', $o);
				if( $untilDay == 0 || ($untilDay > 0 && intval($dj) < $untilDay) )
					$off[] = $dj;
			}


			$start = new DateTimeImmutable("{$year}-{$month}-01");
			$end   = $start->modify('first day of next month');
			$days  = new DatePeriod($start, new DateInterval('P1D'), $end);	
			$count = 0; 
			$dayz = [];
			foreach ($days as $dt) {
				$n = $dt->format('j');

				if(  $untilDay > 0 && $n  >= $untilDay ) 
					break;

				if (in_array($dt->format('N'), $weekdays_off)) 
					$off[] = $n;

				if( !in_array($n,$off) ) { 
					$count++;
					$dayz[] = $n;
				}
			}
			
			// echo '#####';
			// echo implode(" - ",$dayz);
			// echo '#####';

			return  $untilDay ? $count : $start->format('t') - count(array_unique($off));
		}






	/** Crypto **/



    public $skey = SALT;

    public  function safe_b64encode($string) {

        $data = base64_encode($string);
        $data = str_replace(array('+','/','='),array('-','_',''),$data);
        return $data;
    }

    public function safe_b64decode($string) {
        $data = str_replace(array('-','_'),array('+','/'),$string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        return base64_decode($data);
    }

    public  function encrypt($value){

        if(!$value){return false;}
        $text = $value;
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $crypttext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, SALT, $text, MCRYPT_MODE_ECB, $iv);
        return trim(self::safe_b64encode($crypttext));
    }

    public function decrypt($value){

        if(!$value){return false;}
        $crypttext = self::safe_b64decode($value);
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $decrypttext = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, SALT, $crypttext, MCRYPT_MODE_ECB, $iv);
        return trim($decrypttext);
    }


	public static function photoAppnameToTime( $appname = "" ) {
		$time = $appname;
		$tmp = explode("_",$appname);
		if( count($tmp) >= 5 ) {
			//return $tmp[4];
			$time = date("d/m/Y - H\h i\m s",strtotime($tmp[4]));
		}
		return $time;
	}









		public static function includeEditor() {
			echo '
				<script src="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.25.1/trumbowyg.min.js" integrity="sha512-t4CFex/T+ioTF5y0QZnCY9r5fkE8bMf9uoNH2HNSwsiTaMQMO0C9KbKPMvwWNdVaEO51nDL3pAzg4ydjWXaqbg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
				<script src="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.25.1/langs/fr.min.js" integrity="sha512-UcIsJdBCuvuHt4LR6FoShixLYQXvVPTFrHBI+cXa1VNBJ7E+dRLb42xyLzfR6mKiQ7Z/YdRMFjQSDzvYYCr3vw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
				<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.25.1/ui/trumbowyg.min.css" integrity="sha512-nwpMzLYxfwDnu68Rt9PqLqgVtHkIJxEPrlu3PfTfLQKVgBAlTKDmim1JvCGNyNRtyvCx1nNIVBfYm8UZotWd4Q==" crossorigin="anonymous" referrerpolicy="no-referrer" />
			';
		}

		public static function isOkMountPoints() {
			$points = [ DIR_CMD, REF_ARTICLE_PATH, REF_ARBO_PATH ];
			$mounted = true;
			foreach( $points as $p ) {
				if( !is_dir($p) || !is_readable($p) )
					$mounted = false;
			}
			return $mounted;
		}
		public static function checkMountPoints() {
			if( !self::isOkMountPoints() ) {
				echo '
					<div id="alert-baner">
						<i class="fas fa-exclamation-triangle"></i> Un ou plusieurs points de montages sont HS !
						<em>Sans les points de montage, impossible de générer une commande ou mettre à jour le CRM.</em>
					</div>
				';
			}
		}

		public static function readLog() {
			$name = $_POST['name'];
			$path = FILES."debug/".date('Y')."/".date("m")."/".date('d')."/";
			$f = $path.$name;
			if( !file_exists($f) )
				core::ajaxError("Log introuvable");
			$content = file_get_contents($f);

			$obj = json_decode($content,true);
			if( !is_array($obj) ) core::aError("Fichier tronqué, veuillez contacter un administrateur");
			$html = ['<h1 class="text-center"><i class="far fa-file-code"></i> '.$name.'</h1>'];
			foreach( $obj as $id_commande => $c ) {
				if( !isset($c['no_cmd']) || $c['no_cmd'] == 1 ) continue;
				ob_start();
				include(PAGES.'admin/partial_debug_commande.php');
				$html[] = ob_get_contents();
				ob_end_clean();
			}

			foreach( $obj as $id_commande => $c ) {
				if( !isset($c['no_visit']) || $c['no_visit'] == 1 ) continue;
				ob_start();
				$v = $c['visite'];
				include(PAGES.'admin/partial_debug_visite.php');
				$html[] = ob_get_contents();
				ob_end_clean();
			}

			$html[] = '<div class="card "><div class="card-header text-white bg-danger"><i class="fas fa-voicemail"></i> Logs de l\'application</div>';
			$html[] = '<div class="card-body card-logs" style="font-size:11px;">';
			if( isset($obj['logs']) ) {
				foreach($obj['logs'] as $line )
					$html[] = '<p>'.$line.'</p>';
			}
			$html[] = '</div></div>';

			$html[] = '<div class="card "><div class="card-header text-white bg-danger"><i class="fas fa-flask"></i> Debug avancé</div>';
			$html[] = '<div class="card-body jsonElement" style="font-size:11px;"></div></div>';

			die('{ "log" : "'.rawurlencode($content).'", "html" : "'.rawurlencode(implode($html)).'" }');
		}

		public static function getReason( $reason ) {
			if( intval($reason) != $reason ) return $reason;
			global $db, $memoryReason;
			if( !isset($memoryReason[$reason]) ) {
				$db->execute("SELECT * FROM apk_select_options WHERE id = ".intval($reason));
				if( !$db->num() ) return $reason;
				$memoryReason[$reason] = $db->assoc();
			}		
			return l('OPT|'.$memoryReason[$reason]['type']."|".intval($reason));
		}
		public static function getListReason( $type ) {
			global $db;
			$list = [];
			$db->execute("SELECT id,libelle FROM apk_select_options WHERE type = '".$db->escape($type)."' ");
			while( $r = $db->assoc() ) $list[$r['id']] = $r['libelle'];
			return $list;
		}



}
