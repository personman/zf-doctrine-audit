<?php

namespace ZF\Doctrine\Audit\Service;

class RevisionComment
{
    protected $comment;

    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    public function getComment()
    {
        return $this->comment;
    }
}
