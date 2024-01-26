<?php

namespace PauloHortelan\Onmt\Connections;

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
    protected static string $host;

    protected static int $port;

    protected int $timeout;

    protected mixed $stream_timeout_sec;

    protected mixed $stream_timeout_usec;

    private static mixed $instance;

    protected static mixed $socket = null;

    protected mixed $buffer = null;

    protected string $prompt;

    protected mixed $errno;

    protected mixed $errstr;

    protected bool $strip_prompt = true;

    protected string $eol = "\r\n";

    protected bool $enableMagicControl = true;

    protected string $NULL;

    protected string $DC1;

    protected string $WILL;

    protected string $WONT;

    protected string $DO;

    protected string $DONT;

    protected string $IAC;

    protected string $SB;

    protected string $NAWS;

    protected string $SE;

    protected \SplFileObject $global_buffer;

    const TELNET_ERROR = false;

    const TELNET_OK = true;

    /**
     * Constructor. Initialises host, port and timeout parameters
     * defaults to localhost port 23 (standard telnet port)
     *
     * @param  string  $host  Host name or IP address
     * @param  int  $port  TCP port number
     * @param  int  $timeout  Connection timeout in seconds
     * @param  float  $streamTimeout  Stream timeout in decimal seconds
     * @param  string  $username  Login username
     * @param  string  $password  Login password
     * @param  string  $hostType  Host type
     *
     * @throws \Exception
     */
    public function __construct($host = '127.0.0.1', $port = 23, $timeout = 10, $streamTimeout = 1.0, $username = '', $password = '', $hostType = '')
    {
        self::$host = $host;
        self::$port = $port;
        $this->timeout = $timeout;
        $this->setStreamTimeout($streamTimeout);

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
        $this->login($username, $password, $hostType);
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
     * @return Telnet
     */
    public static function getInstance($host, $port, $timeout, $streamTimeout, $username, $password, $hostType)
    {
        if (! isset(self::$instance) || $host !== self::$host || $port !== self::$port) {
            self::$instance = new self($host, $port, $timeout, $streamTimeout, $username, $password, $hostType);
        }

        return self::$instance;
    }

    private function __clone()
    {
    }

    /**
     * Destroy instance, cleans up socket connection and command buffer
     */
    public function destroy(): void
    {
        self::$instance = null;
        $this->disconnect();
        $this->buffer = null;
    }

    public function connect(): self
    {
        // check if we need to convert host to IP
        if (! preg_match('/([0-9]{1,3}\\.){3,3}[0-9]{1,3}/', self::$host)) {
            $ip = gethostbyname(self::$host);

            if (self::$host == $ip) {
                throw new \Exception('Cannot resolve '.self::$host);
            } else {
                self::$host = $ip;
            }
        }

        // attempt connection - suppress warnings
        self::$socket = @fsockopen(self::$host, self::$port, $this->errno, $this->errstr, $this->timeout);

        if (! self::$socket) {
            throw new \Exception('Cannot connect to '.self::$host.'on port'.self::$port);
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
        dump("disconnect");
        if (self::$socket) {
            if (! fclose(self::$socket)) {
                throw new \Exception('Error while closing telnet socket');
            }
            self::$socket = null;
        }

        return $this;
    }

    /**
     * Executes command and returns a string with result.
     * This method is a wrapper for lower level private methods
     *
     * @param  string  $command  Command to execute
     * @param  bool  $add_newline  Default true, adds newline to the command
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
     * @param  string  $command  Command to execute
     * @param  bool  $add_newline  Default true, adds newline to the command
     */
    public function execWithoutResponse($command, $add_newline = true): bool
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
            case 'linux': // General Linux/UNIX
                $user_prompt = 'login:';
                $pass_prompt = 'Password:';
                $prompt_reg = '\$';
                break;

            case 'ZTE-C300':
            case 'ZTE-C600':
                $user_prompt = 'Username:';
                $pass_prompt = 'Password:';
                $prompt_reg = '[>#]';
                break;

            case 'Nokia-FX16':
                $user_prompt = 'login: ';
                $pass_prompt = 'password: ';
                $prompt_reg = '[#]';
                break;

            case 'ios': // Cisco IOS, IOS-XE, IOS-XR
                $user_prompt = 'Username:';
                $pass_prompt = 'Password:';
                $prompt_reg = '[>#]';
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
     * @param  string  $str  String to respond to
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
     * @param  string  $str  Regex string to respond to
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
     * @param    $strip  boolean if the prompt should be stripped.
     */
    public function stripPromptFromBuffer(mixed $strip): void
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
        stream_set_timeout(self::$socket, $this->stream_timeout_sec, $this->stream_timeout_usec);
        $c = fgetc(self::$socket);
        $this->global_buffer->fwrite(strval($c));

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
        if (! self::$socket) {
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
     * @param  string  $buffer  Stuff to write to socket
     * @param  bool  $add_newline  Default true, adds newline to the command
     * @return bool
     *
     * @throws \Exception
     */
    protected function write($buffer, $add_newline = true)
    {        
        if (! self::$socket) {
            throw new \Exception('Telnet connection closed');
        }

        // clear buffer from last command
        $this->clearBuffer();

        if ($add_newline == true) {
            $buffer .= $this->eol;
        }

        $this->global_buffer->fwrite($buffer);

        if (! fwrite(self::$socket, $buffer) < 0) {
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
                fwrite(self::$socket, $this->IAC.$this->WONT.$opt);
            } elseif (($c == $this->WILL) || ($c == $this->WONT)) {
                $opt = $this->getc();
                fwrite(self::$socket, $this->IAC.$this->DONT.$opt);
            } else {
                throw new \Exception('Error: unknown control character '.ord(strval($c)));
            }
        } else {
            throw new \Exception('Error: Something Wicked Happened');
        }

        return self::TELNET_OK;
    }

    /**
     * Reads socket until prompt is encountered
     */
    protected function waitPrompt(): bool
    {
        return $this->readTo($this->prompt);
    }
}
