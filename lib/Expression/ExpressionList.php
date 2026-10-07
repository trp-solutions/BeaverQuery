<?php
/*
BeaverQuery is licensed under the Apache License 2.0 license
https://github.com/trp-solutions/BeaverQuery/blob/main/LICENSE
*/
declare(strict_types=1);
namespace TRP\BeaverQuery\Expression;

class ExpressionList {
	private const EXPRESSION = 0;
	private const ARGUMENT = 1;
	private const IDENTIFIER = 2;
	private const ORDERING = 3;

	protected array $expressions;
	private int $type = 0;

	public static function parse(...$expressions): static {
		$list = new static();
		return $list->add(...$expressions);
	}

	public static function parse_argument(...$expressions){
		$list = new static();
		$list->type = self::ARGUMENT;
		return $list->add(...$expressions);
	}

	public static function parse_identifier(...$expressions){
		$list = new static();
		$list->type = self::IDENTIFIER;
		return $list->add(...$expressions);
	}

	public static function parse_ordering(...$expressions){
		$list = new static();
		$list->type = self::ORDERING;
		return $list->add(...$expressions);
	}

	public function __construct(Expression ...$expressions){
		$this->expressions = $expressions;
	}

	public function __toString(): string {
		$output = implode(',', $this->expressions);
		if($this->type == self::ARGUMENT){
			$output = '('.$output.')';
		}
		return $output;
	}

	public function as_argument_list(): static {
		return match($this->type){
			self::ARGUMENT => $this,
			self::ORDERING => throw new BeaverQueryException('Can\'t convert an Ordering List to an Argument List'),
			default => $this->to_argument_list()
		};
	}

	protected function to_argument_list(): static {
		$list = clone $this;
		$list->type = self::ARGUMENT;
		return $list;
	}

	public function add(...$expressions): static {
		$parse_func = match($this->type){
			self::IDENTIFIER => ['\TRP\BeaverQuery\Expression\Identifier','parse'],
			self::ORDERING => ['\TRP\BeaverQuery\Expression\Ordering','parse'],
			default => ['\TRP\BeaverQuery\Expression\Expression','parse']
		};
		foreach($expressions as $expr){
			$this->expressions[] = $parse_func($expr);
		}
		return $this;
	}
}
