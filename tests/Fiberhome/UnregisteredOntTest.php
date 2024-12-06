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

describe('Fiberhome Unregistered Onts - Success', function () {
    it('can get unregistered onts', function () {
        $unregisteredOnts = $this->fiberhome->unregisteredOnts();

        expect($unregisteredOnts)->toBeArray();
        expect($unregisteredOnts[0]['success'])->toBeTrue();
    });
});
