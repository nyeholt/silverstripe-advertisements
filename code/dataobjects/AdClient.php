<?php

/**
 * Description of AdClient
 *
 * @author Marcus Nyeholt <marcus@silverstripe.com.au>
 * @license BSD http://silverstripe.org/BSD-license
 */
class AdClient extends DataObject {
	public static $db = array(
		'Title'				=> 'Varchar(128)',
		'ContactEmail'		=> 'Text',
	);
}
