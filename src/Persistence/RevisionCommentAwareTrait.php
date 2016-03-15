<?php

namespace ZF\Doctrine\Audit\Persistence;

use ZF\Doctrine\Audit\Service\RevisionComment;

trait RevisionCommentAwareTrait
{
    protected $revisionComment;

    public function setRevisionComment(RevisionComment $revisionComment)
    {
        $this->revisionComment = $revisionComment;

        return $this;
    }

    public function getRevisionComment(): RevisionComment
    {
        return $this->revisionComment;
    }
}
