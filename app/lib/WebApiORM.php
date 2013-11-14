<?php

    /**
     *
     * ORM subclass of Idiorm
     *
     *
     */
     
	namespace WebApi\ORM;

    class ORM extends \ORM {
        
        protected $xml = null;
    
        /**
         * Despite its slightly odd name, this is actually the factory
         * method used to acquire instances of the class. It is named
         * this way for the sake of a readable interface, ie
         * ORM::for_table('table_name')->find_one()-> etc. As such,
         * this will normally be the first method called in a chain.
         * @param string $table_name
         * @param string $connection_name Which connection to use
         * @return ORM
         */
        public static function for_table($table_name, $connection_name = self::DEFAULT_CONNECTION) {
            self::_setup_db($connection_name);
            return new self($table_name, array(), $connection_name);
        }

 
 
        /**
         * Create an ORM instance from the given row (an associative
         * array of data fetched from the database)
         */
        protected function _create_instance_from_row($row) {
            $instance = self::for_table($this->_table_name, $this->_connection_name);
            $instance->use_id_column($this->_instance_id_column);
            $instance->hydrate($row);
            return $instance;
        }

        /**
         * Tell the ORM that you are expecting multiple results
         * from your query, and execute it. Will return a result set object
         * containing instances of the ORM class.
         * @return \ResultSet
         */
        public function find_result_set() {
            return new ResultSet($this->_find_many());
        }

        public function as_json() {
            if (func_num_args() === 0) {
                return json_encode($this->_data);
            }
            $args = func_get_args();
            return json_encode(array_intersect_key($this->_data, array_flip($args)));
        }


		public function as_xml() {
			$data = null;
            if (func_num_args() === 0) {
                $data = $this->_data;
            } else {
				$args = func_get_args();
				$data = array_intersect_key($this->_data, array_flip($args));
			}
						 
			$this->xml = new \DOMDocument();
			$root = $this->xml->appendChild($this->xml->createElement("root"));
			$root = $root->appendChild($this->xml->createElement("data"));
			$this->array_to_xml($root,$this->xml, $data);
			
            return $this->xml;

		}

		// function defination to convert array to xml
		public function array_to_xml($node, $doc = null, $data = null) {
			$table = $this->_table_name;
            if (!is_null($this->_table_alias)) {
				$table = $this->_table_alias;
            }
			
			if(is_null($doc))
				$doc = $this->xml;
				
			if(is_null($data))
				$data = $this->_data;
			
			$tnode = $node->appendChild($doc->createElement($table));
			foreach($data as $key => $value) {
				$tnode->appendChild($doc->createElement($key, $value));
			}
		}

        /**
         * Magic method to capture calls to undefined static class methods. 
         * In this case we are attempting to convert camel case formatted 
         * methods into underscore formatted methods.
         *
         * This allows us to call ORM methods using camel case and remain 
         * backwards compatible.
         * 
         * @param  string   $name
         * @param  array    $arguments
         * @return ORM
         */
        public static function __callStatic($name, $arguments)
        {
            $method = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $name));

            return call_user_func_array(array('ORM', $method), $arguments);
        }
        
        
    }


    /**
     * A result set class for working with collections of model instances
     */
     
    class ResultSet extends \IdiormResultSet {

		protected $xml = null;


        public function as_json() {
            
            $data = $this->get_results();
            $json = array();
            
            foreach($data as $key => $value) {
				array_push($json, $value->as_array());
			}
			
			return json_encode($json);
            
        }

  		
        public function as_xml() {
			 
			$this->xml = new \DOMDocument();
			$root = $this->xml->appendChild($this->xml->createElement("root"));
			$root = $root->appendChild($this->xml->createElement("data"));

			$this->array_to_xml($root, $this->get_results());
            return $this->xml;
        }
		
		public function array_to_xml($node, $data) {
			foreach($data as $key => $value) {
				$value->array_to_xml($node, $this->xml, null);
			}
		}
				
    }
