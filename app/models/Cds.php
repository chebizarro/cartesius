<?php
class Cds extends XMLModel
{
	public function widgets() {
		return $this->has_many_through('Widget');
	}
}


