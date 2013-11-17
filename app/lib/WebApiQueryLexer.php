<?php

namespace WebApi;

const T_AND				= 1;
const T_OR				= 2;
const T_LENGTH			= 3;
const T_TO_UPPER		= 4;
const T_TO_LOWER		= 5;
const T_SUBSTRING_OF	= 6;
const T_SUBSTRING		= 7;
const T_STARTS_WITH		= 8;
const T_ENDS_WITH		= 9;
const T_NOT				= 10;
const T_BLOCK			= 11;
const T_COLUMN			= 12;
const T_GT				= 13;
const T_LT				= 14;
const T_EQ				= 15;
const T_GE				= 16;
const T_LE				= 17;
const T_NE				= 18;
const T_TRUE			= 19;
const T_FALSE			= 20;
const T_NULL			= 21;
const T_REAL_VALUE		= 22;
const T_INT_VALUE		= 23;
const T_STR_VALUE		= 24;
const T_DATETIME_VALUE	= 25;
const T_PARAM			= 26;
const T_GUID_VALUE		= 27;

class QueryLexer {

    protected static $_terminals = array(
		"{(^ and )}" => T_AND,
		"{(^ or )}" => T_OR,
		"{(^length)}" => T_LENGTH,
		"{(^toupper)}" => T_TO_UPPER,
		"{(^tolower)}" => T_TO_LOWER,
		"{(^substringof)}" => T_SUBSTRING_OF,
		"{(^substring)}" => T_SUBSTRING,
		"{(^startswith)}" => T_STARTS_WITH,
		"{(^endswith)}" => T_ENDS_WITH,
		"{(^not )}" => T_NOT,
		"{(\(([^()]|(?R))*\))}" => T_BLOCK,
		"{( and )}" => T_AND,
		"{( or )}" => T_OR,
		"{(^[a-zA-Z\./]+)}" => T_COLUMN,
		"{( gt)}" => T_GT,
		"{( lt)}" => T_LT,
		"{( eq)}" => T_EQ,
		"{( ge)}" => T_GE,
		"{( le)}" => T_LE,
		"{( ne)}" => T_NE,
		"{( true)}" => T_TRUE,
		"{( false)}" => T_FALSE,
		"{( null)}" => T_NULL,
		"{( [0-9]+m)}" => T_REAL_VALUE,
		"{( [0-9]+)}" => T_INT_VALUE,
		"{( '[a-zA-Z0-9-('*)]+')$}" => T_STR_VALUE,
		"{( datetime'[A-Z0-9:.-]+')}" => T_DATETIME_VALUE,
		"{( guid'[a-z0-9-]+')}" => T_GUID_VALUE,
		"{(^[0-9]+m)}" => T_REAL_VALUE,
		"{(^[0-9]+)}" => T_INT_VALUE,
		"{(^'[a-zA-Z0-9-('*)]+')$}" => T_STR_VALUE,
		"{(^[a-zA-Z0-9,'\s]+)}" => T_PARAM

	);

	public static function run($source) {
		$tokens = array();
		$source = trim($source);
		$offset = 0;
		while($offset < strlen($source)) {
			$result = self::_match($source, $offset);

			if($result === false) {
				throw new \Exception("Unable to parse query");
			}
			$offset += strlen($result['match']);
			
			switch ($result["token"]) {
				case T_BLOCK:
					$result["match"] = self::run(preg_replace("{\(([^+]*)\)}", '${1}', $result['match']));
					break;
				case T_DATETIME_VALUE:
					$result["match"] = preg_replace("{ datetime'([A-Z0-9:.-]+)'}", '${1}', $result['match']);
					break;
				case T_GUID_VALUE:
					$result["match"] = preg_replace("{ guid'([a-z0-9-]+)'}", '${1}', $result['match']);
					break;
				case T_STR_VALUE:
					$result["match"] = trim(preg_replace("{'([a-zA-Z0-9':.-]+)'}", '${1}', $result['match']));
					break;
				case T_INT_VALUE:
					$result["match"] = intval($result['match']);
					break;
				case T_PARAM:
					$params = str_getcsv($result['match']);
					$result['match'] = array();
					foreach($params as $param) {
						if(strlen($param) > 0) {
							array_push($result['match'], self::run($param)[0]);
						}
					}
					break;
				case T_FALSE:
				case T_TRUE:
				case T_REAL_VALUE:
				case T_NULL:
					$result["match"] = trim($result['match']);
					break;
			}
			$tokens[] = $result;
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
