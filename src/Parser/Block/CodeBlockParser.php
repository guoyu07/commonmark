<?php

namespace FluxBB\Markdown\Parser\Block;

use FluxBB\Markdown\Common\Text;
use FluxBB\Markdown\Node\CodeBlock;
use FluxBB\Markdown\Parser\AbstractParser;

class CodeBlockParser extends AbstractParser
{

    /**
     * Parse the given content.
     *
     * Any newly created nodes should be pushed to the stack. Any remaining content should be passed to the next parser
     * in the chain.
     *
     * @param Text $content
     * @return void
     */
    public function parse(Text $content)
    {
        $content->handle(
            '{
                (?:\n\n|\A)
                (                      # $1 = the code block -- one or more lines, starting with at least four spaces
                  (?:
                    (?:[ ]{4})         # Lines must start with four spaces
                    .*\n+
                  )+
                )
                (?:(?=^[ ]{0,4}\S)|\Z) # Lookahead for non-space at line-start, or end of doc
            }mx',
            function (Text $whole, Text $code) {
                // TODO: Prepare contents

                // Remove indent
                $code->replace('/^(\t|[ ]{1,4})/m', '');

                $this->stack->acceptCodeBlock(new CodeBlock($code));
            },
            function(Text $part) {
                $this->next->parse($part);
            }
        );
    }

}
