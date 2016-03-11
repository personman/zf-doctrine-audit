<?php
namespace ZF\Doctrine\Audit\View\Helper ;

use Zend\Http\Request;
use Zend\View\Helper\AbstractHelper;
use ZF\Doctrine\Audit\Entity\Persistence;

class DateTimeFormatter extends AbstractHelper implements
    Persistence\AuditOptionsAwareInterface
{
    use Persistence\AuditOptionsAwareTrait;

    public function __invoke(\DateTime $datetime)
    {
        return $datetime->format($this->getAuditOptions()['datetime_format']);
    }
}
