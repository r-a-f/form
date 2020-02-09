<?php

namespace AdamWathan\Form;

interface ValidatorInterface
{
    public function setLang($lang);

    public function setNamings($namings);

    public function validate($values, $validation_rules);

    public function getErrors($as_string = false, $lang = null);

    public function isSuccess();


}
