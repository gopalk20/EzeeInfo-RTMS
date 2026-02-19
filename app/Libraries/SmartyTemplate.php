<?php

namespace App\Libraries;

use Smarty;

/**
 * Smarty Template Library wrapper for CodeIgniter 4
 */
class SmartyTemplate
{
    protected $smarty;

    public function __construct()
    {
        // Include Smarty autoloader
        if (file_exists(ROOTPATH . 'vendor/smarty/libs/Smarty.class.php')) {
            require_once ROOTPATH . 'vendor/smarty/libs/Smarty.class.php';
        } else {
            throw new \Exception('Smarty library not found');
        }

        // Initialize Smarty
        $this->smarty = new Smarty();

        // Set template and compile directories
        $this->smarty->setTemplateDir(APPPATH . '/Views/');
        $this->smarty->setCompileDir(WRITEPATH . '/smarty/compile/');
        $this->smarty->setCacheDir(WRITEPATH . '/smarty/cache/');
        $this->smarty->setPluginsDir(APPPATH . '/Plugins/');

        // Set caching options (0 = no caching, 1 = caching enabled)
        $this->smarty->caching = 0;

        // Enable debugging when in development
        if (ENVIRONMENT === 'development') {
            $this->smarty->debugging = false;
        }
    }

    /**
     * Assign variable to template
     */
    public function assign($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->smarty->assign($k, $v);
            }
        } else {
            $this->smarty->assign($key, $value);
        }
        return $this;
    }

    /**
     * Render template
     */
    public function render($template)
    {
        return $this->smarty->fetch($template . '.tpl');
    }

    /**
     * Get Smarty instance for advanced usage
     */
    public function getSmartyInstance()
    {
        return $this->smarty;
    }
}
