<?php

namespace AdamWathan\Form\Elements;

class RadioButton extends Checkbox
{
    protected $options;

    protected $checked;

    public function __construct($name, $options = [])
    {
        parent::__construct($name);

        $this->setName($name);
        $this->setOptions($options);

    }

    public function select($option)
    {
        $this->checked = $option;

        return $this;
    }

    protected function setOptions($options)
    {
        $this->options = $options;
    }

    public function setOldValue($oldValue)
    {
        $this->oldValue = $oldValue;
    }

    public function unsetOldValue()
    {
        $this->oldValue = null;
    }

    public function options($options)
    {
        $this->setOptions($options);

        return $this;
    }

//    public function check()
//    {
//        $this->unsetOldValue();
//        $this->setChecked(true);
//
//        return $this;
//    }
//
//    public function uncheck()
//    {
//        $this->unsetOldValue();
//        $this->setChecked(false);
//
//        return $this;
//    }

//    protected function checkBinding()
//    {
//        //$currentValue = (string) $this->getAttribute('value');
//
//        $oldValue = $this->oldValue;
//        dump($oldValue);
//
//        if ($currentValue == $oldValue) {
//            return $this->check();
//        }
//    }

    public function render()
    {
        //$this->checkBinding();

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
        //@todo <br> from options
        return vsprintf('<label class="form_input_group"><input type="radio" name="%s" value="%s"%s>&nbsp; %s</label><br>', [
            $this->getAttribute('name'),
            $this->escape($value),
            $this->isSelected($value) ? ' checked="checked"' : '',
            $this->escape($label),
        ]);
    }

    protected function isSelected($value)
    {
        if(isset($this->oldValue) AND $this->oldValue == $value) {
            return true;
        } elseif($this->checked == $value) {
            return true;
        }

        return false;

        //echo $this->selected;
        //return ($this->oldValue == $value OR $this->checked == $value);
//        if($this->oldValue == $value) {
//            return true;
//        } elseif($this->checked == $value) {
//            //return true;
//        }
//        return false;

    }

    public function addOption($value, $label)
    {
        $this->options[$value] = $label;

        return $this;
    }

    public function defaultValue($value)
    {
        if (isset($this->checked)) {
            return $this;
        }

        $this->select($value);

        return $this;
    }

    public function valFinal()
    {
        if(isset($this->oldValue)) {
            return $this->oldValue;
        } elseif($this->checked) {
            return $this->checked;
        }

        return false;
    }


}
