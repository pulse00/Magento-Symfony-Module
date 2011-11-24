<?php

use Symfony\Component\DependencyInjection\ContainerAware;
/**
 * 
 * Trivial singleton to get access to Symfony services.
 * 
 * @author Robert Gruendler <r.gruendler@gmail.com>
 *
 */
class Symfony_Core_DependencyInjection_Container extends ContainerAware {
    
    private $kernel;        
    
    public function setKernel($kernel) {
        
        $this->kernel = $kernel;
        $this->setContainer($kernel->getContainer());
        
    }
        
    public function get($service) {
        
        return $this->container->get($service);
        
    }

}
