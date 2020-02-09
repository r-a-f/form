<?php

namespace AdamWathan\Form\Elements;

class CheckboxMultiple extends FormControl
{
    protected $options;

    protected $selected;

    public function __construct($name, $options = [])
    {
        parent::__construct($name);
        $this->setName($name);
        $this->setOptions($options);

        $this->multiple();
    }

    public function select($option)
    {
        $this->selected = $option;

        return $this;
    }

    protected function setOptions($options)
    {
        $this->options = $options;
    }

    public function options($options)
    {
        $this->setOptions($options);

        return $this;
    }

    public function render()
    {
        return $this->renderOptions();
    }

    protected function renderOptions()
    {
        list($values, $labels) = $this->splitKeysAndValues($this->options);

        $tags = array_map(function ($value, $label) {
            return $this->renderOption($value, $label);
        }, $values, $labels);

        return implode($tags);
    }


    protected function renderOption($value, $label)
    {
        //$attrid = $this->getAttribute('id') . '___' . rand(100,99999);
        $attrid = $this->getAttribute('id') . '___' . md5($label);

        $checkbox_template = '<div class="custom-control custom-checkbox"><input name="%s" value="%s" type="checkbox" id="'.$attrid.'" class="custom-control-input" %s><label class="custom-control-label" for="'.$attrid.'">%s</label></div>';

        //$checkbox_template = '<label class="form_input_group"><input type="checkbox" name="%s value="%s"%s>%s</label><br>'; // oryginal
        //$checkbox_template = '<div class="custom-control custom-checkbox"><label class="custom-control custom-checkbox"><input name="%s" value="%s" type="checkbox" class="custom-control-input" %s><span class="custom-control-label">%s</span></label></div><br>';

        return vsprintf($checkbox_template, [
            $this->getAttribute('name'),
            $this->escape($value),
            $this->isSelected($value) ? ' checked="checked"' : '',
            $this->escape($label),
        ]);
    }

    protected function isSelected($value)
    {
        return in_array($value, (array) $this->selected);
    }

    public function addOption($value, $label)
    {
        $this->options[$value] = $label;

        return $this;
    }

    public function defaultValue($value)
    {
        if (isset($this->selected)) {
            return $this;
        }

        $this->select($value);

        return $this;
    }

    public function multiple()
    {
        $name = $this->attributes['name'];
        if (substr($name, -2) !== '[]') {
            $name .= '[]';
        }

        $this->setName($name);

        return $this;
    }

    public function valFinal()
    {
        return $this->selected;
    }
}
