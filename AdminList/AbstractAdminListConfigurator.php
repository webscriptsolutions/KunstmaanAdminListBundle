<?php

namespace Kunstmaan\AdminListBundle\AdminList;

abstract class AbstractAdminListConfigurator
{

    private $fields = array();
    private $exportFields = array();
    private $customActions = array();
    private $listActions = array();
    private $type = null;
    private $listTemplate = 'KunstmaanAdminListBundle:Default:list.html.twig';
    private $addTemplate = 'KunstmaanAdminListBundle:Default:add.html.twig';
    private $editTemplate = 'KunstmaanAdminListBundle:Default:edit.html.twig';
    private $deleteTemplate = 'KunstmaanAdminListBundle:Default:delete.html.twig';

    abstract function buildFields();
    abstract function getEditUrlFor($item);
    abstract function getAddUrlFor($params = array());
    abstract function getDeleteUrlFor($item);
    abstract function getIndexUrlFor();
    abstract function getRepositoryName();

    /**
     * @param entity $entity
     *
     * @throws \Exception
     * @return AbstractType
     */
    public function getAdminType($entity)
    {
        if (!is_null($this->type)) {
            return $this->type;
        }

        if (method_exists($entity, "getAdminType")) {
            return $entity->getAdminType();
        }

        throw new \Exception("you need to implement the getAdminType method in " . get_class($this) . " or " . get_class($entity));
    }

    /**
     * @param AbstractType $type
     */
    public function setAdminType(AbstractType $type)
    {
        $this->type = $type;
    }

    public function buildFilters(AdminListFilter $builder)
    {
    }

    public function buildActions()
    {
    }

    public function canEdit()
    {
        return true;
    }

    function addField($fieldname, $fieldheader, $sort, $template = null)
    {
        $this->fields[] = new Field($fieldname, $fieldheader, $sort, $template);
    }

    function addExportField($fieldname, $fieldheader, $sort, $template = null)
    {
        $this->exportFields[] = new Field($fieldname, $fieldheader, $sort, $template);
    }

    public function canDelete($item)
    {
        return true;
    }

    public function canAdd()
    {
        return true;
    }

    public function canExport()
    {
        return false;
    }

    public function getExportUrlFor()
    {
        return "";
    }

    function getLimit()
    {
        return 10;
    }

    function getSortFields()
    {
        $array = array();
        foreach ($this->getFields() as $field) {
            if ($field->isSortable())
                $array[] = $field->getFieldname();
        }
        return $array;
    }

    function getFields()
    {
        return $this->fields;
    }

    function getExportFields()
    {
        if (empty($this->exportFields)) {
            return $this->fields;
        } else {
            return $this->exportFields;
        }
    }

    function configureListFields(&$array)
    {
        foreach ($this->getFields() as $field) {
            $array[$field->getFieldheader()] = $field->getFieldname();
        }
    }

    function adaptQueryBuilder($querybuilder, $params = array())
    {
        $querybuilder->where('1=1');
    }

    public function addSimpleAction($label, $url, $icon, $template = null)
    {
        $this->customActions[] = new SimpleAction($url, $icon, $label, $template);
    }

    public function addCustomAction($customaction)
    {
        $this->customActions[] = $customaction;
    }

    public function hasCustomActions()
    {
        return !empty($this->customActions);
    }

    public function getCustomActions()
    {
        return $this->customActions;
    }

    public function hasListActions()
    {
        return !empty($this->listActions);
    }

    public function getListActions()
    {
        return $this->listActions;
    }

    function getValue($item, $columnName)
    {
        if (is_array($item)) {
            if (isset($item[$columnName])) {
                return $item[$columnName];
            } else {
                return '';
                // return sprintf("undefined column %s", $columnName);
            }
        }
        $methodName = $columnName;
        if (method_exists($item, $methodName)) {
            $result = $item->$methodName();
        } else {
            $methodName = "get" . $columnName;
            if (method_exists($item, $methodName)) {
                $result = $item->$methodName();
            } else {
                $methodName = "is" . $columnName;
                if (method_exists($item, $methodName)) {
                    $result = $item->$methodName();
                } else {
                    $methodName = "has" . $columnName;
                    if (method_exists($item, $methodName)) {
                        $result = $item->$methodName();
                    } else {
                        return sprintf("undefined function [get/is/has]%s()", $columnName);
                    }
                }
            }
        }
        return $result;
    }

    function getStringValue($item, $columnName)
    {
        $result = $this->getValue($item, $columnName);
        if (is_bool($result)) {
            return $result ? "true" : "false";
        }
        if ($result instanceof \DateTime) {
            return $result->format('Y-m-d H:i:s');
        } else if ($result instanceof \Doctrine\ORM\PersistentCollection) {
            $results = "";
            foreach ($result as $entry) {
                $results[] = $entry->getName();
            }
            if (empty($results)) {
                return "";
            }
            return implode(', ', $results);
        } else if (is_array($result)) {
            return implode(', ', $result);
        } else {
            return $result;
        }
    }

    public function addListAction(ListActionInterface $listAction)
    {
        $this->listActions[] = $listAction;
    }

    public function useNativeQuery()
    {
        return false;
    }

    function adaptNativeCountQueryBuilder($querybuilder, $params = array())
    {
        throw new \Exception('You have to implement the native count query builder!');
    }

    function adaptNativeItemsQueryBuilder($querybuilder, $params = array())
    {
        throw new \Exception('You have to implement the native items query builder!');
    }

    public function getListTemplate() {
        return $this->listTemplate;
    }

    public function setListTemplate($template) {
        $this->listTemplate = $template;
    }

    public function getAddTemplate() {
        return $this->addTemplate;
    }

    public function setAddTemplate($template) {
        $this->addTemplate = $template;
    }

    public function getEditTemplate() {
        return $this->editTemplate;
    }

    public function setEditTemplate($template) {
        $this->editTemplate = $template;
    }

    public function getDeleteTemplate() {
        return $this->deleteTemplate;
    }

    public function setDeleteTemplate($template) {
        $this->deleteTemplate = $template;
    }

    /**
     * You can override this method to do some custom things you need to do when adding an entity
     * @param entity $entity
     */
    public function decorateNewEntity($entity) {
        return $entity;
    }

}
