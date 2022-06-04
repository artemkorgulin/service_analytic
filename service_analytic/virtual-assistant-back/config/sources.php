<?php

return [
    'input_parsing_ftp_host' => env('RUFAGO_INPUT_FILES_FTP_HOST'),
    'input_parsing_ftp_user' => env('RUFAGO_INPUT_FILES_FTP_USER'),
    'input_parsing_ftp_pass' => env('RUFAGO_INPUT_FILES_FTP_PASS'),

    'output_parsing_ftp_host' => env('RUFAGO_OUTPUT_FILES_FTP_HOST'),
    'output_parsing_ftp_user' => env('RUFAGO_OUTPUT_FILES_FTP_USER'),
    'output_parsing_ftp_pass' => env('RUFAGO_OUTPUT_FILES_FTP_PASS'),

    'parser_local_path' => env('PARSER_PATH', 'parser/'),
    'root_queries_file' => 'root_queries_to_parsing/ozon_seller_root_queries.csv',
    'search_queries_input_path' => 'root_queries_results_from_parsing/',
    'search_queries_weekly_input_path' => 'search_queries_results_from_parsing/',
    'search_queries_weekly_output_file' => 'search_queries_to_parsing/ozon_seller_search_queries.csv',
    'search_queries_weekly_error_file' => 'search_queries_to_parsing/errors.csv',
];
