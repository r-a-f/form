<?php

namespace AdamWathan\Form;

use AdamWathan\Form\Binding\BoundData;
use AdamWathan\Form\Elements\Button;
use AdamWathan\Form\Elements\Checkbox;
use AdamWathan\Form\Elements\CheckboxMultiple;
use AdamWathan\Form\Elements\Date;
use AdamWathan\Form\Elements\DateTimeLocal;
use AdamWathan\Form\Elements\Email;
use AdamWathan\Form\Elements\File;
use AdamWathan\Form\Elements\FormOpen;
use AdamWathan\Form\Elements\Hidden;
use AdamWathan\Form\Elements\Label;
use AdamWathan\Form\Elements\Number;
use AdamWathan\Form\Elements\Password;
use AdamWathan\Form\Elements\RadioButton;
use AdamWathan\Form\Elements\Select;
use AdamWathan\Form\Elements\Text;
use AdamWathan\Form\Elements\TextArea;

class FormBuilder
{
    private const REQUIRED_LABEL_STRING = ' *';
    private const CLASS_ERROR           = 'is-invalid';

    //@todo move this to external template/setting
    private const ERROR_FORMAT_MESSAGE  = '<span class="help-block invalid-feedback">:message</span>';

    protected $oldInput;
    protected $auto_render_elements = [];
    //protected $errorStore;

    protected $errors;
    protected $validator;
    protected $filter;

    protected $csrfToken;
    //protected $control_sum;
    //protected $reserved_fields = ['___control_sum'];

    protected $boundData;

    protected $data_elements;
    protected $data_labels;

    protected $lang;

    public function __construct(ValidatorInterface $validator = null, FilterInterface $filter = null)
    {
        $this->filter    = $filter;
        $this->validator = $validator;
    }

    public function getValidator()
    {
        return $this->validator;
    }
    public function getFilter()
    {
        return $this->filter;
    }

    public function add($type, $name, $label = '')
    {
        $type                       = strtolower($type);
        $this->data_elements[$name] = $this->$type($name);
        if ($type !== 'label' AND $type !== 'radio') {
            $this->data_elements[$name]->id($this->idname . '___' . $name);
        }

        $this->data_labels[$name] = $label;

        return $this->data_elements[$name];
    }

    public function remove($name)
    {
        unset($this->data_elements[$name]);
    }

    public function get($name)
    {
        if (isset($this->data_elements[$name])) {

            return $this->data_elements[$name];
        }

        throw new \RuntimeException("Form element '{$name}' not defined");
    }

    public function val($name)
    {
        $form = $this->get($name);
        if ($form) {
            return $form->val();
        }

        throw new \RuntimeException("Unknow form element '{$name}'");
    }

    public function isValid()
    {
        $this->runFilters();
        //$control_old_sum = '';
        //dump($this->get('_control_sum'));die();

        $validation_rules = $values = [];
        //dump($this->data_elements);DIE();
        foreach ($this->data_elements as $k => $v) {
            $rules = $v->getRules();
            if ($rules) {
                $validation_rules[$k] = $rules;
            }

            $values[$k] = $v->valFinal();
        }

        //        $current_control_sum = $this->calculateControlSum();
        //        $last_control_sum = isset($this->data_elements['___control_sum']) ? $this->data_elements['___control_sum']->valFinal() : '???';
        //
        //        if($last_control_sum) {
        //            $values['___control_sum'] = $current_control_sum;
        //            $validation_rules['___control_sum'] = ['equals('.$last_control_sum.')'];
        //        }

        $this->validator->setNamings($this->data_labels);
        $this->validator->validate($values, $validation_rules);

        // add error html code to this field
        $errors = array_keys($this->validator->getErrors());

        foreach ($errors as $err) {
            //if($err != '_control_sum'){
            $this->get($err)->addClass(self::CLASS_ERROR);
            //}
        }

        return $this->validator->isSuccess();
    }


    /**
     * Filters run only on `value` attribute
     */
    public function runFilters()
    {
        foreach ($this->data_elements as $k => $v) {
            $filters = $v->getFilters();
            if ($filters) {
                $val = $v->val();

                foreach ($filters as $filter => $args) {

                    if (is_string($filter)) {
                        // ['function' => ['arg1', 'arg2', ..]]
                        $this->data_elements[$k]->attribute('value', $this->runFilter($filter, $val, $args));
                    }
                    else {
                        // ['function1', 'function2']
                        $this->data_elements[$k]->attribute('value', $this->runFilter($args, $val));
                    }

                }
            }
        }
    }

