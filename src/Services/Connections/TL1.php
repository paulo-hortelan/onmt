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
    private static mixed $instance;

    /**
     * Attempts login to remote host.
     * This method is a wrapper for lower level private methods and should be
     * modified to reflect telnet implementation details like login/password
     * and line prompts. Defaults to standard unix non-root prompts
     *
     * @param  string  $username  Username
     * @param  string  $password  Password
     * @param  string  $host_type  Type of destination host
     * @return $this
     *
     * @throws \Exception
     */
    public function login($username, $password, $host_type = 'linux')
    {
        $user_prompt = '';
        $pass_prompt = '';
        $prompt_reg = '';

        switch ($host_type) {
            case 'nokia':
                $user_prompt = 'Enter Username   :';
                $pass_prompt = 'Enter Password   :';
                $prompt_reg = '(;\\r\\n\\r\\n<)';
                $this->setPrompt('<');
                $this->waitPrompt();
                $this->write("\n");
                $this->setPrompt('Would you like a TL1 login(T) or TL1 normal session(N) ? [N]: ');
                $this->waitPrompt();
                $this->write('T');
                break;
            case 'Fiberhome-AN551604':
                $this->setPrompt(';');
                $this->write("LOGIN:::CTAG::UN=$username,PWD=$password;");
                $prompt_reg = ';';
                break;
        }

        try {
            if (! empty($user_prompt)) {
                if (! empty($username)) {
                    $this->setPrompt($user_prompt);
                    $this->waitPrompt();
                    $this->write($username);
                }

                $this->setPrompt($pass_prompt);
                $this->waitPrompt();
                $this->write($password);
            }

            // Wait prompt
            $this->setRegexPrompt($prompt_reg);
            $this->waitPrompt();
        } catch (\Exception $e) {
            throw new \Exception('Login failed.');
        }

        return $this;
    }

    /**
     * Creates a new class instance in case it doesn't exist
     *
     * @param  string  $host  Host name or IP address
     * @param  int  $port  TCP port number
     * @param  int  $timeout  Connection timeout in seconds
     * @param  float  $streamTimeout  Stream timeout in decimal seconds
     * @param  string  $username  Login username
     * @param  string  $password  Login password
     * @param  string  $hostType  Host type
     * @return TL1
     */
    public static function getInstance($host, $port, $timeout, $streamTimeout, $username, $password, $hostType)
    {
        if (! isset(self::$instance) || $host !== self::$host || $port !== self::$port) {
            self::$instance = new self($host, $port, $timeout, $streamTimeout, $username, $password, $hostType);
        }

        return self::$instance;
    }
}
