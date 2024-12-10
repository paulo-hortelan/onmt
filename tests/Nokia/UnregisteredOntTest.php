<?php

use PauloHortelan\Onmt\Facades\Nokia;

uses()->group('Nokia');

beforeEach(function () {
    $ipOlt = env('NOKIA_OLT_IP');
    $username = env('NOKIA_OLT_USERNAME_TELNET');
    $password = env('NOKIA_OLT_PASSWORD_TELNET');

    $this->nokia = Nokia::connectTelnet($ipOlt, $username, $password, 23);
});

describe('Nokia Unregistered Onts - Success', function () {
    it('can get unregistered onts', function () {
        $unregisteredOnts = $this->nokia->unregisteredOnts();

        expect($unregisteredOnts)->toBeArray();
        expect($unregisteredOnts[0]['success'])->toBeTrue();
    });
});
