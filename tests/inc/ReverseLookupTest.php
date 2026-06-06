<?php

namespace Tests\inc;

use PHPUnit\Framework\TestCase;

class ReverseLookupTest extends TestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public static function setUpBeforeClass(): void
    {
        // Provide dummy GET parameters so reverse_lookup.php doesn't just exit.
        // It will proceed to try querying the database, but since we are in a test
        // environment, the connection or query might fail. The script catches Throwable,
        // echoes a JSON response, and continues to the end of the file, safely defining
        // the functions, including buildChain.
        $_GET['lat'] = 1;
        $_GET['lng'] = 1;

        // Suppress warnings from headers already sent or undefined $db
        @ob_start();
        @require_once realpath(__DIR__ . '/../../apps/inc/reverse_lookup.php');
        @ob_get_clean();
    }

    public function testBuildChainNormal()
    {
        $names = [
            '11' => 'Provinsi Aceh',
            '11.01' => 'Kab. Aceh Selatan',
            '11.01.01' => 'Kec. Bakongan',
            '11.01.01.2001' => 'Kel. Keude Bakongan'
        ];

        $result = buildChain('11.01.01.2001', $names);

        $this->assertEquals([
            'prov' => ['kode' => '11', 'nama' => 'Provinsi Aceh'],
            'kab'  => ['kode' => '11.01', 'nama' => 'Kab. Aceh Selatan'],
            'kec'  => ['kode' => '11.01.01', 'nama' => 'Kec. Bakongan'],
            'kel'  => ['kode' => '11.01.01.2001', 'nama' => 'Kel. Keude Bakongan'],
        ], $result);
    }

    public function testBuildChainEmpty()
    {
        $result = buildChain('', []);

        $this->assertEquals([
            'prov' => ['kode' => '', 'nama' => null],
            'kab'  => null,
            'kec'  => null,
            'kel'  => null,
        ], $result);
    }

    public function testBuildChainShort()
    {
        $names = ['11' => 'Provinsi Aceh'];
        $result = buildChain('11', $names);

        $this->assertEquals([
            'prov' => ['kode' => '11', 'nama' => 'Provinsi Aceh'],
            'kab'  => null,
            'kec'  => null,
            'kel'  => null,
        ], $result);

        $namesKab = ['11' => 'Provinsi Aceh', '11.01' => 'Kab. Aceh Selatan'];
        $resultKab = buildChain('11.01', $namesKab);

        $this->assertEquals([
            'prov' => ['kode' => '11', 'nama' => 'Provinsi Aceh'],
            'kab'  => ['kode' => '11.01', 'nama' => 'Kab. Aceh Selatan'],
            'kec'  => null,
            'kel'  => null,
        ], $resultKab);
    }

    public function testBuildChainMissingNames()
    {
        $result = buildChain('11.01.01.2001', []);

        $this->assertEquals([
            'prov' => ['kode' => '11', 'nama' => null],
            'kab'  => ['kode' => '11.01', 'nama' => null],
            'kec'  => ['kode' => '11.01.01', 'nama' => null],
            'kel'  => ['kode' => '11.01.01.2001', 'nama' => null],
        ], $result);
    }

    public function testBuildChainPartialNames()
    {
        $names = [
            '11' => 'Provinsi Aceh',
            // Missing kab
            '11.01.01' => 'Kec. Bakongan',
            // Missing kel
        ];

        $result = buildChain('11.01.01.2001', $names);

        $this->assertEquals([
            'prov' => ['kode' => '11', 'nama' => 'Provinsi Aceh'],
            'kab'  => ['kode' => '11.01', 'nama' => null],
            'kec'  => ['kode' => '11.01.01', 'nama' => 'Kec. Bakongan'],
            'kel'  => ['kode' => '11.01.01.2001', 'nama' => null],
        ], $result);
    }
}
