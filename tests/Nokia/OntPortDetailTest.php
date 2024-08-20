<?php

use PauloHortelan\Onmt\Facades\Nokia;

uses()->group('Nokia');

beforeEach(function () {
    $ipServer = env('NOKIA_OLT_IP');
    $username = env('NOKIA_OLT_USERNAME');
    $password = env('NOKIA_OLT_PASSWORD');

    $this->interface1 = env('NOKIA_INTERFACE_1');
    $this->interface2 = env('NOKIA_INTERFACE_2');
    $this->interface3 = env('NOKIA_INTERFACE_3');

    $this->nokia = Nokia::connect($ipServer, $username, $password);
});

describe('Nokia Port Detail', function () {
    it('can get single detail', function () {
        $detail = $this->nokia->ontsPortDetail([$this->interface1]);

        expect($detail)->toBeArray();
        expect($detail[0]['result']['oprStatus'])->toBeString();

        $detail = $this->nokia->interface($this->interface1)->ontsPortDetail();

        expect($detail)->toBeArray();
        expect($detail[0]['result']['oprStatus'])->toBeString();
    });

    it('can get multiple details', function () {
        $interfaces = [$this->interface1, $this->interface2, $this->interface3];

        $details = $this->nokia->ontsPortDetail($interfaces);

        expect($details)->toBeArray();
        expect($details[0]['result']['oprStatus'])->toBeString();
        expect($details[1]['result']['oprStatus'])->toBeString();
        expect($details[2]['result']['oprStatus'])->toBeString();

        $details = $this->nokia->interfaces($interfaces)->ontsPortDetail();

        expect($details)->toBeArray();
        expect($details[0]['result']['oprStatus'])->toBeString();
        expect($details[1]['result']['oprStatus'])->toBeString();
        expect($details[2]['result']['oprStatus'])->toBeString();
    });
});

afterAll(function () {
    $this->nokia->disconnect();
});
