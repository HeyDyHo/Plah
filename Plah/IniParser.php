<?php

namespace Plah;

class IniParser extends Singleton
{
    /**
     * Parse ini file and return values as array.
     *
     * @param string $file
     * @param bool $sections
     * @return array
     */
    public function get($file, $sections = false)
    {
        $data = array();

        if (is_file($file)) {
            $data = parse_ini_file($file, $sections);
            if ($data === false) {
                $data = array();
            }
        }

        return $data;
    }
}
