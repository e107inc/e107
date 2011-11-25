<?php

class core_system_index_controller extends eController
{
	/**
	 * Temporary redirect to site Index
	 * XXX - move the index.php Front page detection to index/index/index, make index.php the entry point and _forward here
	 */
	public function actionIndex()
	{
		$this->_redirect('/', false, 301);
	}
}
