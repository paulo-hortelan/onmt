<?php

use PauloHortelan\Onmt\Models\CommandResultBatch;

it('returns a positive integer execution time across Carbon versions', function () {
    $batch = new CommandResultBatch([
        'created_at' => '2026-09-18 10:00:00',
        'finished_at' => '2026-09-18 10:00:05',
    ]);

    expect($batch->executionTimeInSeconds())
        ->toBeInt()
        ->toBe(5);
});
