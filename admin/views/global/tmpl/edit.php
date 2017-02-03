<?php
/**
 * @package      ITPMeta
 * @subpackage   Component
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>
<div class="row-fluid">
	<div class="span6 form-horizontal">
        <form action="<?php echo JRoute::_('index.php?option=com_itpmeta'); ?>" method="post" name="adminForm" id="global-form" class="form-validate">
        	<fieldset>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('title'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('title'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('published'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('published'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('id'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('id'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('type'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('type'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('content'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('content'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('tag'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('tag'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('output'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('output'); ?></div>
                </div>
            </fieldset>
            
            <?php echo $this->form->getInput('name'); ?>
            <input type="hidden" name="task" value="" />
            <?php echo JHtml::_('form.token'); ?>
        </form>
    </div>

	<?php 
    $layout = new JLayoutFile('tags', $basePath = JPATH_COMPONENT_ADMINISTRATOR .'/layouts');	
	echo $layout->render(null);
	?>
</div>
