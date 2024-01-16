<?php

use PauloHortelan\OltMonitoring\Models\Olt;

it('can create', function () {
    $olt = Olt::create([
        'name' => 'olt-test1',
        'host' => '127.0.0.1',
        'username' => 'test',
        'password' => '1234',
        'model' => 'Nokia',
    ]);

    $this->assertNotNull($olt);
    $this->assertEquals(1, Olt::count());
});
