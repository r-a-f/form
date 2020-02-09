<?php

namespace AdamWathan\Form\Elements;

abstract class Element
{
    protected $attributes, $rules, $filters, $filter_view = [];

    public function setRules($rules = [])
    {
        $this->rules = array_merge((array)$this->rules, (array)$rules);
        return $this;
    }

//    public function unsetRule($rule_name)
//    {
//        dump($this->rules);die();
//        unset($this->rules[$rule_name]);
//        return $this;
//    }

    public function getRules()
    {
        return $this->rules;
    }

    public function setFilters($filters = [])
    {
        $this->filters = $filters;
        return $this;
    }

    public function setViewFilters($filters = [])
    {
        $this->filter_view = $filters;
        return $this;
    }

    public function getFilters()
    {
        return $this->filters;
    }

    public function getViewFilters()
    {
        return $this->filter_view;
    }

    protected function setAttribute($attribute, $value = null)
    {
        if ($value === null) {
            return;
        }

        $this->attributes[$attribute] = $value;
    }

    protected function removeAttribute($attribute)
    {
        unset($this->attributes[$attribute]);
    }

    public function getAttribute($attribute)
    {
        return $this->attributes[$attribute] ?? '';
    }

    public function val()
    {
        return $this->getAttribute('value');
    }

    public function valFinal()
    {
       return $this->val();
    }

    public function data($attribute, $value = null)
    {
        if (is_array($attribute)) {
            foreach ($attribute as $key => $val) {
                $this->setAttribute('data-'.$key, $val);
            }
        } else {
            $this->setAttribute('data-'.$attribute, $value);
        }

        return $this;
    }

    public function attribute($attribute, $value)
    {
        $this->setAttribute($attribute, $value);

        return $this;
    }

    public function clear($attribute)
    {
        if (! isset($this->attributes[$attribute])) {
            return $this;
        }

        $this->removeAttribute($attribute);

        return $this;
    }

    public function addClass($class)
    {
        if (isset($this->attributes['class'])) {
            $class = $this->attributes['class'] . ' ' . $class;
        }

        $this->setAttribute('class', $class);

        return $this;
    }


    public function removeClass($class)
    {
        if (! isset($this->attributes['class'])) {
            return $this;
        }

        $class = trim(str_replace($class, '', $this->attributes['class']));
        if ($class == '') {
            $this->removeAttribute('class');
            return $this;
        }

        $this->setAttribute('class', $class);

        return $this;
    }

    public function id($id)
    {
        $this->setId($id);

        return $this;
    }

    protected function setId($id)
    {
        $this->setAttribute('id', $id);
    }

    abstract public function render();

    public function __toString()
    {
        return $this->render();
    }

    protected function renderAttributes()
    {
        list($attributes, $values) = $this->splitKeysAndValues($this->attributes);

        return implode(array_map(function ($attribute, $value) {
            return sprintf(' %s="%s"', $attribute, $this->escape($value));
        }, $attributes, $values));
    }

    protected function splitKeysAndValues($array)
    {
        // Disgusting crap because people might have passed a collection
        $keys = [];
        $values = [];

        foreach ($array as $key => $value) {
            $keys[] = $key;
            $values[] = $value;
        }

        return [$keys, $values];
    }

    protected function setBooleanAttribute($attribute, $value)
    {
        if ($value) {
            $this->setAttribute($attribute, $attribute);
        } else {
            $this->removeAttribute($attribute);
        }
    }

    protected function escape($value)
    {
        return htmlentities($value, ENT_QUOTES, 'UTF-8');
    }

    public function __call($method, $params)
    {
        $params = count($params) ? $params : [$method];
        $params = array_merge([$method], $params);
        call_user_func_array([$this, 'attribute'], $params);

        return $this;
    }
}
