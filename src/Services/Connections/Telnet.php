<?php

namespace PauloHortelan\Onmt\Services\Connections;

/**
 * Telnet class
 *
 * Used to execute remote commands via telnet connection
 * Usess sockets functions and fgetc() to process result
 *
 * All methods throws Exceptions on error
 */
class Telnet
{
    private bool $debug = false;

    private bool $cleanAnsiSequences = true;

    private static array $instances = [];

    protected string $host;

    protected int $port;

    protected string $username;

    protected string $password;

    protected string $hostType;

    protected int $timeout;

    protected int $streamTimeoutSec;

    protected int $streamTimeoutUsec;

    protected bool $isAuthenticated = false;

    protected $socket = null;

    protected string $buffer = '';

    protected string $prompt = '';

    protected string $promptRegex = '';

    protected bool $stripPrompt = true;

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

    protected \SplFileObject $globalBuffer;

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
     *
     * @throws \Exception
     */
    public function __construct(
        string $host,
        int $port,
        int $timeout,
        float $streamTimeout,
    ) {
        $this->host = $host;
        $this->port = $port;
        $this->timeout = $timeout;

        $this->setSpecialCharacters();
        $this->setStreamTimeout($streamTimeout);

        $this->globalBuffer = new \SplFileObject('php://temp', 'r+b');

        $this->connect();
    }

    /**
     * Set special characters
     */
    private function setSpecialCharacters(): void
    {
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
    }

    /**
     * Sets the stream timeout.
     */
    private function setStreamTimeout(float $timeout): void
    {
        $this->streamTimeoutSec = (int) $timeout;
        $this->streamTimeoutUsec = (int) (fmod($timeout, 1) * 1000000);
    }

    /**
     * Gets the static class instance
     */
    public static function getInstance(
        string $host,
        int $port,
        int $timeout,
        float $streamTimeout,
    ): self {
        $instanceKey = static::class.":{$host}:{$port}";

        if (! isset(self::$instances[$instanceKey])) {
            self::$instances[$instanceKey] = new static($host, $port, $timeout, $streamTimeout);
        }

        if (! self::$instances[$instanceKey]->isConnectionAlive()) {
            self::$instances[$instanceKey]->connect();
        }

        return self::$instances[$instanceKey];
    }

    public function connect(int $retries = 3): void
    {
        for ($attempt = 0; $attempt < $retries; $attempt++) {
            $this->socket = @fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);

            if ($this->socket) {
                $this->isAuthenticated = false;

                return;
            }

            sleep(2 ** $attempt);
        }

