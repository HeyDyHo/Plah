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
            $fh = fopen($file, 'r');
            while ($l = fgets($fh)) {
                if (!preg_match('/^#/', $l)) {
                    if (preg_match('/^(.*?)=(.*?)$/', $l, $found)) {
                        $data[$found[1]] = $found[2];
                    }
                }
            }
            fclose($fh);
        }

        return $data;
    }
}
