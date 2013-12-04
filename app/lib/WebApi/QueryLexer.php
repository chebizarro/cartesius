<?php

namespace WebApi;

class QueryLexer {

    protected static $_terminals = array(
		
		"expand" => array(
			"{(^,[a-zA-Z][^/]+)$}" => T_RESOURCE,
			"{(^[a-zA-Z][^/]+),}" => T_RESOURCE,
			"{(^[a-zA-Z][^/]+)$}" => T_RESOURCE,
			"{(^,[a-zA-Z/]+)}" => T_EXPAND,
			"{(^[a-zA-Z/\.]+)}" => T_EXPAND,
			"{(^[a-zA-Z,/\s]+)}" => T_RESOURCE,
			"{(^[a-zA-Z'\s]+)}" => T_RESOURCE,

		),
		
		"filter" => array(
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
			"{(^[a-zA-Z\./]+)}" => T_RESOURCE,
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
			"{(^[a-zA-Z0-9,'\s]+)}" => T_PARAM,
			
		),
		
		"select" => array(
			"{(^,[a-zA-Z][^/]+)$}" => T_RESOURCE,
			"{(^[a-zA-Z][^/]+),}" => T_RESOURCE,
			"{(^[a-zA-Z][^/]+)$}" => T_RESOURCE,
			"{(^,[a-zA-Z/]+)}" => T_EXPAND,
			"{(^[a-zA-Z/\.]+)}" => T_EXPAND,
			"{(^[a-zA-Z,/\s]+)}" => T_RESOURCE,
			"{(^[a-zA-Z'\s]+)}" => T_RESOURCE,

		),
		"orderby" => array(
			"{(^,[a-zA-Z][^/]+ desc,)}" => T_ORDERBYDESC,
			"{(^,[a-zA-Z][^/]+,)}" => T_ORDERBY,
			"{(^,[a-zA-Z][^/]+ desc$)}" => T_ORDERBYDESC,
			"{(^,[a-zA-Z][^/]+)$}" => T_ORDERBY,
			"{(^[a-zA-Z][^/]+ desc,)}" => T_ORDERBYDESC,
			"{(^[a-zA-Z][^/]+),}" => T_ORDERBY,
			"{(^[a-zA-Z'\s]+ desc$)}" => T_ORDERBYDESC,
			"{(^[a-zA-Z'\s]+)$}" => T_ORDERBY,
			"{(^,[a-zA-Z/]+ desc)}" => T_EXPAND,
			"{(^[a-zA-Z/\.]+ desc)}" => T_EXPAND,
			"{(^,[a-zA-Z/]+)}" => T_EXPAND,
			"{(^[a-zA-Z/\.]+)}" => T_EXPAND,
		),

	);

	public static function run($query, $source) {
		$tokens = array();
		$source = trim($source);
		$offset = 0;
		while($offset < strlen($source)) {
			$result = self::_match($query, $source, $offset);

			if($result === false) {
				throw new \Exception("Unable to parse query");
			}
			$offset += strlen($result['match']);
			
			switch ($result["token"]) {
				case T_RESOURCE:
					$expand = explode("/",str_replace(",","",$result["match"]));
					if(count($expand) > 1) {
						$result["match"] = array();
						$result["token"] = T_EXPAND;
						foreach($expand as $expanded) {
							array_push($result["match"], self::run($query, $expanded)[0]);
						}
					} else {
						$result["match"] = $expand[0];
					}
					break;
				case T_ORDERBYDESC:
					$result["match"] = str_replace(" desc","",$result["match"]);
				case T_ORDERBY:
					$result["match"] = str_replace(",","",$result["match"]);
					break;
				case T_EXPAND:
					$expand = explode("/",str_replace(",","",$result["match"]));
					$result["match"] = array();
					foreach($expand as $expanded) {
						array_push($result["match"], self::run($query, $expanded)[0]);
					}
					break;
				case T_BLOCK:
					$result["match"] = self::run($query, preg_replace("{\(([^+]*)\)}", '${1}', $result['match']));
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
							array_push($result['match'], self::run($query, $param)[0]);
						}
					}
					break;
				case T_FALSE:
				case T_TRUE:
				case T_REAL_VALUE:
				case T_NULL:
					$result["match"] = trim($result['match']);
					break;
				case T_GT: $result["match"] = ">"; break;
				case T_LT: $result["match"] = "<"; break;
				case T_EQ: $result["match"] = "="; break;
				case T_GE: $result["match"] = ">="; break;
				case T_LE: $result["match"] = "<="; break;
				case T_NE: $result["match"] = "!="; break;

			}
			$tokens[] = $result;
		}
	 
		return $tokens;
	}

	protected static function _match($query, $line, $offset) {
		$string = substr($line, $offset);

		foreach(self::$_terminals[$query] as $pattern => $name) {
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
