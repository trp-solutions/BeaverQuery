<?php
/*
BeaverQuery is licensed under the Apache License 2.0 license
https://github.com/trp-solutions/BeaverQuery/blob/main/LICENSE
*/
declare(strict_types=1);
namespace TRP\BeaverQuery\Expression;

class BooleanOr extends BooleanOperation {
	protected const OPERATOR = "\n  OR ";
	protected const INNER_STRENGTH = BindingStrength::Or;

	protected static function filter(Expression $expression): bool {
		return !($expression instanceof Atom && ($expression->is_false_atom() || $expression->is_null_atom()));
	}

	public function or(...$expr): Expression {
		$this->expressions = array_merge($this->expressions, self::parse_and_filter($expr));
		return $this;
	}
}
