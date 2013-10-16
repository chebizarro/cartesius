<?php
class Workbench extends XMLModel
{
	public function layers() {
		return $this->has_many_through('Layer', 'WorkbenchLayer');
	}
}

class Layer extends XMLModel
{
	public function workbenches() {
		return $this->has_many_through('Workbench', 'WorkbenchLayer');
	}
}

class WorkbenchLayer extends XMLModel
{
	public function layers() {
		$this->set_orm($this->orm->join('layer', array('workbench_layer.layer_id', '=', 'layer.id'))->find_many());
		return $this;
    }

}
