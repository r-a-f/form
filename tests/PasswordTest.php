<?php

use AdamWathan\Form\Elements\Password;

class PasswordTest extends \PHPUnit\Framework\TestCase
{
    use TextSubclassContractTest;

    protected function newTestSubjectInstance($name)
    {
        return new Password($name);
    }

    protected function getTestSubjectType()
    {
        return 'password';
    }
}
