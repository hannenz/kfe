<?php
namespace KFE;
use Contentomat\Controller;
use KFE\Seller;
use KFE\Market;
use KFE\Circular;
use \Exception;


if (!class_exists('\KFE\CircularsController')) {
class CircularsController extends Controller {

	/**
	 * @var \KFE\Circular
	 */
	protected $Circular;

	/**
	 * @var \KFE\Seller
	 */
	protected $Seller;

	/**
	 * @var \KFE\Market
	 */
	protected $Market;

	/**
	 * @var string
	 * Original CMT action (we are interested in "new" or "edit")
	 */
	protected $cmt_action;

	/**
	 * @var Array
	 * The record's data to be saved;
	 */
	protected $data;

	/**
	 * @var Array
	 */
	protected $cmt_functions;



	public function __construct($cmt_action, $cmtTableDataRaw, $cmt_functions) {

		$this->cmt_action = $cmt_action;
		$this->cmt_functions = $cmt_functions;
		$this->data = $cmtTableDataRaw;
		parent::__construct();
	}

	public function init() {
		$this->Market = new Market();
		$this->Seller = new Seller();
		$this->Circular = new Circular();
		if (!empty($this->getvars['id'])) {
			$this->circularId = $this->getvars['id'];
		}
	}

	public function initActions($action = '') {
		if (!empty($this->getvars['cmt_action'])) {
			$this->action = $this->getvars['cmt_action'];
			return;
		}

		if ($this->cmt_action == 'new') {
			$this->action = 'beforeSave';
			return;
		}
		else if (!empty($this->cmt_functions)) {
			$this->action = 'onshowEntry';
			return;
		}
		// parent::initActions($action);
	}


	public function actionSend() {
		try {
			$this->Circular->send($this->circularId);
		}
		catch (Exception $e) {
			die("Error: ".$e->getMessage());
		}
	}


	public function actionSendLoop() {
		try {
			$this->Circular->sendLoop();
		}
		catch (Exception $e) {
			die("Error: ".$e->getMessage());
		}
	}


	/**
	 * BeforeSave: Get recipients from market sellers
	 */
	public function actionBeforeSave() {

		$sellers = $this->Seller->findByMarket($this->data['circular_market_id']);
		$recipients = [];

		foreach ($sellers as $seller) {
			$recipients[] = [
				'active' => true,
				'seller_id' => $seller['id'],
				'email' => $seller['seller_email'],
				'firstname' => $seller['seller_firstname'],
				'lastname' => $seller['seller_lastname'],
				'seller_nr' => $seller['seller_nr']
			];
		}
		$this->data['circular_recipients'] = json_encode($recipients);
	}


	/**
	 * undocumented function
	 *
	 * @return void
	 */
	public function actionOnshowEntry() {
		if ($this->data['circular_status'] == 'draft') {
			$button = sprintf('<a class="cmtIcon circularSendBtn" href="%s&launch=%u&cmt_action=send&id=%u" style="background-image:url(/admin/templates/default/administration/img/icons/email_xlarge.png); background-size: contain;" title="Rundmail versenden" data-id="%u"></a>', 
				SELFURL,
				APPID,
				$this->data['id'],
				$this->data['id']
			);
			$this->cmt_functions['send'] = $button;
		}
		else {
			unset($this->cmt_functions['edit']);
		}
	}
	


	/**
	 * Getter for data
	 */
	public function getData() {
		return $this->data;
	}


	/**
	 * Getter for cmt_functions
	 *
	 * @return Arrat
	 */
	public function getCmtFunctions() {
	    return $this->cmt_functions;
	}
}
}

$ctl = new CircularsController($cmt_action, $cmtTableDataRaw, $cmt_functions); 
$ctl->work();
$cmtTableDataRaw = $ctl->getData();
$cmt_functions = $ctl->getCmtFunctions();
?>
