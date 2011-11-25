<?php

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

/**
 * 
 * Base block which can be used to render Magento templates using the
 * Symfony EngineInterface.
 * 
 * 
 * @author Robert Gruendler <r.gruendler@gmail.com>
 *
 */
class Symfony_Core_Block_Template extends Mage_Core_Block_Template implements Twig_ExtensionInterface {

    
    /** @var Symfony\Bundle\FrameworkBundle\Templating\EngineInterface $engine */
    protected $engine;
    
    public function __construct() {
        
        parent::_construct();
        
        $container = \Mage::getSingleton('Symfony_Core_DependencyInjection_Container');
        $this->engine = $container->get('templating');
        
        if (!$this->engine instanceof EngineInterface) {            
            throw new \Exception("Unable to load Symfony templating engine");            
        }
                
        $twig = $container->get('twig');        
        
        if ($twig instanceof  \Twig_Environment) {
            
            $twig->addExtension($this);
            $methods = get_class_methods($this);
            foreach ($methods as $method)
            {
                if('TwigExtension' == substr($method,-13))
                {
                    $function = substr($method, 0, strlen($method) - 13);
                    $twig->addFunction($function, new Twig_Function_Method($this,$method));
                }
            }
        }        
    }
    
    
    /**
     * Modified version of from https://github.com/huguesalary/Magento-Twig
     * to work with the symfony templating engine. 
     * 
     * Maybe this can be implemented in the Magento-Twig extension,
     * so it gets an Engine injected into the constructor?
     * 
     */
    public function fetchView($fileName) { 

        Varien_Profiler::start($fileName);
        

        extract ($this->_viewVars);
        $do = $this->getDirectOutput();
                
        if (!$do) {
            ob_start();
        }
        if ($this->getShowTemplateHints()) {
            echo '<div style="position:relative; border:1px dotted red; margin:6px 2px; padding:18px 2px 2px 2px; zoom:1;"><div style="position:absolute; left:0; top:0; padding:2px 5px; background:red; color:white; font:normal 11px Arial; text-align:left !important; z-index:998;" onmouseover="this.style.zIndex=\'999\'" onmouseout="this.style.zIndex=\'998\'" title="'.$fileName.'">'.$fileName.'</div>';
            if (self::$_showTemplateHintsBlocks) {
                $thisClass = get_class($this);
                echo '<div style="position:absolute; right:0; top:0; padding:2px 5px; background:red; color:blue; font:normal 11px Arial; text-align:left !important; z-index:998;" onmouseover="this.style.zIndex=\'999\'" onmouseout="this.style.zIndex=\'998\'" title="'.$thisClass.'">'.$thisClass.'</div>';
            }
        }
        
        try {            
            echo $this->engine->render($fileName, $this->_viewVars);
        } catch (Exception $e) {
            ob_get_clean();
            throw $e;
        }

        if ($this->getShowTemplateHints()) {
            echo '</div>';
        }
        
        if (!$do) {
            $html = ob_get_clean();
        } else {
            $html = '';
        }
        
        Varien_Profiler::stop($fileName);
        return $html;        
        
    }
    
    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'TwigExtension-'.get_class($this);
    }
    
    /**
     * Initializes the runtime environment.
     *
     * This is where you can load some file that contains filter functions for instance.
     *
     * @param Twig_Environment $environment The current Twig_Environment instance
     */
    public function initRuntime(Twig_Environment $environment)
    {
    }
    
    /**
     * Returns the token parser instances to add to the existing list.
     *
     * @return array An array of Twig_TokenParserInterface or Twig_TokenParserBrokerInterface instances
     */
    public function getTokenParsers()
    {
        return array();
    }
    
    /**
     * Returns the node visitor instances to add to the existing list.
     *
     * @return array An array of Twig_NodeVisitorInterface instances
     */
    public function getNodeVisitors()
    {
        return array();
    }
    
    /**
     * Returns a list of filters to add to the existing list.
     *
     * @return array An array of filters
     */
    public function getFilters()
    {
        return array();
    }
    
    /**
     * Returns a list of tests to add to the existing list.
     *
     * @return array An array of tests
     */
    public function getTests()
    {
        return array();
    }
    
    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array();
    }
    
    /**
     * Returns a list of operators to add to the existing list.
     *
     * @return array An array of operators
     */
    public function getOperators()
    {
        return array();
    }
    
    /**
     * Returns a list of global functions to add to the existing list.
     *
     * @return array An array of global functions
     */
    public function getGlobals()
    {
        return array();
    }
    
    
}
