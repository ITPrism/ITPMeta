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
 * ITPMeta URLs Controller
 *
 * @package     ITPrism Components
 * @subpackage  ITPMeta
 */
class ItpmetaControllerUrls extends Prism\Controller\Admin
{
    public function __construct($config = array())
    {
        parent::__construct($config);

        // Define task mappings.

        // Value = 0
        $this->registerTask('disableau', 'enableau');
    }

    public function getModel($name = 'Url', $prefix = 'ItpmetaModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }

    /**
     * Remove items.
     *
     * @throws  Exception
     * @return  void
     * @since   11.1
     */
    public function delete()
    {
        $redirectOptions = array(
            'view' => 'urls'
        );

        $cid       = $this->input->post->get('cid', array(), 'array');

        $modelTags = $this->getModel('Tag');
        /** @var ItpmetaModelTag $modelTags */

        try {
            $modelTags->deleteByUrlId($cid);
            parent::delete();
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_itpmeta');
            throw new Exception(JText::_('COM_ITPMETA_ERROR_SYSTEM'));
        }

        $msg = JText::plural($this->text_prefix . '_N_ITEMS_DELETED', count($cid));
        $this->displayMessage($msg, $redirectOptions);
    }

    /**
     * Enable auto-update.
     *
     * @throws  Exception
     * @return  void
     */
    public function enableau()
    {
        // Check for request forgeries
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $redirectOptions = array(
            'view' => 'urls'
        );

        $cid = $this->input->post->get('cid', array(), 'array');
        $cid = Joomla\Utilities\ArrayHelper::toInteger($cid);

        $data = array(
            'enableau'  => Prism\Constants::ENABLED,
            'disableau' => Prism\Constants::DISABLED
        );

        $task  = $this->getTask();
        $value = Joomla\Utilities\ArrayHelper::getValue($data, $task, 0, 'int');

        if (count($cid) === 0) {
            $this->displayNotice(JText::_($this->text_prefix . '_ERROR_NO_ITEM_SELECTED'), $redirectOptions);
            return;
        }

        $model = $this->getModel();
        /** @var ItpmetaModelUrl $model */

        try {
            $model->updateAutoupdate($cid, $value);
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_itpmeta');
            throw new Exception(JText::_('COM_ITPMETA_ERROR_SYSTEM'));
        }

        if ((int)$value === Prism\Constants::ENABLED) {
            $msg = $this->text_prefix . '_N_ITEMS_AUTOUPDATE_ENABLED';
        } else {
            $msg = $this->text_prefix . '_N_ITEMS_AUTOUPDATE_DISABLED';
        }

        $this->displayMessage(JText::plural($msg, count($cid)), $redirectOptions);
    }
}
