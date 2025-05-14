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

                    $this->write('INH-MSG-ALL::ALL:::;', false);
                    $this->setPrompt('M  0 COMPLD');
                    $this->waitPrompt();

                    $this->setRegexPrompt('(\\n< )');
                    $this->waitPrompt();

                    $promptRegex = '(;\\r\\n\\r\\n<)';
                    $this->setRegexPrompt($promptRegex);
                    break;
                case 'Fiberhome-AN5516-04':
                case 'Fiberhome-AN5516-06':
                case 'Fiberhome-AN5516-06B':
                    $this->setPrompt(';');
                    $this->write("LOGIN:::CTAG::UN=$username,PWD=$password;");
                    $promptRegex = ';';
                    $this->setRegexPrompt($promptRegex);
                    $this->waitPrompt();
                    break;
            }
        } catch (\Exception $e) {
            if ($this->socket) {
                @fclose($this->socket);
                $this->socket = null;
            }

            $instanceKey = static::class.":{$this->host}:{$this->port}";
            if (isset(self::$instances[$instanceKey])) {
                unset(self::$instances[$instanceKey]);
            }

            throw new \Exception('Login failed: '.$e->getMessage(), 0, $e);
        }
    }
}
