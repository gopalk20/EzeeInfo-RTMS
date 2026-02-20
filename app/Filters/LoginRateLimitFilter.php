<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Throttle\ThrottlerInterface;

/**
 * Rate limit login attempts (FR-032).
 * Only applies to POST (login); GET passes through.
 */
class LoginRateLimitFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (strtoupper($request->getMethod()) !== 'POST') {
            return;
        }

        $throttler = \Config\Services::throttler();
        $ip = $request->getIPAddress();
        $key = 'login_' . md5($ip); // IPv6 contains : which is reserved in cache keys

        // 5 attempts per minute per IP
        if ($throttler->check($key, 5, 60) === false) {
            return redirect()->to('/')
                ->with('error', 'Too many login attempts. Please try again in a minute.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
