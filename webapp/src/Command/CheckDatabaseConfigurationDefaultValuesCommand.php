<?php declare(strict_types=1);

namespace App\Command;

use App\Service\ConfigurationService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CheckDatabaseConfigurationDefaultValuesCommand extends Command
{
    protected static $defaultName = 'domjudge:db-config:check';
    protected static $defaultDescription = 'Check if the default values of the database configuration are valid';

    public function __construct(protected readonly ConfigurationService $config, string $name = null)
    {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style    = new SymfonyStyle($input, $output);
        $messages = [];
        foreach ($this->config->getConfigSpecification() as $specification) {
            $message = sprintf(
                'Configuration %s (in category %s) is of type %s but has wrong type for default_value (%s)',
                $specification['name'],
                $specification['category'],
                $specification['type'],
                json_encode($specification['default_value'], JSON_THROW_ON_ERROR)
            );
            switch ($specification['type']) {
                case 'bool':
                    if (!is_bool($specification['default_value'])) {
                        $messages[] = $message;
                    }
                    break;
                case 'int':
                    if (!is_int($specification['default_value'])) {
                        $messages[] = $message;
                    }
                    break;
                case 'string':
                    if (!is_string($specification['default_value'])) {
                        $messages[] = $message;
                    }
                    break;
                case 'array_val':
                    if (!(empty($specification['default_value']) || (
                            is_array($specification['default_value']) &&
                            is_int(key($specification['default_value']))))) {
                        $messages[] = $message;
                    }
                    break;
                case 'array_keyval':
                    if (!(empty($specification['default_value']) || (
                            is_array($specification['default_value']) &&
                            is_string(key($specification['default_value']))))) {
                        $messages[] = $message;
                    }
                    break;
            }
        }
        if (empty($messages)) {
            $style->success('All default values have the correct type');
        } else {
            $style->error('Some default values have the wrong type:');
            $style->listing($messages);
        }

        return Command::SUCCESS;
    }
}