        throw new \Exception("Unable to connect to {$this->host}:{$this->port}");
    }

    public function isConnectionAlive(): bool
    {
        if ($this->socket) {
            $result = @fwrite($this->socket, '');

            return $result !== false;
        }

        return false;
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
     * This method is a wrapper for lower level private methods and should be
     * modified to reflect telnet implementation details like login/password
     * and line prompts. Defaults to standard unix non-root prompts
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
        $this->username = $username;
        $this->password = $password;
        $this->hostType = $hostType;

        $userPrompt = '';
        $passPrompt = '';

        try {

            switch ($hostType) {
                case 'linux': // General Linux/UNIX
                    $userPrompt = 'login:';
                    $passPrompt = 'Password:';
                    break;

                case 'ZTE-C300':
                case 'ZTE-C600':
                    $userPrompt = 'Username:';
                    $passPrompt = 'Password:';
                    break;

                case 'Nokia-FX16':
                    $userPrompt = 'login: ';
                    $passPrompt = 'password: ';
                    break;

                case 'ios': // Cisco IOS, IOS-XE, IOS-XR
                    $userPrompt = 'Username:';
                    $passPrompt = 'Password:';
                    break;

                case 'digistar':
                    $passPrompt = 'Password: ';
                    break;

                case 'phyhome':
                    $userPrompt = 'Username(1-32 chars):';
                    $passPrompt = 'Password(1-16 chars):';
                    break;

                case 'Datacom-DM4612':
                    $userPrompt = 'login:';
                    $passPrompt = 'Password:';
                    break;
            }

            $promptRegex = $this->getPromptRegexForHostType($hostType);
            $this->promptRegex = $promptRegex;

            $this->writeCommand($userPrompt, $username);

            $this->writeCommand($passPrompt, $password);

            $this->setRegexPrompt($promptRegex);
            $this->waitPrompt();
        } catch (\Exception $e) {
            if ($this->socket) {
                @fclose($this->socket);
                $this->socket = null;
            }

            $instanceKey = static::class.":{$this->host}:{$this->port}";
            unset(self::$instances[$instanceKey]);

            throw new \Exception('Login failed: '.$e->getMessage());
        }
    }

    /**
     * Closes the socket connection and cleans up the instance
     */
    public function disconnect(): void
    {
        if ($this->socket) {
            if (! fclose($this->socket)) {
                throw new \Exception('Error while closing telnet socket');
            }
            $this->socket = null;
        }

        $instanceKey = static::class.":{$this->host}:{$this->port}";
        if (isset(self::$instances[$instanceKey])) {
            unset(self::$instances[$instanceKey]);
        }

        $this->buffer = '';
        $this->isAuthenticated = false;
    }

    /**
     * Executes command and returns a string with result.
     * This method is a wrapper for lower level private methods
     *
     * @param  string  $command  Command to execute
     * @param  bool  $addNewline  Default true, adds newline to the command
     * @return string Command result
     */
    public function exec($command, $addNewline = true)
    {
        if (! $this->isConnectionAlive()) {
            $this->connect();
        }

        $this->write($command, $addNewline);
        $this->waitPrompt();

        return $this->getBuffer();
    }

    /**
     * Executes command and doesn't treat the response
     * This method is a wrapper for lower level private methods
     *
     * @param  string  $command  Command to execute
     * @param  bool  $addNewline  Default true, adds newline to the command
     */
    public function execWithoutResponse($command, $addNewline = true): bool
    {
        $this->write($command, $addNewline);

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
        $this->stripPrompt = false;

        return $this;
    }

    /**
     * Enable strip prompt
     *
     * @return $this
     */
    public function enableStripPrompt()
    {
        $this->stripPrompt = true;

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

    protected function writeCommand(string $prompt, string $input): void
    {
        $this->setPrompt($prompt);
        $this->waitPrompt();
        $this->write($input);
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
     * Set if the buffer should be stripped from the buffer after reading.
     *
     * @param  $strip  boolean if the prompt should be stripped.
     */
    public function stripPromptFromBuffer(mixed $strip): void
    {
        $this->stripPrompt = $strip;
    }

    /**
     * Destroy instance, cleans up socket connection and command buffer
     */
    public function destroy(): void
    {
        $instanceKey = static::class.":{$this->host}:{$this->port}";
        if (isset(self::$instances[$instanceKey])) {
            unset(self::$instances[$instanceKey]);
        }

        $this->disconnect();
        $this->buffer = '';
    }

    /**
     * Gets character from the socket
     *
     * @return string|bool $c character string
     */
    protected function getc()
    {
        stream_set_timeout($this->socket, $this->streamTimeoutSec, $this->streamTimeoutUsec);
        $c = fgetc($this->socket);
        $this->globalBuffer->fwrite(strval($c));

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
            throw new \Exception('Connection closed');
        }

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

            if ($this->debug && strpos($this->buffer, $this->eol) !== false) {
                $lines = explode($this->eol, $this->buffer);
                // $this->buffer = array_pop($lines);

                foreach ($lines as $line) {
                    $this->debugLogLine($line);
                }
            }

            // we've encountered the prompt. Break out of the loop
            if (! empty($prompt) && preg_match("/{$prompt}$/", $this->buffer)) {
                return self::TELNET_OK;
            }
        } while ($c != $this->NULL || $c != $this->DC1);

        return false;
    }

    private function debugLogLine(string $buffer): void
    {
        $lines = explode("\n", $buffer);
        foreach ($lines as $line) {
            if (trim($line) !== '') {
                var_dump(addcslashes($line, "\r\n"));
            }
        }
    }

    /**
     * Write command to a socket
     *
     * @param  string  $buffer  Stuff to write to socket
     * @param  bool  $addNewline  Default true, adds newline to the command
     * @return bool
     *
     * @throws \Exception
     */
    protected function write($buffer, $addNewline = true)
    {
        if (! $this->socket) {
            throw new \Exception('Telnet connection closed');
        }

        // clear buffer from last command
        $this->clearBuffer();

        if ($addNewline == true) {
            $buffer .= $this->eol;
        }

        $this->globalBuffer->fwrite($buffer);

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
        // Clean ANSI escape sequences if enabled
        $buffer = $this->cleanAnsiSequences ? $this->cleanAnsiSequences($this->buffer) : $this->buffer;

        // Remove all carriage returns from line breaks
        $buf = str_replace(["\n\r", "\r\n", "\n", "\r"], "\n", $buffer);

        // Cut last line from buffer (almost always prompt)
        if ($this->stripPrompt) {
            $buf = explode("\n", $buf);
            unset($buf[count($buf) - 1]);
            $buf = implode("\n", $buf);
        }

        return trim($buf);
    }

    /**
     * Cleans ANSI escape sequences from the buffer
     *
     * @param  string  $buffer  The buffer to clean
     * @return string The cleaned buffer
     */
    protected function cleanAnsiSequences(string $buffer): string
    {
        // Quick return for empty buffer
        if (empty($buffer)) {
            return $buffer;
        }

        // Check if we need to clean anything
        $hasEscapeSequence = strpos($buffer, "\x1B") !== false;
        $hasSpinnerSequence = strpos($buffer, '-\|/') !== false;
        $hasCursorMovement = strpos($buffer, "\x1B[1D") !== false ||
                             strpos($buffer, "\x1B[A") !== false ||
                             strpos($buffer, "\x1B[C") !== false;

        if (! $hasEscapeSequence && ! $hasSpinnerSequence && ! $hasCursorMovement) {
            return $buffer;
        }

        // 1. First pass: Simple string replacement for common sequences
        $commonSequences = [
            "\x1B[1D", "\x1B[K", "\x1B[0m", "\x1B[2K", "-\|/", "\x1B[?25l", "\x1B[?25h",
            "\x1B[H", "\x1B[J", "\x1B[1;1H", "\x1B[1A", "\x1B[1B",
        ];
        $cleaned = str_replace($commonSequences, '', $buffer);

        // 2. Second pass: Regular expression for more complex ANSI sequences
        if (strpos($cleaned, "\x1B") !== false) {
            // Match both CSI sequences and non-CSI escape sequences
            $pattern = '/\x1B(?:[@-Z\\-_]|\[[0-?]*[ -/]*[@-~])/';
            $regexCleaned = preg_replace($pattern, '', $cleaned);

            // Apply a second pattern for any missed complex sequences
            if ($regexCleaned !== null && strpos($regexCleaned, "\x1B") !== false) {
                $complexPattern = '/\x1B\[[\d;]*[A-Za-z]/';
                $regexCleaned = preg_replace($complexPattern, '', $regexCleaned);
            }

            // If regex worked, use that result
            if ($regexCleaned !== null && $regexCleaned !== $cleaned) {
                $cleaned = $regexCleaned;
            }
        }

        // 3. Handle spinner animations and cursor movement artifacts
        $cleaned = preg_replace('/[-\\\\|\/]{2,}/', '', $cleaned);

        // 4. Last resort: Character-by-character cleaning if escape sequences still exist
        if (strpos($cleaned, "\x1B") !== false) {
            $filtered = '';
            $inEscSeq = false;

            for ($i = 0; $i < strlen($cleaned); $i++) {
                $char = $cleaned[$i];

                if ($char === "\x1B") {
                    $inEscSeq = true;

                    continue;
                }

                if ($inEscSeq) {
                    // Look for the end of the escape sequence
                    // CSI sequences typically end with a letter
                    if (preg_match('/[a-zA-Z]/', $char)) {
                        $inEscSeq = false;
                    }

                    continue;
                }

                $filtered .= $char;
            }

            return $filtered;
        }

        return $cleaned;
    }

    /**
     * Enable cleaning of ANSI escape sequences from buffer
     *
     * @return $this
     */
    public function enableAnsiCleaning()
    {
        $this->cleanAnsiSequences = true;

        return $this;
    }

    /**
     * Disable cleaning of ANSI escape sequences from buffer
     *
     * @return $this
     */
    public function disableAnsiCleaning()
    {
        $this->cleanAnsiSequences = false;

        return $this;
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

        $controlChar = $this->getc();
        $opt = $this->getc();

        $response = match ($controlChar) {
            $this->DO, $this->DONT => $this->WONT,
            $this->WILL, $this->WONT => $this->DONT,
            default => throw new \Exception('Unknown control character: '.ord($controlChar))
        };

        fwrite($this->socket, $this->IAC.$response.$opt);

        return self::TELNET_OK;
    }

    /**
     * Reads socket until prompt is encountered
     */
    protected function waitPrompt(): bool
    {
        return $this->readTo($this->prompt);
    }

    public function enableDebug(): void
    {
        $this->debug = true;
    }

    public function disableDebug(): void
    {
        $this->debug = false;
    }

    /**
     * Changes the promptRegex to a new value
     *
     * @param  string  $promptRegex  New regex to use as prompt
     * @return $this
     */
    public function changePromptRegex(string $promptRegex)
    {
        $this->promptRegex = $promptRegex;
        $this->setRegexPrompt($promptRegex);

        return $this;
    }

    /**
     * Resets the promptRegex to default value based on hostType
     *
     * @return $this
     */
    public function resetPromptRegex()
    {
        $promptRegex = $this->getPromptRegexForHostType($this->hostType);

        $this->promptRegex = $promptRegex;
        $this->setRegexPrompt($promptRegex);

        return $this;
    }

    /**
     * Get the appropriate prompt regex for a given host type
     *
     * @param  string  $hostType  The type of host
     * @return string The prompt regex pattern
     */
    private function getPromptRegexForHostType(string $hostType): string
    {
        return match ($hostType) {
            'linux' => '\$',
            'ZTE-C300', 'ZTE-C600', 'Nokia-FX16', 'Datacom-DM4612' => '[#]',
            'ios' => '[>#]',
            'digistar', 'phyhome' => '[>]',
            default => '\$',
        };
    }
}
