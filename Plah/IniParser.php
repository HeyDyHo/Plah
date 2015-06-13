<?php

namespace Plah;

class IniParser extends Singleton
{
    /**
     * Parse ini file and return values as array.
     *
     * @param string $file
     * @return array
     */
    public function parse($file)
    {
        $data = array();

        if (is_file($file)) {
            $data = parse_ini_file($file);
            if ($data === false) {
                $data = array();
            }
        }

        return $data;
    }
}
