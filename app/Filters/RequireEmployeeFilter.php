<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RequireEmployeeFilter extends RoleFilter
{
    public function before(RequestInterface $request, $arguments = null)
    {
        return parent::before($request, ['Employee']);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }
}
