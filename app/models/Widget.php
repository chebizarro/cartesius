<?php
class Widget extends XMLModel
{
	public function users() {
        return $this->has_many_through('User');
    }
}

class UserWidget extends XMLModel
{
	public function user_widgets() {
		$this->set_orm($this->orm->join('widget', array('user_widget.widget_id', '=', 'widget.id'))->find_many());
		return $this;
    }

}

