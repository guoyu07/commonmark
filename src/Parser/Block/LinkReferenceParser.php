<?php

namespace FluxBB\CommonMark\Parser\Block;

use FluxBB\CommonMark\Common\Collection;
use FluxBB\CommonMark\Common\Text;
use FluxBB\CommonMark\Node\Container;
use FluxBB\CommonMark\Parser\AbstractBlockParser;

class LinkReferenceParser extends AbstractBlockParser
{

    /**
     * @var Collection
     */
    protected $links = [];

    /**
     * @var Collection
     */
    protected $titles = [];


    public function __construct(Collection $links, Collection $titles)
    {
        $this->links = $links;
        $this->titles = $titles;
    }

    /**
     * Parse the given content.
     *
     * Any newly created nodes should be pushed to the stack. Any remaining content should be passed to the next parser
     * in the chain.
     *
     * @param Text $content
     * @param Container $target
     * @return void
     */
    public function parseBlock(Text $content, Container $target)
    {
        $content->handle(
            '{
                ^
                (?:                 # Ensure blank line before (or beginning of subject)
                    (?<=\n\n)|
                    (?<=\A\n)|
                    (?<=\A)
                )
                [ ]{0,3}\[(.+)\]:  # id = $1
                  [ \t]*
                  \n?               # maybe *one* newline
                  [ \t]*
                (?|                 # url = $2
                  ([^\s<>]+)            # either without spaces
                  |
                  <([^<>\\n]*)>         # or enclosed in angle brackets
                )
                (?:
                  [ \t]*
                  \n?               # maybe one newline
                  [ \t]*
                    (?<=\s)         # lookbehind for whitespace
                    ["\'(]
                    (.+?)           # title = $3
                    ["\')]
                    [ \t]*
                )?  # title is optional
                (?:\n+|\Z)
            }xm',
            function (Text $whole, Text $id, Text $url, Text $title = null) {
                $id = $id->lower()->getString();

                // Throw away duplicate reference definitions
                if ( ! $this->links->exists($id)) {
                    $url->decodeEntities();

                    // Replace special characters in the URL
                    $url->encodeUrl();

                    $this->links->set($id, $url);

                    if ($title) {
                        $title->decodeEntities();
                        $this->titles->set($id, $title);
                    }
                }
            },
            function (Text $part) use ($target) {
                $this->next->parseBlock($part, $target);
            }
        );
    }

}
