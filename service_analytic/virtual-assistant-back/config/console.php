<?php

return [
    /**
     * Max coroutines count from console command
     */
    'max_console_coroutines' => env('MAX_CONSOLE_COROUTINES', 100),
    'max_coroutines_load_feature_options' => env('MAX_COROUTINES_LOAD_FEATURE_OPTIONS', 100),
    'max_coroutines_load_features' => env('MAX_COROUTINES_LOAD_FEATURES', 100),
    'disable_console_scheduler' => env('DISABLE_CONSOLE_SCHEDULER', false),
];
