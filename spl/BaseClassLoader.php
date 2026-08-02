<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

class BaseClassLoader extends \Threaded implements ClassLoader{

    
    private $parent;
    
    private $lookup;
    
    private $classes;

    
    public function __construct(ClassLoader $parent = null){
        $this->parent = $parent;
        $this->lookup = new \Threaded;
        $this->classes = new \Threaded;
    }

    
    public function addPath($path, $prepend = false){

        foreach($this->lookup as $p){
            if($p === $path){
                return;
            }
        }

        if($prepend){
			$this->synchronized(function($path){
				$entries = $this->getAndRemoveLookupEntries();
				$this->lookup[] = $path;
				foreach($entries as $entry){
					$this->lookup[] = $entry;
				}
			}, $path);
        }else{
            $this->lookup[] = $path;
        }
    }
    
    protected function getAndRemoveLookupEntries(){
		$entries = [];
		while($this->count() > 0){
			$entries[] = $this->shift();
		}
		return $entries;
	}

    
    public function removePath($path){
        foreach($this->lookup as $i => $p){
            if($p === $path){
                unset($this->lookup[$i]);
            }
        }
    }

    
    public function getClasses(){
		$classes = [];
		foreach($this->classes as $class){
			$classes[] = $class;
		}
        return $classes;
    }

    
    public function getParent(){
        return $this->parent;
    }

    
    public function register($prepend = false){
        spl_autoload_register([$this, "loadClass"], true, $prepend);
    }

    
    public function loadClass($name){
        $path = $this->findClass($name);
        if($path !== null){
            include($path);
            if(!class_exists($name, false) and !interface_exists($name, false) and !trait_exists($name, false)){
	            if($this->getParent() === null){
		            throw new ClassNotFoundException("Class $name not found");
	            }
                return false;
            }

	        if(method_exists($name, "onClassLoaded") and (new ReflectionClass($name))->getMethod("onClassLoaded")->isStatic()){
		        $name::onClassLoaded();
	        }
	        
	        $this->classes[] = $name;

            return true;
        }elseif($this->getParent() === null){
	        throw new ClassNotFoundException("Class $name not found");
        }

        return false;
    }

    
    public function findClass($name){
        $components = explode("\\", $name);

        $baseName = implode(DIRECTORY_SEPARATOR, $components);

        foreach($this->lookup as $path){
            if(PHP_INT_SIZE === 8 and file_exists($path . DIRECTORY_SEPARATOR . $baseName . "__64bit.php")){
                return $path . DIRECTORY_SEPARATOR . $baseName . "__64bit.php";
            }elseif(PHP_INT_SIZE === 4 and file_exists($path . DIRECTORY_SEPARATOR . $baseName . "__32bit.php")){
                return $path . DIRECTORY_SEPARATOR . $baseName . "__32bit.php";
            }elseif(file_exists($path . DIRECTORY_SEPARATOR . $baseName . ".php")){
                return $path . DIRECTORY_SEPARATOR . $baseName . ".php";
            }
        }

        return null;
    }
}
