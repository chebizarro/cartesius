<?php

class peopleList
{

	public function getData($format) {
		if($format == 'json')
			return Model::factory('Account')->select_many('email', 'username')->find_array();
	}

}


