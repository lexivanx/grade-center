<?php
class RedirectUtil {

    ### Redirects to a given path on the local server
    public static function redirectToPath($path) {
        if (headers_sent($file, $line)) {
            echo "Headers already sent in $file on line $line. Cannot redirect.";
            exit;
        } else {
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
                $protocol = 'https';
            } else {
                $protocol = 'http';
            }
            header("Location: $protocol://" . $_SERVER['HTTP_HOST'] . $path);
            exit;
        }
    }
    
}

?>