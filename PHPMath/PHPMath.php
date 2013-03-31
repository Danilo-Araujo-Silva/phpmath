<?php
namespace PHPMath;

/**
 * Main class of PHPMath.
 */
class PHPMath
{
    /**
     * Property with the PHPMath root path.
     * @var string PHPMath root path.
     * @access private
     */
    private $rootPath;
    
    /**
     * Property with the PHPMath core path.
     * @var string PHPMath core path.
     * @access private
     */
    private $corePath;
    
    /**
     * Property with the PHPMath Backend path.
     * @var string PHPMath Backend path.
     * @access private
     */
    private $backendPath;
    
    /**
     * Property with the PHPMath Model path.
     * @var string PHPMath Model path.
     * @access private
     */
    private $modelPath;
    
    /**
     * Property with the PHPMath Config path.
     * @var string PHPMath Config path.
     * @access private
     */
    private $configPath;
    
    /**
     * Property with the PHPMath Mathematica Model path.
     * @var string PHPMath Mathematica Model path.
     */
    private $mathematicaPath;
    
    /**
     * Property with the configuration.
     * @var array Configuration.
     * @access private
     */
    private $config = array();
    
    /**
     * Property with the array of the errors catched.
     * @var array Array of errors catched.
     * @access private
     */
    private $errors = array();
    
    /**
     * Construct method.
     * @access public
     * @param array $configuration Array with a configuration.
     */
    public function __construct($configuration = null)
    {
        $this->setPaths();
        $this->configure($configuration);
    }
    
    /**
     * Set utils folder paths to the class.
     * @return boolean True if the paths was setted correctly.
     */
    public function setPaths ()
    {
        $this->rootPath = dirname(__FILE__)."/../../../../";
        $this->backendPath = "{$this->rootPath}Backend/";
        $this->modelPath = "{$this->backendPath}Model/";
        $this->mathematicaPath = "{$this->modelPath}Mathematica/";
        
        return true;
    }
    
    /**
     * Performs the configuration.
     * @access public
     * @static
     * @param array $configuration Configuration array.
     */
    public static function configure ($configuration = null)
    {
        if (empty($configuration)) {
            $this->config = $this->getConfig();
        } else {
            $this->setConfig($configuration);
        }
        
        $this->test();
    }
    
    /**
     * Get the configuration and return a array with the data.
     * @access public
     */
    public function getConfig ()
    {
        try {
            if (empty($this->config)) {
                $configPath = "{$this->configPath}Mathematica/Config.json";
                $configuration = json_decode($configPath, true);
            }
        } catch (Exception $exception) {
            $this->errors["config"]["mathematica"] = $exception->getMessage();
        }
    }
    
    /**
     * 
     * @param type $configuration
     */
    public function setConfig ($configuration)
    {   
        try {
            if ($this->isValidConfiguration($configuration)) {
                $this->config = $configuration;
            }
        } catch (Exception $exception) {
            echo $exception->getMessage();
        }
    }
    
    /**
     * 
     * @param type $configuration
     * @return boolean
     */
    public function isValidConfiguration ($configuration)
    {
        if (empty($configuration)) {
            $error = "You can't set a empty configuration";
            $this->errors["config"]["user"] = $error;
            
            trow \Exception($this->errors["config"]["user"]);
        }
        
        return true;
    }
    
    /**
     * 
     * @return boolean
     */
    public function test ()
    {
        $test = "Zeta[2]";
        $answer =
"Pi^2/6
";
        $result = $this->run($test);
        
        if ($result === $answer) {
            return true;
        } elseif (!empty($this->erros["run"])) {
            $notFoundLicense = "Mathematica cannot find a valid password";
            if (strpos($result, $notFoundLicense)) {
                echo "
                    Was not possible the find the Mathematica license.
                    <br>
                    Copy the Mathematica license folder to the PHP user home
                    (for example, /var/www/.Mathematica).
                    <br>
                    The Mathematica license usually can be found on a hidden folder
                    at the licensed user home (for example, /home/user/.Mathematica).
                    <br>
                    $result
                ";
            }            
        } else {
            echo $result;
        }
        
        return false;
    }
    
    /**
     * 
     * @param type $call
     * @return boolean
     */
    public function run ($call)
    {
        try {
            $completeCall = "{$this->config["Mathematica"]["Executable"]} '$call'";
            $return = shell_exec($completeCall);
            
            return $return;
        } catch (Exception $exception) {
            $this->errors["run"] = $exception->getMessage();
            
            return false;
        }
    }
}