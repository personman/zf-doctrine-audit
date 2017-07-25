<?php

namespace ZF\Doctrine\Audit\Query\Mysql;

use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\QueryException;

class CloseRevisionAudit
{
    private $userId;
    private $userName;
    private $userEmail;
    private $comment;

    public function parse(Parser $parser)
    {
        $lexer = $parser->getLexer();
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $parser->match(Lexer::T_IDENTIFIER);
        $this->userId = $lexer->token['value'];
        $parser->match(Lexer::T_COMMA);
        $parser->match(Lexer::T_IDENTIFIER);
        $this->userName = $lexer->token['value'];
        $parser->match(Lexer::T_COMMA);
        $parser->match(Lexer::T_IDENTIFIER);
        $this->userEmail = $lexer->token['value'];
        $parser->match(Lexer::T_COMMA);
        $parser->match(Lexer::T_IDENTIFIER);
        $this->comment = $lexer->token['value'];
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker)
    {
           // throw QueryException::semanticalError('EXTRACT() does not support unit "' . $unit . '".');

        return "close_revision_audit("
            . $this->userId
            . ", '"
            . addslashes($this->userName)
            . "', '"
            . addslashes($this->userEmail)
            . "', '"
            . addslashes($this->comment)
            . "')"
            ;
    }
}