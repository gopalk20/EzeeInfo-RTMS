<?php

namespace App\Controllers;

use App\Libraries\SmartyEngine;
use App\Models\MilestoneModel;
use App\Models\ProductModel;

class MilestoneController extends BaseController
{
    protected $helpers = ['form'];

    public function index()
    {
        $session = session();
        $userId = (int) $session->get('user_id');

        $productModel = new ProductModel();
        $products = $productModel->getProductsForUser($userId);

        $milestoneModel = new MilestoneModel();
        $allMilestones = [];
        foreach ($products as $p) {
            $ms = $milestoneModel->getByProduct($p['id']);
            foreach ($ms as $m) {
                $m['product_name'] = $p['name'];
                $allMilestones[] = $m;
            }
        }

        $smarty = new SmartyEngine();
        return $smarty->render('milestones/list.tpl', [
            'title'          => 'Milestones',
            'milestones'     => $allMilestones,
            'products'       => $products,
            'user_email'     => $session->get('user_email'),
            'user_role'      => $session->get('user_role'),
            'is_super_admin'=> $session->get('user_role') === 'Super Admin',
            'success'        => $session->getFlashdata('success'),
            'error'          => $session->getFlashdata('error'),
        ]);
    }
}
