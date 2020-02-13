<?php

use AdamWathan\Form\Elements\File;

class FileTest extends \PHPUnit\Framework\TestCase
{
    use InputContractTest;

    protected function newTestSubjectInstance($name)
    {
        return new File($name);
    }

    protected function getTestSubjectType()
    {
        return 'file';
    }
}
