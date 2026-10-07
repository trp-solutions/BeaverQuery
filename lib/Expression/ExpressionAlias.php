<?php
/*
BeaverQuery is licensed under the Apache License 2.0 license
https://github.com/trp-solutions/BeaverQuery/blob/main/LICENSE
*/
declare(strict_types=1);
namespace TRP\BeaverQuery\Expression;
use TRP\BeaverQuery\Parser;

class ExpressionAlias {
	protected string $alias;

	public static function parse($expression, ?string $alias = null): static|Expression {
		if($expression instanceof ExpressionAlias){
			if(isset($alias)){
				$expression->set_alias($alias);
			}
			return $expression;
		} elseif(isset($alias)){
			return new static(Expression::parse($expression), $alias);
		} else {
			return Identifier::parse($expression);
		}
	}

	public function __construct(protected Expression $expression, string $alias){
		$this->set_alias($alias);
	}

	public function set_alias(string $alias){
		$this->alias = Parser::escape_identifier($alias);
	}

	public function alias(): string {
		return $this->alias;
	}

	public function __toString(): string {
		return ($this->expression->print(BindingStrength::Undefined))." AS `$this->alias`";
	}
}
