<?php

use PauloHortelan\Onmt\Models\Olt;

uses()->group('Models');

it('can create', function () {
    $olt = Olt::create([
        'name' => 'olt-test1',
        'host_connection' => '127.0.0.1',
        'host_server' => '127.0.0.1',
        'username' => 'test',
        'password' => '1234',
        'brand' => 'ZTE',
        'model' => 'C300',
    ]);

    $this->assertNotNull($olt);
    $this->assertEquals(1, Olt::count());
});
