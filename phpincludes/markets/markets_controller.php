<?php
namespace KFE;

use KFE\Market;
use Contentomat\Controller;
use Contentomat\PsrAutoloader;

ini_set('display_errors', true);
error_reporting(E_ALL & ~ E_NOTICE & ~E_DEPRECATED);

class MarketsController extends Controller {


	/**
	 * @var \KFE\Market
	 */
	protected $Market;

	protected $registrationPageId = 7;


	public function init() {
		$this->Market = new Market();
		$this->Market->setRegistrationPageId($this->registrationPageId);
		$this->templatesPath = $this->templatesPath . 'markets/';
	}


	/**
	 * Init actions
	 *
	 * @return void
	 */
	public function initActions($action = '') {
		parent::initActions($action);

		$this->action = 'upcoming';

	}

	public function actionUpcoming() {
		$markets = $this->Market->getUpcoming();
		$this->parser->setParserVar('markets', $markets);
		$this->content = $this->parser->parseTemplate($this->templatesPath . 'upcoming.tpl');
	}
	
}

$autoLoad = new PsrAutoloader();
$autoLoad->addNamespace('KFE', PATHTOWEBROOT . 'phpincludes/classes');
$autoLoad->addNamespace('Contentomat', INCLUDEPATHTOADMIN , 'classes');
$ctl = new MarketsController();
$content = $ctl->work();
?>
