<?php
/*
BeaverQuery is licensed under the Apache License 2.0 license
https://github.com/trp-solutions/BeaverQuery/blob/main/LICENSE
*/
declare(strict_types=1);
namespace TRP\BeaverQuery\Expression;

class BooleanAnd extends BooleanOperation {
	protected const OPERATOR = "\n  AND ";
	protected const INNER_STRENGTH = BindingStrength::And;

	protected static function filter(Expression $expression): bool {
		return !($expression instanceof Atom && $expression->is_true_atom());
	}

	public function and(...$expr): Expression {
		$this->expressions = array_merge($this->expressions, self::parse_and_filter(...$expr));
		return $this;
	}
}