    //@todo make arguments like in Validator '(x)'
    protected function runFilter($filter_name, $val, $args = [])
    {
        if($this->filter) {
            return (string) $this->filter->_($filter_name, $val, $args);
        }

        return $val;
    }

    /**
     * @param null|string $field
     *
     * @return array|string
     */
    public function getData($field = null)
    {
        if ($field) {

            $data = isset($this->data_elements[$field]) ? $this->data_elements[$field]->valFinal() : '';

        }
        else {
            $data = [];
            foreach ($this->data_elements as $k => $v) {
                //if(!in_array($k, [$this->reserved_fields]))
                $data[$k] = $v->valFinal();
            }

        }

        return $data;
    }

    public function populate(array $data, array $data_map = [])
    {
        //        foreach ($data as $k=>$v) {
        //            if(isset($this->data_elements[$k])) {
        //
        //                $class = (new \ReflectionClass($v))->getShortName();
        //                echo $class.';';die();
        //
        //                $this->data_elements[$k]->value($v);
        //            }
        //        }

        $this->populateDataMap = $data_map;

        if ($data) {

            if ($data_map) {
                $data_populate = $data;
                foreach ($data_map as $k => $v) {
                    $data_populate[$v] = $data[$k] ?? null; // this allow to use 'array.subarray'
                }

                //                dump($data);
                //                dump($data_populate);
                //                dump($data + $data_populate);

                $this->oldInput = ($data + $data_populate);
            }
            else {
                $this->oldInput = $data;
            }

            //$this->control_sum = arrget($data, '_control_sum');
        }
    }

    public function getValueFor($name, $run_view_filter = false)
    {
        if ($this->oldInput) {

            $return_value = $this->getOldInput($name);

            //            dump($this->oldInput);
            //            dump($this->populateDataMap);
            //            dump($return_value);
            //            dump($name);die();

            if ($run_view_filter) {
                //dump(($name));
                //dump($this->get($name));
                //$return_value = $return_value . '+';
                //$return_value = $this->runFilter();
            }

            return $return_value;
        }

        //        if ($this->hasBoundData()) {
        //            return $this->getBoundValue($name, null);
        //        }

        return null;
    }

    protected function getOldInput($name)
    {
        if (isset($this->oldInput[$name])) {

            return $this->oldInput[$name];

        }

        return null;

    }

    //    protected function transformKey($key)
    //    {
    //       return str_replace(['.', '[]', '[', ']'], ['_', '', '.', ''], $key);
    //    }
    //    public function setOldInputProvider(OldInputInterface $oldInputProvider)
    //    {
    //        $this->oldInput = $oldInputProvider;
    //    }

    //    public function setErrorStore(ErrorStoreInterface $errorStore)
    //    {
    //        $this->errorStore = $errorStore;
    //    }

    public function setToken($token)
    {
        $this->csrfToken = $token;
    }


    public function action($action)
    {
        $this->action = $action;
    }

    //@todo feature

    //    private function calculateControlSum()
    //    {
    //        $sum = '';
    //        if(is_array($this->data_elements)) {
    //            foreach ($this->data_elements as $k => $v) {
    //
    //                // obly by fields names
    //                if($k != '___control_sum'){
    //                    $sum .= $k;
    //                }
    //
    //                //                $tmp = $this->data_elements[$k]->valFinal();
    //                //                if (is_array($tmp)) {
    //                //                    $tmp_val = array_sum($tmp);
    //                //                } else {
    //                //                    $tmp_val = $tmp;
    //                //                }
    //                //                $sum .= $tmp_val;
    //
    //            }
    //        }
    //
    //        return $sum ? md5($sum) : '';
    //    }

    //    /**
    //     * Run after defined all elements
    //     *
    //     */
    //    public function setControlSum()
    //    {
    //        $sum = $this->calculateControlSum();
    //        //$this->control_sum = $sum;
    //
    //        $token_el = $this->add('hidden', '___control_sum');
    //        $token_el->value($sum);
    //
    //        $this->addAutoRender($token_el);
    //    }

    private function addAutoRender($el)
    {
        $this->auto_render_elements[] = $el;
    }

    public function open()
    {
        $open = new FormOpen;
        $open->action($this->action);

        if ($this->hasToken()) {
            $open->token($this->csrfToken);
        }

        $open->autoRenderElements($this->auto_render_elements);

        return $open;
    }

    protected function hasToken()
    {
        return (bool)$this->csrfToken;
    }

    //    protected function hasControlSum()
    //    {
    //        return (bool) $this->control_sum;
    //    }

    public function close()
    {
        $this->unbindData();

        return '</form>';
    }

    public function text($name)
    {
        $text = new Text($name);

        if (($value = $this->getValueFor($name, true)) !== null) {
            $text->value($value);
        }

        return $text;
    }

