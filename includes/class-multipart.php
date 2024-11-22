<?php

namespace HTTP_BRIDGE;

if (!defined('ABSPATH')) {
    exit;
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

    /**
     * Creates a random mime boundary.
     */
    public function __construct()
    {
        $this->_mime_boundary = md5(microtime(true));
    }


    /**
     * Add part header boundary
     */
    private function _add_part_header()
    {
        $this->_data .= '--' . $this->_mime_boundary . self::EOL;
    }

    /**
     * Encode array data as multipart text.
     *
     * @param array<string|int, mixed> $data Input data.
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
                    $this->add_part($prefix . '[' . (is_numeric($key) ? '' : $key) . ']', $value);
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
        $this->_data .= 'Content-Disposition: form-data; name="' . $key . '"' . self::EOL;
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
        $this->_data .= 'Content-Disposition: form-data; name="' . $key . '"; filename="' . basename($filename) . '"' . self::EOL;
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
     * @return string $content_type Mime content type.
     */
    public function content_type()
    {
        return 'multipart/form-data; boundary=' . $this->_mime_boundary;
    }

    /**
     * Get content data.
     *
     * @return string $data Content data.
     */
    public function data()
    {
        // add the final content boundary
        return $this->_data .= '--' . $this->_mime_boundary . '--' . self::EOL . self::EOL;
    }
}
