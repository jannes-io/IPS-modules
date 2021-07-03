<?php

namespace IPS\thirdinf\modules\front\servers;

use IPS\Dispatcher\Controller;

/**
 * index
 *
 * @package IPS\thirdinf\modules\front\servers
 */
class _index extends Controller
{
    public function execute()
    {
        echo 'hello world';
        parent::execute();
    }

    public function manage()
    {
        echo 'hello world';
    }

    public function otherMethod()
    {
        echo 'hello world';
    }
}
