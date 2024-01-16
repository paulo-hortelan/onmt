<?php

namespace PauloHortelan\OltMonitoring\Connections;

/**
 * Telnet class
 *
 * Used to execute remote commands via telnet connection
 * Usess sockets functions and fgetc() to process result
 *
 * All methods throw Exceptions on error
 */
class Telnet
{
    protected $host;

    protected $port;

    protected $timeout;

    protected $stream_timeout_sec;

    protected $stream_timeout_usec;

    protected $socket = null;

    protected $buffer = null;

    protected $prompt;

    protected $errno;

    protected $errstr;

    protected $strip_prompt = true;

    protected $eol = "\r\n";

    protected $enableMagicControl = true;

    protected $NULL;

    protected $DC1;

    protected $WILL;

    protected $WONT;

    protected $DO;

    protected $DONT;

    protected $IAC;

    protected $SB;

    protected $NAWS;

    protected $SE;

    protected $global_buffer;

    const TELNET_ERROR = false;

    const TELNET_OK = true;

    /**
     * Constructor. Initialises host, port and timeout parameters
     * defaults to localhost port 23 (standard telnet port)
     *
     * @param  string  $host Host name or IP addres
     * @param  int  $port TCP port number
     * @param  int  $timeout Connection timeout in seconds
     * @param  float  $stream_timeout Stream timeout in decimal seconds
     *
     * @throws \Exception
     */
    public function __construct($host = '127.0.0.1', $port = 23, $timeout = 10, $stream_timeout = 1.0)
    {
        $this->host = $host;
        $this->port = $port;
        $this->timeout = $timeout;
        $this->setStreamTimeout($stream_timeout);

        // set some telnet special characters
        $this->NULL = chr(0);
        $this->DC1 = chr(17);
        $this->WILL = chr(251);
        $this->SB = chr(250);
        $this->SE = chr(240);
        $this->NAWS = chr(31);
        $this->WONT = chr(252);
        $this->DO = chr(253);
        $this->DONT = chr(254);
        $this->IAC = chr(255);

        // open global buffer stream
        $this->global_buffer = new \SplFileObject('php://temp', 'r+b');

        $this->connect();
    }

    /**
     * Destructor. Cleans up socket connection and command buffer
     *
     * @return void
     */
    public function __destruct()
    {
        // clean up resources
        $this->disconnect();
        $this->buffer = null;
    }

    public function connect()
    {
        // check if we need to convert host to IP
        if (! preg_match('/([0-9]{1,3}\\.){3,3}[0-9]{1,3}/', $this->host)) {
            $ip = gethostbyname($this->host);

            if ($this->host == $ip) {
                throw new \Exception("Cannot resolve $this->host");
            } else {
                $this->host = $ip;
            }
        }

        // attempt connection - suppress warnings
        $this->socket = @fsockopen($this->host, $this->port, $this->errno, $this->errstr, $this->timeout);

        if (! $this->socket) {
            throw new \Exception("Cannot connect to $this->host on port $this->port");
        }

        if (! empty($this->prompt)) {
            $this->waitPrompt();
        }

        return $this;
    }

    /**
     * Closes IP socket
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function disconnect()
    {
        if ($this->socket) {
            if (! fclose($this->socket)) {
                throw new \Exception('Error while closing telnet socket');
            }
            $this->socket = null;
        }

        return $this;
    }

    /**
     * Change window size in terminal
     * Use its method when device respond with new line
     *
     * @param  int  $wide
     * @param  int  $high
     * @return bool
     *
     * @throws \Exception
     */
    public function setWindowSize($wide = 80, $high = 40)
    {
        fwrite($this->socket, $this->IAC.$this->WILL.$this->NAWS);
        $c = $this->getc();
        if ($c != $this->IAC) {
            throw new \Exception('Error: unknown control character '.ord($c));
        }
        $c = $this->getc();
        if ($c == $this->DONT || $c == $this->WONT) {
            throw new \Exception('Error: server refuses to use NAWS');
        } elseif ($c != $this->DO && $c != $this->WILL) {
            throw new \Exception('Error: unknown control character '.ord($c));
        }
        fwrite($this->socket, $this->IAC.$this->SB.$this->NAWS. 0 .$wide. 0 .$high.$this->IAC.$this->SE);

        return self::TELNET_OK;
    }

    /**
     * Executes command and returns a string with result.
     * This method is a wrapper for lower level private methods
     *
     * @param  string  $command Command to execute
     * @param  bool  $add_newline Default true, adds newline to the command
     * @return string Command result
     */
    public function exec($command, $add_newline = true)
    {
        $this->write($command, $add_newline);
        $this->waitPrompt();

        return $this->getBuffer();
    }

