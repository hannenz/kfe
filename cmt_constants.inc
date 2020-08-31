<?php
/**
 * Conten-o-mat Einstellungen/Konstanten
 */

// Pfad, in dem sich der ROOT der Homepage befindet (z.B. "/" bei www.meinedomain.de (Standard) oder 
// "myweb/" bei www.meinedomain/myweb/), immer mit führendem und ggf. abschließendem '/' angeben!
define ('WEBROOT', '/');

// Pfad/Ordner zum Admin-Bereich: Standard 'admin/'
define ('ADMINPATH', 'admin/');

// Zentraler Ordner für Downloads: Standard ist 'downloads/'
define ('DOWNLOADPATH', 'downloads/');

// Standardsprache für mehrsprachige Websites
define ('DEFAULTLANGUAGE', 'de');

// veraltet: Zeichensatz, der für die Seite verwendet wird
define ('CHARSET', 'utf-8');

// Zeichensatz, der verwendet wird, falls kein anderer Zeichensatz angegeben wird
define ('CMT_DEFAULTCHARSET', 'utf-8');

// Name des Webs
define ('WEBNAME', 'Kinderflohmarkt Erbach');

define ('CMT_ERRORREPORTING_LEVEL', 'error');

// Datenbankfehler Logging
define ('CMT_DBERRORLOG', '1');

// Sollen Cookies benutzt werden?
define('CMT_USECOOKIES', '1');

// Cookies nutzen, auch wenn keine Cookies aktiviert sind?
define('CMT_FORCECOOKIES', '1');

// Apache Mod-Rewrite nutzen?
define('CMT_MODREWRITE', '1');

// Standardzeitzone für PHP-Funktion 'date'
define('CMT_DEFAULTTIMEZONE', 'Europe/Berlin');


/* [[UPDATES HERE]] */

// URL des Skriptes
define ("SELF", basename($_SERVER['PHP_SELF']));

// aktueller Pfad minus WEBROOT ergibt den aktuellen ROOT
$actPath = preg_replace('/^'.preg_quote(WEBROOT, '/').'/', '', $_SERVER['PHP_SELF']);

// ROOT berechnen
$depth = substr_count ($actPath, '/');
define ('ROOT', str_pad('', $depth*3, '../'));

// PATHTOADMIN berechnen
define ('PATHTOADMIN', str_replace('//', '/', ROOT.ADMINPATH));

// PATHTODOWNLOADS berechnen
define ('PATHTODOWNLOADS', ROOT.DOWNLOADPATH);

// PATHTOWEBROOT berechnen
define ('PATHTOWEBROOT', ROOT);

// Include-Pfade für PHP-Skripte (im Idealfall immer absolut angeben!):
// Include-Pfad zum Webroot
define ('INCLUDEPATH', $_SERVER['DOCUMENT_ROOT'] .'/');

// Include-Pfad zum Adminbereich
define ('INCLUDEPATHTOADMIN', INCLUDEPATH.ADMINPATH);

define ('PATHTOTMP', INCLUDEPATH . 'tmp' . DIRECTORY_SEPARATOR);


@require_once(PATHTOADMIN . 'includes' . DIRECTORY_SEPARATOR . 'logger.inc');
// Log Level (see admin/classes/class_logger.php for available values and their meanings)
// Only log messages with this or higher log level
define ('CMT_LOG_LEVEL', LOG_LEVEL_INFO);

// Log Target(s)
define ('CMT_LOG_TARGET', LOG_TARGET_FILE);

// Path to Log File, if log target LOG_TARGET_FILE is selected (must be writable by web server user)
define ('CMT_LOG_FILE', PATHTOTMP . 'logs' . DIRECTORY_SEPARATOR . 'contentomat.log');


if ($_SERVER['SERVER_NAME'] == 'www.kinderflohmarkt-erbach.de') {
	define('ISPRODUCTION', true);
}
else {
	define('ISPRODUCTION', false);
}
?>
