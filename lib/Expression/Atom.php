<?php
/*
BeaverQuery is licensed under the Apache License 2.0 license
https://github.com/trp-solutions/BeaverQuery/blob/main/LICENSE
*/
declare(strict_types=1);
namespace TRP\BeaverQuery\Expression;
use TRP\BeaverQuery\{Parser,BeaverQueryException};

class Atom extends Expression {
	private static self $true;
	private static self $false;
	private static self $null;

	public static function literal(mixed $value): Atom {
		if(is_bool($value)){
			return $value ? self::true() : self::false();
		} elseif(is_int($value)){
			return new self((string) $value);
		} elseif(is_float($value)){
			return new self(sprintf('%F', $value));
		} elseif(is_string($value)){
			return new self(Parser::string_literal($value));
		} elseif(!isset($value)){
			return self::null();
		} elseif($value instanceof IntervalUnit){
			return new self($value->value);
		} else {
			throw new BeaverQueryException("Can't convert value to literal");
		}
	}

	public static function true(){
		self::$true ??= new self('TRUE');
		return self::$true;
	}

	public static function false(){
		self::$false ??= new self('FALSE');
		return self::$false;
	}

	public static function null(){
		self::$null ??= new self('NULL');
		return self::$null;
	}

	protected function __construct(private string $term){

	}

	public function is_true_atom(): bool {
		return $this->term === 'TRUE';
	}

	public function is_false_atom(): bool {
		return $this->term === 'FALSE';
	}

	public function is_null_atom(): bool {
		return $this->term === 'NULL';
	}

	public function __toString(): string {
		return $this->term;
	}

	public function print(BindingStrength $outer_strength): string {
		return (string) $this;
	}
}
