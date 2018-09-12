<?php

interface Preparer
{
	public function snapshot();
	public function rollback();
}