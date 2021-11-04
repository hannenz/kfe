<?php
namespace KFE;

use Contentomat\MLog\Posts;
use Contentomat\Controller;

class MLogController extends Controller {

	/**
	 * @var \Contentomat\MLog\Posts;
	 */
	protected $Post;

	public function init() {
		$this->cmt->addAutoloadNamespace('Contentomat\MLog\\', INCLUDEPATHTOADMIN.'classes/app_mlog');
		$this->Post = new Posts();
		$this->templatesPath = PATHTOWEBROOT . 'templates/mlog/';
	}

	public function actionDefault() {
		$posts = $this->Post->search([]);
		$this->parser->setParserVar('posts', $posts);
		$this->content = $this->parser->parseTemplate($this->templatesPath . 'index.tpl');
	}
}

$ctl = new MLogController();
$content = $ctl->work();
