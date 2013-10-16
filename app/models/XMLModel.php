<?php

interface XML {
	public function getDOM(\DOMDocument $document);
	public function getXML(\DOMDocument $document);
}

interface JSON {
	public function getJSON();
}

class XMLModel extends Model implements XML, JSON
{
	
	protected $dom;
	
	public function getJSON() {
	}
	
	public function getXML(\DOMDocument $document) {
		
	}

	public function getDOM(\DOMDocument $document) {
		if($document == null)
			return;
	
		$result = $document->createDocumentFragment();
		
		$table_name = self::_get_table_name(get_class($this));
		if(is_array($this->orm)) {
			for($i = 0; $i < count($this->orm); $i++) {
				$element = $document->createElement($table_name);
				foreach ($this->orm[$i]->as_array() as $key => $value) {
					$element->appendChild($document->createElement($key, $value));
				}
				$result->appendChild($element);
			}
		} elseif(is_object($this->orm)) {
			$element = $document->createElement($table_name);
			foreach ($this->orm->as_array() as $key => $value) {
				$element->appendChild($document->createElement($key, $value));
			}
			$result = $element;

		}
		return $result;
	}
}
