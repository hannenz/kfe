<?php
namespace KFE;

use KFE\Market;
use Contentomat\Controller;
use Contentomat\Contentomat;
use Contentomat\PsrAutoloader;


if (!class_exists('\KFE\MarketsController')) {
	class MarketsController extends Controller {


		/**
		 * @var \KFE\Market
		 */
		protected $Market;

		/**
		 * @var Integer
		 */
		protected $detailPageId = 15;

		/**
		 * @var Integer
		 */
		protected $registrationPageId = 7;



		/**
		 * Init
		 *
		 * @access public
		 * @return void
		 */
		public function init() {
			$this->Market = new Market();
			$this->Market->setDetailPageId($this->detailPageId);
			$this->Market->setRegistrationPageId($this->registrationPageId);
			$this->templatesPath = $this->templatesPath . 'markets/';
			$this->Cmt = Contentomat::getContentomat();
			$this->Session = $this->Cmt->getSession();

			if (isset($_REQUEST['bypass'])) {
				if ((int)$_REQUEST['bypass'] == 0) {
					$this->Session->deleteSessionVar('bypass');
				}
				else {
					$this->Session->setSessionVar('bypass', true);
				}
				$this->Session->saveSessionVars();
			}

			$contentData = $this->Cmt->getVar('cmtContentData');
			$this->params = array(
				$contentData['head1'],
				$contentData['head2'],
				$contentData['head3'],
				$contentData['head4'],
				$contentData['head5']
			);
		}



		/**
		 * Init actions
		 *
		 * @access public
		 * @return void
		 */
		public function initActions($action = '') {
			parent::initActions($action);

			if (!empty($this->params[0])) {
				$this->action = $this->params[0];
				return;
			}

			if (preg_match('/\:(\d+)\.html$/', $_SERVER['REQUEST_URI'], $match)) {
				$this->marketId = (int)$match[1];
				$this->action = 'detail';
				return;
			}

			if ($this->pageId == 18) {
				$this->action = 'archive';
				return;
			}

			$this->action = 'upcoming';
		}



		/**
		 * Show upcoming markets
		 *
		 * @access public
		 * @return void
		 */
		public function actionUpcoming() {
			$markets = $this->Market->getUpcoming();
			$this->parser->setParserVar('markets', $markets);
			$this->content = $this->parser->parseTemplate($this->templatesPath . 'upcoming.tpl');
		}



		/**
		 * Detail action
		 *
		 * @access public
		 * @return void
		 */
		public function actionDetail () {
			$market = $this->Market->findById($this->marketId, ['fetchMedia' => [
				'image' => 'marketImages',
				'document' => 'marketDocuments',
				'map' => 'marketMaps'
			]]);

			// echo '<pre>'; var_dump($market); echo '</pre>'; die();

			$this->parser->setMultipleParserVars($market);
			$this->content = $this->parser->parseTemplate($this->templatesPath . 'detail.tpl');
		}


		/**
		 * Output upcoming (hero) on home page
		 */
		public function actionNextUpcoming() {
			$market = $this->Market->getUpcoming(1);
			if (empty($market)) {
				return;
			}
			$market = array_shift($market);

			$availableNumbers = $this->Market->getAvailableNumbers($market['id']);
			$this->parser->setParserVar('availableNumbers', $availableNumbers);


			$this->parser->setMultipleParserVars($market);
			$this->content = $this->parser->parseTemplate($this->templatesPath . 'detail.tpl');
		}



		/**
		 * Archive action
		 */
		public function actionArchive() {
			$markets = $this->Market->findArchived();
			$this->parser->setParserVar('markets', $markets);
			$this->content = $this->parser->parseTemplate($this->templatesPath . 'archive.tpl');
		}
	}
}

$autoLoad = new PsrAutoloader();
$autoLoad->addNamespace('KFE', PATHTOWEBROOT . 'phpincludes/classes');
$autoLoad->addNamespace('Contentomat', INCLUDEPATHTOADMIN , 'classes');
$ctl = new MarketsController();
$content = $ctl->work();
?>