    /**
     * Executes command and doesn't treat the response
     * This method is a wrapper for lower level private methods
     *
     * @param  string  $command Command to execute
     * @param  bool  $add_newline Default true, adds newline to the command
     */
    public function execWithoutResponse($command, $add_newline = true)
    {
        $this->write($command, $add_newline);

        return true;
    }

    /**
     * Disable sending magic symbols for wait
     *
     * @return $this
     */
    public function disableMagicControl()
    {
        $this->enableMagicControl = false;

        return $this;
    }

    /**
     * Enable sending magic symbols for wait
     *
     * @return $this
     */
    public function enableMagicControl()
    {
        $this->enableMagicControl = true;

        return $this;
    }

    /**
     * Disable strip prompt
     *
     * @return $this
     */
    public function disableStripPrompt()
    {
        $this->strip_prompt = false;

        return $this;
    }

    /**
     * Enable strip prompt
     *
     * @return $this
     */
    public function enableStripPrompt()
    {
        $this->strip_prompt = true;

        return $this;
    }

    /**
     * Setted EOL symbol for new line in linux style (\n)
     *
     * @return $this
     */
    public function setLinuxEOL()
    {
        $this->eol = "\n";

        return $this;
    }

    /**
     * Setted EOL symbol for new line in windows style (\r\n)
     *
     * @return $this
     */
    public function setWinEOL()
    {
        $this->eol = "\r\n";

        return $this;
    }

    /**
     * Attempts login to remote host.
     * This method is a wrapper for lower level private methods and should be
     * modified to reflect telnet implementation details like login/password
     * and line prompts. Defaults to standard unix non-root prompts
     *
     * @param  string  $username Username
     * @param  string  $password Password
     * @param  string  $host_type Type of destination host
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
            case 'linux': // General Linux/UNIX
                $user_prompt = 'login:';
                $pass_prompt = 'Password:';
                $prompt_reg = '\$';
                break;

            case 'zte300':
                $user_prompt = 'Username:';
                $pass_prompt = 'Password:';
                $prompt_reg = '[>#]';
                break;

            case 'ios': // Cisco IOS, IOS-XE, IOS-XR
                $user_prompt = 'Username:';
                $pass_prompt = 'Password:';
                $prompt_reg = '[>#]';
                break;

            case 'junos': // Juniper Junos OS
                $user_prompt = 'login:';
                $pass_prompt = 'Password:';
                $prompt_reg = '[%>#]';
                break;

            case 'alaxala': // AlaxalA, HITACHI
                $user_prompt = 'login:';
                $pass_prompt = 'Password:';
                $prompt_reg = '[>#]';
                break;

            case 'dlink': // Dlink
                $user_prompt = 'ame:';
                $pass_prompt = 'ord:';
                $prompt_reg = '[>|#]';
                break;

            case 'xos': // Xtreme routers and switches
                $user_prompt = 'login:';
                $pass_prompt = 'password:';
                $prompt_reg = '\.[0-9]{1,3} > ';
                break;

            case 'bdcom': // BDcom PON switches
                $user_prompt = 'login:';
                $pass_prompt = 'password:';
                $prompt_reg = '[ > ]';
                break;

            case 'nokia':
                $user_prompt = 'login: ';
                $pass_prompt = 'password: ';
                $prompt_reg = '[#]';
                break;

            case 'digistar':
                $pass_prompt = 'Password: ';
                $prompt_reg = '[>]';
                break;

            case 'phyhome':
                $user_prompt = 'Username(1-32 chars):';
                $pass_prompt = 'Password(1-16 chars):';
                $prompt_reg = '[>]';
                break;
        }

        try {
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

            // wait prompt
            $this->setRegexPrompt($prompt_reg);
            $this->waitPrompt();
        } catch (\Exception $e) {
            throw new \Exception('Login failed. '.$e->getMessage());
        }

        return $this;
    }

    /**
     * Sets the string of characters to respond to.
     * This should be set to the last character of the command line prompt
     *
     * @param  string  $str String to respond to
     * @return $this
     */
    public function setPrompt($str)
    {
        $this->setRegexPrompt(preg_quote($str, '/'));

        return $this;
    }

    /**
     * Sets a regex string to respond to.
     * This should be set to the last line of the command line prompt.
     *
     * @param  string  $str Regex string to respond to
     * @return $this
     */
    public function setRegexPrompt($str)
    {
        $this->prompt = $str;

        return $this;
    }

    /**
     * Sets the stream timeout.
     *
     * @param  float  $timeout
     * @return void
     */
    public function setStreamTimeout($timeout)
    {
        $this->stream_timeout_usec = (int) (fmod($timeout, 1) * 1000000);
        $this->stream_timeout_sec = (int) $timeout;
    }

