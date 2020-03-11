<?php
namespace KFE;

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);

use Contentomat\Mail;
use Contentomat\ApplicationController;


class SellersMailController extends ApplicationController {


	/**
	 * @var Contentomat\Mail
	 */
	protected $Mail;


	public function init() {
		$this->Mail = new Mail();
		$this->templatesPath = PATHTOWEBROOT . "templates/sellers/";
	}

	public function actionDefault() {

		$sellers = $this->Seller->findAll();

		$this->content = $this->parser->parseTemplate($this->templatesPath . "sellers_mail.tpl");
	}
}

$ctl = new SellersMailController();
$content = $ctl->work();
?>
