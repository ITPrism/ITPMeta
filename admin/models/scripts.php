<?php
/**
 * @package      ITPrism Components
 * @subpackage   ITPMeta
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2010 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * ITPMeta is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

class ItpMetaModelScripts extends JModelAdmin {
    
    /**
     * @var     string  The prefix to use with controller messages.
     * @since   1.6
     */
    protected $text_prefix = 'COM_ITPMETA';
    
    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param   type    The table type to instantiate
     * @param   string  A prefix for the table class name. Optional.
     * @param   array   Configuration array for model. Optional.
     * @return  JTable  A database object
     * @since   1.6
     */
    public function getTable($type = 'Url', $prefix = 'ItpMetaTable', $config = array()){
        return JTable::getInstance($type, $prefix, $config);
    }
    
    /**
     * Method to get the record form.
     *
     * @param   array   $data       An optional array of data for the form to interogate.
     * @param   boolean $loadData   True if the form is to load its own data (default case), false if not.
     * @return  JForm   A JForm object on success, false on failure
     * @since   1.6
     */
    public function getForm($data = array(), $loadData = true){

        // Get the form.
        $form = $this->loadForm($this->option.'.scripts', 'scripts', array('control' => 'jform', 'load_data' => $loadData));
        if(empty($form)){
            return false;
        }
        
        return $form;
    }
    
    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed   The data for the form.
     * @since   1.6
     */
    protected function loadFormData(){
        // Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState($this->option.'.edit.scripts.data', array());
        
        if(empty($data)){
            
            $app = JFactory::getApplication();
        	/** @var $app JAdministrator **/
            
            $itemId = $app->input->get->get("url_id", 0, "integer");
            
            $data   = $this->getItem($itemId);
            if(empty($data->url_id)) {
                $data->url_id = $itemId;
            }
        }
        
        return $data;
    }
    
    
	/**
     * Save an item
     * 
     * @param $data        All data for the category in an array
     * 
     */
    public function save($data){
        
        $id         = JArrayHelper::getValue($data, "url_id", null);
        $afterBody  = JArrayHelper::getValue($data, "after_body_tag", "");
        $beforeBody = JArrayHelper::getValue($data, "before_body_tag", "");
        
        // Load item data
        $row = $this->getTable();
        $row->load($id);
        
        $row->set("after_body_tag", $afterBody);
        $row->set("before_body_tag", $beforeBody);
        $row->store();
        
        return $row->id;
    
    }
    
}