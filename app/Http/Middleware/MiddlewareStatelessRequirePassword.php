<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Auth\Middleware\RequirePassword;
use PragmaRX\Google2FALaravel\MiddlewareStateless;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Exception;

class MiddlewareStatelessRequirePassword
{
    protected $responseFactory, $urlGenerator;

    public function __construct(ResponseFactory $responseFactory, UrlGenerator $urlGenerator)
    {
        $this->responseFactory = $responseFactory;
        $this->urlGenerator = $urlGenerator;
    }

    public function handle($request, Closure $next)
    {
        $secret_name = config('google2fa.otp_secret_column');
        if(!empty($request->user()->{$secret_name}))
            return app(MiddlewareStateless::class)->handle($request, function ($request) use ($next) {
                return $next($request);
            });
        else {
            $rpm = new RequirePassword($this->responseFactory, $this->urlGenerator, 1);
            return $rpm->handle($request, function ($request) use ($next) {
                return $next($request);
            });
        }
    }
}
