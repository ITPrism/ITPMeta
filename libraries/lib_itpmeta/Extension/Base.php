<?php
/**
 * @package      ItpMeta
 * @subpackage   Extensions
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace ItpMeta\Extension;

defined('JPATH_PLATFORM') or die;

/**
 * The base class that will be used
 * for collecting meta data for third-party extensions.
 *
 * @package ItpMeta
 * @subpackage   Extensions
 */
abstract class Base
{
    /**
     * Database driver.
     *
     * @var \JDatabaseDriver
     */
    protected $db;

    protected $view;
    protected $task;
    protected $menuItemId;

    protected $genMetaDesc = false;

    abstract public function getData($options = array());

    /**
     * Initialize the object.
     *
     * <code>
     * $options = array(
     *    "view" => "article",
     *    "task" => "...",
     *    "menu_item_id" => 1,
     *    "generate_metadesc" => true, // Generate or not meta description.
     * );
     *
     * $extension = new ItpMeta\Extension\Content($options);
     * </code>
     *
     * @param $options
     */
    public function __construct($options)
    {
        $this->view        = \JArrayHelper::getValue($options, "view");
        $this->task        = \JArrayHelper::getValue($options, "task");
        $this->menuItemId  = \JArrayHelper::getValue($options, "menu_item_id");
        $this->genMetaDesc = \JArrayHelper::getValue($options, "generate_metadesc", false, "bool");
    }

    /**
     * Set database object.
     *
     * <code>
     * $extension = new ItpMeta\Extension\Content("/my-page", $options);
     * $extension->setDb(/JFactory::getDbo());
     * </code>
     *
     * @param \JDatabaseDriver $db
     */
    public function setDb(\JDatabaseDriver $db)
    {
        $this->db = $db;
    }

    /**
     * Prepare page data based on menu item.
     *
     * @param int $menuItemId
     *
     * @return array
     */
    protected function getDataByMenuItem($menuItemId)
    {
        $data = array();

        $menuItem         = $this->getMenuItem($menuItemId);

        $data["id"]       = null;
        $data["created"]  = null;
        $data["modified"] = null;

        $data["title"]    = (!$menuItem->params->get("page_title")) ? $menuItem->title : $menuItem->params->get("page_title");
        $data["metadesc"] = $menuItem->params->get("menu-meta_description");
        $data["image"]    = $menuItem->params->get("menu_image");

        return $data;
    }

    /**
     * Get a menu item object.
     *
     * @param int $menuItemId
     *
     * @return null|object
     */
    protected function getMenuItem($menuItemId)
    {
        $app  = \JFactory::getApplication();
        $menu = $app->getMenu();

        $menuItem = $menu->getItem($menuItemId);

        return $menuItem;
    }

    /**
     * Prepare data about category.
     *
     * @param int $categoryId
     * @param string $viewName
     *
     * @return array
     */
    protected function getCategoryData($categoryId, $viewName = "category")
    {
        $data     = array();

        if (!empty($categoryId)) {

            $excluded = array("params", "description");

            $query = $this->db->getQuery(true);

            $query
                ->select("a.title, a.description, a.params, a.metadesc, a.created_time AS created, a.modified_time AS modified")
                ->from($this->db->quoteName("#__categories", "a"))
                ->where("a.id=" . (int)$categoryId);

            $this->db->setQuery($query);
            $result = (array)$this->db->loadAssoc();

            if (!empty($result)) {

                foreach ($result as $key => $value) {
                    if (!in_array($key, $excluded)) {
                        $data[$key] = \JString::trim($value);
                    }
                }

                // Get image
                $params        = json_decode($result["params"]);
                $data["image"] = null;

                if (!empty($params->image)) {
                    $data["image"] = $params->image;
                }

                // If it is a menu item, get menu item meta data.
                $menuItem   = $this->getMenuItem($this->menuItemId);

                // Use menu item title and description, if the items is set to a menu item.
                if ((strcmp($viewName, $menuItem->query["view"]) == 0) and ($categoryId == (int)$menuItem->query["id"])) {

                    $menuItemData = $this->getDataByMenuItem($this->menuItemId);

                    // Get title
                    if (!empty($menuItemData["title"])) {
                        $data["title"] = $menuItemData["title"];
                    }

                    // Get meta description
                    if (!empty($menuItemData["metadesc"])) {
                        $data["metadesc"] = $menuItemData["metadesc"];
                    }

                }

                // Generate meta description from category description.
                if (!$data["metadesc"] and !empty($this->genMetaDesc)) {
                    $data["metadesc"] = $this->prepareMetaDesc($result["description"]);
                }

                unset($result);
            }

        }

        return $data;
    }

    /**
     * Prepare default data that comes from menu item or document object.
     *
     * @return array
     */
    protected function getDefaultData()
    {
        $data = $this->getDataByMenuItem($this->menuItemId);

        if (!empty($data)) {

            $doc = \JFactory::getDocument();

            if (empty($data["title"])) {
                $data["title"] = $doc->getTitle();
            }

            if (empty($data["metadesc"])) {
                $data["metadesc"] = $doc->getDescription();
            }

        } else {
            $data = array();
        }

        return $data;
    }

    protected function clean($content)
    {
        $content = strip_tags($content);

        return \JString::trim(preg_replace('/\r|\n/', ' ', $content));
    }

    protected function prepareMetaDesc($content)
    {
        $minLength = 50;
        $length    = 160;
        $strLength = \JString::strlen($content);

        $metaDesc = "";

        $content = $this->clean($content);

        if ($minLength <= \JString::strlen($content)) {

            if ($strLength > $length) {
                $pos      = \JString::strpos($content, ' ', $length);
                $metaDesc = \JString::substr($content, 0, $pos);
            } else {
                $metaDesc = $content;
            }
        }

        return $metaDesc;
    }
}
