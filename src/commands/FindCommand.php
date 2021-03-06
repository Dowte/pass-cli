<?php
/**
 * Password - A command-line tool to help you manage your password
 *
 * @author  admin@dowte.com
 * @link    https://github.com/dowte/password
 * @license https://opensource.org/licenses/MIT
 */

namespace Dowte\Password\commands;

use Dowte\Password\forms\PasswordForm;
use Dowte\Password\pass\Password;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class FindCommand extends Command
{
    protected function configure()
    {
        $this->setName('find')
            ->setDescription('Get a password by keyword')
            ->setHelp('This command allows you to get a password...')
            ->addArgument('keyword', InputArgument::OPTIONAL, 'Get password by the password keyword.')
            ->addOption('list', 'a', InputOption::VALUE_NONE, 'Show passwords list.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('list')) {
            $lists = PasswordForm::pass()->getDecryptedKey("<fg=green>%-20s </>");
            if ($lists) {
                $output->writeln(trim($lists));
            } else {
                $this->_io->note('No password exists');
            }
            return;
        }

        $key = $input->getArgument('keyword');
        if ($loadPassword = $this->loadPassword()) {
            $user = $this->validPassword($loadPassword);
        } else {
            $user = $this->validPassword();
            $this->setPassword($user['password']);
        }

        while (empty($key)) {
            $helper = $this->getHelper('question');
            $question = new Question('Which password is you want to get:' . PHP_EOL);
            $key = Password::encryptPasswordKey($helper->ask($input, $output, $question));
        }
        $pass = PasswordForm::pass()->findPassword($user['id'], $key);
        if ($pass) {
            Password::toPasteDecode($user['password'], $pass['password'], $this->_io);
            $this->_io->comment('description : '.$pass['description']);
        } else {
            $this->_io->error('You provide password is wrong or keyword is not exist!');
        }
    }

    /**
     * get options of keyword
     * @param CompletionContext $context
     * @return array|string
     */
    protected function getArgumentKeyword(CompletionContext $context)
    {
        $keys = PasswordForm::pass()->getDecryptedKey();

        return $keys;
    }

    /**
     * create a readonly password file
     * @param $password
     */
    private function setPassword($password)
    {
        @unlink(TEMP_PASSWORD_FILE);
        file_put_contents(TEMP_PASSWORD_FILE, $password);
        chmod(TEMP_PASSWORD_FILE, '0400');
    }

    private function loadPassword()
    {
        if (file_exists(TEMP_PASSWORD_FILE)) {
            if (time() - filectime(TEMP_PASSWORD_FILE) > PASSWORD_IGNORE_TIME) {
                return false;
            }
            $password = file_get_contents(TEMP_PASSWORD_FILE);
            if ($this->validPassword($password)) {
                return $password;
            }
        }
        return false;
    }
}