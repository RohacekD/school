<?php

/**
 * Samozdřejmě mohlo být cachování mnohem rozsáhlejší a mohl jsem používat cache pro menší maximum a 
 * podle toho pak měnit síto ovšem potom bych asi moc odskočil od zadaného algoritmu...
 * Toto cachvání jde vypnout a tak myslím, že jsem zadání dodržel. 
 * Plně funguje na PHP 5+, prohlížeči podporující HTML5, ovšem by mě fungovat i na starším standardu.
 * 
 * 
 * Úspora času byla při limitu: 500000
 * Bez cache: 						3,9492259025574 ms (ani neukládal jen vypočítal)
 * S cache:  						3,9842281341553 ms (ukládal do cache)
 * S cahce s nacachovanými výpočty:	0,091004848480225 ms
 * 					
 * Ukládání zabralo:				0,0350022315979 ms
 * Úspora je:						3,893223285675075 ms
 * 
 */



/**
 * 
 * Class for cache serialized data into file system.
 * @author Ratan
 *
 */
class Cache {
	private $path;
	
	/**
	 * 
	 * Default constructor of Cache. Get string path to folder where cached data as parameter.
	 * @param string $path
	 */
	public function __construct($path) {
		$this->path=$path;
		if(!is_dir($this->path)) {
			mkdir($path);
		}
	}
	
	/**
	 * 
	 * Load data from cache.
	 * @param int $number
	 */
	public function load($number) {
		return unserialize(file_get_contents($this->path."/".$number.".txt"));
	}
	
	/**
	 * 
	 * Save calculated data into cache.
	 * @param int $number
	 * @param array $data
	 */
	public function save($number, $data) {
		file_put_contents($this->path."/".$number.".txt", serialize($data));
	}
	
	/**
	 * 
	 * Pokud existuje pro tento limit už vypočítaná varianta vrací true.
	 * @param int $number jaký je limit pro maximum do kterého počítáme
	 */
	public function exists($number) {
		if(file_exists($this->path."/".$number.".txt")) {
			return true;
		}
		return false;
	}
};

/**
 * 
 * Calculated all prime number lower than limit.
 * Algorythm based on eratosthenes sieve and could cache it's solutions.
 * @author Ratan
 *
 */
class Eratos {
		private $maximum;/**< Limit of prime number */
		private $solution = array();/**< Calculated prime numbers */
		private $numbers = array();/**< Numbers for test */
		/**
		 * 
		 * Instance for caching solutions as parameters.
		 * @var Cahce
		 */
		private $cache;
		
		/**
		 * 
		 * Default constructor for Eratos. Get maxumum prime number and if you want instance for caching solutions as parameters.
		 * Cache can be switch off by pass null or nothing as second parameter.
		 * @param int $maximum
		 * @param Cache $cache
		 */
		public function __construct($maximum, $cache = null) {
			$this->maximum=$maximum;
			$this->cache=$cache;
			if($cache && $cache->exists($this->maximum)) {
				$this->solution=$cache->load($this->maximum);
				echo "<b>Jsem chytrý algoritmus a čtu si to z cache.</b>";
			}
			else {
				$this->genArray();
				$this->solve();
				if($this->cache) {
					$this->cache->save($this->maximum, $this->solution);
				}
			}
		}
		
		/**
		 * 
		 * Calculate all.
		 */
		private function solve() {
			$number=1;
			$sqr=sqrt($this->maximum);/**< Some save of time because sqrt is really lazy function */

			for($i=0;$number<$sqr;$i++) {
				if(isset($this->numbers[$i])) {
					$number=$this->numbers[$i];
					$this->deleteNumber($number);
					$this->solution[]=$number;
				}
			}
			$this->solution=array_merge($this->solution, $this->numbers);
		}
		
		/**
		 * 
		 * Generate array of numbers lower than maximum and greater than 2 because one isn't prime number. 
		 */
		private function genArray() {
			for($i=2;$i<=$this->maximum;$i++) {
				$this->numbers[]=$i;
			}
		}
		
		/**
		 * 
		 * Delete multiples of @param $number from $this->numbers.
		 * @param int $number
		 */
		private function deleteNumber($number) {
			foreach ($this->numbers as $key => $value) {
				if($value%$number==0) {
					unset($this->numbers[$key]);
				} 
			}
		}
		
		public function print_that() {
			$i=0;
			echo "<table border=\"1px\">";
			foreach ($this->solution as $value) {
				$i++;
				if($i==1){
					echo "<tr>";
				}
				echo "<td>";
				echo $value;
				echo "</td>";
				if($i==10) {
					$i=0;
					echo "</tr>";
				}
			}
			if($i!=0) {
				for($i;$i<10;$i++) {
					echo "<td></td>";
				}
				echo "</tr>";
			}
			echo "</table>";
		}
};
?>
<!DOCTYPE html>
<html>
<head>
	<base href="/DMP/" />
	<meta charset="UTF-8">
	<meta name="author" content="Dominik Roháček">
	<title>Eratosthenes sieve</title>
</head>
<body>
	<header><h1>Eratosthenovo síto</h1></header>
	<form method="get">
		<label for="limit">Limit pro prvočísla</label>
		<input type="number" min="2" name="limit" id="limit" placeholder="Limit pro prvočísla" <?php if(isset($_GET["limit"])) echo "value=\"".$_GET["limit"]."\" ";?>required /><br />
		<label for="cache">Cachovat výsledky?</label>
		<input type="checkbox" id="cache" name="cache" checked="checked" /><br />
		<input type="submit" value="Spočítat"/>
	</form>
</body>
<?php
$start = microtime(true);
if(isset($_GET["limit"])) {
	if($_GET["limit"]<2) {
		echo "V tomto rozsahu není žádné prvočíslo.";
	}
	else {
		if(isset($_GET["cache"])) {
			$c=new Cache("log");
		}
		else {
			$c=null;
		}
		$a=new Eratos($_GET["limit"], $c);/**< If you don't pass second parameter. Eratos will not cache calculations.*/
		$a->print_that();
	}
}

echo "Proces strávil " . $time_taken = microtime(true) - $start .
    " ms svými výpočty\n";

?>
</html>