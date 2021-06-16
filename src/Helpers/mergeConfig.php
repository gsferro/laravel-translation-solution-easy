<?php

use Illuminate\Support\Arr;

if (!function_exists('mergeConfig')) {
    /**
     * Merges the configs together and takes multi-dimensional arrays into account.
     *
     * @param array $original
     * @param array $merging
     * @return array
     */
    function mergeConfig(array $original, array $merging)
    {
        $array = array_merge($merging, $original);
        dump($array);

        foreach ($original as $key => $value) {
            if (!is_array($value)) {
                continue;
            }

            if (!Arr::exists($merging, $key)) {
                continue;
            }

            if (is_numeric($key)) {
                continue;
            }

            $array[ $key ] = mergeConfig($value, $merging[ $key ]);
        }

        return $array;
    }
}