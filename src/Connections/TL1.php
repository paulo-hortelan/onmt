<?php

namespace PauloHortelan\Onmt\Connections;

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
            case 'fiberhome':
                $this->setPrompt(';');
                $this->write("LOGIN:::CTAG::UN=$username,PWD=$password;");
                $prompt_reg = ';';
                break;
        }

        try {
            if (! empty($user_prompt)) {
                // username
                if (! empty($username)) {
                    $this->setPrompt($user_prompt);
                    $this->waitPrompt();
                    $this->write($username);
                }

                // password
                $this->setPrompt($pass_prompt);
                $this->waitPrompt();
                $this->write($password);
            }

            // wait prompt
            $this->setRegexPrompt($prompt_reg);
            $this->waitPrompt();
        } catch (\Exception $e) {
            throw new \Exception('Login failed.');
        }

        return $this;
    }
}
