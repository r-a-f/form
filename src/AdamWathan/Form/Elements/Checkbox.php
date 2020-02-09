<?php

namespace AdamWathan\Form\Elements;

class Checkbox extends Input
{
    protected $attributes = [
        'type' => 'checkbox',
    ];

    protected $checked;

    protected $oldValue;

    public function __construct($name, $value = 1)
    {
        parent::__construct($name);
        $this->setValue($value);
    }

    public function setOldValue($oldValue)
    {
        $this->oldValue = $oldValue;
    }

    public function unsetOldValue()
    {
        $this->oldValue = null;
    }

    /**
     * We need to pass $do_chceck because specification on checkbox/radio populate
     *
     * @param bool|true $do_check
     *
     * @return $this
     */
    public function defaultToChecked($do_check = true)
    {
        if($do_check) {

            if (! isset($this->checked) && $this->oldValue === null) {
                $this->check();
            }
        }

        return $this;
    }

    public function defaultToUnchecked()
    {
        if (! isset($this->checked) && $this->oldValue === null) {
            $this->uncheck();
        }

        return $this;
    }

    public function defaultCheckedState($state)
    {
        $state ? $this->defaultToChecked() : $this->defaultToUnchecked();

        return $this;
    }

    public function check()
    {
        $this->unsetOldValue();
        $this->setChecked(true);

        return $this;
    }

    public function uncheck()
    {
        $this->unsetOldValue();
        $this->setChecked(false);

        return $this;
    }

    protected function setChecked($checked = true)
    {
        $this->checked = $checked;
        $this->removeAttribute('checked');

        if ($checked) {
            $this->setAttribute('checked', 'checked');
        }
    }

    protected function checkBinding()
    {
        $currentValue = (string) $this->getAttribute('value');

        $oldValue = $this->oldValue;
        $oldValue = is_array($oldValue) ? $oldValue : [$oldValue];
        $oldValue = array_map('strval', $oldValue);

        if (in_array($currentValue, $oldValue)) {
            return $this->check();
        }
    }

    public function valFinal()
    {
        if($this->oldValue || $this->checked) {
            return $this->getAttribute('value');
        }

        return false;
    }

    public function render()
    {
        $this->checkBinding();

        //$template = '<label class="form-check-label"><input name="" class="form-check-input" type="checkbox" value="">xx</label>';
        //return $template;
        return parent::render();
    }
}