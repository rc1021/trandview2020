<?php

return [

    'type' => env('NODE_TYPE', 'slave'),

    'webhooks' => array_filter(explode(';', env('NODE_WEBHOOK', ''))),

];
