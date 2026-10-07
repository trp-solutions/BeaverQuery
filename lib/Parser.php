<?php
/*
BeaverQuery is licensed under the Apache License 2.0 license
https://github.com/trp-solutions/BeaverQuery/blob/main/LICENSE
*/
declare(strict_types=1);
namespace TRP\BeaverQuery;

class Parser {
	const UNQUOTED_IDENTIFIER_REGEX = '/^[0-9]*[a-zA-Z$_\\x{0080}-\\x{FFFF}][0-9a-zA-Z$_\\x{0080}-\\x{FFFF}]*$/u';
	const DOTTED_NAME_ALIAS_REGEX = '/^(?:([^.\s`]+|`(?:[^`\\x{0000}]|\\\\`)+`)\s*\.\s*)?([^.\s`]+|`(?:[^`\\x{0000}]|\\\\`)+`)(?:\s+[Aa][Ss]\s+([^\s`]+|`(?:[^`\\x{0000}]|\\\\`)+`))?$/u';
	const QUOTED_IDENTIFIER = '/^`((?:[^`\\x{0000}]|\\\\`)+)`$/u';
	const ESCAPED_IDENTIFIER = '/^((?:[^`\\x{0000}]|\\\\`)+)$/u';

	const STRING_ESCAPES_SINGLE = [
		"\0"  =>'\\0',
		"\n"  =>'\\n',
		"\r"  =>'\\r',
		"\\"  =>'\\\\',
		"'"   =>'\\\'',
		"\x1A"=>'\\Z',
	];

	const STRING_ESCAPES_DOUBLE = [
		"\0"  =>'\\0',
		"\n"  =>'\\n',
		"\r"  =>'\\r',
		"\\"  =>'\\\\',
		"\""  =>'\\"',
		"\x1A"=>'\\Z',
	];

	const IDENTIFIER_ESCAPES = [
		"\0"  =>'\\0',
		"\\" => '\\\\',
		"`"  => '\\`',
	];

	public static function is_identifier($identifier){
		return is_string($identifier) && preg_match(self::UNQUOTED_IDENTIFIER_REGEX, $identifier) === 1;
	}

	public static function identifier($identifier){
		if(empty($identifier)){
			return null;
		}
		if(preg_match(self::QUOTED_IDENTIFIER, $identifier, $matches)){
			return $matches[1];
		} elseif(self::is_identifier($identifier)){
			return $identifier;
		} else {
			return null;
		}
	}

	public static function function_name(string $name){
		if(
			preg_match(self::UNQUOTED_IDENTIFIER_REGEX, $name) === 1
			|| preg_match(self::QUOTED_IDENTIFIER, $name) === 1
		){
			return $name;
		} else {
			return '`'.self::escape_identifier($name).'`';
		}
	}

	public static function dotted_name_with_alias(string $expr){
		$expr = trim($expr);
		if(preg_match(self::DOTTED_NAME_ALIAS_REGEX, $expr, $matches)){
			return [
				self::identifier($matches[2]??null),
				self::identifier($matches[3]??null),
				self::identifier($matches[1]??null)
			];
		}
		return null;
	}

	public static function string_literal(string $value, bool $doublequote = false): string {
		$unicode_value = mb_scrub($value, 'UTF-8');
		if($doublequote){
			$escaped_value = strtr($unicode_value, self::STRING_ESCAPES_DOUBLE);
			return '"'.$escaped_value.'"';
		} else {
			$escaped_value = strtr($unicode_value, self::STRING_ESCAPES_SINGLE);
			return "'".$escaped_value."'";
		}
	}

	public static function escape_identifier(string $identifier): string {
		if(preg_match(self::ESCAPED_IDENTIFIER, $identifier) === 1){
			return $identifier;
		} else {
			return strtr($identifier, self::IDENTIFIER_ESCAPES);
		}
	}

	public static function is_pair($pair){
		return is_array($pair) && count($pair) == 2 && array_key_exists(0, $pair) && array_key_exists(1, $pair);
	}

	public static function list(array $list): array {
		return count($list) == 1 && is_array($list[0]??null) ? $list[0] : $list;
	}
}
