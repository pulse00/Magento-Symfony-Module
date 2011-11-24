<?php

class Symfony_Core_HttpKernel_Kernel {

    /** 
     * @var Symfony\Component\HttpKernel\Kernel $kernel
     */
    private $kernel = null;    
    
    public function bootstrap() {
                
        if ($this->kernel !== null)
            return;
        
        $config = Mage::getConfig();        
        $symfony = $config->getNode('global/symfony')->asArray();
        
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
        
        die('kernel');
        
    }
        

}
