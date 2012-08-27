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
?>
<tr>
    <th width="15">
        <input type="checkbox" name="checkall-toggle" value="" onclick="checkAll(this)" />
    </th>
    <th class="title">
        <?php echo JHtml::_('grid.sort',  'COM_ITPMETA_TITLE', 'a.title', $this->listDirn, $this->listOrder); ?>
    </th>
    <th>
        <?php echo JText::_("COM_ITPMETA_TAG"); ?>
    </th>
    <th width="30"><?php echo JHtml::_('grid.sort',  'JPUBLISHED', 'a.published', $this->listDirn, $this->listOrder); ?></th>
    <th width="15"><?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ID', 'a.id', $this->listDirn, $this->listOrder); ?></th>
</tr>
	  