<?php
/*
BeaverQuery is licensed under the Apache License 2.0 license
https://github.com/trp-solutions/BeaverQuery/blob/main/LICENSE
*/
declare(strict_types=1);
namespace TRP\BeaverQuery\Expression;
use TRP\BeaverQuery\{Parser,BeaverQueryException};

class Identifier extends Atom {
	protected string $name;
	protected ?string $alias = null;
	protected string|Identifier|null $context = null;

	public static function parse($expr, $context = null): Expression {
		if($expr instanceof Expression){
			return $expr;
		}
		return static::parse_strict($expr, $context);
	}

	public static function parse_strict($expr, $context = null): static {
		if($expr instanceof static){
			return $expr;
		}
		if(is_string($expr)){
			$dotted_name = Parser::dotted_name_with_alias($expr);
			if(isset($dotted_name)){
				if(isset($context)){
					$dotted_name[2] = $context;
				}
				return new static(...$dotted_name);
			}
		} elseif(is_array($expr)){
			if(isset($context)){
				$expr[2] = $context;
			}
			return new static(...$expr);
		}
		throw new BeaverQueryException("Invalid identifier");
	}

	public function __construct(string $name, ?string $alias = null, string|Identifier|null $context = null){
		$this->name = Parser::escape_identifier($name);
		if(isset($alias)){
			$this->set_alias($alias);
		}
		if(isset($context)){
			if($context instanceof Identifier){
				$this->context = $context;
			} else {
				$this->context = Parser::escape_identifier($context);
			}
		}
	}

	public function set_alias(string $alias){
		$this->alias = Parser::escape_identifier($alias);
	}

	public function __toString(){
		return $this->identifier_with_alias();
	}

	public function print(BindingStrength $outer_strength): string {
		return $this->identifier();
	}

	public function identifier(): string {
		$identifier = "`$this->name`";
		if(isset($this->context)){
			if($this->context instanceof Identifier){
				$context = $this->context->alias ?? $this->context->name;
				$identifier = "`$context`.".$identifier;
			} else {
				$identifier = "`$this->context`.".$identifier;
			}
		}
		return $identifier;
	}

	public function identifier_with_alias(): string {
		$identifier = $this->identifier();
		if(isset($this->alias)){
			$identifier .= " AS `$this->alias`";
		}
		return $identifier;
	}

	public function identifier_unqualified(): string {
		return "`$this->name`";
	}

	public function alias(array $used_names = []): string {
		$alias = $this->alias ?? $this->name;
		if(!empty($used_names)){
			$alias_is_used = in_array($alias, $used_names);
			if($alias_is_used){
				for($offset = 1; $offset < 10; $offset++){
					$generated_alias = $alias.$offset;
					if(!in_array($generated_alias, $used_names)){
						$this->alias = $generated_alias;
						return $this->alias;
					}
				}
				throw new BQException("Failed autogenerating alias for '".$this->identifier_with_alias()."'");
			}
		}
		return $alias;
	}
}
