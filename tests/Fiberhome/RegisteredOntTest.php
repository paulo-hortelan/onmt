<?php

use PauloHortelan\Onmt\Facades\Fiberhome;

uses()->group('Fiberhome');

beforeEach(function () {
    $ipOlt = env('FIBERHOME_OLT_IP');
    $ipServer = env('FIBERHOME_IP_SERVER');
    $username = env('FIBERHOME_OLT_USERNAME');
    $password = env('FIBERHOME_OLT_PASSWORD');

    $this->fiberhome = Fiberhome::connect($ipOlt, $username, $password, 3337, $ipServer);
});

describe('Fiberhome Registered Onts - Success', function () {
    it('can get registered onts', function () {
        $registeredOnts = $this->fiberhome->registeredOnts();

        expect($registeredOnts)->toBeArray();
        expect($registeredOnts[0]['success'])->toBeTrue();
    });
});
