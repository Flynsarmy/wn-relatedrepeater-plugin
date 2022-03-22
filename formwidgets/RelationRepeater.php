<?php

namespace Flynsarmy\RelatedRepeater\FormWidgets;

use ApplicationException;
use Lang;
use Backend\FormWidgets\Repeater;
use Illuminate\Database\Eloquent\Collection;

/**
 * The Repeater widget with added support for using it with relation fields.
 */
class RelationRepeater extends Repeater
{
    protected bool $isRelation = false;
    protected string $relationModelClass = '';
    protected Collection $relatedData;

    public function init()
    {
        // Determines if the Repeater will work as a relation or a JSON field
        // Only hasMany relation types are supported
        $this->isRelation =
            $this->model->hasRelation($this->valueFrom) &&
            $this->model->getRelationType($this->valueFrom) === 'hasMany';

        if ($this->isRelation) {
            $this->relationModelClass = $this->model->getRelationDefinition($this->valueFrom)[0];
            $this->sortable = in_array('Winter\Storm\Database\Traits\Sortable', class_uses($this->relationModelClass));
        }
        // $this->addViewPath("modules/backend/formwidgets/repeater/partials");

        parent::init();
    }

    public function prepareVars()
    {
        parent::prepareVars();

        $this->vars['isRelation'] = $this->isRelation;
        $this->vars['sortable'] = $this->sortable;
    }

    protected function loadAssets()
    {
        $this->assetPath = "/modules/backend/formwidgets/repeater/assets";
        parent::loadAssets();
    }

    protected function processSaveValue($value)
    {
        if (!is_array($value) || !$value) {
            return $value;
        }

        if ($this->minItems && count($value) < $this->minItems) {
            throw new ApplicationException(Lang::get('backend::lang.repeater.min_items_failed', ['name' => $this->fieldName, 'min' => $this->minItems, 'items' => count($value)]));
        }
        if ($this->maxItems && count($value) > $this->maxItems) {
            throw new ApplicationException(Lang::get('backend::lang.repeater.max_items_failed', ['name' => $this->fieldName, 'max' => $this->maxItems, 'items' => count($value)]));
        }

        /*
         * Give repeated form field widgets an opportunity to process the data.
         */
        foreach ($value as $index => $data) {
            if (isset($this->formWidgets[$index])) {
                if ($this->useGroups) {
                    $value[$index] = array_merge($this->formWidgets[$index]->getSaveData(), ['_group' => $data['_group']]);
                } else {
                    $value[$index] = $this->formWidgets[$index]->getSaveData();
                }

                if (isset($data['_id'])) {
                    $value[$index] = array_merge($value[$index], ['_id' => $data['_id']]);
                }
            }
        }

        $value = array_values($value);

        if ($this->isRelation) {
            $this->loadRelatedData();

            foreach ($value as $index => $data) {
                $record = null;

                if ($this->sortable) {
                    $data['sort_order'] = $index + 1;
                }

                // Try to load an existing related record
                if (isset($data['_id'])) {
                    $record = $this->relatedData->firstWhere('id', $data['_id']);
                }

                // No existing related record. Create a new one
                if (!$record) {
                    $recordModel = get_class($this->formWidgets[$index]->model);
                    $record = new $recordModel();
                }

                $record->fill($data);
                $record->save();

                // Recreate them
                $model = $this->model->{$this->valueFrom}()->add($record, $this->sessionKey);
            }

            $value = [];
        }

        return $value;
    }

    /**
     * Creates a form widget based on a field index and optional group code.
     * @param int $index
     * @param string $index
     * @return \Backend\Widgets\Form
     */
    protected function makeItemFormWidget($index = 0, $groupCode = null)
    {
        $configDefinition = $this->useGroups
            ? $this->getGroupFormFieldConfig($groupCode)
            : $this->form;

        $config = $this->makeConfig($configDefinition);
        if ($this->isRelation) {
            $model = $this->getValueFromIndex($index);
            if ($model) {
                $config->model = $model;
            } else {
                $config->model = new ($this->model->getRelationDefinition($this->valueFrom)[0]);
            }
        } else {
            $config->model = $this->model;
        }
        $config->data = $this->getValueFromIndex($index);
        $config->alias = $this->alias . 'Form' . $index;
        $config->arrayName = $this->getFieldName().'['.$index.']';
        $config->isNested = true;
        if (self::$onAddItemCalled || $this->minItems > 0) {
            $config->enableDefaults = true;
        }

        $widget = $this->makeWidget('Backend\Widgets\Form', $config);
        $widget->previewMode = $this->previewMode;
        $widget->bindToController();

        $this->indexMeta[$index] = [
            'groupCode' => $groupCode
        ];

        return $this->formWidgets[$index] = $widget;
    }

    protected function getValueFromIndex($index)
    {
        // Only attempt to load relation data if we're not adding a new record
        if (!self::$onAddItemCalled && $this->isRelation) {
            $this->loadRelatedData();

            if (isset($this->relatedData[$index])) {
                return $this->relatedData[$index];
            }

            return [];
        }

        return parent::getValueFromIndex($index);
    }

    protected function loadRelatedData()
    {
        if (!isset($this->relatedData)) {
            if ($this->isRelation) {
                $this->relatedData = $this->model->{$this->valueFrom};
            } else {
                $this->relatedData = collect([]);
            }
        }
    }
}
