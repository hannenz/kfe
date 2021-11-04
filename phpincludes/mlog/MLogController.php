<?php
namespace KFE;

use Contentomat\PsrAutoloader;
use Contentomat\MLog\Posts;
use Contentomat\Controller;

class MLogController extends Controller {

	/**
	 * @var \Contentomat\MLog\Posts;
	 */
	protected $Post;

	public function init() {
		$this->Post = new Posts();
		$this->templatesPath = PATHTOWEBROOT . 'templates/mlog/';
	}

	public function actionDefault() {
		$posts = $this->Post->search([]);
		$this->parser->setParserVar('posts', $posts);
		$this->content = $this->parser->parseTemplate($this->templatesPath . 'index.tpl');
	}
}

$autoload = new PsrAutoloader();
$autoload->addNamespace('Contentomat', INCLUDEPATHTOADMIN . 'classes');
$autoload->addNamespace('Contentomat\MLog', INCLUDEPATHTOADMIN . 'classes/app_mlog');
$ctl = new MLogController();
$content = $ctl->work();
