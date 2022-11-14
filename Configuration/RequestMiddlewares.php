<?php

return [
    'backend' => [
        'middleware-apiNewsletter' => [
            'target' => \Mirko\Newsletter\Middleware\ApiNewsletterMiddleware::class,
        ],
    ],
];