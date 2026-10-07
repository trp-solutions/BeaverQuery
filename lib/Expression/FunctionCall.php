<?php
/*
BeaverQuery is licensed under the Apache License 2.0 license
https://github.com/trp-solutions/BeaverQuery/blob/main/LICENSE
*/
declare(strict_types=1);
namespace TRP\BeaverQuery\Expression;

use TRP\BeaverQuery\{Parser, BeaverQueryException};

class FunctionCall extends Atom {
	protected string $name;

	public static function parse($name, ...$expr): static {
		$uc_name = mb_strtoupper($name);
		return match($uc_name) {
			'GROUP_CONCAT' => static::group_concat($name, $expr),
			'AVG','COUNT','MAX','MIN','SUM' => static::aggregate($name, $expr),
			'DATE_ADD','DATE_SUB' => static::date_arithmetic($name, true, ...$expr),
			'ADDDATE','SUBDATE' => static::date_arithmetic($name, false, ...$expr),
			'TIMESTAMPADD','TIMESTAMPDIFF' => static::timestamp($name, ...$expr),
			'EXTRACT' => static::extract($name, ...$expr),
			default => new static($name, Expression::map_parse($expr))
		};
	}

	protected static function keyword(string $keyword, array &$expr, $default = null){
		if(array_key_exists($keyword, $expr)){
			$value = $expr[$keyword];
			unset($expr[$keyword]);
		}
		return $value ?? $default;
	}

	protected static function aggregate(string $name, $expr, array $prefix = [], array $postfix = []): static {
		if(static::keyword('distinct', $expr, false)){
			$prefix[] = 'DISTINCT';
		}
		return new static($name, Expression::map_parse($expr), $prefix, $postfix);
	}

	protected static function group_concat(string $name, $expr): static {
		$postfix = [];
		$orderby = static::keyword('orderby', $expr);
		if(isset($orderby)){
			$orderby = is_array($orderby) ? $orderby : [$orderby];
			$postfix[] = 'ORDER BY '.ExpressionList::parse_ordering(...$orderby);
		}
		$separator = static::keyword('separator', $expr);
		if(isset($separator)){
			$postfix[] = 'SEPARATOR '.Parser::string_literal($separator);
		}
		return static::aggregate($name, $expr, postfix: $postfix);
	}

	protected static function date_arithmetic(string $name, bool $require_interval, $date, $interval, $unit = null): static {
		// DATE_ADD(date,INTERVAL expr unit), DATE_SUB(date,INTERVAL expr unit)
		// ADDDATE(date,INTERVAL expr unit), SUBDATE(date,INTERVAL expr unit)
		// ADDDATE(date,days), SUBDATE(date,days)
		if(isset($unit)){
			$interval = Interval::parse($interval, $unit);
		}
		if($require_interval && !is_a($interval, '\TRP\BeaverQuery\Expression\Interval')){
			throw new BeaverQueryException("Expected (date, expr, unit) or (date, interval) when creating function $name(date, INTERVAL expr unit)");
		}
		return new static($name, [Expression::parse($date), $interval]);
	}

	protected static function timestamp(string $name, $unit, $arg1, $arg2): static {
		return new static($name, Expression::map_parse([IntervalUnit::parse($unit), $arg1, $arg2]));
	}

	protected static function extract($unit, ...$expr){
		return new static($name, Expression::map_parse($expr), prefix_terms: [IntervalUnit::parse($unit),'FROM']);
	}

	protected function __construct(
		string $name,
		protected array $arguments,
		protected array $prefix_terms = [],
		protected array $postfix_terms = [],
	){
		$this->name = Parser::function_name($name);
	}

	public function __toString(): string {
		$arguments = implode(',',$this->arguments);
		$terms = implode(' ',array_merge($this->prefix_terms, [$arguments], $this->postfix_terms));
		return $this->name.'('.$terms.')';
	}
}
