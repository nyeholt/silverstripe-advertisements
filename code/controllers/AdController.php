<?php

/**
 * Description of AdController
 *
 * @author Marcus Nyeholt <marcus@silverstripe.com.au>
 * @license BSD http://silverstripe.org/BSD-license
 */
class AdController extends Controller {
	public function imp() {
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
}
