<?php

namespace FluxBB\Markdown\Node;

class HorizontalRule extends Block implements NodeAcceptorInterface
{

    public function getType()
    {
        return 'horizontal_rule';
    }

    public function visit(NodeVisitorInterface $visitor)
    {
        $visitor->visitHorizontalRule($this);
    }

}
