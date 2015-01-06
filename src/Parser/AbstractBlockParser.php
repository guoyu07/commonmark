<?php

namespace FluxBB\CommonMark\Parser;

use FluxBB\CommonMark\Common\Text;
use FluxBB\CommonMark\Node\NodeAcceptorInterface;

abstract class AbstractBlockParser implements BlockParserInterface
{

    /**
     * @var NodeAcceptorInterface
     */
    protected $stack;

    /**
     * @var BlockParserInterface
     */
    protected $next;

    /**
     * @var BlockParserInterface
     */
    protected $first;


    public function setNextParser(BlockParserInterface $next)
    {
        $this->next = $next;
    }

    public function setFirstParser(BlockParserInterface $first)
    {
        $this->first = $first;
    }

    public function setStack(NodeAcceptorInterface $stack)
    {
        $this->stack = $stack;
    }

    protected function splitBlocks(Text $text, $pattern, $resultHandler)
    {
        $text->handle(
            $pattern,
            $resultHandler,
            function (Text $part) {
                $this->next->parseBlock($part);
            }
        );
    }

}
