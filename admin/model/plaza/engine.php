<?php
class ModelPlazaEngine extends Model
{
    public function setupEngine() {
        $this->setPermission();

        $this->load->model('plaza/modification');
        $this->model_plaza_modification->setModifications();

        $this->load->model('plaza/content_builder');
        $this->model_plaza_content_builder->setup();
    }

    public function setPermission() {
        $this->load->model('user/user_group');

        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'plaza/engine');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'plaza/content_builder');

        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'plaza/engine');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'plaza/content_builder');
    }

    public function displayMenuFeatures() {
        $menuItems = array();


    }
}