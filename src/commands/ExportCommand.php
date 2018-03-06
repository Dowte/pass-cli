<?php

namespace Dowte\Password\commands;


use Dowte\Password\forms\PasswordForm;
use Dowte\Password\pass\PassSecret;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class ExportCommand extends Command
{
    const PASSWORD_YAML_DIR = __DIR__ . '/../../';

    protected function configure()
    {
        $this->setName('export')
            ->setDescription('Export your password library.')
            ->setHelp('This command help you to export your password library as a yaml file, you could use the file to import on other machine.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $user = $this->validPassword();
        $passwords = PasswordForm::pass()->findModels(['name', 'password', 'description'], ['user_id' => $user['id']]);
        foreach ($passwords as &$password) {
            $password['name'] = PassSecret::decryptedData($password['name']);
            $password['password'] = PassSecret::decryptedData($password['password']);
        }
        file_put_contents(self::PASSWORD_YAML_DIR . 'password-' . time() . '.yaml', Yaml::dump([$passwords]));
    }
}