<?php

namespace WebApi;

class QueryLexer {

    protected static $_terminals = array(
		"/(^\(([^()]|(?R))*\))/" => "T_BLOCK",
		"/(^length)/" => "T_LENGTH",
		"/(^toupper)/" => "T_TO_UPPER",
		"/(^tolower)/" => "T_TO_LOWER",
		"/(^substringof)/" => "T_SUBSTRING_OF",
		"/(^startswith)/" => "T_STARTSWITH",
		"/(^not )/" => "T_NOT",
		"/(^[a-zA-Z]+)/" => "T_COLUMN",
		"/( or)/" => "T_OR",
		"/( gt)/" => "T_GT",
		"/( lt)/" => "T_LT",
		"/( eq)/" => "T_EQ",
		"/( ge)/" => "T_GE",
		"/( le)/" => "T_LE",
		"/( ne)/" => "T_NE",
		"/( true)/" => "T_TRUE",
		"/( false)/" => "T_FALSE",
		"/( null)/" => "T_NULL",
		"/( [0-9]+m)/" => "T_REAL_VALUE",
		"/( [0-9]+)/" => "T_INT_VALUE",
		"/( '[a-zA-Z]+')/" => "T_STR_VALUE",
		"/( datetime'[0-9A-Z:-.]+')/" => "T_DATETIME_VALUE"
	);

	public static function run($source) {
		$tokens = array();
	 
			$offset = 0;
			while($offset < strlen($source)) {
				$result = self::_match($source, $offset);

				echo $source . "\n";
				print_r($result) . "\n";
				echo "----------\n\n";

				if($result === false) {
					throw new \Exception("Unable to parse query");
				}
				$tokens[] = $result;
				$offset += strlen($result['match']);
			}
	 
		return $tokens;
	}

	protected static function _match($line, $offset) {
		$string = substr($line, $offset);

		foreach(self::$_terminals as $pattern => $name) {
			if(preg_match($pattern, $string, $matches)) {
				return array(
					'match' => $matches[1],
					'token' => $name
				);
			}
		}
	 
		return false;
	}

}
