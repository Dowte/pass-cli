<?php

namespace Dowte\Password\commands;

use Dowte\Password\forms\UserForm;
use Dowte\Password\pass\PassSecret;
use Dowte\Password\pass\Password;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
use Stecman\Component\Symfony\Console\BashCompletion\Completion\CompletionAwareInterface;

abstract class Command extends \Symfony\Component\Console\Command\Command implements CompletionAwareInterface
{
    const GET_ARGUMENT = 'getArgument';

    const GET_OPTION = 'getOption';

    /**
     * @var SymfonyStyle
     */
    protected $_io;

    /**
     * @var $_input InputInterface
     */
    protected $_input;

    /**
     * @var $_output OutputInterface
     */
    protected $_output;

    public function completeArgumentValues($argumentName, CompletionContext $context)
    {
        return $this->getArgumentValues($argumentName, $context);
    }

    public function completeOptionValues($optionName, CompletionContext $context)
    {
        return $this->getOptionValues($optionName, $context);
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->_input = $input;
        $this->_output = $output;
        $this->_io = new SymfonyStyle($input, $output);
    }

    protected function getOptionValues($optionName, CompletionContext $context)
    {
        if (method_exists($this, self::GET_OPTION . ucfirst($optionName))) {
            return call_user_func([$this, self::GET_OPTION . ucfirst($optionName)], $context);
        }
        return [];
    }

    protected function getArgumentValues($argumentName, CompletionContext $context)
    {
        if (method_exists($this, self::GET_ARGUMENT . ucfirst($argumentName))) {
            return call_user_func([$this, self::GET_ARGUMENT . ucfirst($argumentName)], $context);
        }
        return [];
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output); // TODO: Change the autogenerated stub
    }

    /**
     * @return SymfonyStyle
     */
    public function getIO()
    {
        return $this->_io;
    }

    protected function encryptOption($name)
    {
        $messages = $this->_input->getOption($name);
        return $messages ? PassSecret::encryptData($messages) : null;
    }

    protected function encryptAsk(QuestionHelper $helper, Question $question)
    {
        $messages = $helper->ask($this->_input, $this->_output, $question);
        return PassSecret::encryptData($messages);
    }

    protected function validPassword($password = '')
    {
        if (! $password) {
            $helper = $this->getHelper('question');
            $question = new Question('What is the database password?');
            $question->setHidden(true);
            $question->setHiddenFallback(false);
            $password = $this->encryptAsk($helper, $question);
        }
        $user = UserForm::user()->findUser(Password::getUser(), $password);
        if (! $user) {
            Password::error('Please check the password is right!');
        } else {
            return $user;
        }
    }
}