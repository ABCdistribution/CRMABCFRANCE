<?php

global $memoryLang;
$memoryLang = [];

class lang {

    public static function detectLang() {
		$lang = DEFAULT_LANG;
        if( isset($_SESSION['lang']) || !isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ) return;

		$prefLocales = array_reduce(
		explode(
			',', 
			$_SERVER['HTTP_ACCEPT_LANGUAGE']), 
			function ($res, $el) { 
				list($l, $q) = array_merge(explode(';q=', $el), [1]); 
				$res[$l] = (float) $q; 
				return $res; 
			}, 
			[]
		);
		arsort($prefLocales);
		global $db;
		foreach( $prefLocales as $code => $value ) {
			if( trim($code) == "" ) continue;
			$db->execute("SELECT * FROM lang WHERE parser LIKE '%".e($code)."%'");
			if( $db->num() ) {
				$lang = $db->assoc()['code'];
				break;
			}
		}
		$_SESSION['lang'] = $lang;
		return;
	}

    public static function setLang( $die = true ) {
        global $db;
        $lang = e(trim(strtoupper($_POST['lang']??"")));
        $db->execute("SELECT * FROM lang WHERE code = '$lang' ");
        $_SESSION['lang'] = $db->num() ? $db->assoc()['code'] : DEFAULT_LANG;
        if( $die ) core::end();
        return ;
    }

    public static function getLang() {
        return $_SESSION['lang'] ?? DEFAULT_LANG;
    }

    public static function getTrad( $code, $lang = null ) {
        global $memoryLang,$db;
        if( $lang == null ) $lang = self::getLang();
        if( empty($memoryLang) ) {
            $db->execute("SELECT code,trad FROM lang_trad WHERE lang = '$lang'");
            while( $r = $db->assoc() ) $memoryLang[$r['code']] = $r['trad'];
        }
        return $memoryLang[$code] ?? "#missing[$lang|$code]#";
    }

    public static function saveTrad() {
        if( !securite::can(8) ) core::ajaxError("Accès interdit");
        $lang = trim(strtoupper($_POST['lang'] ?? DEFAULT_LANG));
        $code = e($_POST['code']??"");
        $val = nl2br($_POST['val']??"");
        global $db;
        $db->execute("UPDATE lang_trad SET trad = '".e($val)."' WHERE code = '$code' AND lang = '$lang' LIMIT 1");
        core::ajax(["trad" => $val]);
    }

    public static function createTrad() {
        if( !securite::can(8) ) core::ajaxError("Accès interdit");
        global $db;

        $trad_code = e($_POST['code']??"");
        if( $trad_code == "" ) core::ajaxError("Code manquant");
        $db->execute("SELECT id FROM lang_trad WHERE code = '$trad_code' ");
        if( $db->num() ) core::ajaxError("Code traduction déjà existant");

        $codes_langes = self::getCodesLangues();

        $default_trad = trim($_POST[DEFAULT_LANG]??"");
        if( $default_trad == "" ) core::ajaxError("La traduction dans la langue par défaut ne peut pas être vide");

        foreach( $codes_langes as $code_langue ) {
            $val = trim( isset($_POST[$code_langue]) && $_POST[$code_langue] != "" ? $_POST[$code_langue] : $default_trad);
            $db->execute("INSERT INTO lang_trad (lang,code,trad) VALUES ('$code_langue','$trad_code','".e($val)."')");
        }

        core::end();
    }

    public static function getLangues() {
        global $db;
        $db->execute("SELECT * FROM lang");
        return $db->getArray();
    }
    
    public static function getCodesLangues() {
        global $db;
        $db->execute("SELECT code FROM lang");
        $codes = [];
        while( $r = $db->assoc() ) $codes[] = $r['code'];
        return $codes;
    }

    public static function getTrads() {
        global $db;
        $trads = [];
        $db->execute("SELECT * FROM lang_trad");
        while( $r = $db->assoc() ) {
            if( !isset($trads[$r['code']])) $trads[$r['code']] = [];
            $trads[$r['code']][$r['lang']] = $r['trad'];
        }
        core::ajax($trads);
    }


    public static function jsTrad() {
        $script = ['<script>'];

        $script[] = "let _gT = [];";
        global $db;
        $lang = self::getLang();
        $db->execute("SELECT code,trad FROM lang_trad WHERE lang = '$lang' AND code LIKE 'js-%'");
        while( $r = $db->assoc() )
            $script[] = "_gT['".$r['code']."'] = '".rawurlencode($r['trad'])."';";

        $script[] = "const l = code => _gT[code] ? decodeURIComponent(_gT[code]) : '#missing[js|'+code+']#';";

        $script[] = '</script>';
        echo implode($script);
        return;
    }

    public static function getDeported() {
        global $db;
        $langues = [];
        $langues['liste'] = self::getLangues();
        
        global $db;
        $db->execute("SELECT * FROM lang_trad");
        $trads = [];
        while( $r = $db->assoc() ) {
            if( !isset($trads[$r['code']])) $trads[$r['code']] = [];
            $trads[$r['code']][$r['lang']] = $r['trad'];
        }
        $langues['trads'] = $trads;
        return $langues;
    }

}