<?php
/**
 * @package      ITPMeta
 * @subpackage   Component
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Tags Controller
 *
 * @package     ITPrism Components
 * @subpackage  ITPMeta
 */
class ItpmetaControllerTags extends Prism\Controller\Admin
{
    public function getModel($name = 'Tag', $prefix = 'ItpmetaModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }

    /**
     * Remove an item.
     *
     * @throws  Exception
     * @return  void
     *
     * @since   12.2
     */
    public function delete()
    {
        // Check for request forgeries
        JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

        $app = JFactory::getApplication();
        /* @var $app JApplicationAdministrator */

        // Gets the data from the form
        $cid = $this->input->post->get('cid', array(), 'array');
        $cid = Joomla\Utilities\ArrayHelper::toInteger($cid);

        $urlId = $app->getUserState('url.id');

        $redirectData = array(
            'view'   => 'url',
            'layout' => 'edit',
            'id'     => $urlId
        );

        if (!$cid) {
            $this->displayWarning(JText::_('COM_ITPMETA_ERROR_INVALID_ITEMS'), $redirectData);
            return;
        }

        $model = $this->getModel();
        /** @var ItpmetaModelTag $model */

        try {
            $model->delete($cid);
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_itpmeta');
            throw new Exception(JText::_('COM_ITPMETA_ERROR_SYSTEM'));
        }

        $msg = JText::plural($this->text_prefix . '_N_ITEMS_DELETED', count($cid));
        $this->displayMessage($msg, $redirectData);
    }
}
