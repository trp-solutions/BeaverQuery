<?php
/*
BeaverQuery is licensed under the Apache License 2.0 license
https://github.com/trp-solutions/BeaverQuery/blob/main/LICENSE
*/
declare(strict_types=1);
namespace TRP\BeaverQuery\Expression;
use TRP\BeaverQuery\BeaverQueryException;

class Expression {
	public static function parse($expr): Expression {
		if($expr instanceof Expression){
			return $expr;
		} else {
			return Atom::literal($expr);
		}
	}

	public static function map_parse(array $expr): array {
		return array_map(fn($e)=>static::parse($e), $expr);
	}

	protected function __construct(private string $expr, protected BindingStrength $inner_strength = BindingStrength::Undefined){

	}

	public function as(string $alias): ExpressionAlias {
		return ExpressionAlias::parse($this, $alias);
	}

	public function is($expr, $allow_null = false): Expression {
		if(!isset($expr)){
			return $allow_null ? self::is_null() : throw new BeaverQueryException('Attempting to compare with null without explicit permission');
		} elseif(is_bool($expr)){
			return Operation::is_op($this, Atom::literal($expr));
		} else {
			return Operation::compare_op($this, '=', self::parse($expr));
		}
	}

	public function is_not($expr, $allow_null = false): Expression {
		if(!isset($expr)){
			return $allow_null ? self::is_not_null() : throw new BeaverQueryException('Attempting to compare with null without explicit permission');
		} elseif(is_bool($expr)){
			return Operation::is_not_op($this, Atom::literal($expr));
		} else {
			return Operation::compare_op($this, '!=', self::parse($expr));
		}
	}

	public function is_null(): Expression {
		return Operation::is_op($this, Atom::null());
	}

	public function is_not_null(): Expression {
		return Operation::is_not_op($this, Atom::null());
	}

	public function in_range($from, $to, $start_inclusive = true, $end_inclusive = true): Expression {
		$after = $start_inclusive ? '>=' : '>';
		$before = $end_inclusive ? '<=' : '<';
		return BooleanAnd::parse(
			Operation::compare_op($this, $after, self::parse($from)),
			Operation::compare_op($this, $before, self::parse($to))
		);
	}

	public function between($from, $to): Expression {
		return Operation::between_op($this, self::parse($from), self::parse($to));
	}

	public function lt($expr): Expression {
		return Operation::compare_op($this, '<', self::parse($expr));
	}

	public function lteq($expr): Expression {
		return Operation::compare_op($this, '<=', self::parse($expr));
	}

	public function gt($expr): Expression {
		return Operation::compare_op($this, '>', self::parse($expr));
	}

	public function gteq($expr): Expression {
		return Operation::compare_op($this, '>=', self::parse($expr));
	}

	public function eq($expr): Expression {
		return Operation::compare_op($this, '=', self::parse($expr));
	}

	public function not_eq($expr): Expression {
		return Operation::compare_op($this, '!=', self::parse($expr));
	}

	public function eq_nullsafe($expr): Expression {
		return Operation::compare_op($this, '<=>', self::parse($expr));
	}

	public function in(array $list): Expression {
		return Operation::in_op($this, ExpressionList::parse_argument(...$list));
	}

	public function not_in(array $list): Expression {
		return Operation::not_in_op($this, ExpressionList::parse_argument(...$list));
	}

	public function func(string $function, ...$additional_arguments): Expression {
		return FunctionCall::parse($function, $this, ...$additional_arguments);
	}

	public function and(...$expr): Expression {
		return BooleanAnd::parse($this, ...$expr);
	}

	public function or(...$expr): Expression {
		return BooleanOr::parse($this, ...$expr);
	}

	public function xor(...$expr): Expression {
		return BooleanXor::parse($this, ...$expr);
	}

	public function __toString(): string {
		return $this->expr;
	}

	protected function inner_strength(): BindingStrength {
		return $this->inner_strength;
	}

	public function print(BindingStrength $outer_strength): string {
		return $outer_strength->stronger_than($this->inner_strength()) ? "($this)" : (string) $this;
	}
}
