<?php
/*
BeaverQuery is licensed under the Apache License 2.0 license
https://github.com/trp-solutions/BeaverQuery/blob/main/LICENSE
*/
declare(strict_types=1);
namespace TRP\BeaverQuery\Join;
use TRP\BeaverQuery\Table;
use TRP\BeaverQuery\BeaverQueryException;
use TRP\BeaverQuery\Expression\{Expression,BooleanAnd};

#[\Property('table', '\TRP\BeaverQuery\Table', set: false)]
class Join {
	protected Table $table;
	protected ?Expression $on;
	protected ?IdentifierList $using;

	public static function left_join(string|array|Table $table, $on = null, $using = null): static {
		return static::specified_join(Type::Left, $table, $on, $using);
	}

	public static function right_join(string|array|Table $table, $on = null, $using = null): static {
		return static::specified_join(Type::Right, $table, $on, $using);
	}

	public static function inner_join(string|array|Table $table, $on = null, $using = null): static {
		return static::specified_join(Type::Inner, $table, $on, $using);
	}

	public static function specified_join(Type $type, string|array|Table $table, $on = null, $using = null): static {
		$join = new static($type, $table);
		if(isset($on)){
			$join->on($on);
		} elseif(isset($using)){
			$join->using($using);
		}
		return $join;
	}

	public function __construct(protected Type $type, string|array|Table $table){
		$this->table = Table::parse($table);
	}

	public function on(...$expr): Expression {
		if(!$this->type->allows_specification()){
			throw new BeaverQueryException("ON clause not permitted with ".$this->type->value);
		}
		if(!isset($this->on)){
			$this->on = BooleanAnd::parse(...$expr);
		} else {
			$this->on->and(...$expr);
		}
		return $this->on;
	}

	public function using(...$columns): IdentifierList {
		if(!$this->type->allows_specification()){
			throw new BeaverQueryException("USING clause not permitted with ".$this->type->value);
		}
		if(!isset($this->using)){
			$this->using = IdentifierList::parse(...$expr);
		} else {
			$this->using->add(...$expr);
		}
		return $this->using;
	}

	public function __toString(): string {
		$sql = $this->type->value."\n  ".$this->table->get_name()->identifier_with_alias();
		if(isset($this->on)){
			$sql .= "\n  ON ".$this->on;
		} elseif(isset($this->using)){
			$sql .= "\n  USING (".$this->using.')';
		} elseif($this->type->requires_specification()){
			throw new BeaverQueryException("Missing ON condition or USING column list in '$sql'");
		}
		return $sql;
	}

	public function __isset($key){
		if($key === 'table'){
			return isset($this->table);
		} else {
			return false;
		}
	}

	public function __get($key){
		if($key === 'table'){
			return $this->table;
		}
	}
}
