<?php

namespace ZF\Doctrine\Audit\Persistence;

use ZF\Doctrine\Audit\RevisionComment;

interface RevisionCommentAwareInterface
{
    public function setRevisionComment(RevisionComment $revisionComment);
    public function getRevisionComment(): RevisionComment;
}
