<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace pocketmine\event;

class TranslationContainer extends TextContainer{

	
	protected $params = [];

	
	public function __construct($text, array $params = []){
		parent::__construct($text);

		$this->setParameters($params);
	}

	
	public function getParameters(){
		return $this->params;
	}

	
	public function getParameter($i){
		return isset($this->params[$i]) ? $this->params[$i] : null;
	}

	
	public function setParameter($i, $str){
		if($i < 0 or $i > count($this->params)){ 
			throw new \InvalidArgumentException("Invalid index $i, have " . count($this->params));
		}

		$this->params[(int) $i] = $str;
	}

	
	public function setParameters(array $params){
		$i = 0;
		foreach($params as $str){
			$this->params[$i] = (string) $str;

			++$i;
		}
	}
}