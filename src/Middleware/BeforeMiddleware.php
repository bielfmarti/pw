<?php

namespace Pw\SlimApp\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

final class BeforeMiddleware
{
    public function __invoke(Request $request, RequestHandler $next): Response
    {
        $response = $next->handle($request);

        $existingContent = (string) $response->getBody();

        $response = new Response();
        $response->getBody()->write('BEFORE' . $existingContent);

        return $response;
    }
}