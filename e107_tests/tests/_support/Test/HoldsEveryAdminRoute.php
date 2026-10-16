<?php
namespace Test;

/**
 * For an e_admin_ui probe whose administrator holds every route the probe exercises.
 */
trait HoldsEveryAdminRoute
{
	/**
	 * @param string $action
	 * @return bool
	 */
	protected function hasModeRouteAccess($action)
	{
		return true;
	}
}
