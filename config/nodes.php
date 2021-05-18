<?php

return [

    'type' => env('NODE_TYPE'),

    'webhooks' => array_filter(explode(';', env('NODE_WEBHOOK', ''))),

];
