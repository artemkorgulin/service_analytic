<?php

namespace App\Console\Commands;

trait AcceptsCommaSeparatedArrayOptions
{

    /**
     * @param  string  $optionName
     *
     * @return string[]
     */
    private function getArrayOption(string $optionName): array
    {
        return array_filter(explode(',', $this->option($optionName)));
    }
}
