<?php
/*
BeaverQuery is licensed under the Apache License 2.0 license
https://github.com/trp-solutions/BeaverQuery/blob/main/LICENSE
*/
declare(strict_types=1);
namespace TRP\BeaverQuery\Statement;
use TRP\BeaverQuery\Expression\{Expression,BindingStrength,ExpressionAlias};
use TRP\BeaverQuery\BeaverQueryException;

class ExpressionProxy extends Expression {
	protected ?Expression $inner = null;
	protected ?ExpressionAlias $alias = null;

	public function __construct(protected Statement $statement, protected string $callback){

	}

	public function __call(string $name, array $arguments){
		if(isset($this->inner) && is_callable([$this->inner, $name])){
			return $this->proxy($this->inner->$name(...$arguments));
		}
	}

	public function __get(string $name){
		return $this->proxy($this->statement->table->$name);
	}

	protected function proxy($obj){
		if($obj instanceof Expression){
			if(!isset($this->inner) && is_callable([$this->statement, $this->callback])){
				call_user_func([$this->statement, $this->callback], $this);
			}
			$this->inner = $obj;
			if(isset($this->alias)){
				$this->alias = $this->inner->as($this->alias->alias());
			}
			return $this;
		} else {
			return $obj;
		}
	}

	public function as(string $alias): ExpressionAlias {
		if(isset($this->$alias)){
			$this->alias->alias($alias);
		} else {
			$expr = $this->inner ?? Atom::null();
			$this->alias = $expr->alias($alias);
		}
		return $this->alias;
	}

	public function is($expr, $allow_null = false): Expression {
		return $this->__call('is', [$expr, $allow_null]);
	}

	public function is_not($expr, $allow_null = false): Expression {
		return $this->__call('is_not', [$expr, $allow_null]);
	}

	public function is_null(): Expression {
		return $this->__call('is_null', []);
	}

	public function is_not_null(): Expression {
		return $this->__call('is_not_null', []);
	}

	public function in_range($from, $to, $start_inclusive = true, $end_inclusive = true): Expression {
		return $this->__call('in_range',[$from, $to, $start_inclusive, $end_inclusive]);
	}

	public function between($from, $to): Expression {
		return $this->__call('between',[$from, $to]);
	}

	public function lt($expr): Expression {
		return $this->__call('lt',[$expr]);
	}

	public function lteq($expr): Expression {
		return $this->__call('lteq',[$expr]);
	}

	public function gt($expr): Expression {
		return $this->__call('gt',[$expr]);
	}

	public function gteq($expr): Expression {
		return $this->__call('gteq',[$expr]);
	}

	public function eq($expr): Expression {
		return $this->__call('eq',[$expr]);
	}

	public function not_eq($expr): Expression {
		return $this->__call('not_eq',[$expr]);
	}

	public function eq_nullsafe($expr): Expression {
		return $this->__call('eq_nullsafe',[$expr]);
	}

	public function in(array $list): Expression {
		return $this->__call('in',[$list]);
	}

	public function not_in(array $list): Expression {
		return $this->__call('not_in',[$list]);
	}

	public function func(string $function, ...$additional_arguments): Expression {
		return $this->__call('func',[$function,...$additional_arguments]);
	}

	public function and(...$expr): Expression {
		return $this->__call('and',$expr);
	}

	public function or(...$expr): Expression {
		return $this->__call('or',$expr);
	}

	public function xor(...$expr): Expression {
		return $this->__call('xor',$expr);
	}

	public function __toString(){
		return (string) ($this->alias ?? $this->inner ?? '');
	}

	public function print(BindingStrength $outer_strength): string {
		return isset($this->inner) ? $this->inner->print($outer_strength) : '';
	}
}
