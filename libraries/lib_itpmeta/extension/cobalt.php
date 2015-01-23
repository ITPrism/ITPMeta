<?php
/**
 * @package      ITPMeta
 * @subpackage   Libraries
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

jimport("itpmeta.tag");

/**
 * This helper provides functionality
 * for Cobalt (com_cobalt)
 */
class ItpMetaExtensionCobalt extends ItpMetaExtension
{
    public function getData()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $view = "";
        $data = array();

        // Parse the URL
        $router = $app->getRouter();
        $parsed = $router->parse($this->uri);

        $id         = JArrayHelper::getValue($parsed, "id");
        $sectionId  = JArrayHelper::getValue($parsed, "section_id");
        $categoryId = JArrayHelper::getValue($parsed, "cat_id");

        $userId = JArrayHelper::getValue($parsed, "user_id");

        $userCategoryId = JArrayHelper::getValue($parsed, "ucat_id");

        // If missing ID I have to get information from menu item.
        if (!is_null($id)) {
            $view = "item";
        } elseif (!is_null($sectionId) and !is_null($userCategoryId)) { // It is user category
            $view = "usercategory";
        } elseif (!is_null($sectionId) and !is_null($categoryId)) { // It is category
            $view = "category";
        } elseif (!is_null($sectionId) and is_null($categoryId) and is_null($userId)) { // It is section
            $view = "section";
        } elseif (!is_null($sectionId) and !is_null($userId)) { // It is author profile
            $view = "author";
        }

        switch ($view) {

            case "item":
                $data = $this->getItemData($id);
                break;

            case "category":
                $data = $this->getCobaltCategoryData($categoryId);
                break;

            case "usercategory":
                $data = $this->getUserCategoryData($userCategoryId);
                break;

            case "section":
                $data = $this->getSectionData($sectionId);
                break;

            case "author":
                $data = $this->getAuthorData($userId, $sectionId);
                break;

            default: // Get menu item
                if (!empty($this->menuItemId)) {
                    $data = $this->getDataByMenuItem($this->menuItemId);
                }
                break;
        }

