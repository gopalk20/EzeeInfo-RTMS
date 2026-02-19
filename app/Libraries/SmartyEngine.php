<?php

namespace App\Libraries;

/**
 * Smarty Template Engine Library for CodeIgniter 4
 * 
 * This library provides a simple wrapper for Smarty 5.x template engine,
 * enabling easy integration with CodeIgniter 4 controllers.
 */
class SmartyEngine
{
    protected object $smarty;

    public function __construct()
    {
        // Calculate paths relative to this file
        $appDir = dirname(__DIR__);  // Go up to app/
        $rootDir = dirname($appDir); // Go up to root
        
        // Load Smarty library
        $smartyPath = $rootDir . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'smarty' . DIRECTORY_SEPARATOR . 'libs' . DIRECTORY_SEPARATOR . 'Smarty.class.php';
        
        if (!file_exists($smartyPath)) {
            throw new \RuntimeException("Smarty library not found: {$smartyPath}");
        }
        
        require_once $smartyPath;
        
        // Initialize Smarty 5.x (uses Smarty\Smarty namespace)
        try {
            $this->smarty = new \Smarty\Smarty();
        } catch (\Exception $e) {
            throw new \RuntimeException("Failed to load Smarty: " . $e->getMessage());
        }
        
        $this->configure();
    }

    /**
     * Configure Smarty directories and settings
     */
    private function configure(): void
    {
        try {
            // Calculate paths relative to this file
            $appDir = dirname(__DIR__);      // app/
            $rootDir = dirname($appDir);     // project root
            $writableDir = $rootDir . DIRECTORY_SEPARATOR . 'writable' . DIRECTORY_SEPARATOR;
            
            // Set template directories
            $templatePath = $appDir . DIRECTORY_SEPARATOR . 'templates';
            $compilePath = $writableDir . 'smarty_compile' . DIRECTORY_SEPARATOR;
            $cachePath = $writableDir . 'smarty_cache' . DIRECTORY_SEPARATOR;
            $configPath = $appDir . DIRECTORY_SEPARATOR . 'smarty_config' . DIRECTORY_SEPARATOR;
            
            // Ensure directories exist
            if (!is_dir($compilePath)) {
                @mkdir($compilePath, 0755, true);
            }
            if (!is_dir($cachePath)) {
                @mkdir($cachePath, 0755, true);
            }
            
            // Configure Smarty
            $this->smarty->setTemplateDir($templatePath);
            $this->smarty->setCompileDir($compilePath);
            $this->smarty->setCacheDir($cachePath);
            $this->smarty->setConfigDir($configPath);
            
            // Set Smarty configuration
            $this->smarty->caching = false;
            $this->smarty->cache_lifetime = 3600;
            
            // Check if ENVIRONMENT constant is defined
            if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
                $this->smarty->force_compile = true;
            }
        } catch (\Exception $e) {
            throw new \RuntimeException("Smarty configuration failed: " . $e->getMessage());
        }
    }

    /**
     * Assign a variable to the template
     *
     * @param string|array $key Variable name or array of variables
     * @param mixed $value Variable value (ignored if $key is array)
     * @return $this
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
     * Render and return template content
     *
     * @param string $template Template file name
     * @param array $data Additional data to assign
     * @return string Rendered HTML
     */
    public function render(string $template, array $data = []): string
    {
        if (!empty($data)) {
            $this->assign($data);
        }
        return $this->smarty->fetch($template);
    }

    /**
     * Display template directly  
     *
     * @param string $template Template file name
     * @param array $data Additional data to assign
     * @return void
     */
    public function display(string $template, array $data = []): void
    {
        if (!empty($data)) {
            $this->assign($data);
        }
        $this->smarty->display($template);
    }

    /**
     * Get the underlying Smarty instance
     *
     * @return object Smarty instance
     */
    public function getInstance()
    {
        return $this->smarty;
    }
}
