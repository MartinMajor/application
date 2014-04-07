<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Application;

use Nette,
	Nette\Application\UI\Presenter;


/**
 * Service for storing and restoring requests from session.
 *
 * @author     Martin Major
 */
class RequestStash extends Nette\Object implements IRequestStash
{
	/** URL parameter key */
	const REQUEST_KEY = '_rid';

	/** @var Nette\Http\Request */
	private $httpRequest;

	/** @var Nette\Http\Session */
	private $session;

	/** @var Nette\Security\User */
	private $user;


	public function __construct(Nette\Http\Request $httpRequest, Nette\Http\Session $session, Nette\Security\User $user)
	{
		$this->httpRequest = $httpRequest;
		$this->session = $session;
		$this->user = $user;
	}


	/**
	 * Stores current request to session.
	 * @param  Request application request
	 * @param  string expiration time
	 * @return string key
	 */
	public function storeRequest(Request $request, $expiration = '+ 10 minutes')
	{
		$session = $this->session->getSection('Nette.Application/requests');
		do {
			$key = Nette\Utils\Random::generate(5);
		} while (isset($session[$key]));

		$url = clone $this->httpRequest->getUrl();

		$session[$key] = array(
			'user' => $this->user->getId(),
			'url' => $url->appendQuery(array(static::REQUEST_KEY => $key))->absoluteUrl,
			'request' => $request,
		);
		$session->setExpiration($expiration, $key);
		return $key;
	}


	/**
	 * Restores request from session.
	 * @param  string key
	 * @param  \Nette\Application\UI\Presenter
	 * @throws \Nette\Application\AbortException
	 * @return void
	 */
	public function restoreRequest($key, Presenter $presenter)
	{
		list($request, $url) = $this->loadRequestFromSession($key);
		if ($request === NULL) {
			return;
		}

		if ($presenter->hasFlashSession()) {
			$url .= '&' . Presenter::FLASH_KEY . '=' . $presenter->getParameter(Presenter::FLASH_KEY);
		}
		$presenter->redirectUrl($url);
	}


	/**
	 * Returns stored request.
	 * @param  \Nette\Http\Request
	 * @return Request|NULL
	 */
	public function getRequest(Nette\Http\Request $httpRequest)
	{
		$key = $httpRequest->getQuery(static::REQUEST_KEY);

		list($request, $url) = $this->loadRequestFromSession($key);
		if ($request === NULL) {
			return NULL;
		}

		$flash = $this->httpRequest->getUrl()->getQueryParameter(Presenter::FLASH_KEY);
		if ($flash !== NULL) {
			$parameters = $request->getParameters();
			$request->setParameters($parameters + array(Presenter::FLASH_KEY => $flash));
		}

		return $request;
	}


	/**
	 * Loads request from session by its key.
	 * @param  string key
	 * @return array(Request, string)
	 */
	protected function loadRequestFromSession($key)
	{
		$session = $this->session->getSection('Nette.Application/requests');
		if (!isset($session[$key]) || ($session[$key]['user'] !== NULL && $session[$key]['user'] !== $this->user->getId())) {
			return array(NULL, NULL);
		}

		$request = clone $session[$key]['request'];
		$request->setFlag(Request::RESTORED, TRUE);

		return array($request, $session[$key]['url']);
	}

}
