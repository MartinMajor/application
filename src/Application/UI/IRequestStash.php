<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Application;

use Nette;


/**
 * Interface for storing and restoring requests.
 *
 * @author     Martin Major
 */
interface IRequestStash
{

	/**
	 * Stores current request and returns key.
	 * @param  Request application request
	 * @param  string expiration time
	 * @return string key
	 */
	public function storeRequest(Request $request, $expiration = '+ 10 minutes');


	/**
	 * Restores request by its key.
	 * @param  string key
	 * @param  UI\Presenter
	 * @throws AbortException
	 * @return void
	 */
	public function restoreRequest($key, UI\Presenter $presenter);


	/**
	 * Returns stored request.
	 * @param  \Nette\Http\Request
	 * @return Request|NULL
	 */
	public function getRequest(Nette\Http\Request $httpRequest);

}
