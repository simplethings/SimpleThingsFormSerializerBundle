<?php
/**
 * SimpleThings FormSerializerBundle
 *
 * LICENSE
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kontakt@beberlei.de so I can send you a copy immediately.
 */

namespace SimpleThings\FormSerializerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use SimpleThings\FormSerializerBundle\Serializer\JMSSerializerConverter;
use Metadata\MetadataFactory;

/**
 * Converter JMS Serializer Metadata into Form-Types for Serialization
 */
class ConvertJMSSerializerCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('simplethings:convert-jms-metadata')
            ->setDescription('Command helping with conversion of JMS Metadata to Form-Types.')
            ->addArgument('class', InputArgument::REQUIRED, 'Class Name to convert')
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $driver    = $this->getContainer()->get('jms_serializer.metadata_driver');
        $converter = new JMSSerializerConverter(new MetadataFactory($driver));

        $code = $converter->generateFormPhpCode($input->getArgument('class'));

        $output->writeln($code);
    }
}

