<?php

namespace FluxBB\CommonMark\Console;

use FluxBB\CommonMark\Console\Command\CommonMarkCommand;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Run Ciconia as a single command application.
 *
 * @author Kazuyuki Hayashi <hayashi@valnur.net>
 */
class Application extends BaseApplication
{

    /**
     * Overridden so that the application doesn't expect the command
     * name to be the first argument.
     */
    public function getDefinition()
    {
        $inputDefinition = parent::getDefinition();
        // clear out the normal first argument, which is the command name
        $inputDefinition->setArguments();

        return $inputDefinition;
    }

    /**
     * {@inheritdoc}
     */
    protected function getCommandName(InputInterface $input)
    {
        return 'cm';
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultCommands()
    {
        return array_merge(parent::getDefaultCommands(), [
            new CommonMarkCommand()
        ]);
    }

}
