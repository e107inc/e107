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

/**
 * Raw AES-CBC with PKCS#7 padding, from whichever backend the host offers.
 *
 * This is the whole of the block cipher surface e107 needs. It carries no
 * authentication of its own: everything that uses it goes through
 * {@see \e107\Security\Jwe\AesCbcHmacSha2}, which supplies the MAC and
 * checks it before it ever asks for a decryption. An implementation of this
 * interface used on its own would be a padding oracle.
 *
 * The AES variant follows from the key length, so the caller never names a
 * cipher: 16 octets means AES-128, 24 means AES-192, 32 means AES-256.
 *
 * @see \e107\Security\Cipher\CipherFactory for the runtime selection
 */
interface CbcCipherInterface
{
	/**
	 * Whether this backend can be used on this host.
	 *
	 * @return bool
	 */
	public static function isAvailable();

	/**
	 * Short stable identifier for this backend, for tests and diagnostics.
	 *
	 * @return string
	 */
	public function name();

	/**
	 * @param string $key raw octets, 16, 24 or 32 of them
	 * @param string $iv raw octets, exactly 16
	 * @param string $plaintext raw octets, any length including zero
	 * @return string|false ciphertext, always at least one block longer than
	 *                      the plaintext because the padding is unconditional
	 */
	public function encrypt($key, $iv, $plaintext);

	/**
	 * @param string $key raw octets, 16, 24 or 32 of them
	 * @param string $iv raw octets, exactly 16
	 * @param string $ciphertext raw octets, a non-zero multiple of 16
	 * @return string|false plaintext, or false when the padding is not valid
	 */
	public function decrypt($key, $iv, $ciphertext);
}
