<?php
/*
BeaverQuery is licensed under the Apache License 2.0 license
https://github.com/trp-solutions/BeaverQuery/blob/main/LICENSE
*/
declare(strict_types=1);
namespace TRP\BeaverQuery;
use TRP\BeaverQuery\Expression\Identifier;
use TRP\BeaverQuery\Statement\{Insert,Select,Update,Delete};

class Table {
	protected array $column_list = [];
	protected readonly Identifier $name;

	public static function parse(string|array|Table $table, bool $implicit_database = false): self {
		if($table instanceof self){
			return $table;
		} elseif(is_string($table)){
			$dotted_name = Parser::dotted_name_with_alias($table);
			if(isset($dotted_name)){
				return new self(...$dotted_name, implicit_database: $implicit_database);
			} else {
				throw new BeaverQueryException("Invalid table name: '$table'");
			}
		} elseif(is_array($table)){
			$name = $table[0] ?? $table['name'] ?? null;
			if(is_string($name)){
				return new self(
					$name,
					$table[1] ?? $table['alias'] ?? null,
					$table[2] ?? $table['database'] ?? null,
					$implicit_database || ($table[3] ?? $table['implicit_database'] ?? false)
				);
			} elseif(isset($name)) {
				throw new BeaverQueryException("Invalid table name: '$name'");
			}
		}
		throw new BeaverQueryException("Failed creating table");
	}

	public function __construct(string $name, ?string $alias = null, ?string $database = null, bool $implicit_database = false){
		if(!$implicit_database){
			$database ??= BeaverQuery::getDefaultDatabase();
		}
		if(!isset($database) && !$implicit_database){
			throw new BeaverQueryException("Implicit database not enabled. Database missing from table `$name`");
		}
		$this->name = new Identifier($name, $alias, $database);
	}

	public function columns(string|array|Column ...$column_list): static {
		foreach($column_list as $column){
			$this->add_column($column);
		}

		return $this;
	}

	protected function add_column(string|array|Column $column){
		$this->column_list[] = Identifier::parse_strict($column, $this->name);
	}

	public function select(): Select {
		return new Select(table: $this);
	}

	public function insert(): Insert {
		return new Insert(table: $this);
	}

	public function update(): Update {
		return new Update(table: $this);
	}

	public function delete(): Delete {
		return new Delete(table: $this);
	}

	public function get_name(): Identifier {
		return $this->name;
	}

	public function set_alias(string $name) {
		$this->name->set_alias($name);
	}

	public function get_column_list(): array {
		return $this->column_list;
	}

	public function get_column(string $name, ?string $column_alias = null): Identifier {
		return new Identifier($name, $column_alias, $this->name);
	}

	public function __get(string $name): Identifier {
		return $this->get_column($name);
	}
}
