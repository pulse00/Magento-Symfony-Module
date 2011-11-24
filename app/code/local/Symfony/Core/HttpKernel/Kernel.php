<?php

use Symfony\Component\HttpFoundation\Request;

/**
 * 
 * Observer for the controller_front_init_before Magento event.
 * 
 * Bootstraps the Symfony Kernel of your app.
 * 
 * 
 * @author Robert Gruendler <r.gruendler@gmail.com>
 *
 */
class Symfony_Core_HttpKernel_Kernel {

    /** 
     * @var Symfony\Component\HttpKernel\Kernel $kernel
     */
    private $kernel = null;    
    
    public function bootstrap() {

        // symfony is already running
        if ($this->kernel !== null || class_exists("Symfony\Component\HttpKernel\Kernel"))
            return;
                
        $symfony =  Mage::getConfig()->getNode('global/symfony')->asArray();
        
        $kernelClass = $symfony['kernel'];
        $base = $symfony['app_dir'];        
        $placeHolder = "{{root_dir}}";        
        
        if (strpos($base, $placeHolder) !== false) {            
            $base = str_replace($placeHolder, Mage::getRoot(), $base);
        }
        
        $env =  $symfony['env'];        
        $kernelFile = $base . DIRECTORY_SEPARATOR . $kernelClass . '.php';
        $bootstrapFile = $base . DIRECTORY_SEPARATOR . 'bootstrap.php.cache';
        
        if (!file_exists($kernelFile)) {
            throw new \Exception("Unable to load the Symfony Kernel from " . $kernelFile);
        }

        require_once $bootstrapFile;
        require_once $kernelFile;
        
        $this->kernel = new $kernelClass($env, false);
        $this->kernel->loadClassCache();
        $this->kernel->boot();
        
        // enter the request scope
        $container = $this->kernel->getContainer();
        $container->enterScope('request');        
        $request = Request::createFromGlobals();
        $container->set('request', new Request());
        
        $mageContainer = Mage::getSingleton('Symfony_Core_DependencyInjection_Container');
        $mageContainer->setKernel($this->kernel);
        
    }
    

    /**
     * 
     * Handle the symfony_on_kernel_request event fired by Symfony to set the 
     * service container. 
     * 
     * @param Varien_Event_Observer $event
     */
    public function onKernelRequestInit(Varien_Event_Observer $event) {
        
        $data = $event->getData();        
        $container = Mage::getSingleton('Symfony_Core_DependencyInjection_Container');
        $container->setContainer($data['container']);
        
    }
        
}
