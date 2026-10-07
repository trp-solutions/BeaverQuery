<?php
/*
BeaverQuery is licensed under the Apache License 2.0 license
https://github.com/trp-solutions/BeaverQuery/blob/main/LICENSE
*/
declare(strict_types=1);
namespace TRP\BeaverQuery;
use TRP\BeaverQuery\Expression\{Expression, Identifier, BooleanAnd, BooleanOr, FunctionCall, Atom, Interval, Unsafe};
use TRP\BeaverQuery\Join\{Join,Type};
use TRP\BeaverQuery\Statement\{Insert, Select, Update, Delete};

class BeaverQuery {
	private static ?string $default_database = null;
	private static bool $implicit_database_allowed = false;
	/*
		IDEA: Maybe make a static configure method that returns a BeaverQuery instance.
		Static calls would use the configurations from either the first or the last instance,
		or fall back to default values.
	*/

	public static function setDefaultDatabase(?string $database): void {
		self::$default_database = $database;
	}

	public static function getDefaultDatabase(): ?string {
		return self::$default_database;
	}

	public static function allowImplicitDatabase(bool $allow = true): void {
		self::$implicit_database_allowed = $allow;
	}

	public static function insert($table, array $columns = [], array ...$rows): Insert {
		$insert = new Insert(Table::parse($table, self::$implicit_database_allowed));
		if(!empty($columns)){
			$insert->columns($columns);
		}
		if(!empty($rows)){
			foreach($rows as $row){
				$insert->values(...$row);
			}
		}
		return $insert;
	}

	public static function select($table = null, array $values = []): Select {
		if(isset($table)){
			$table = Table::parse($table, self::$implicit_database_allowed);
			$table_name = $table->get_name();
			$columns = [];
			$expr = [];
			foreach($values as $value){
				if(is_string($value)){
					$identifier = Parser::dotted_name_with_alias($value);
					if(isset($identifier)){
						$identifier[2] ??= $table_name;
						if($identifier[2] == $table_name){
							$columns[] = $identifier;
							continue;
						}
					}
				}
				$expr[] = $value;
			}
			$table->columns(...$columns);
		} else {
			$expr = $values;
		}		

		return new Select($table, $expr);
	}

	public static function update($table, ...$values): Update {
		return new Update(Table::parse($table, self::$implicit_database_allowed));
	}

	public static function delete($table, $where = null): Delete {
		$delete = new Delete(Table::parse($table, self::$implicit_database_allowed));
		if(isset($where)){
			$delete->where($where);
		}
		return $delete;
	}

	public static function join($table, $type = 'JOIN'): Join {
		return Join::specified_join(Type::parse($type), Table::parse($table, self::$implicit_database_allowed));
	}

	public static function table($table, array $columns = []): Table {
		$table = Table::parse($table, self::$implicit_database_allowed);
		$table->columns(...$columns);
		return $table;
	}

	public static function name(string $name, ?string $subname = null): Identifier {
		if(isset($subname)){
			return new Identifier(context: $name, name: $subname);
		} else {
			return new Identifier($name);
		}
	}

	public static function func(string $name, ...$expressions): FunctionCall {
		return FunctionCall::parse($name, ...$expressions);
	}

	public static function and(...$expressions): Expression {
		return BooleanAnd::parse(...$expressions);
	}

	public static function or(...$expressions): Expression {
		return BooleanOr::parse(...$expressions);
	}

	public static function xor(...$expressions): Expression {
		return BooleanXor::parse(...$expressions);
	}

	public static function literal($value): Expression {
		return Atom::literal($value);
	}

	public static function interval($expression, string $unit): Expression {
		return Interval::parse($expression, $unit);
	}

	public static function unsafe(string $raw_sql): Unsafe {
		return Unsafe::unsafe($raw_sql);
	}
}
