<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    /**
     * @param list<string>|null $arguments Allowed role names (e.g. ['Manager', 'Super Admin'])
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        $userRole = $session->get('user_role');

        if (!$userRole) {
            return redirect()->to('/')->with('error', 'Please log in.');
        }

        $allowed = $arguments ?? [];
        if (empty($allowed)) {
            return $request;
        }

        if (!in_array($userRole, $allowed, true)) {
            return redirect()->back()->with('error', 'You do not have permission to access this page.');
        }

        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }
}
