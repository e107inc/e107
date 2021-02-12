<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2020 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

class e_file_inspectorTest extends \Codeception\Test\Unit
{
    /**
     * @var e_file_inspector
     */
    private $e_integrity;
    const TEST_INTEGRITY_CONTENTS = [
        'index.php' => [
            'v1.0.0' => 'd41d8cd98f00b204e9800998ecf8427e'
        ],
        'e107_admin' => [
            'e107_update.php' => [
                'v1.0.0' => 'd41d8cd98f00b204e9800998ecf8427e'
            ]
        ],
        'e107_themes' => [
            'index.html' => [
                'v1.0.0' => 'd41d8cd98f00b204e9800998ecf8427e'
            ]
        ]
    ];

    public function _before()
    {
        $tmpfile = tmpfile();
        $tmpfilePath = stream_get_meta_data($tmpfile)['uri'];
        $testIntegrityImage = '<?php $core_image = ' .
            var_export(json_encode(self::TEST_INTEGRITY_CONTENTS), true) .
            ';';
        file_put_contents($tmpfilePath, $testIntegrityImage);
        require_once(e_HANDLER . "e_file_inspector_json.php");
        $this->e_integrity = new e_file_inspector_json($tmpfilePath);
        $this->e_integrity->loadDatabase();
    }

    public function testGetChecksums()
    {
        $checksums = $this->e_integrity->getChecksums("e107_admin/e107_update.php");
        $this->assertIsArray($checksums);
        $this->assertNotEmpty($checksums);

        $checksums = $this->e_integrity->getChecksums("e107_handlers/nonexistent.php");
        $this->assertIsArray($checksums);
        $this->assertEmpty($checksums);
    }

    public function testGetCurrentVersion()
    {
        $actualVersion = $this->e_integrity->getCurrentVersion();

        $this->assertIsString($actualVersion);
        $this->assertNotEmpty($actualVersion);
    }

    public function testGetPathIterator()
    {
        $iterator = $this->e_integrity->getPathIterator();
        $this->assertGreaterThanOrEqual(1, iterator_count($iterator));

        $iterator = $this->e_integrity->getPathIterator("0.0.1-fakeNonExistentVersion");
        $this->assertEquals(0, iterator_count($iterator));
    }

    public function testValidate()
    {
        $result = $this->e_integrity->validate("e107_themes/index.html");
        $this->assertGreaterThanOrEqual(1, $result & e_file_inspector::VALIDATED);
        $this->assertGreaterThanOrEqual(1, $result & e_file_inspector::VALIDATED_PATH_KNOWN);
        $this->assertGreaterThanOrEqual(1, $result & e_file_inspector::VALIDATED_PATH_VERSION);
        $this->assertGreaterThanOrEqual(1, $result & e_file_inspector::VALIDATED_FILE_EXISTS);
        $this->assertGreaterThanOrEqual(1, $result & e_file_inspector::VALIDATED_HASH_EXISTS);
        $this->assertGreaterThanOrEqual(1, $result & e_file_inspector::VALIDATED_HASH_CURRENT);
        $this->assertGreaterThanOrEqual(1, $result & e_file_inspector::VALIDATED_HASH_CALCULABLE);
        $this->assertGreaterThanOrEqual(1, $result & e_file_inspector::VALIDATED_FILE_SECURITY);

        $result = $this->e_integrity->validate("file/does/not/exist.php");
        $this->assertEquals(0, $result & e_file_inspector::VALIDATED_FILE_EXISTS);
    }

    public function testCustomPathToDefaultPath()
    {
        $object = $this->createCustomPathFileInspector();

        $input = "e963_admin/index.php";
        $expected = "e107_admin/index.php";
        $actual = $object->customPathToDefaultPath($input);

        $this->assertEquals($expected, $actual);
    }

    public function testDefaultPathToCustomPath()
    {
        $object = $this->createCustomPathFileInspector();

        $input = "e107_admin/index.php";
        $expected = "e963_admin/index.php";
        $actual = $object->defaultPathToCustomPath($input);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @return e_file_inspector
     * @throws ReflectionException if e_file_inspector is broken
     */
    private function createCustomPathFileInspector()
    {
        /** @var e_file_inspector $object */
        $object = $this->make('e_file_inspector');
        $class = new ReflectionClass(get_class($object));
        $object->customPathToDefaultPath('populate_cache');
        $member = $class->getProperty('customDirsCache');
        $member->setAccessible(true);
        $customDirs = $member->getValue($object);
        $customDirs['ADMIN_DIRECTORY'] = 'e963_admin/';
        $member->setValue($object, $customDirs);
        return $object;
    }
}
