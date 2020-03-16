<?php
/**
 * content-o-mat: Website Seitenausgabe
 * 
 * Datei gibt angeforderte Seite einer Website zurück.
 * 
 * @author J.Hahn <info@content-o-mat.de>, J.Braun <info@content-o-mat.de>
 * @version 2015-12-08
 * 
 */

use \Contentomat\PsrAutoloader;
use \Contentomat\Contentomat;
use \Contentomat\Parser;
use \Contentomat\AppParser;
use \Contentomat\CmtPage;
use \Contentomat\SessionHandler;
use \Contentomat\DBCex;

ini_set('display_errors', true);

	/*
	 * Do some loading work
	 */
	require('cmt_constants.inc');
	require(PATHTOADMIN . 'classes/class_psrautoloader.inc');
	// Autolaoder for Composer packagse
	require(PATHTOADMIN . 'vendor/autoload.php');
	
	$autoloader = new PsrAutoloader();
	$autoloader->addNamespace('Contentomat', PATHTOWEBROOT . 'phpincludes/classes/');
	$autoloader->addNamespace('Contentomat', PATHTOADMIN . 'classes/');
	
	require (PATHTOADMIN.'cmt_functions_website.inc');

    // Session überprüfen
    $cmt = Contentomat::getContentomat();
	$cmt->setErrorReporting('error');
	$page = new cmtPage();
    $session = SessionHandler::getSession();
	$db = new DBCex();

	/* --------------------------------------------------
		Sichtbarkeit- und Domainüberprüfung
	   -------------------------------------------------- */
	// Seitendaten und Seiten-Quelltext auslesen
	// Zuerst getPageToDisplay() da dort auch $page->initPage() aufgerufen wird
	$pageSource = $page->getPageToDisplay();
	$pageData = $page->getPageData();	

	// werden Cookies verwendet?
	// checken, ob SID in der URL mitgegeben werden muss
	$cc = $session->checkSidCookie();
	if (!$cc && CMT_FORCECOOKIES != '1') {
		define ('ADDSID', 'sid='.SID);
		$addSid = '&amp;sid='.SID;
		$addSidJs = '&sid='.SID;
	} else {
		define ('ADDSID', '');
	}
	
	// vorherige Seite holen
	$refering_page = $session->GetSessionVar('cmt_refering_page');
	$current_page = $session->GetSessionVar('cmt_current_page');

	/*
	 * weitere Konstanten definieren
	 */
	
	/* 
	 * SELFURL: verwenden, wenn Mod Rewrite nicht aktiviert!
	 * SELFURL entspricht SELF, ggf mit Session-ID
	 */
	$pid = $page->getPageID();
	$lang = $page->getPageLang();
	
	$selfURL = SELF;
	if (CMT_FORCECOOKIES == '0') {
		$selfURL .= '?sid='.SID;
	}
	define('SELFURL', $selfURL);

	/* neu: PAGEURL und JSPAGEURL */
	// 2015-12-10: Was this ever used???
	if (CMT_USECOOKIES == '1') {
		$pageURL = $page->makePageFilePath().$page->makePageFileName();

		define('PAGEURL', $pageURL);
		// OUTDATED
		define('JSPAGEURL', SELF.'?pid='.$pid.'&lang='.$lang);
	} else {
		define('PAGEURL', SELF.'?pid='.$pid.'&amp;lang='.$lang.$addSid);
		// OUTDATED
		define('JSPAGEURL', SELF.'?pid='.$pid.'&lang='.$lang.$addSidJs);
	}

	define ('PAGEID', $pid);
	define ('PAGELANG', $lang);
	define ('PAGETITLE', $pageData['cmt_title']);
	define ('TEMPLATEID', intval($pageData['cmt_template']));
	// ??? define ('PAGEFILENAME', $pageFilename);
	define ('PARENTID', $pageData['cmt_parentid']);
	define ('CMT_MODE', 'view');
	define ('REQUEST_URI', $_GET['requestURI']);

	// TODO 2.0: Auf Globale verzichten, stattdessen $cmt->settings
	$pagesTable = $page->getPagesTable();
	$contentsTable = $page->getContentsTable();
	$linksTable = $page->getLinksTable();

	define ('CMT_PAGES', $pagesTable);
	define ('CMT_CONTENT', $contentsTable);
	define ('CMT_LINKS', $linksTable);
	
	// init parser class here, because some constants are needed for i18n!
	$parser = new AppParser();
	
	// Seitendaten an Parser übergeben
	$parser->setPageVars($pageData);
	$parser->setPageId($pageData['id']);
	$parser->setParentId($pageData['cmt_parentid']);
	$parser->setPageLanguage($lang);

	$parser->setPagesTable($pagesTable);
	$parser->setContentsTable($contentsTable);
	$parser->setLinksTable($linksTable);
	$parser->setPathToWebroot(PATHTOWEBROOT);

	// aktuelle Seiten-ID noch als Sessionvariable speichern
	if ($current_page != $pid) {
		$session->setSessionVar('cmt_refering_page', $current_page);
		$session->setSessionVar('cmt_current_page', $pid);
		$refering_page = $current_page;
	} else {
		$session->setSessionVar('cmt_current_page', $pid);
	}

	$session->saveSessionVars();
	
	$parser->pagevars['refering_page'] = $refering_page; 
	define ('REFERINGPAGE', $refering_page);

	ob_start_gzipped();
	echo $parser->parse(stripslashes($pageSource));
	
	// Zeitmessung Ende und Ausgabe
	// $time = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
	// echo "<p>Skriptausführung: {$time} Sek.";
	
	ob_end_flush();
?>
