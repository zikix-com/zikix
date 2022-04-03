<?php

namespace Zikix\Zikix;

class XLSXWriterBuffererWriter
{
    protected $fd;
    protected $buffer     = '';
    protected $check_utf8 = false;

    /**
     * @param        $filename
     * @param string $fd_fopen_flags
     * @param bool $check_utf8
     */
    public function __construct($filename, string $fd_fopen_flags = 'wb', bool $check_utf8 = false)
    {
        $this->check_utf8 = $check_utf8;
        $this->fd         = fopen($filename, $fd_fopen_flags);
        if ($this->fd === false) {
            XLSXWriter::log("Unable to open $filename for writing.");
        }
    }

    /**
     * @param $string
     *
     * @return void
     */
    public function write($string): void
    {
        $this->buffer .= $string;
        if (isset($this->buffer[8191])) {
            $this->purge();
        }
    }

    /**
     * @return void
     */
    protected function purge(): void
    {
        if ($this->fd) {
            if ($this->check_utf8 && !self::isValidUTF8($this->buffer)) {
                XLSXWriter::log("Error, invalid UTF8 encoding detected.");
                $this->check_utf8 = false;
            }
            fwrite($this->fd, $this->buffer);
            $this->buffer = '';
        }
    }

    protected static function isValidUTF8($string): bool
    {
        if (function_exists('mb_check_encoding')) {
            return mb_check_encoding($string, 'UTF-8');
        }

        return (bool) preg_match("//u", $string);
    }

    public function __destruct()
    {
        $this->close();
    }

    public function close(): void
    {
        $this->purge();
        if ($this->fd) {
            fclose($this->fd);
            $this->fd = null;
        }
    }

    public function ftell()
    {
        if ($this->fd) {
            $this->purge();

            return ftell($this->fd);
        }

        return -1;
    }

    public function fseek($pos): int
    {
        if ($this->fd) {
            $this->purge();

            return fseek($this->fd, $pos);
        }

        return -1;
    }
}
