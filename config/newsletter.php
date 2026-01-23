<?php

return [
    'provider' => env('NEWSLETTER_PROVIDER', 'smtp'),
    'from_email' => env('NEWSLETTER_FROM_EMAIL', env('MAIL_FROM_ADDRESS')),
    'from_name' => env('NEWSLETTER_FROM_NAME', env('MAIL_FROM_NAME')),
    'double_opt_in' => env('NEWSLETTER_DOUBLE_OPT_IN', true),
    'unsubscribe_base_url' => env('APP_URL', 'http://localhost'),
];
