<?php

class NoopDeployer extends Deployer
{

	public function start()
	{
		// Noop
		return null;
	}

	public function stop()
	{
		// Noop
		return null;
	}
}