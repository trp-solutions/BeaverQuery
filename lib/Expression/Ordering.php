<?php
/*
BeaverQuery is licensed under the Apache License 2.0 license
https://github.com/trp-solutions/BeaverQuery/blob/main/LICENSE
*/
declare(strict_types=1);
namespace TRP\BeaverQuery\Expression;
use TRP\BeaverQuery\Parser;

class Ordering {
	protected ?bool $ascending;
	public static function parse($expr, $order = null){
		if(!isset($order) && Parser::is_pair($expr) && is_string($expr[1])){
			$order = $expr[1];
			$expr = $expr[0];
		}
		return new static(Expression::parse($expr), $order);
	}

	public function __construct(protected Expression $expression, ?string $order = null){
		if(is_string($order)){
			$order = strtolower($order);
		}
		$this->ascending = match($order){
			'asc'=> true,
			'desc'=> false,
			default => null
		};
	}

	public function __toString(): string {
		return match($this->ascending){
			true => $this->expression.' ASC',
			false => $this->expression.' DESC',
			default => (string) $this->expression
		};
	}
}
