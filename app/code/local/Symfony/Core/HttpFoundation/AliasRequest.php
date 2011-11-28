<?php

use Symfony\Component\HttpFoundation\Request;

class Symfony_Core_HttpFoundation_AliasRequest extends Request
{    
    protected $alias = null;
    
    public function setAlias($alias) {
        
        $this->alias = $alias;
        
    }
        
    protected function prepareBaseUrl() 
    {        
        $baseUrl = parent::prepareBaseUrl();        
       
        if (null === $this->alias) { 
            return $baseUrl;
        }
        
        if ($baseUrl == $this->alias) {
            return "";
        } else if (strpos($baseUrl, $this->alias) === 0) {            
            $baseUrl = str_replace($this->alias, "/", $baseUrl);
        }
        
        return $baseUrl;

    }    
}