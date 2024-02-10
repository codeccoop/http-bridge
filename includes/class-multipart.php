<?php

namespace WPCT_HB;

class Multipart
{
    const EOL = "\r\n";

    private $_data = '';
    private $_mime_boundary;

    public function __construct()
    {
        $this->_mime_boundary = md5(microtime(true));
    }

    private function _add_part_header()
    {
        $this->_data .= '--' . $this->_mime_boundary . self::EOL;
    }

    public function add_array($data, $prefix = '')
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if ($prefix)
                    $this->add_array($value, $prefix . '[' . $key . ']');
                else
                    $this->add_array($value, $key);
            } else {
                if ($prefix)
                    $this->add_part($prefix . '[' . (is_numeric($key) ? '' : $key) . ']', $value);
                else
                    $this->add_part($key, $value);
            }
        }
    }

    public function add_part($key, $value)
    {
        $this->_add_part_header();
        $this->_data .= 'Content-Disposition: form-data; name="' . $key . '"' . self::EOL;
        $this->_data .= self::EOL;
        $this->_data .= $value . self::EOL;
    }

    public function add_file($key, $filename, $type, $content = null)
    {
        $this->_add_part_header();
        $this->_data .= 'Content-Disposition: form-data; name="' . $key . '"; filename="' . basename($filename) . '"' . self::EOL;
        $this->_data .= 'Content-Type: ' . $type . self::EOL;
        $this->_data .= 'Content-Transfer-Encoding: binary' . self::EOL;
        $this->_data .= self::EOL;
        if (!$content) $this->_data .= file_get_contents($filename) . self::EOL;
        else $this->_data .= $content . self::EOL;
    }

    public function content_type()
    {
        return 'multipart/form-data; boundary=' . $this->_mime_boundary;
    }

    public function data()
    {
        // add the final content boundary
        return $this->_data .= '--' . $this->_mime_boundary . '--' . self::EOL . self::EOL;
    }
}
