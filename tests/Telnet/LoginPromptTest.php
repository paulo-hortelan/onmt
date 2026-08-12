<?php

use PauloHortelan\Onmt\Services\Connections\Telnet;

class FakeLoginTelnet extends Telnet
{
    public array $promptHistory = [];

    public array $writes = [];

    public function connect(int $retries = 3): void
    {
        $this->socket = fopen('php://temp', 'r+');
        $this->isAuthenticated = false;
    }

    protected function waitPrompt(): bool
    {
        $this->promptHistory[] = $this->prompt;

        return true;
    }

    protected function write($buffer, $addNewline = true)
    {
        $this->writes[] = [
            'buffer' => $buffer,
            'addNewline' => $addNewline,
        ];

        return self::TELNET_OK;
    }
}

describe('Telnet login prompts', function () {
    it('uses flexible Nokia prompts during authentication', function () {
        $telnet = new FakeLoginTelnet('127.0.0.1', 23, 1, 1);

        $telnet->login('admin', 'secret', 'Nokia-FX16');

        expect($telnet->promptHistory)->toBe([
            '(?:[Ll]ogin|[Uu]sername):\s*',
            '[Pp]assword:\s*',
            '[#]',
        ]);

        expect($telnet->writes)->toBe([
            ['buffer' => 'admin', 'addNewline' => true],
            ['buffer' => 'secret', 'addNewline' => true],
        ]);
    });

    it('keeps ZTE prompts unchanged', function () {
        $telnet = new FakeLoginTelnet('127.0.0.1', 23, 1, 1);

        $telnet->login('admin', 'secret', 'ZTE-C300');

        expect($telnet->promptHistory)->toBe([
            'Username\:',
            'Password\:',
            '[#]',
        ]);
    });
});
