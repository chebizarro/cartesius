<?php

class layers
{

	public function getData($format) {
		if($format == 'json')
			return Model::factory('Layer')->find_array();
	}

}

class Layer extends XMLModel
{
	
}

