<?php

namespace ZF\Doctrine\Audit\Persistence;

use ZF\Doctrine\Audit\RevisionComment;

/**
 * This is a convenience class for implementation in a project
 * and not used internally to this module
 */
trait RevisionCommentAwareTrait
{
    protected $revisionComment;

    // @codeCoverageIgnoreStart
    public function setRevisionComment(RevisionComment $revisionComment)
    {
        $this->revisionComment = $revisionComment;

        return $this;
    }

    public function getRevisionComment(): RevisionComment
    {
        return $this->revisionComment;
    }
    // @codeCoverageIgnoreEnd
}
