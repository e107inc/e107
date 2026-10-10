<?php

namespace Sandbox;

/**
 * One sandbox's file system: a tmpfs upper layer over read-only lower layers, mounted where Apache serves it.
 */
class Overlay
{
	/** @var Shell */
	private $shell;

	/** @var string */
	private $mountPoint;

	/** @var string */
	private $state;

	/** @var string */
	private $sessions;

	/**
	 * @param Shell $shell
	 * @param string $mountPoint where the merged tree appears
	 * @param string $state where the tmpfs holding the upper layer is mounted
	 * @param string $sessions where Apache keeps the PHP sessions of the site served from $mountPoint (docker/Dockerfile)
	 */
	public function __construct(Shell $shell, $mountPoint, $state, $sessions)
	{
		$this->shell = $shell;
		$this->mountPoint = $mountPoint;
		$this->state = $state;
		$this->sessions = $sessions;
	}

	/** @return string */
	public function mountPoint()
	{
		return $this->mountPoint;
	}

	/** @return string */
	public function upper()
	{
		return $this->state.'/up';
	}

	/**
	 * Replace whatever is mounted with an empty upper layer over $lowers, and the site's sessions with none.
	 *
	 * @param string[] $lowers topmost first; the last one lends the merged root its owner and mode
	 * @return void
	 */
	public function mount(array $lowers)
	{
		$mp = escapeshellarg($this->mountPoint);
		$st = escapeshellarg($this->state);
		$se = escapeshellarg($this->sessions);
		$this->unmount();
		$this->shell->run("set -e; rm -rf $se; mkdir -p $mp $st $se; chmod 1733 $se; mount -t tmpfs -o mode=0755 sandbox $st; mkdir $st/up $st/wk"
			."; chmod --reference=".escapeshellarg(end($lowers))." $st/up; chown --reference=".escapeshellarg(end($lowers))." $st/up"
			."; mount -t overlay sandbox -o ".self::options($lowers, ",upperdir={$this->state}/up,workdir={$this->state}/wk")." $mp");
	}

	/**
	 * Take the merged tree down but keep the upper layer, read-only from now on, to be a lower layer of others, and the tree it made read-only at {@see view()}.
	 *
	 * @param string[] $lowers as mounted
	 * @return void
	 */
	public function freeze(array $lowers)
	{
		$view = escapeshellarg($this->view());
		$this->shell->run('set -e; umount '.escapeshellarg($this->mountPoint)."; mkdir $view; mount -t overlay sandbox -o "
			.self::options(array_merge(array($this->upper()), $lowers), '')." $view");
	}

	/** @return string where a frozen sandbox's tree stays readable */
	public function view()
	{
		return $this->state.'/view';
	}

	/**
	 * Discard everything, mounted or not, once no request is running in the merged tree, or after ten seconds by detaching it from under the request.
	 *
	 * @return bool false when the tree had to be detached: a request outliving its test can then reach the next sandbox by absolute path
	 */
	public function unmount()
	{
		$mp = escapeshellarg($this->mountPoint);
		$view = escapeshellarg($this->view());
		$st = escapeshellarg($this->state);
		$output = $this->shell->run("set -e; detached=0; if mountpoint -q $mp; then i=0; until umount $mp 2>/dev/null; do i=\$((i + 1));"
			." if [ \$i -ge 100 ]; then umount -l $mp; detached=1; break; fi; sleep 0.1; done; fi"
			."; if mountpoint -q $view; then umount $view; fi; if mountpoint -q $st; then umount $st; fi; echo \$detached");

		return end($output) === '0';
	}

	/**
	 * Mount over a lower layer of one directory holding one file, remove both through the merged tree, and take it all down again.
	 *
	 * @param string $lower where to build the lower layer; removed afterwards
	 * @return bool whether the directory could be removed, which takes the user extended attributes tmpfs keeps from Linux 6.6 on
	 */
	public function removesLowerDirectories($lower)
	{
		$lo = escapeshellarg($lower);
		$this->shell->run("rm -rf $lo; mkdir -p $lo/d; touch $lo/d/f");
		$this->mount(array($lower));
		$output = $this->shell->run('rm -r '.escapeshellarg($this->mountPoint.'/d').' 2>/dev/null && echo 1 || echo 0');
		$this->unmount();
		$this->shell->run("rm -rf $lo ".escapeshellarg($this->mountPoint).' '.escapeshellarg($this->state).' '.escapeshellarg($this->sessions));

		return end($output) === '1';
	}

	/**
	 * The options the kernel forces inside a user namespace are spelt out so a rootful Docker host overlays the same way: renaming a directory from a lower layer fails with EXDEV on both.
	 *
	 * @param string[] $lowers
	 * @param string $upper the upperdir and workdir options, or '' for a read-only overlay
	 * @return string
	 */
	private static function options(array $lowers, $upper)
	{
		return escapeshellarg('lowerdir='.implode(':', $lowers).$upper.',userxattr,redirect_dir=nofollow');
	}
}
