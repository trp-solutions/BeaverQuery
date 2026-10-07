<?php
/*
BeaverQuery is licensed under the Apache License 2.0 license
https://github.com/trp-solutions/BeaverQuery/blob/main/LICENSE
*/
declare(strict_types=1);
namespace TRP\BeaverQuery\Expression;
use TRP\BeaverQuery\BeaverQueryException;

class Operation extends Expression {
	protected array $terms;

	public static function is_op(Expression $left, Expression $right): static {
		return new static(BindingStrength::Comparison, $left, 'IS', $right);
	}
	public static function is_not_op(Expression $left, Expression $right): static {
		return new static(BindingStrength::Comparison, $left, 'IS NOT', $right);
	}

	public static function between_op(Expression $expr, Expression $left, Expression $right): static {
		return new static(BindingStrength::Case,$expr, 'BETWEEN', $left, 'AND', $right);
	}

	public static function not_between_op(Expression $expr, Expression $left, Expression $right): static {
		return new static(BindingStrength::Case, $expr, 'NOT BETWEEN', $left, 'AND', $right);
	}

	public static function like_op(Expression $left, Expression $right): static {
		return new static(BindingStrength::Comparison, $left, 'LIKE', $right);
	}

	public static function not_like_op(Expression $left, Expression $right): static {
		return new static(BindingStrength::Comparison, $left, 'NOT LIKE', $right);
	}

	public static function compare_op(Expression $left, string $infix, Expression $right): static {
		if(!in_array($infix, ['>','>=','<','<=','<>','!=','<=>','='])){
			throw new BeaverQueryException("Invalid comparison operator '$infix'");
		}
		return new static(BindingStrength::Comparison, $left, $infix, $right);
	}

	public static function in_op(Expression $left, ExpressionList $list): static {
		return new static(BindingStrength::Comparison, $left, 'IN', $list->as_argument_list());
	}

	public static function not_in_op(Expression $left, ExpressionList $list): static {
		return new static(BindingStrength::Comparison, $left, 'NOT IN', $list->as_argument_list());
	}

	public static function asc_order(Expression $expr): static {
		return new static(BindingStrength::Undefined, $expr, 'ASC');
	}

	public static function desc_order(Expression $expr): static {
		return new static(BindingStrength::Undefined, $expr, 'DESC');
	}

	public static function assignment(Expression $left, Expression $right): static {
		return new static(BindingStrength::Assignment, $left, '=', $right);
	}

	protected function __construct(protected BindingStrength $inner_strength, Expression|ExpressionList|string ...$terms){
		$this->terms = $terms;
	}

	public function __toString(): string {
		$term_to_string = fn($term) => $term instanceof Expression ? $term->print($this->inner_strength) : (string) $term;
		return implode(' ', array_map($term_to_string, $this->terms));
	}
}
