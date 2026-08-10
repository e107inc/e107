<?php
/**
 * JOSE and HKDF test vectors, extracted programmatically from the raw RFC texts.
 *
 * Do not edit by hand. Every octet string is stored as a lowercase hex string.
 *
 * Sources, in the order the vectors appear below:
 *
 *  - aes_cbc_hmac_sha2['A128CBC-HS256']
 *      RFC 7518 Appendix B.1, Test Cases for AES_128_CBC_HMAC_SHA_256
 *      https://www.rfc-editor.org/rfc/rfc7518.txt
 *  - aes_cbc_hmac_sha2['A192CBC-HS384']
 *      RFC 7518 Appendix B.2, Test Cases for AES_192_CBC_HMAC_SHA_384
 *      https://www.rfc-editor.org/rfc/rfc7518.txt
 *  - aes_cbc_hmac_sha2['A256CBC-HS512']
 *      RFC 7518 Appendix B.3, Test Cases for AES_256_CBC_HMAC_SHA_512
 *      https://www.rfc-editor.org/rfc/rfc7518.txt
 *  - jwe_compact['rfc7516_a3']
 *      RFC 7516 Appendix A.3, Example JWE Using AES Key Wrap and
 *      AES_128_CBC_HMAC_SHA_256. Sub-sections A.3.1 to A.3.7 supply the
 *      protected header, CEK, encrypted key, IV, AAD, ciphertext and tag.
 *      https://www.rfc-editor.org/rfc/rfc7516.txt
 *  - hkdf_sha256['A.1'], ['A.2'], ['A.3']
 *      RFC 5869 Appendix A.1, A.2 and A.3, HKDF-SHA256 test cases 1 to 3.
 *      https://www.rfc-editor.org/rfc/rfc5869.txt
 *
 * Notes for implementers, both taken from RFC 7518 section 5.2.2:
 *
 *  - K splits into MAC_KEY followed by ENC_KEY. The MAC key is the FIRST half
 *    of K and the encryption key is the SECOND half.
 *  - AL is the octet length of A expressed as a 64-bit big-endian number OF
 *    BITS, so 42 octets of A give AL = 0000000000000150.
 *  - T is the leading half of M, truncated to the MAC key length.
 *
 * The 'jwe_compact' entry uses alg=A128KW rather than dir, so its
 * 'encrypted_key' is not reproducible without AES Key Wrap. Its value to a
 * direct-encryption implementation is the content encryption step: sealing
 * 'plaintext' under 'cek' with 'iv' and 'aad' must yield 'ciphertext' and 'tag'.
 */