        return $data;
    }

    /**
     * Extract data about category
     */
    public function getCobaltCategoryData($categoryId)
    {
        if (!$categoryId) {
            return null;
        }

        $data = array();

        $query = $this->db->getQuery(true);
        $query
            ->select("a.id, a.title, a.description, a.metadesc, a.image, a.created_time AS created, a.modified_time AS modified")
            ->from($this->db->quoteName("#__js_res_categories", "a"))
            ->where("a.id=" . (int)$categoryId);

        $this->db->setQuery($query);
        $result = $this->db->loadAssoc();

        if (!empty($result)) {

            foreach ($result as $key => $value) {
                $data[$key] = $value;
            }
            unset($results);

            $data["metadesc"] = $this->clean($data["metadesc"]);

            // Generate meta description from textarea or HTML field.
            if (!$data["metadesc"] and !empty($this->genMetaDesc)) {
                $data["metadesc"] = $this->prepareMetaDesc($data["description"]);
            }

        }

        return $data;

    }

    /**
     * Extract data about category
     */
    public function getAuthorData($userId, $sectionId)
    {
        if (!$userId) {
            return null;
        }

        $data = array();

        $query = $this->db->getQuery(true);
        $query
            ->select("a.name, b.name AS section")
            ->from($this->db->quoteName("#__users", "a"))
            ->from($this->db->quoteName("#__js_res_sections", "b"))
            ->where("a.id = " . (int)$userId)
            ->where("b.id = " . (int)$sectionId);

        $this->db->setQuery($query);
        $result = $this->db->loadAssoc();

        if (!empty($result)) {
            $data["title"]    = JText::sprintf("LIB_ITPMETA_VIEW_USER_TITLE", $result["name"]);
            $data["metadesc"] = JText::sprintf("LIB_ITPMETA_VIEW_SECTION_USER_METADESC", $result["section"], $result["name"]);
        }

        return $data;
    }

    /**
     * Extract data about user category
     */
    public function getUserCategoryData($categoryId)
    {
        if (!$categoryId) {
            return null;
        }

        $data = array();

        $query = $this->db->getQuery(true);
        $query
            ->select("a.id, a.name AS title, a.description, a.params, a.ctime AS created, a.mtime AS modified")
            ->from($this->db->quoteName("#__js_res_category_user", "a"))
            ->where("a.id=" . (int)$categoryId);

        $this->db->setQuery($query);
        $result = $this->db->loadAssoc();

        if (!empty($result)) {

            $params = JArrayHelper::getValue($result, "params");
            unset($result["params"]);

            foreach ($result as $key => $value) {
                $data[$key] = $value;
            }

            $params = json_decode($params, true);

            $data["metadesc"] = JArrayHelper::getValue($params, "meta_descr");
            $data["image"]    = JArrayHelper::getValue($params, "image");
            unset($params);
            unset($result);

            $data["metadesc"] = $this->clean($data["metadesc"]);

            // Generate meta description from textarea or HTML field.
            if (!$data["metadesc"] and !empty($this->genMetaDesc)) {
                $data["metadesc"] = $this->prepareMetaDesc($data["description"]);
            }

        }

        return $data;
    }

    /**
     * Extract data about item.
     */
    public function getItemData($itemId)
    {
        if (!$itemId) {
            return null;
        }

        $data = array();

        $query = $this->db->getQuery(true);
        $query
            ->select("a.id, a.title, a.meta_descr AS metadesc, a.ctime AS created, a.mtime AS modified")
            ->from($this->db->quoteName("#__js_res_record", "a"))
            ->where("a.id=" . (int)$itemId);

        $this->db->setQuery($query);
        $result = $this->db->loadAssoc();

        if (!empty($result)) {

            // Prepare data
            foreach ($result as $key => $value) {
                $data[$key] = JString::trim($value);
            }
            unset($results);

            $data["metadesc"] = $this->clean($data["metadesc"]);

            // Get images
            $data["image"] = $this->getItemImage($itemId);

            // Generate meta description from textarea or HTML field.
            if (!$data["metadesc"] and !empty($this->genMetaDesc)) {
                $data["metadesc"] = $this->getItemDescription($itemId);
            }

        }

        return $data;

    }

    /**
     * Get image of an item from database.
     *
     * @param integer $itemId
     *
     * @return NULL|string
     */
    protected function getItemImage($itemId)
    {
        $imageData = array();

        // Get images
        $query = $this->db->getQuery(true);
        $query
            ->select("a.field_label AS title, a.field_value AS image")
            ->from($this->db->quoteName("#__js_res_record_values", "a"))
            ->where("a.record_id  = " . (int)$itemId)
            ->where("a.field_type = " . $this->db->quote("image"));

        $this->db->setQuery($query);
        $results = $this->db->loadAssocList();

        if (!empty($results)) {
            $imageData = array_shift($results);
            unset($results);
        }

        return JArrayHelper::getValue($imageData, "image");
    }

    /**
     * Generate meta description from textarea or HTML fields.
     *
     * @param integer $itemId
     *
     * @return string
     */
    public function getItemDescription($itemId)
    {
        $metaDesc = "";

        // Get images
        $query = $this->db->getQuery(true);
        $query
            ->select("a.field_type AS type, a.field_value AS text")
            ->from($this->db->quoteName("#__js_res_record_values", "a"))
            ->where("a.record_id  = " . (int)$itemId)
            ->where("(a.field_type = " . $this->db->quote("html") . " OR " . "a.field_type = " . $this->db->quote("textarea") . ")");

        $this->db->setQuery($query);
        $results = $this->db->loadAssocList();

        if (!empty($results)) {

            $htmlFields     = array();
            $textAreaFields = array();

            foreach ($results as $value) {
                if (strcmp("html", $value["type"]) == 0) {
                    $htmlFields[] = $value;
                } else {
                    $textAreaFields[] = $value;
                }
            }

            // Generate meta description from HTML field.
            foreach ($htmlFields as $value) {
                $metaDesc = $this->prepareMetaDesc($value["text"]);
                if (!empty($metaDesc)) {
                    break;
                }
            }

            // Generate meta description from TextArea field.
            if (!$metaDesc) {

                foreach ($textAreaFields as $value) {
                    $metaDesc = $this->prepareMetaDesc($value["text"]);
                    if (!empty($metaDesc)) {
                        break;
                    }
                }

            }

            unset($htmlFields);
            unset($textAreaFields);
            unset($results);
        }

        return $metaDesc;
    }

    /**
     * Extract data about section.
     */
    public function getSectionData($sectionId)
    {
        if (!$sectionId) {
            return null;
        }

        $data = array();

        $query = $this->db->getQuery(true);
        $query
            ->select("a.id, a.title, a.description")
            ->from($this->db->quoteName("#__js_res_sections", "a"))
            ->where("a.id=" . (int)$sectionId);

        $this->db->setQuery($query);
        $result = $this->db->loadAssoc();

        if (!empty($result)) {

            $data["title"] = JArrayHelper::getValue($result, "title");

            $description = JArrayHelper::getValue($result, "description");

            $metaDesc         = JString::substr(JString::trim(strip_tags($description)), 0, 160);
            $data["metadesc"] = $metaDesc;

        }

        return $data;
    }
}