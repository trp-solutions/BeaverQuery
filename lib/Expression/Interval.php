<?php
/*
BeaverQuery is licensed under the Apache License 2.0 license
https://github.com/trp-solutions/BeaverQuery/blob/main/LICENSE
*/
declare(strict_types=1);
namespace TRP\BeaverQuery\Expression;

class Interval extends Atom {
	public static function parse($expr, $unit = null): Expression {
		if($expr instanceof Interval){
			return $expr;
		}
		return new static(
			Expression::parse($expr),
			IntervalUnit::parse($unit)
		);
	}

	protected function __construct(
		protected Expression $expr,
		protected IntervalUnit $unit,
	){

	}

	public function __toString(): string {
		$expr = (string) $this->expr;
		$unit = $this->unit->value;
		return "INTERVAL $expr $unit";
	}
}
