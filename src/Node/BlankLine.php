<?php

namespace FluxBB\Markdown\Node;

use FluxBB\Markdown\Common\Text;

class BlankLine extends LeafBlock implements NodeAcceptorInterface
{

    /**
     * @var Text
     */
    protected $content;


    public function __construct(Text $content)
    {
        $this->content = $content;
    }

    /**
     * @return Text
     */
    public function getContent()
    {
        return $this->content->copy();
    }

}
