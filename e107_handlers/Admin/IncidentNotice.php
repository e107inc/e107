<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Admin;

/**
 * An {@see Incident} the administrator should be shown, until they say
 * otherwise.
 *
 * What ends a dismissal is the fingerprint the caller presents: the cron
 * token, so regenerating it asks again, or the time a run of events began, so
 * a fresh run asks again while the one already answered stays quiet. A record
 * older than something that settles it, such as a cron run that has since
 * succeeded, is not news either.
 *
 * The wording belongs to whichever subsystem owns the notice, because only it
 * knows which language file is loaded.
 */
class IncidentNotice
{
	/** @var NoticeSuppression */
	private $suppression;

	/** @var string */
	private $id;

	/** @var string */
	private $fingerprint;

	/** @var array|null */
	private $incident;

	/** @var int */
	private $supersededAt;

	/** @var callable|null */
	private $onDismiss;

	/**
	 * @param NoticeSuppression $suppression
	 * @param string $id
	 *   One of {@see Notices}.
	 * @param string $fingerprint
	 *   What the dismissal holds for.
	 * @param array|null $incident
	 *   {@see Incident::last()}'s shape.
	 * @param int $supersededAt
	 *   A time after which the record is history; 0 when nothing settles it.
	 * @param callable|null $onDismiss
	 *   Called with the id once dismissed, for a surface holding its own copy.
	 */
	public function __construct(NoticeSuppression $suppression, $id, $fingerprint, $incident = null, $supersededAt = 0, $onDismiss = null)
	{
		$this->suppression = $suppression;
		$this->id = (string) $id;
		$this->fingerprint = (string) $fingerprint;
		$this->incident = is_array($incident) ? $incident : null;
		$this->supersededAt = (int) $supersededAt;
		$this->onDismiss = is_callable($onDismiss) ? $onDismiss : null;
	}

	/**
	 * @return string
	 */
	public function id()
	{
		return $this->id;
	}

	/**
	 * @return array|null
	 *   The record to show, or null when there is none, it has been dismissed, or something has settled it.
	 */
	public function toReport()
	{
		if($this->incident === null || $this->suppression->isSuppressed($this->id, $this->fingerprint))
		{
			return null;
		}

		return ($this->incident['last'] >= $this->supersededAt) ? $this->incident : null;
	}

	/**
	 * Hides the notice until the fingerprint changes.
	 *
	 * @return void
	 */
	public function dismiss()
	{
		$this->suppression->suppress($this->id, $this->fingerprint);

		if($this->onDismiss !== null)
		{
			call_user_func($this->onDismiss, $this->id);
		}
	}
}
