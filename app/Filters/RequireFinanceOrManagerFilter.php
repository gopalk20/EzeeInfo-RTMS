<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RequireFinanceOrManagerFilter extends RoleFilter
{
    public function before(RequestInterface $request, $arguments = null)
    {
        return parent::before($request, ['Finance', 'Manager']);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }
}
