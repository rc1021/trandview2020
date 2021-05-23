<?php

for ($i = 1; isset($hosts[$i - 1]); $i++) {
    if (isset($_ENV['AUTH_TYPE'])) {
        $cfg['Servers'][$i]['auth_type'] = $_ENV['AUTH_TYPE'];
    }
}
