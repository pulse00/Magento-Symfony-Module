<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;



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
        $alias = isset($symfony['alias']) ? $symfony['alias'] : null;
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
        $request = Symfony_Core_HttpFoundation_AliasRequest::createFromGlobals();
        $request->setAlias($alias);
        $container->set('request', $request);
        
        $mageContainer = Mage::getSingleton('Symfony_Core_DependencyInjection_Container');
        $mageContainer->setKernel($this->kernel);
        
        $security = $container->get('security.context');
        $dispatcher = $container->get('event_dispatcher');        
        
        try {
            // dispatch the kernel.request so the security contexts gets initialized properly            
            $event = new GetResponseEvent($kernel, $request, Symfony\Component\HttpKernel\HttpKernelInterface::MASTER_REQUEST);
            $dispatcher->dispatch(Symfony\Component\HttpKernel\KernelEvents::REQUEST, $event);
            
        } catch (Exception $e) {
            // the event will fail because the alias cannot be matched by the router, but the
            // security context is properly initialized
        }
        
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
        
        // initialize the Magento twig extension during a Symfony request
        \Mage::getSingleton('Symfony_Core_Templating_Engine');
        
    }
        
}