return [
	'aes_cbc_hmac_sha2' => [
		'A128CBC-HS256' => [
			'section' => 'RFC 7518 Appendix B.1',
			'enc' => 'A128CBC-HS256',
			'K' => '000102030405060708090a0b0c0d0e0f101112131415161718191a1b1c1d1e1f',
			'MAC_KEY' => '000102030405060708090a0b0c0d0e0f',
			'ENC_KEY' => '101112131415161718191a1b1c1d1e1f',
			'P' => '41206369706865722073797374656d206d757374206e6f7420626520726571756972656420746f206265207365637265742c20616e64206974206d7573742062652061626c6520746f2066616c6c20696e746f207468652068616e6473206f662074686520656e656d7920776974686f757420696e636f6e76656e69656e6365',
			'IV' => '1af38c2dc2b96ffdd86694092341bc04',
			'A' => '546865207365636f6e64207072696e6369706c65206f662041756775737465204b6572636b686f666673',
			'AL' => '0000000000000150',
			'E' => 'c80edfa32ddf39d5ef00c0b468834279a2e46a1b8049f792f76bfe54b903a9c9a94ac9b47ad2655c5f10f9aef71427e2fc6f9b3f399a221489f16362c703233609d45ac69864e3321cf82935ac4096c86e133314c54019e8ca7980dfa4b9cf1b384c486f3a54c51078158ee5d79de59fbd34d848b3d69550a67646344427ade54b8851ffb598f7f80074b9473c82e2db',
			'M' => '652c3fa36b0a7c5b3219fab3a30bc1c4e6e54582476515f0ad9f75a2b71c73ef',
			'T' => '652c3fa36b0a7c5b3219fab3a30bc1c4',
		],
		'A192CBC-HS384' => [
			'section' => 'RFC 7518 Appendix B.2',
			'enc' => 'A192CBC-HS384',
			'K' => '000102030405060708090a0b0c0d0e0f101112131415161718191a1b1c1d1e1f202122232425262728292a2b2c2d2e2f',
			'MAC_KEY' => '000102030405060708090a0b0c0d0e0f1011121314151617',
			'ENC_KEY' => '18191a1b1c1d1e1f202122232425262728292a2b2c2d2e2f',
			'P' => '41206369706865722073797374656d206d757374206e6f7420626520726571756972656420746f206265207365637265742c20616e64206974206d7573742062652061626c6520746f2066616c6c20696e746f207468652068616e6473206f662074686520656e656d7920776974686f757420696e636f6e76656e69656e6365',
			'IV' => '1af38c2dc2b96ffdd86694092341bc04',
			'A' => '546865207365636f6e64207072696e6369706c65206f662041756775737465204b6572636b686f666673',
			'AL' => '0000000000000150',
			'E' => 'ea65da6b59e61edb419be62d19712ae5d303eeb50052d0dfd6697f77224c8edb000d279bdc14c1072654bd30944230c657bed4ca0c9f4a8466f22b226d1746214bf8cfc2400add9f5126e479663fc90b3bed787a2f0ffcbf3904be2a641d5c2105bfe591bae23b1d7449e532eef60a9ac8bb6c6b01d35d49787bcd57ef484927f280adc91ac0c4e79c7b11efc60054e3',
			'M' => '8490ac0e58949bfe51875d733f93ac2075168039ccc733d74594f886b3faafd486f25c7131e3281e36c7a2d130afde57',
			'T' => '8490ac0e58949bfe51875d733f93ac2075168039ccc733d7',
		],
		'A256CBC-HS512' => [
			'section' => 'RFC 7518 Appendix B.3',
			'enc' => 'A256CBC-HS512',
			'K' => '000102030405060708090a0b0c0d0e0f101112131415161718191a1b1c1d1e1f202122232425262728292a2b2c2d2e2f303132333435363738393a3b3c3d3e3f',
			'MAC_KEY' => '000102030405060708090a0b0c0d0e0f101112131415161718191a1b1c1d1e1f',
			'ENC_KEY' => '202122232425262728292a2b2c2d2e2f303132333435363738393a3b3c3d3e3f',
			'P' => '41206369706865722073797374656d206d757374206e6f7420626520726571756972656420746f206265207365637265742c20616e64206974206d7573742062652061626c6520746f2066616c6c20696e746f207468652068616e6473206f662074686520656e656d7920776974686f757420696e636f6e76656e69656e6365',
			'IV' => '1af38c2dc2b96ffdd86694092341bc04',
			'A' => '546865207365636f6e64207072696e6369706c65206f662041756775737465204b6572636b686f666673',
			'AL' => '0000000000000150',
			'E' => '4affaaadb78c31c5da4b1b590d10ffbd3dd8d5d302423526912da037ecbcc7bd822c301dd67c373bccb584ad3e9279c2e6d12a1374b77f077553df829410446b36ebd97066296ae6427ea75c2e0846a11a09ccf5370dc80bfecbad28c73f09b3a3b75e662a2594410ae496b2e2e6609e31e6e02cc837f053d21f37ff4f51950bbe2638d09dd7a4930930806d0703b1f6',
			'M' => '4dd3b4c088a7f45c216839645b2012bf2e6269a8c56a816dbc1b267761955bc5fd30a565c616ffb2f364baece68fc40753bcfc025dde3693754aa1f5c3373b9c',
			'T' => '4dd3b4c088a7f45c216839645b2012bf2e6269a8c56a816dbc1b267761955bc5',
		],
	],
	'jwe_compact' => [
		'rfc7516_a3' => [
			'section' => 'RFC 7516 Appendix A.3',
			'alg' => 'A128KW',
			'enc' => 'A128CBC-HS256',
			'cek' => '04d31fc5549dfcfe0b649dfa3faa6ace6b7cd42d6f6b09dbc8b100f08f9c2ccf',
			'encrypted_key' => 'e8a07bd3b74cf584c8807b4bbed81643c98ac1ba095b7a1ff65a1c8b39034c7cc10b6225ad3d6839',
			'iv' => '03163c0c2b4368696c6c69636f746865',
			'plaintext' => '4c697665206c6f6e6720616e642070726f737065722e',
			'aad' => '65794a68624763694f694a424d544934533163694c434a6c626d4d694f694a424d54493451304a444c5568544d6a5532496e30',
			'protected_json' => '{"alg":"A128KW","enc":"A128CBC-HS256"}',
			'protected_b64' => 'eyJhbGciOiJBMTI4S1ciLCJlbmMiOiJBMTI4Q0JDLUhTMjU2In0',
			'ciphertext' => '283953b577218594c6b9f31898e6064b81df7f13d252b7e6a821d7688f703866',
			'tag' => '5349bf6268cdd380c9bdc7852026c255',
			'compact' => 'eyJhbGciOiJBMTI4S1ciLCJlbmMiOiJBMTI4Q0JDLUhTMjU2In0.6KB707dM9YTIgHtLvtgWQ8mKwboJW3of9locizkDTHzBC2IlrT1oOQ.AxY8DCtDaGlsbGljb3RoZQ.KDlTtXchhZTGufMYmOYGS4HffxPSUrfmqCHXaI9wOGY.U0m_YmjN04DJvceFICbCVQ',
		],
	],
	'hkdf_sha256' => [
		'A.1' => [
			'section' => 'RFC 5869 Appendix A.1',
			'note' => 'basic test case with SHA-256',
			'hash' => 'sha256',
			'ikm' => '0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b',
			'salt' => '000102030405060708090a0b0c',
			'info' => 'f0f1f2f3f4f5f6f7f8f9',
			'length' => 42,
			'prk' => '077709362c2e32df0ddc3f0dc47bba6390b6c73bb50f9c3122ec844ad7c2b3e5',
			'okm' => '3cb25f25faacd57a90434f64d0362f2a2d2d0a90cf1a5a4c5db02d56ecc4c5bf34007208d5b887185865',
		],
		'A.2' => [
			'section' => 'RFC 5869 Appendix A.2',
			'note' => 'SHA-256 with longer inputs/outputs',
			'hash' => 'sha256',
			'ikm' => '000102030405060708090a0b0c0d0e0f101112131415161718191a1b1c1d1e1f202122232425262728292a2b2c2d2e2f303132333435363738393a3b3c3d3e3f404142434445464748494a4b4c4d4e4f',
			'salt' => '606162636465666768696a6b6c6d6e6f707172737475767778797a7b7c7d7e7f808182838485868788898a8b8c8d8e8f909192939495969798999a9b9c9d9e9fa0a1a2a3a4a5a6a7a8a9aaabacadaeaf',
			'info' => 'b0b1b2b3b4b5b6b7b8b9babbbcbdbebfc0c1c2c3c4c5c6c7c8c9cacbcccdcecfd0d1d2d3d4d5d6d7d8d9dadbdcdddedfe0e1e2e3e4e5e6e7e8e9eaebecedeeeff0f1f2f3f4f5f6f7f8f9fafbfcfdfeff',
			'length' => 82,
			'prk' => '06a6b88c5853361a06104c9ceb35b45cef760014904671014a193f40c15fc244',
			'okm' => 'b11e398dc80327a1c8e7f78c596a49344f012eda2d4efad8a050cc4c19afa97c59045a99cac7827271cb41c65e590e09da3275600c2f09b8367793a9aca3db71cc30c58179ec3e87c14c01d5c1f3434f1d87',
		],
		'A.3' => [
			'section' => 'RFC 5869 Appendix A.3',
			'note' => 'SHA-256 with zero-length salt/info',
			'hash' => 'sha256',
			'ikm' => '0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b',
			'salt' => '',
			'info' => '',
			'length' => 42,
			'prk' => '19ef24a32c717b167f33a91d6f648bdf96596776afdb6377ac434c1c293ccb04',
			'okm' => '8da4e775a563c18f715f802a063c5a31b8a11f5c5ee1879ec3454e5f3c738d2d9d201395faa4b61a96c8',
		],
	],
];
