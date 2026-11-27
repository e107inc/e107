<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Answers a request the core route table claims, then exits; include it at file scope, never from inside a function.
 *
 */

if (!defined('e107_INIT')) { exit; }

$routedRequest = \e107\Http\Request::fromGlobals($_SERVER);
$routeTable = require(e_CORE.'route_table.php');
$routeMatch = $routeTable->match($routedRequest);
unset($routeTable);

if($routeMatch !== null)
{
	(new eRequest())->setLegacyQstring()->setLegacyPage();
	e107::route($routeMatch->getName());

	foreach($routeMatch->getParameters() as $routeAttribute => $routeValue)
	{
		$routedRequest = $routedRequest->withAttribute($routeAttribute, $routeValue);
	}

	$routeResponse = $routeMatch->createHandler()->handle($routedRequest);

	if($routeResponse->isRedirect())
	{
		e107::getRedirect()->go($routeResponse->getHeaderLine('Location'), true, $routeResponse->getStatusCode());
		exit;
	}

	$routePage = $routeResponse->getPage();
	http_response_code($routeResponse->getStatusCode());
	e107::title($routePage->getTitle());
	unset($routedRequest, $routeMatch, $routeResponse, $routeAttribute, $routeValue);

	require_once(HEADERF);

	if($routePage->getCaption() === null)
	{
		echo $routePage->render();
	}
	else
	{
		e107::getRender()->tablerender($routePage->getCaption(), $routePage->render(), $routePage->getMode());
	}

	require_once(FOOTERF);
	exit;
}

unset($routedRequest, $routeMatch);
