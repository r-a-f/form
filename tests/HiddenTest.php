<?php

use AdamWathan\Form\Elements\Hidden;

class HiddenTest extends \PHPUnit\Framework\TestCase
{
    use InputContractTest;

    protected function newTestSubjectInstance($name)
    {
        return new Hidden($name);
    }

    protected function getTestSubjectType()
    {
        return 'hidden';
    }
}
