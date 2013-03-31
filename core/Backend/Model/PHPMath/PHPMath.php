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
     * @access private
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
        $this->setRootPath();
        $this->corePath = "{$this->rootPath}core/";
        $this->backendPath = "{$this->corePath}Backend/";
        $this->modelPath = "{$this->corePath}Model/";
        $this->mathematicaPath = "{$this->modelPath}Mathematica/";
        $this->configPath = "{$this->backendPath}Config/";
        
        return true;
    }
    
    /**
     * Performs the configuration.
     * @access public
     * @param array $configuration Configuration array.
     * @return boolean True if the configuration was done.
     */
    public function configure ($configuration = null)
    {
        if (empty($configuration)) {
            $this->config = $this->getConfig();
        } else {
            $this->setConfig($configuration);
        }
        
        $mathematicaExecutablePath = $this->config["Mathematica"]["Executable"];
        if (!$this->isExecutable($mathematicaExecutablePath)) {
            if (!$this->makeReadableAndExecutable($mathematicaExecutablePath)) {
                $exception = "'{$this->config["Mathematica"]["Executable"]}' should be readable and executable.";
                throw new \Exception($exception);
            }
        }
        
        if ($this->test()) {
            return true;
        } else {
            return false;
        }        
    }
    
    /**
     * Get the configuration and return a array with the data.
     * @access public
     * @return array Array with the configuration.
     */
    public function getConfig ()
    {
        try {
            if (empty($this->config)) {
                $configPath = "{$this->configPath}Mathematica/Config.json";
                $configurationContent = file_get_contents($configPath);
                $configuration = json_decode($configurationContent, true);
                
                if ($this->isValidConfiguration($configuration)) {
                    $configuration["Mathematica"]["Executable"] = 
                        "{$this->rootPath}{$configuration["Mathematica"]["Executable"]}";
                    
                    return $configuration;
                } else {
                    throw new \Exception("Invalid configuration");                    
                }
                
                return $configuration;                
            }
        } catch (Exception $exception) {
            $this->errors["config"]["mathematica"] = $exception->getMessage();
            
            return false;
        }
    }
    
    /**
     * Set the configuration.
     * @param array $configuration Array with the configuration.
     * @return boolean True if the configuration was setted.
     */
    public function setConfig ($configuration)
    {   
        try {
            if ($this->isValidConfiguration($configuration)) {
                $this->config = $configuration;
            }
            
            return true;
        } catch (Exception $exception) {
            echo $exception->getMessage();
            
            return false;
        }
    }
    
    /**
     * Checks if a array passed is a valid configuration.
     * @param array $configuration Array with the configuration.
     * @return boolean True if a valid configuration was passed.
     */
    public function isValidConfiguration ($configuration)
    {
        if (empty($configuration)) {
            $error = "You can't set a empty configuration";
            $this->errors["config"]["user"] = $error;
            
            throw new \Exception($this->errors["config"]["user"]);
            
            return false;
        }
        
        return true;
    }
    
    /**
     * Test if is possible do math with Mathematica.
     * @return boolean True if is possible do math with Mathematica.
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
        } elseif (!empty($this->errors["run"])) {
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
                
                return false;
            }            
        } else {
            echo $result;
            
            return false;
        }     
    }
    
    /**
     * Run Mathematica functions.
     * @param string $call Call the Mathematica execute.
     * @return string Result of the calculation.
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
    
    /**
     * Set the PHPMath root path.
     * @return boolean True if the root path was setted.
     */
    public function setRootPath()
    {
        $filePath = dirname(__FILE__);
        $pathArray = explode("/", $filePath);
        array_pop($pathArray);
        array_pop($pathArray);
        array_pop($pathArray);
        array_pop($pathArray);
        
        $rootPath = implode("/", $pathArray)."/";
        $this->rootPath = $rootPath;
        
        return true;
    }
    
    /**
     * Checks if the file of the string passed is readable and executable.
     * @param string $filePath File path.
     * @return boolean True if the file of the string passed is readable and 
     *  executable.
     */
    public function isExecutable($filePath)
    {
        if (!is_readable($filePath)) {
            return false;
        } elseif (!is_executable($filePath)) {
            return false;
        } else {
            return true;
        }
    }
    
    /**
     * Make the file pointed readable and executable (to user, group and others).
     * @param string $filePath File path.
     * @return boolean True if the file pointed by the $filePath became readable
     *  and executable.
     */
    public function makeReadableAndExecutable($filePath)
    {
        try {
            if (chmod($filePath, 0755)) {
                return true;
            } else {
                $exception = "Was not possible make '$filePath' readable and executable.";
                throw new \Exception($exception);                
            }     
        } catch (Exception $exception) {
            $this->errors["run"] = $exception->getMessage();
            
            return false;
        }
    }
}