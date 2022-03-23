<?php
return [
    'access_key_id'     => env('ALIYUN_ACCESS_KEY_ID'),
    'access_key_secret' => env('ALIYUN_ACCESS_KEY_SECRET'),

    /**
     * https://help.aliyun.com/document_detail/29008.html
     */
    'sls_endpoint'      => env('SLS_ENDPOINT'),
    'sls_project'       => env('SLS_PROJECT'),
    'sls_store'         => env('SLS_STORE', ''),
    'sls_topic'         => env('SLS_TOPIC'),
    'ding_token'        => env('DING_TOKEN'),
];