    /**
     * Set if the buffer should be stripped from the buffer after reading.
     *
     * @param $strip boolean if the prompt should be stripped.
     * @return void
     */
    public function stripPromptFromBuffer($strip)
    {
        $this->strip_prompt = $strip;
    }

    /**
     * Gets character from the socket
     *
     * @return string|bool $c character string
     */
    protected function getc()
    {
        stream_set_timeout($this->socket, $this->stream_timeout_sec, $this->stream_timeout_usec);
        $c = fgetc($this->socket);
        $this->global_buffer->fwrite($c);

        return $c;
    }

    /**
     * Clears internal command buffer
     *
     * @return $this
     */
    public function clearBuffer()
    {
        $this->buffer = '';

        return $this;
    }

    /**
     * Reads characters from the socket and adds them to command buffer.
     * Handles telnet control characters. Stops when prompt is ecountered.
     *
     * @param  string  $prompt
     * @return bool
     *
     * @throws \Exception
     */
    protected function readTo($prompt)
    {
        if (! $this->socket) {
            throw new \Exception('Telnet connection closed');
        }

        // clear the buffer
        $this->clearBuffer();

        $until_t = time() + $this->timeout;
        do {
            // time's up (loop can be exited at end or through continue!)
            if (time() > $until_t) {
                throw new \Exception("Couldn't find the requested : '$prompt' within {$this->timeout} seconds");
            }

            $c = $this->getc();
            if ($c === false) {
                if (empty($prompt)) {
                    return self::TELNET_OK;
                }
                throw new \Exception("Couldn't find the requested : '".$prompt."', it was not in the data returned from server: ".$this->buffer);
            }

            // Interpreted As Command
            if ($c == $this->IAC) {
                if ($this->negotiateTelnetOptions()) {
                    continue;
                }
            }

            // append current char to global buffer
            $this->buffer .= $c;

            // we've encountered the prompt. Break out of the loop
            if (! empty($prompt) && preg_match("/{$prompt}$/", $this->buffer)) {
                return self::TELNET_OK;
            }
        } while ($c != $this->NULL || $c != $this->DC1);

        return false;
    }

    /**
     * Write command to a socket
     *
     * @param  string  $buffer Stuff to write to socket
     * @param  bool  $add_newline Default true, adds newline to the command
     * @return bool
     *
     * @throws \Exception
     */
    protected function write($buffer, $add_newline = true)
    {
        if (! $this->socket) {
            throw new \Exception('Telnet connection closed');
        }

        // clear buffer from last command
        $this->clearBuffer();

        if ($add_newline == true) {
            $buffer .= $this->eol;
        }

        $this->global_buffer->fwrite($buffer);

        if (! fwrite($this->socket, $buffer) < 0) {
            throw new \Exception('Error writing to socket');
        }

        return self::TELNET_OK;
    }

    /**
     * Returns the content of the command buffer
     *
     * @return string Content of the command buffer
     */
    protected function getBuffer()
    {
        // Remove all carriage returns from line breaks
        $buf = str_replace(["\n\r", "\r\n", "\n", "\r"], "\n", $this->buffer);
        // Cut last line from buffer (almost always prompt)
        if ($this->strip_prompt) {
            $buf = explode("\n", $buf);
            unset($buf[count($buf) - 1]);
            $buf = implode("\n", $buf);
        }

        return trim($buf);
    }

    /**
     * Returns the content of the global command buffer
     *
     * @return string Content of the global command buffer
     */
    public function getGlobalBuffer()
    {
        $this->global_buffer->rewind();

        return $this->global_buffer->fpassthru();
    }

    /**
     * Telnet control character magic
     *
     * @return bool
     *
     * @throws \Exception
     *
     * @internal param string $command Character to check
     */
    protected function negotiateTelnetOptions()
    {
        if (! $this->enableMagicControl) {
            return self::TELNET_OK;
        }

        $c = $this->getc();
        if ($c != $this->IAC) {
            if (($c == $this->DO) || ($c == $this->DONT)) {
                $opt = $this->getc();
                fwrite($this->socket, $this->IAC.$this->WONT.$opt);
            } elseif (($c == $this->WILL) || ($c == $this->WONT)) {
                $opt = $this->getc();
                fwrite($this->socket, $this->IAC.$this->DONT.$opt);
            } else {
                throw new \Exception('Error: unknown control character '.ord($c));
            }
        } else {
            throw new \Exception('Error: Something Wicked Happened');
        }

        return self::TELNET_OK;
    }

    /**
     * Reads socket until prompt is encountered
     */
    protected function waitPrompt()
    {
        return $this->readTo($this->prompt);
    }
}
