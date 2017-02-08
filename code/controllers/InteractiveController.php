<?php

/**
 * @author Marcus Nyeholt <marcus@silverstripe.com.au>
 * @license BSD http://silverstripe.org/BSD-license
 */
class InteractiveController extends Controller {

	private static $record_impressions = true;

    private static $allowed_events = array(
        'clk'   => 'Click',
        'imp'   => 'View',
        'int'   => 'Interact',
        'cpl'   => 'Complete',
    );

	private static $allowed_actions = array(
        'trk',
		'imp',
		'go',
		'clk',
	);

    public function trk() {
        $ids = $this->request->requestVar('ids');
        $sig = $this->request->requestVar('sig');
        $event = $this->request->requestVar('evt');
        $allowed = self::config()->allowed_events;
        $trackAs = isset($allowed[$event]) ? $allowed[$event] : null;
        if ($trackAs && $ids) {
			$ids = explode(',', $this->request->requestVar('ids'));
			foreach ($ids as $id) {
				$id = (int) $id;
				if ($id) {
					$imp = InteractiveImpression::create(['Interaction' => $trackAs, 'Signature' => $sig]);
					$imp->InteractiveID = $id;
					$imp->write();
				}
			}
            return 1;
		}
        return 0;
    }

	public function imp() {
		if (!self::config()->record_impressions) {
			return;
		}
		if ($this->request->requestVar('ids')) {
			$ids = explode(',', $this->request->requestVar('ids'));
			foreach ($ids as $id) {
				$id = (int) $id;
				if ($id) {
					$imp = new InteractiveImpression;
					$imp->InteractiveID = $id;
					$imp->write();
				}
			}
		}
	}

	public function clk() {
		if ($this->request->requestVar('id')) {
			$id = (int) $this->request->requestVar('id');
			if ($id) {
				$imp = InteractiveImpression::create(['Interaction' => 'Click']);
				$imp->InteractiveID = $id;
				$imp->write();
			}
		}
	}

	public function go() {
		$id = (int) $this->request->param('ID');

		if ($id) {
			$ad = DataObject::get_by_id('Interactive', $id);
			if ($ad && $ad->exists()) {
				$imp = InteractiveImpression::create(['Interaction' => 'Click']);
				$imp->InteractiveID = $id;
				$imp->write();

				$this->redirect($ad->getTarget());
				return;
			}
		}
	}
}
