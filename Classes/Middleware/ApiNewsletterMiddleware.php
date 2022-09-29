<?php

namespace Mirko\Newsletter\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ApiNewsletterMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->hasHeader('content-type') && $request->getHeader('content-type')[0] == 'json') {
            $request = $request->withAttribute('jsonRequest', true);
        }

        return $handler->handle($request);
    }
}