    public function number($name)
    {
        $text = new Number($name);

        if (($value = $this->getValueFor($name, true)) !== null) {
            $text->value($value);
        }

        return $text;
    }

    public function password($name)
    {
        $text = new Password($name);

        if (($value = $this->getValueFor($name)) !== null) {
            $text->value($value);
        }

        return $text;
    }

    public function date($name)
    {
        $date = new Date($name);

        if (($value = $this->getValueFor($name)) !== null) {
            $date->value($value);
        }

        return $date;
    }

    public function dateTimeLocal($name)
    {
        $date = new DateTimeLocal($name);

        if (($value = $this->getValueFor($name)) !== null) {
            $date->value($value);
        }

        return $date;
    }

    public function email($name)
    {
        $email = new Email($name);

        if (($value = $this->getValueFor($name)) !== null) {
            $email->value($value);
        }

        return $email;
    }

    public function hidden($name)
    {
        $hidden = new Hidden($name);

        if (($value = $this->getValueFor($name)) !== null) {
            $hidden->value($value);
        }

        return $hidden;
    }

    public function textarea($name)
    {
        $textarea = new TextArea($name);

        if (($value = $this->getValueFor($name)) !== null) {
            $textarea->value($value);
        }

        return $textarea;
    }

    public function checkbox($name, $value = 1)
    {
        $checkbox = new Checkbox($name, $value);

        $oldValue = $this->getValueFor($name);
        $checkbox->setOldValue($oldValue);

        return $checkbox;
    }

    public function radio($name, $value = null)
    {
        $radio = new RadioButton($name, $value);

        $oldValue = $this->getValueFor($name);
        $radio->setOldValue($oldValue);

        return $radio;
    }

    public function checkbox_multiple($name, $options = [])
    {
        $select = new CheckboxMultiple($name, $options);

        $selected = $this->getValueFor($name);
        $select->select($selected);

        return $select;
    }

    public function button($value, $name = null)
    {
        return new Button($value, $name);
    }

    public function reset($value = 'Reset')
    {
        $reset = new Button($value);
        $reset->attribute('type', 'reset');

        return $reset;
    }

    public function submit($value = 'Submit')
    {
        $submit = new Button($value);
        $submit->attribute('type', 'submit');

        return $submit;
    }

    public function select($name, $options = [])
    {
        $select = new Select($name, $options);

        $selected = $this->getValueFor($name);
        $select->select($selected);

        return $select;
    }

    public function label($label)
    {
        return new Label($label);
    }

    public function file($name)
    {
        return new File($name);
    }

    public function token()
    {
        $token = $this->hidden('_token');

        if (isset($this->csrfToken)) {
            $token->value($this->csrfToken);
        }

        return $token;
    }

    public function hasError($name, $rule_name = null)
    {
        if ($this->getValidator()) {
            return $this->getValidator()->has($name, $rule_name);
        }

        return false;
    }

    public function getError($name, $format = null)
    {
        $message = '';
        if (empty($format)) {
            $format = self::ERROR_FORMAT_MESSAGE;
        }
        if ($this->getValidator()) {
            $errors = $this->getValidator()->getErrors();

            // do not display empty error template (ERROR_FORMAT_MESSAGE)
            if (! $errors) {
                return '';
            }

            if (is_array($errors)) {
                $message = $errors[$name] ?? '';
            }
        }

        if ($format) {
            $message = str_replace(':message', $message, $format);
        }

        return $message;
    }

    public function bind($data)
    {
        $this->boundData = new BoundData($data);
    }

    //    protected function hasBoundData()
    //    {
    //        return isset($this->boundData);
    //    }
    //
    //    protected function getBoundValue($name, $default)
    //    {
    //        return $this->boundData->get($name, $default);
    //    }

    public function getLabel($name)
    {
        $label = isset($this->data_labels[$name]) ? $this->data_labels[$name] : '';

        $field = $this->get($name);

        if ($field) {
            if (in_array('required', (array)$field->getRules())) {
                $label .= self::REQUIRED_LABEL_STRING;
            }
        }
        else {
            $label = '';
        }

        return $label;
    }

    public function getId($name)
    {
        if (isset($this->data_elements[$name])) {
            return $this->data_elements[$name]->getAttribute('id');
        }

        throw new \RuntimeException("Unknow form element ID'{$name}'");
    }

    protected function unbindData()
    {
        $this->boundData = null;
    }

    public function __toString()
    {
        $string = $this->open();
        //ump($this->data_elements);
        foreach ($this->data_elements as $d) {
            $string .= (string)$d;
        }

        $string .= $this->close();

        return $string;
    }
}
