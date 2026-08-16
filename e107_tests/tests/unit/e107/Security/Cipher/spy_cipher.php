<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Security\Cipher;

use e107\Reflection\ReflectionProperty;

/**
 * A real backend that counts the calls made to it.
 *
 * Encrypt-then-MAC is only encrypt-then-MAC if the MAC is checked before the
 * block cipher is touched. Every observable result of decrypting first and
 * comparing afterwards is identical, so the only way to pin the ordering is
 * to watch whether the cipher was asked at all.
 */
class SpyCipher implements CbcCipherInterface
{
	/**
	 * @var int
	 */
	public $encryptCalls = 0;

	/**
	 * @var int
	 */
	public $decryptCalls = 0;

	/**
	 * @var CbcCipherInterface
	 */
	private $inner;

	/**
	 * @param CbcCipherInterface $inner the backend to pass the work on to
	 */
	public function __construct(CbcCipherInterface $inner)
	{
		$this->inner = $inner;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function isAvailable()
	{
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function name()
	{
		return 'spy';
	}

	/**
	 * {@inheritdoc}
	 */
	public function encrypt($key, $iv, $plaintext)
	{
		$this->encryptCalls++;

		return $this->inner->encrypt($key, $iv, $plaintext);
	}

	/**
	 * {@inheritdoc}
	 */
	public function decrypt($key, $iv, $ciphertext)
	{
		$this->decryptCalls++;

		return $this->inner->decrypt($key, $iv, $ciphertext);
	}

	/**
	 * Put this spy in front of the memoised backend inside
	 * {@see CipherFactory}, which has no setter and should not grow one.
	 *
	 * @return SpyCipher
	 */
	public static function install()
	{
		$spy = new self(CipherFactory::create());

		$property = new ReflectionProperty('e107\\Security\\Cipher\\CipherFactory', 'instance');
		$property->setValue(null, $spy);

		return $spy;
	}
}
