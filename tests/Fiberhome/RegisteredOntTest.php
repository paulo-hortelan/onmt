<?php

use PauloHortelan\Onmt\Facades\Fiberhome;

uses()->group('Fiberhome');

beforeEach(function () {
    $ipOlt = env('FIBERHOME_OLT_IP');
    $ipServer = env('FIBERHOME_IP_SERVER');
    $username = env('FIBERHOME_OLT_USERNAME');
    $password = env('FIBERHOME_OLT_PASSWORD');

    $this->serial1 = env('FIBERHOME_SERIAL_1');
    $this->serial2 = env('FIBERHOME_SERIAL_2');
    $this->serial3 = env('FIBERHOME_SERIAL_3');

    $this->interface1 = env('FIBERHOME_INTERFACE_1');
    $this->interface2 = env('FIBERHOME_INTERFACE_2');
    $this->interface3 = env('FIBERHOME_INTERFACE_3');

    $this->fiberhome = Fiberhome::connect($ipOlt, $username, $password, $ipServer);
});

describe('Fiberhome Registered Onts - Success', function () {
    it('can get registered onts', function () {
        $registeredOnts = $this->fiberhome->registeredOnts();

        var_dump($registeredOnts);

        expect($registeredOnts)->toBeArray();
        expect($registeredOnts[0]['success'])->toBeTrue();
    });
});
