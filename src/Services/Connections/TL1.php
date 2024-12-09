<?php

namespace PauloHortelan\Onmt\Services\Connections;

/**
 * TL1 class
 *
 * Used to execute remote commands via TL1 connection
 * Usess sockets functions and fgetc() to process result
 *
 * All methods throw Exceptions on error
 */
class TL1 extends Telnet
{
    private static mixed $instance = null;

    public static function getInstance(
        string $host,
        int $port,
        int $timeout,
        float $streamTimeout,
    ): self {
        if (self::$instance === null) {
            self::$instance = new self($host, $port, $timeout, $streamTimeout);
        }

        if (! self::$instance->isConnectionAlive()) {
            self::$instance->connect();
        }

        return self::$instance;
    }

    public function authenticate(string $username, string $password, string $hostType): void
    {
        if ($this->isAuthenticated) {
            return;
        }

        if (! $this->socket) {
            $this->connect();
        }

        $this->login($username, $password, $hostType);
        $this->isAuthenticated = true;
    }

    /**
     * Attempts login to remote host.
     *
     * @param  string  $username  Username
     * @param  string  $password  Password
     * @param  string  $hostType  Type of destination host
     * @return $this
     *
     * @throws \Exception
     */
    public function login($username, $password, $hostType = 'linux'): void
    {
        $promptRegex = '';

        try {
            switch ($hostType) {
                case 'Nokia-FX16':
                    $this->setPrompt('<');
                    $this->waitPrompt();
                    $this->write("\n");
                    $this->setPrompt('Would you like a TL1 login(T) or TL1 normal session(N) ? [N]: ');
                    $this->waitPrompt();
                    $this->write('T');

                    $this->writeCommand('Enter Username   :', $username);
                    $this->writeCommand('Enter Password   :', $password);

                    $this->setPrompt('M  0 COMPLD');
                    $this->waitPrompt();

                    $this->setRegexPrompt('(\\n< )');
                    $this->waitPrompt();

                    $promptRegex = '(;\\r\\n\\r\\n<)';
                    $this->setRegexPrompt($promptRegex);
                    break;
                case 'Fiberhome-AN551604':
                    $this->setPrompt(';');
                    $this->write("LOGIN:::CTAG::UN=$username,PWD=$password;");
                    $promptRegex = ';';
                    $this->setRegexPrompt($promptRegex);
                    $this->waitPrompt();
                    break;
            }

            // var_dump($this->prompt);
            // $this->waitPrompt();
        } catch (\Exception $e) {
            throw new \Exception('Login failed: '.$e->getMessage());
        }
    }
}
