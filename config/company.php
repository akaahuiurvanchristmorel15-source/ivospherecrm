<?php

return [
    'phone' => env('COMPANY_PHONE', null),
    'whatsapp' => env('COMPANY_WHATSAPP', null),
    'email' => env('COMPANY_EMAIL', env('MAIL_FROM_ADDRESS', 'contact@ivosphere.com')),
    'address' => env('COMPANY_ADDRESS', null),
];
