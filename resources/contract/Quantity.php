<?php
	namespace contract;

	class Quantity{
		private string $unit;
		private float $number;
		
		function __construct(float $number, string $unit){
			$this->unit = $unit;
			$this->number = $number;
		}
		public function getUnit():string{
			return $this->unit;
		}
		public function setUnit(string $unit){
			$this->unit = $unit;
		}
		public function getNumber():float{
			return $this->number;
		}
		public function setNumber(float $number){
			$this->number = $number;
		}
	}
?>