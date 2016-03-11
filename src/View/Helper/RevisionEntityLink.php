<?php

namespace ZF\Doctrine\Audit\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\View\Model\ViewModel;

final class RevisionEntityLink extends AbstractHelper
{
    public function __invoke($revisionEntity)
    {
        $model = new ViewModel();
        $model->setTemplate('zf-doctrine-audit/helper/revision-entity-link.phtml');
        $model->setVariable('revisionEntity', $revisionEntity);
        $model->setOption('has_parent', true);

        return $this->getView()->render($model);
    }
}
