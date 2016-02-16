<?php

/**
 * Description of AdController
 *
 * @author Marcus Nyeholt <marcus@silverstripe.com.au>
 * @license BSD http://silverstripe.org/BSD-license
 */
class AdController extends Controller {

	public static $record_impressions = true;

	private static $allowed_actions = array(
		'imp',
		'go',
		'clk',
	);

	public function imp() {
		if (!self::$record_impressions) {
			return;
		}
		if ($this->request->requestVar('ids')) {
			$ids = explode(',', $this->request->requestVar('ids'));
			foreach ($ids as $id) {
				$id = (int) $id;
				if ($id) {
					$imp = new AdImpression;
					$imp->AdID = $id;
					$imp->write();
				}
			}
		}
	}

	public function clk() {
		if ($this->request->requestVar('id')) {
			$id = (int) $this->request->requestVar('id');
			if ($id) {
				$imp = new AdClick;
				$imp->AdID = $id;
				$imp->write();
			}
		}
	}

	public function go() {
		$id = (int) $this->request->param('ID');

		if ($id) {
			$ad = DataObject::get_by_id('Advertisement', $id);
			if ($ad && $ad->exists()) {
				$imp = new AdClick;
				$imp->AdID = $id;
				$imp->write();

				$this->redirect($ad->getTarget());
				return;
			}
		}
	}
}
