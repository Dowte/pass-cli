<?php

namespace Dowte\Password\pass;

use Symfony\Component\Console\Output\ConsoleOutput;

class SymfonyConsoleOutput extends ConsoleOutput
{
    public function writePaste($messages, $description = '复制剪切板成功')
    {
        $messages = Password::decryptedData($messages);
        $this->copy($messages);
        parent::write($description, true, self::OUTPUT_NORMAL);
    }

    protected function copy($messages)
    {
        shell_exec('echo "'. $messages. '" | pbcopy');
    }
}