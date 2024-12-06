<?php

namespace HTTP_BRIDGE;

use Exception;

if (!defined('ABSPATH')) {
    exit();
}

/**
 * Multipart data encoder.
 */
class Multipart
{
    /**
     * End of line handler.
     *
     * @var string EOL end of line chars.
     */
    public const EOL = "\r\n";

    private const EOL_RE = "(?:\n|\r|\t)";

    /**
     * Encoded data handler.
     *
     * @var string $_data encoded data.
     */
    private $_data = '';

    /**
     * Mime boundary handler.
     *
     * @var string $_mime_boundary Unique part ID.
     */
    private $_mime_boundary;

    public static function from($data, $boundary = null)
    {
        try {
            return new Multipart($data, $boundary);
        } catch (Exception) {
            return null;
        }
    }

    /**
     * Creates a random mime boundary.
     */
    public function __construct($data = null, $boundary = null)
    {
        if (is_string($data)) {
            $this->set_data($data, $boundary);
        } else {
            $this->_mime_boundary = 'HttpBridge' . md5(microtime(true));
        }
    }

    /**
     * Add part header boundary
     */
    private function _add_part_header()
    {
        $this->_data .= '--------' . $this->_mime_boundary . self::EOL;
    }

    private function set_data($data, $boundary = null)
    {
        $this->_data .= $data . self::EOL;
        if ($boundary) {
            if (
                preg_match(
                    '/^' .
                        self::EOL_RE .
                        '*--+' .
                        $boundary .
                        '' .
                        self::EOL_RE .
                        '+/',
                    $data
                )
            ) {
                $this->_mime_boundary = $boundary;
            } else {
                throw new Exception('Invalid multipart/form-data boundary');
            }
        } else {
            if (
                preg_match(
                    '/^' . self::EOL_RE . '*--+(.*)' . self::EOL_RE . '+/',
                    $data,
                    $match
                )
            ) {
                $this->_mime_boundary = $match[1];
            } else {
                throw new Exception('Invalid multipart/form-data payload');
            }
        }

        return $this;
    }

    /**
     * Encode array data as multipart text.
     *
     * @param array<string|int>, mixed> $data Input data.
     * @param string $prefix Field name prefix.
     */
    public function add_array($data, $prefix = '')
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if ($prefix) {
                    $this->add_array($value, $prefix . '[' . $key . ']');
                } else {
                    $this->add_array($value, $key);
                }
            } else {
                if ($prefix) {
                    $this->add_part(
                        $prefix . '[' . (is_numeric($key) ? '' : $key) . ']',
                        $value
                    );
                } else {
                    $this->add_part($key, $value);
                }
            }
        }
    }

    /**
     * Add new payload part.
     *
     * @param string $key Part name.
     * @param any $value Part value.
     */
    public function add_part($key, $value)
    {
        $this->_add_part_header();
        $this->_data .=
            'Content-Disposition: form-data; name="' . $key . '"' . self::EOL;
        $this->_data .= self::EOL;
        $this->_data .= $value . self::EOL;
    }

    /**
     * Add file to the payload.
     *
     * @param string $key Part name.
     * @param string $filename File name.
     * @param string $type File type.
     * @param string|null $content File content.
     */
    public function add_file($key, $filename, $type, $content = null)
    {
        $this->_add_part_header();
        $this->_data .=
            'Content-Disposition: form-data; name="' .
            $key .
            '"; filename="' .
            basename($filename) .
            '"' .
            self::EOL;
        $this->_data .= 'Content-Type: ' . $type . self::EOL;
        $this->_data .= 'Content-Transfer-Encoding: binary' . self::EOL;
        $this->_data .= self::EOL;
        if (!$content) {
            $this->_data .= file_get_contents($filename) . self::EOL;
        } else {
            $this->_data .= $content . self::EOL;
        }
    }

    /**
     * Get bounded mime content type.
     *
     * @return string Mime content type.
     */
    public function content_type()
    {
        return 'multipart/form-data; boundary=' . $this->_mime_boundary;
    }

    /**
     * Get content data.
     *
     * @return string Content data.
     */
    public function data()
    {
        // add the final content boundary
        return $this->_data .=
            '--------' . $this->_mime_boundary . '--' . self::EOL . self::EOL;
    }

    /**
     * Decodes multipart/form-data payloads and returns array of field descriptiors.
     *
     * @return array Field descriptors.
     */
    public function decode()
    {
        $fields = [];

        $lines = preg_split('/' . self::EOL_RE . '/', $this->_data);
        $name = null;
        $filename = null;
        $content_type = null;
        $value = '';
        $buffering = false;
        foreach ($lines as $line) {
            if (empty(trim($line))) {
                if ($name !== null) {
                    echo 'bufferring' . self::EOL;
                    $buffering = true;
                }
                continue;
            }

            if (
                preg_match(
                    '/^--+' .
                        $this->_mime_boundary .
                        '-*' .
                        self::EOL_RE .
                        '?/',
                    $line
                )
            ) {
                if ($name) {
                    if ($filename && $content_type === null) {
                        $content_type = 'application/octet-stream';
                    }
                    $fields[] = [
                        'name' => $name,
                        'filename' => $filename,
                        'content-type' => $content_type,
                        'value' => $value,
                    ];
                    $name = null;
                    $filename = null;
                    $content_type = null;
                    $value = '';
                    $buffering = false;
                }
                continue;
            }

            if ($buffering) {
                $value .= $line . self::EOL;
            }

            if (
                $name === null &&
                preg_match('/name="((?:.(?!"))+.)"/', $line, $match)
            ) {
                $name = $match[1];

                if (preg_match('/filename="((?:.(?!"))+.)"/', $line, $match)) {
                    $filename = $match[1];
                }
            }

            if ($filename) {
                if (preg_match('/Content-Type\s*\:([^;]+)/i', $line, $match)) {
                    $content_type = trim($match[1]);
                }
            }
        }

        return $fields;
    }
}
