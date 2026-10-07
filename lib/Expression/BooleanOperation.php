<?php
/*
BeaverQuery is licensed under the Apache License 2.0 license
https://github.com/trp-solutions/BeaverQuery/blob/main/LICENSE
*/
declare(strict_types=1);
namespace TRP\BeaverQuery\Expression;

abstract class BooleanOperation extends Expression {
	protected static BindingStrength $static_inner_strength;
	protected array $expressions;

	protected function inner_strength(): BindingStrength {
		return static::INNER_STRENGTH;
	}

	public static function parse(...$expr): Expression {
		$expr = static::parse_and_filter(...$expr);
		if(count($expr) == 1){
			return $expr[0];
		} else {
			return new static(...$expr);
		}
	}

	protected static function parse_and_filter(...$expr): array {
		return array_filter(Expression::map_parse($expr), fn($e) => static::filter($e));
	}

	abstract protected static function filter(Expression $expression): bool ;

	protected function __construct(Expression ...$expr){
		$this->expressions = $expr;
	}

	public function __toString(): string {
		$print = fn($e)=>$e->print(static::INNER_STRENGTH);
		return implode(static::OPERATOR, array_map($print, $this->expressions));
	}
}
