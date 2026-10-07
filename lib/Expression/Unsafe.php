<?php
/*
BeaverQuery is licensed under the Apache License 2.0 license
https://github.com/trp-solutions/BeaverQuery/blob/main/LICENSE
*/
declare(strict_types=1);
namespace TRP\BeaverQuery\Expression;
use TRP\BeaverQuery\{Parser,BeaverQueryException};

final class Unsafe extends Expression {
	private static ?bool $is_enabled = null;

	public static function enable(){
		if(!isset(self::$is_enabled)){
			self::$is_enabled = true;
		} elseif(self::$is_enabled === false) {
			throw new BeaverQueryException("Unsafe has explicitly been disabled.");
		}
	}

	public static function disable(){
		if(!isset(self::$is_enabled)){
			self::$is_enabled = false;
		} elseif(self::$is_enabled === true) {
			throw new BeaverQueryException("Unable to disable Unsafe. It has explicitly been enabled.");
		}
	}

	public static function unsafe(string $raw_sql): self {
		return new self($raw_sql);
	}

	private function __construct(private readonly string $raw_sql){
		if(self::$is_enabled === false){
			throw new BeaverQueryException("Unsafe expressions are disabled.");
		}
	}

	public function __toString(): string {
		return $this->raw_sql;
	}

	protected function inner_strength(): BindingStrength {
		return BindingStrength::Undefined;
	}
}