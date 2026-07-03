<?php
function autoloader( $classe ) {
    $directories = array(
        CLASS_DIR
    );
    foreach($directories as $directory) {
        /* Si a la racine */
        if( file_exists($directory . $classe . '.class.php')  ) {
            require_once($directory . $classe . '.class.php');
            return;
        }
        elseif ( file_exists($directory . $classe . '.lib.php')  ) {
            require_once($directory . $classe . '.lib.php');
            return;
        }
        /* Si sous dossier */
        $scan = array_diff(scandir($directory),array(".",".."));
        if( !empty($scan) ) {
            foreach ($scan as $k => $e) {
                $path = $directory . "/" . $e;
                if (is_dir($path)) {
                    if (file_exists($path . "/" . $classe . '.class.php')) {
                        require_once($path . "/" . $classe . '.class.php');
                        return;
                    } elseif (file_exists($path . "/" . $classe . '.lib.php')) {
                        require_once($path . "/" . $classe . '.lib.php');
                        return;
                    }
                }
            }
        }
    }
}
